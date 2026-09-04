<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SubCustomer;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\BillingCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;
use SimpleXMLElement;

class ExcelImportController extends Controller
{
    private static $cachedRates = null;
    private static $cachedDateRates = [];
    /**
     * Import subscription, customer, invoice, and payment data from uploaded Excel (.xlsx) file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return back()->with('error', 'Failed to open the Excel file. Please ensure it is a valid .xlsx file.');
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $val) {
                    // Extract text (some nodes might have multiple formatted runs <r> or simple <t>)
                    if (isset($val->t)) {
                        $sharedStrings[] = (string) $val->t;
                    } elseif (isset($val->r)) {
                        $textRun = '';
                        foreach ($val->r as $run) {
                            $textRun .= (string) $run->t;
                        }
                        $sharedStrings[] = $textRun;
                    } else {
                        $sharedStrings[] = (string) $val;
                    }
                }
            }
        }

        // 2. Read sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            return back()->with('error', 'Could not locate worksheet data in the Excel file.');
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml) {
            $zip->close();
            return back()->with('error', 'Invalid XML format in worksheet.');
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $r = (string) $cell['r']; // Cell coordinate e.g., A1, B1
                $colLetter = preg_replace('/[0-9]/', '', $r);
                $t = (string) $cell['t']; // Type ('s' for shared string, 'inlineStr' for inline text, 'str' for formula string)
                $v = (string) $cell->v;

                if ($t === 's') {
                    $rowData[$colLetter] = $sharedStrings[(int)$v] ?? $v;
                } elseif ($t === 'inlineStr') {
                    $rowData[$colLetter] = isset($cell->is->t) ? (string) $cell->is->t : $v;
                } else {
                    $rowData[$colLetter] = $v;
                }
            }
            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        $zip->close();

        if (empty($rows)) {
            return back()->with('error', 'No data found in the Excel sheet.');
        }

        // 3. Detect Headers & Map Data
        $headerRowIndex = -1;
        $headers = [];

        foreach ($rows as $index => $row) {
            // Look for a row containing Business Name / Busines Name
            foreach ($row as $col => $val) {
                $cleanVal = strtolower(trim(str_replace('.', '', $val)));
                if ($cleanVal === 'busines name' || $cleanVal === 'business name' || $cleanVal === 'email') {
                    $headerRowIndex = $index;
                    break 2;
                }
            }
        }

        if ($headerRowIndex === -1) {
            return back()->with('error', 'Could not detect header row (must contain "Busines Name" or "Email" columns).');
        }

        // Map column letters to header labels
        foreach ($rows[$headerRowIndex] as $col => $val) {
            $headers[$col] = trim($val);
        }

        // Process data rows
        $successCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $rowData = $rows[$i];
                if (empty($rowData)) continue;

                // Map row data using header keys
                $mappedRow = [];
                foreach ($headers as $colLetter => $headerName) {
                    $mappedRow[$headerName] = $rowData[$colLetter] ?? null;
                }

                // Check required fields
                $businessName = trim($mappedRow['Business Name'] ?? $mappedRow['Busines Name'] ?? '');
                $email = trim($mappedRow['Email'] ?? '');

                if (!$businessName && !$email) {
                    continue; // Skip empty rows
                }


                // Extract values
                $phone = trim($mappedRow['Phone'] ?? '');
                $role = trim($mappedRow['Role'] ?? '');
                $status = strtolower(trim($mappedRow['Status'] ?? 'active'));
                $billingCycleInput = trim($mappedRow['subscrip.'] ?? 'Monthly');
                $country = trim($mappedRow['COUNTRY'] ?? 'Global Region');
                $purDateInput = trim($mappedRow['PUR DATE'] ?? '');
                $purAmtInput = trim($mappedRow['PUR AMT'] ?? '');
                $recAmtInput = trim($mappedRow['REC AMT'] ?? '');
                $billNo = trim($mappedRow['BILL NOS.'] ?? '');
                $mode = trim($mappedRow['MODE'] ?? 'Stripe');

                // Extract Sub-Customer values
                $subCustomerName = trim($mappedRow['Sub Customer'] ?? $mappedRow['Sub Customer Name'] ?? '');
                $subCustomerEmail = trim($mappedRow['Sub Customer Email'] ?? '');
                $subCustomerPhone = trim($mappedRow['Sub Customer Phone'] ?? '');
                $subCustomerAddress = trim($mappedRow['Sub Customer Address'] ?? '');
                $closedBy = trim($mappedRow['Closed by'] ?? $mappedRow['closed by'] ?? '');

                // Standardize dates
                $purDate = now()->format('Y-m-d');
                if ($purDateInput) {
                    if (is_numeric($purDateInput)) {
                        $timestamp = (int)(($purDateInput - 25569) * 86400);
                        $purDate = date('Y-m-d', $timestamp);
                    } else {
                        $parsedTime = strtotime($purDateInput);
                        if ($parsedTime !== false) {
                            $purDate = date('Y-m-d', $parsedTime);
                        }
                    }
                }

                // Standardize amounts and currencies
                $purParsed = $this->parseAmountAndCurrency($purAmtInput);
                $recParsed = $this->parseAmountAndCurrency($recAmtInput);

                $purAmt = $purParsed['amount'];
                $currency = $purParsed['currency'];

                // Fallback to COUNTRY column if no explicit currency is in PUR AMT
                if (!$purParsed['has_explicit_currency'] && $country) {
                    $countryLower = strtolower(trim($country));
                    $countryToCurrency = [
                        'india' => 'INR',
                        'singapore' => 'SGD',
                        'united states' => 'USD',
                        'us' => 'USD',
                        'usa' => 'USD',
                        'united kingdom' => 'GBP',
                        'uk' => 'GBP',
                        'europe' => 'EUR',
                        'uae' => 'AED',
                        'united arab emirates' => 'AED',
                        'new zealand' => 'NZD',
                        'nz' => 'NZD',
                        'australia' => 'AUD',
                        'canada' => 'CAD',
                        'germany' => 'EUR',
                        'france' => 'EUR',
                        'italy' => 'EUR',
                        'spain' => 'EUR',
                        'netherlands' => 'EUR',
                        'ireland' => 'EUR',
                    ];

                    if (isset($countryToCurrency[$countryLower])) {
                        $currency = $countryToCurrency[$countryLower];
                    } elseif (strlen($countryLower) === 3) {
                        $currency = strtoupper($countryLower);
                    }
                }

                $recAmt = $recParsed['amount'];
                $paymentCurrency = $recParsed['has_explicit_currency'] ? $recParsed['currency'] : $currency;

                // Handle Billing Cycle
                $billingCycle = 'monthly';
                $cycleCode = 'monthly';
                $billingCycleLower = strtolower(str_replace(' ', '_', $billingCycleInput));
                if (str_contains($billingCycleLower, 'annual') || str_contains($billingCycleLower, 'year')) {
                    $billingCycle = 'annual';
                    $cycleCode = 'annual';
                }

                // Calculate next billing / end dates
                $cycleModel = BillingCycle::where('code', $cycleCode)->first();
                $months = $cycleModel ? $cycleModel->duration_months : 1;
                $nextBillingDate = date('Y-m-d', strtotime("+{$months} months", strtotime($purDate)));

                // 1. Find or create Customer (including soft deleted)
                $customer = null;
                if ($email) {
                    $customer = Customer::withTrashed()->where('email', $email)->first();
                }
                if (!$customer && $businessName) {
                    $customer = Customer::withTrashed()->where('name', $businessName)->first();
                }

                if (!$customer) {
                    // Generate fallback email if empty
                    $fallbackEmail = $email ?: strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $businessName)) . '@example.com';
                    
                    // In case this fallback email already exists in the database, append a unique suffix
                    $checkEmail = $fallbackEmail;
                    $counter = 1;
                    while (Customer::withTrashed()->where('email', $checkEmail)->exists()) {
                        $parts = explode('@', $fallbackEmail);
                        $checkEmail = $parts[0] . '-' . $counter . '@' . ($parts[1] ?? 'example.com');
                        $counter++;
                    }
                    $fallbackEmail = $checkEmail;

                    $customer = Customer::create([
                        'name' => $businessName ?: 'New Customer',
                        'email' => $fallbackEmail,
                        'phone' => $phone ?: null,
                        'role' => $role ?: null,
                        'country' => $country ?: 'Global Region',
                        'status' => 'active',
                        'plan' => 'Starter Plan',
                        'mrr' => $this->convertToUsd($purAmt, $currency, $purDate),
                        'joined_date' => $purDate,
                    ]);
                } else {
                    if ($customer->trashed()) {
                        $customer->restore();
                    }
                    $customer->update([
                        'name' => $businessName ?: $customer->name,
                        'phone' => $phone ?: $customer->phone,
                        'role' => $role ?: $customer->role,
                        'country' => $country ?: $customer->country,
                        'mrr' => $this->convertToUsd($purAmt, $currency, $purDate),
                    ]);
                }

                // 1.5 Create or update Sub-Customer
                $subCustomer = null;
                if ($subCustomerName || $subCustomerEmail) {
                    if ($subCustomerEmail) {
                        $subCustomer = SubCustomer::withTrashed()
                            ->where('customer_id', $customer->id)
                            ->where('email', $subCustomerEmail)
                            ->first();
                    }
                    if (!$subCustomer && $subCustomerName) {
                        $subCustomer = SubCustomer::withTrashed()
                            ->where('customer_id', $customer->id)
                            ->where('name', $subCustomerName)
                            ->first();
                    }

                    if (!$subCustomer) {
                        $subCustomer = SubCustomer::create([
                            'customer_id' => $customer->id,
                            'name' => $subCustomerName ?: ($subCustomerEmail ? explode('@', $subCustomerEmail)[0] : 'Sub Customer'),
                            'email' => $subCustomerEmail ?: null,
                            'phone' => $subCustomerPhone ?: null,
                            'address' => $subCustomerAddress ?: null,
                            'status' => 'active',
                        ]);
                    } else {
                        if ($subCustomer->trashed()) {
                            $subCustomer->restore();
                        }
                        $subCustomer->update([
                            'name' => $subCustomerName ?: $subCustomer->name,
                            'email' => $subCustomerEmail ?: $subCustomer->email,
                            'phone' => $subCustomerPhone ?: $subCustomer->phone,
                            'address' => $subCustomerAddress ?: $subCustomer->address,
                        ]);
                    }
                }
                // 2. Create or update Subscription (including soft deleted)
                $subscription = Subscription::withTrashed()->where('customer_email', $customer->email)->first();
                if ($subscription) {
                    if ($subscription->trashed()) {
                        $subscription->restore();
                    }
                    $subscription->update([
                        'customer_name' => $customer->name,
                        'start_date' => $purDate,
                        'plan_name' => 'Starter Plan',
                        'billing_cycle' => $billingCycle,
                        'amount' => $purAmt,
                        'status' => 'active',
                        'next_billing_date' => $nextBillingDate,
                        'renewal_date' => $nextBillingDate,
                        'auto_renewal' => true,
                        'sub_customer_id' => $subCustomer ? $subCustomer->id : null,
                    ]);
                } else {
                    Subscription::create([
                        'customer_email' => $customer->email,
                        'customer_name' => $customer->name,
                        'start_date' => $purDate,
                        'plan_name' => 'Starter Plan',
                        'billing_cycle' => $billingCycle,
                        'amount' => $purAmt,
                        'status' => 'active',
                        'next_billing_date' => $nextBillingDate,
                        'renewal_date' => $nextBillingDate,
                        'auto_renewal' => true,
                        'sub_customer_id' => $subCustomer ? $subCustomer->id : null,
                    ]);
                }

                // 3. Create or update Invoice (including soft deleted)
                $invoiceNumber = $billNo ?: 'INV-' . date('Y') . '-' . strtoupper(Str::random(6));
                $invoice = Invoice::withTrashed()->where('invoice_number', $invoiceNumber)->first();
                if ($invoice) {
                    if ($invoice->trashed()) {
                        $invoice->restore();
                    }
                    $invoice->update([
                        'customer_name' => $customer->name,
                        'amount' => $purAmt,
                        'tax' => 0,
                        'total' => $purAmt,
                        'invoice_date' => $purDate,
                        'due_date' => $purDate,
                        'status' => 'paid',
                        'billing_cycle' => $billingCycle,
                        'currency' => $currency,
                        'usd_amount' => $this->convertToUsd($purAmt, $currency, $purDate),
                        'closed_by' => $closedBy ?: null,
                    ]);
                } else {
                    $invoice = Invoice::create([
                        'invoice_number' => $invoiceNumber,
                        'customer_name' => $customer->name,
                        'amount' => $purAmt,
                        'tax' => 0,
                        'total' => $purAmt,
                        'invoice_date' => $purDate,
                        'due_date' => $purDate,
                        'status' => 'paid',
                        'billing_cycle' => $billingCycle,
                        'currency' => $currency,
                        'usd_amount' => $this->convertToUsd($purAmt, $currency, $purDate),
                        'closed_by' => $closedBy ?: null,
                    ]);
                }

                // 4. Create or update Payment (including soft deleted)
                $isRenewal = false;
                if ($customer) {
                    $isRenewal = Payment::where('customer_id', $customer->id)
                        ->where('status', 'successful')
                        ->where('invoice_ref', '!=', $invoiceNumber)
                        ->exists();
                }

                $payment = Payment::withTrashed()->where('invoice_ref', $invoiceNumber)->first();
                if ($payment) {
                    if ($payment->trashed()) {
                        $payment->restore();
                    }
                    $payment->update([
                        'customer_id' => $customer ? $customer->id : null,
                        'customer_name' => $customer->name,
                        'amount' => $recAmt,
                        'payment_method' => $mode ?: 'Stripe',
                        'method_type' => 'stripe',
                        'status' => 'successful',
                        'payment_date' => $purDate,
                        'billing_cycle' => $billingCycle,
                        'currency' => $paymentCurrency,
                        'usd_amount' => $this->convertToUsd($recAmt, $paymentCurrency, $purDate),
                        'end_date' => date('M d, Y', strtotime($nextBillingDate)),
                        'payment_context' => $isRenewal ? 'renewal' : 'new',
                        'sub_customer_id' => $subCustomer ? $subCustomer->id : null,
                        'closed_by' => $closedBy ?: null,
                    ]);
                } else {
                    Payment::create([
                        'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                        'customer_id' => $customer ? $customer->id : null,
                        'customer_name' => $customer->name,
                        'amount' => $recAmt,
                        'payment_method' => $mode ?: 'Stripe',
                        'method_type' => 'stripe',
                        'status' => 'successful',
                        'invoice_ref' => $invoiceNumber,
                        'payment_date' => $purDate,
                        'billing_cycle' => $billingCycle,
                        'currency' => $paymentCurrency,
                        'usd_amount' => $this->convertToUsd($recAmt, $paymentCurrency, $purDate),
                        'end_date' => date('M d, Y', strtotime($nextBillingDate)),
                        'payment_context' => $isRenewal ? 'renewal' : 'new',
                        'sub_customer_id' => $subCustomer ? $subCustomer->id : null,
                        'closed_by' => $closedBy ?: null,
                    ]);
                }

                $successCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Database import error: ' . $e->getMessage());
        }

        if (!empty($errors)) {
            $errorMsg = 'Imported ' . $successCount . ' records with some issues: ' . implode('; ', $errors);
            return back()->with('warning', $errorMsg);
        }

        $context = $request->input('import_context', 'customers');
        if ($context === 'payments') {
            return back()->with('success', 'Successfully imported ' . $successCount . ' payment and invoice records!');
        }

        return back()->with('success', 'Successfully imported ' . $successCount . ' customer and subscription records!');
    }

    /**
     * Parse amount and currency from string (e.g. "USD 349" -> [349, 'USD'])
     */
    private function parseAmountAndCurrency($value)
    {
        $value = trim($value);
        $amount = 0.0;
        $currency = 'USD';
        $hasExplicitCurrency = false;

        if (preg_match('/([A-Za-z]{3})?\s*([0-9.,]+)/', $value, $matches)) {
            if (!empty($matches[1])) {
                $currency = strtoupper($matches[1]);
                $hasExplicitCurrency = true;
            }
            $amount = (float) str_replace(',', '', $matches[2]);
        } else {
            $amount = (float) preg_replace('/[^0-9.]/', '', $value);
        }

        return [
            'amount' => $amount,
            'currency' => $currency,
            'has_explicit_currency' => $hasExplicitCurrency
        ];
    }

    /**
     * Fetch latest exchange rates from public API or fallback to hardcoded rates.
     */
    private function getLatestExchangeRates()
    {
        if (self::$cachedRates !== null) {
            return self::$cachedRates;
        }

        $fallbackRates = [
            'SGD' => 1.35,
            'USD' => 1.00,
            'INR' => 83.5,
            'AED' => 3.67,
            'EUR' => 0.91,
            'GBP' => 0.77,
            'NZD' => 1.64,
            'AUD' => 1.50,
            'CAD' => 1.39,
        ];

        try {
            $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/USD');
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['result']) && $data['result'] === 'success' && isset($data['rates'])) {
                    self::$cachedRates = $data['rates'];
                    return self::$cachedRates;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Exchange rate API call failed, using fallback: ' . $e->getMessage());
        }

        self::$cachedRates = $fallbackRates;
        return self::$cachedRates;
    }

    /**
     * Fetch historical exchange rates from public API or fallback to empty array.
     */
    private function getHistoricalExchangeRates($date)
    {
        if (isset(self::$cachedDateRates[$date])) {
            return self::$cachedDateRates[$date];
        }

        try {
            $response = Http::timeout(5)->get("https://api.frankfurter.dev/v1/{$date}?base=USD");
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rates'])) {
                    self::$cachedDateRates[$date] = $data['rates'];
                    return self::$cachedDateRates[$date];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Historical exchange rate API call failed for date {$date}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Convert amount to USD using live API rates (with fallback)
     */
    private function convertToUsd($amount, $currency, $date = null)
    {
        $currency = strtoupper(trim($currency));
        if ($currency === 'USD' || !$currency) {
            return $amount;
        }

        // Try historical rates first if date is provided
        if ($date) {
            $historicalRates = $this->getHistoricalExchangeRates($date);
            if (isset($historicalRates[$currency]) && $historicalRates[$currency] > 0) {
                return round($amount / $historicalRates[$currency], 2);
            }
        }

        // Fallback to latest rates
        $rates = $this->getLatestExchangeRates();
        if (isset($rates[$currency]) && $rates[$currency] > 0) {
            return round($amount / $rates[$currency], 2);
        }

        return $amount;
    }
}
