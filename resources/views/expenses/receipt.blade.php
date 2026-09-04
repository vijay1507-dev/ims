<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Receipt - {{ $expense->reference_number ?? 'Voucher #'.$expense->id }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 40px;
            background-color: #f8fafc;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        .logo-section h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -0.025em;
        }
        .logo-section p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #64748b;
        }
        .title-section {
            text-align: right;
        }
        .title-section h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .title-section .doc-id {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #64748b;
            font-family: monospace;
        }
        .details-grid {
            display: grid;
            grid-cols: 1;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        .detail-card h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }
        .detail-card p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            color: #334155;
        }
        .detail-card .highlight {
            font-weight: 600;
            color: #0f172a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .items-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .items-table .amount-col {
            text-align: right;
        }
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .summary-box {
            width: 300px;
            border-top: 2px solid #e2e8f0;
            padding-top: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #64748b;
        }
        .summary-row.total {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
            margin-top: 8px;
        }
        .notes-section {
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }
        .notes-section h4 {
            margin: 0 0 6px 0;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .actions-bar {
            max-width: 800px;
            margin: 20px auto 0 auto;
            display: flex;
            justify-content: flex-end;
        }
        .btn-print {
            background-color: #4f46e5;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            transition: background-color 0.2s;
        }
        .btn-print:hover {
            background-color: #4338ca;
        }
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .actions-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="logo-section">
                <h1>SaaS Manager</h1>
                <p>Internal Operations Registry</p>
            </div>
            <div class="title-section">
                <h2>Expense Voucher</h2>
                <div class="doc-id">ID: EXP-{{ str_pad($expense->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <div class="details-grid">
            <div class="detail-card">
                <h3>Voucher Metadata</h3>
                <p><span class="highlight">Logged Date:</span> {{ $expense->expense_date->format('F d, Y') }}</p>
                <p><span class="highlight">Payment Type:</span> {{ ucfirst($expense->type) }} Expense</p>
                <p><span class="highlight">Reference Ref:</span> {{ $expense->reference_number ?? 'N/A' }}</p>
            </div>
            <div class="detail-card">
                <h3>Classification</h3>
                <p><span class="highlight">Category:</span> {{ $expense->category }}</p>
                <p><span class="highlight">Created By:</span> {{ $expense->creator ? $expense->creator->name : 'System Administrator' }}</p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 70%;">Description / Purpose</th>
                    <th class="amount-col" style="width: 30%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="color: #0f172a;">{{ $expense->title }}</strong>
                        @if($expense->description)
                            <div style="font-size: 12px; color: #64748b; margin-top: 6px;">{{ $expense->description }}</div>
                        @endif
                    </td>
                    <td class="amount-col font-semibold" style="font-weight: 600; color: #0f172a;">
                        ${{ number_format((float)$expense->amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="summary-section">
            <div class="summary-box">
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>${{ number_format((float)$expense->amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="notes-section">
            <h4>Verification & Audit Notes</h4>
            <p>This document constitutes an official internal operational expense record logged within the SaaS Manager ERP system. For accounting and tax reconciliation, please verify the reference numbers matching the ledger statement details.</p>
        </div>
    </div>

    <div class="actions-bar">
        <button onclick="window.print()" class="btn-print">
            <svg style="width: 16px; height: 16px; margin-right: 8px; fill: currentColor;" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
            Print Receipt / Save PDF
        </button>
    </div>
</body>
</html>
