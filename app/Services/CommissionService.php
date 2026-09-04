<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use App\Models\CommissionSlab;
use App\Models\Commission;
use Carbon\Carbon;

class CommissionService
{
    /**
     * Calculate commission details for a specific employee and period.
     */
    public function calculate(string $employeeName, float $salary, int $month, int $year): array
    {
        // Try to find matching user model, but it is optional
        $user = User::where('name', $employeeName)->first();
        $userId = $user ? $user->id : null;

        // Fetch successful payments closed by this employee name string
        $payments = Payment::where('status', 'successful')
            ->where('closed_by', $employeeName)
            ->get();

        // Filter by selected month and year
        $eligiblePayments = $payments->filter(function ($p) use ($month, $year) {
            $dateStr = $p->payment_date ?? $p->created_at->format('Y-m-d');
            $ts = strtotime($dateStr);
            if ($ts === false) {
                // Try regex fallback matching
                if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($dateStr), $matches)) {
                    $ts = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
                }
            }
            if ($ts === false) {
                return false;
            }
            return (int)date('n', $ts) === (int)$month && (int)date('Y', $ts) === (int)$year;
        });

        // Sum the eligible payments, converting to INR if in other currencies
        $totalClosedAmount = 0.0;
        foreach ($eligiblePayments as $p) {
            $totalClosedAmount += $this->convertToInr($p);
        }

        // Find the highest achieved slab
        $slabs = CommissionSlab::active()
            ->orderBy('achievement_amount', 'desc')
            ->get();

        $applicableSlab = null;
        foreach ($slabs as $slab) {
            if ($totalClosedAmount >= (float)$slab->achievement_amount) {
                $applicableSlab = $slab;
                break;
            }
        }

        // Calculate commission fields based on slab achievements
        if ($applicableSlab) {
            $achievementPercentage = (float)$applicableSlab->achievement_percentage;
            $points = (int)$applicableSlab->points;
            $slabAmount = (float)$applicableSlab->achievement_amount;
            $commissionPercentage = (float)$applicableSlab->commission_percentage;
            $commissionAmount = $salary * ($commissionPercentage / 100);
            $slabId = $applicableSlab->id;
            $slabName = '₹' . number_format($slabAmount);
        } else {
            $achievementPercentage = 0.0;
            $points = 0;
            $slabAmount = 0.0;
            $commissionPercentage = 0.0;
            $commissionAmount = 0.0;
            $slabId = null;
            $slabName = 'No Slab Achieved';
        }

        // Format month and year label
        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        $periodLabel = "{$monthName} {$year}";

        // Format payments for display in the table
        $paymentBreakdown = $eligiblePayments->map(function ($p) {
            return [
                'id' => $p->id,
                'transaction_id' => $p->transaction_id,
                'customer_name' => $p->customer_name,
                'payment_date' => $p->payment_date ?? $p->created_at->format('M d, Y'),
                'amount' => $p->formatted_amount,
                'raw_amount' => $p->amount,
                'currency' => $p->currency ?? 'USD',
                'inr_amount' => '₹' . number_format($this->convertToInr($p), 2),
                'status' => $p->status,
                'closed_by' => $p->closed_by,
            ];
        })->values();

        return [
            'user_id' => $userId,
            'employee_name' => $employeeName,
            'salary' => $salary,
            'month' => $month,
            'year' => $year,
            'period' => $periodLabel,
            'total_payments_closed' => $totalClosedAmount,
            'achievement_percentage' => $achievementPercentage,
            'points' => $points,
            'slab_name' => $slabName,
            'slab_id' => $slabId,
            'commission_percentage' => $commissionPercentage,
            'commission_amount' => $commissionAmount,
            'payments' => $paymentBreakdown,
        ];
    }

    /**
     * Save the commission calculation to the database.
     */
    public function save(string $employeeName, float $salary, int $month, int $year, string $status = 'draft'): Commission
    {
        // If a commission is already approved or paid, check if it exists and prevent changes
        $existing = Commission::where('employee_name', $employeeName)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && in_array($existing->status, ['approved', 'paid'])) {
            return $existing;
        }

        // Perform calculation
        $result = $this->calculate($employeeName, $salary, $month, $year);

        // Update or create commission record
        return Commission::updateOrCreate(
            [
                'employee_name' => $employeeName,
                'month' => $month,
                'year' => $year,
            ],
            [
                'user_id' => $result['user_id'],
                'salary' => $salary,
                'total_payments_closed' => $result['total_payments_closed'],
                'achievement_percentage' => $result['achievement_percentage'],
                'points' => $result['points'],
                'commission_slab_id' => $result['slab_id'],
                'commission_percentage' => $result['commission_percentage'],
                'commission_amount' => $result['commission_amount'],
                'calculation_date' => Carbon::now()->toDateString(),
                'status' => $status,
            ]
        );
    }

    /**
     * Normalize payment amounts to INR using exchange rates fetched from Frankfurter API based on the payment date.
     */
    private function convertToInr(Payment $payment): float
    {
        $currency = strtoupper($payment->currency ?? 'USD');
        if ($currency === 'INR') {
            return (float)$payment->amount;
        }

        // Resolve date string to Y-m-d format
        $dateStr = $payment->payment_date ?? $payment->created_at->format('Y-m-d');
        $ts = strtotime($dateStr);
        if ($ts === false) {
            if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($dateStr), $matches)) {
                $ts = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
            }
        }
        
        $date = $ts !== false ? date('Y-m-d', $ts) : date('Y-m-d');
        
        // Fetch rate from API (from payment currency directly to INR)
        $rate = $this->getExchangeRate($currency, 'INR', $date);
        
        return (float)$payment->amount * $rate;
    }

    /**
     * Fetch exchange rate with local cache using the laravel-currency converter.
     */
    private function getExchangeRate(string $from, string $to, string $date): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        
        if ($from === $to) {
            return 1.0;
        }

        // Prevent API calls for future dates by falling back to latest
        $formattedDate = $date;
        if (strtotime($date) > time()) {
            $formattedDate = 'latest';
        }

        $cacheKey = "commission_xr_{$from}_{$to}_{$formattedDate}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($from, $to, $formattedDate) {
            try {
                $conv = \AmrShawky\Currency\Facade\Currency::convert()
                    ->from($from)
                    ->to($to);

                if ($formattedDate !== 'latest') {
                    $conv = $conv->date($formattedDate);
                }

                $rate = $conv->get();
                if ($rate !== null && $rate > 0) {
                    return (float)$rate;
                }
            } catch (\Exception $e) {
                // Ignore and fall through to hardcoded fallback
            }

            // Fallback rates if API fails or doesn't support the currency (represented in currency -> USD)
            $exchangeRatesFallback = [
                'SGD' => 0.74,
                'USD' => 1.00,
                'INR' => 0.012,
                'AED' => 0.27,
                'EUR' => 1.10,
                'GBP' => 1.30,
                'AUD' => 0.65,
                'CAD' => 0.74,
            ];

            $fromUsdRate = $exchangeRatesFallback[$from] ?? 1.00;
            $toUsdRate = $exchangeRatesFallback[$to] ?? 1.00;

            return $fromUsdRate / $toUsdRate;
        });
    }
}
