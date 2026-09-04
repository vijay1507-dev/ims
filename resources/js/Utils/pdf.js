import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

export const generatePaymentPDF = (payment) => {
    const doc = new jsPDF();
    const invoiceNo = payment.invoice_ref || `INV-GEN-${payment.id}`;

    // Header
    doc.setFontSize(22);
    doc.setTextColor(99, 102, 241); // Indigo-600
    doc.text('OFFICIAL TAX INVOICE', 105, 20, { align: 'center' });

    // Subheader
    doc.setFontSize(10);
    doc.setTextColor(107, 114, 128); // Gray-500
    doc.text('Cloud Business Infrastructure Operations', 105, 28, { align: 'center' });

    // Divider
    doc.setDrawColor(229, 231, 235); // Gray-200
    doc.line(20, 35, 190, 35);

    // Document Info
    doc.setFontSize(12);
    doc.setTextColor(17, 24, 39); // Gray-900
    doc.setFont('helvetica', 'bold');
    doc.text('Invoice Details', 20, 45);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Invoice Ref: ${invoiceNo}`, 20, 55);
    doc.text(`Transaction ID: ${payment.transaction_id}`, 20, 62);
    doc.text(`Date: ${payment.payment_date}`, 20, 69);

    // Customer Info
    doc.setFont('helvetica', 'bold');
    doc.text('Bill To', 120, 45);
    doc.setFont('helvetica', 'normal');
    doc.text(payment.customer_name, 120, 55);
    doc.text('Enterprise Subscription License', 120, 62);

    // Helper for currency formatting symbol
    const currencySymbols = {
        USD: '$',
        EUR: '€',
        GBP: '£',
        INR: '₹',
        SGD: 'S$',
        AED: 'د.إ'
    };
    const currencySign = currencySymbols[payment.currency] || (payment.currency || '$');

    // Helper to format values properly by stripping any existing '$' prefix
    const formatVal = (val) => {
        if (!val) return `${currencySign}0.00`;
        const valStr = String(val).replace(/^\$/, '').trim();
        return `${currencySign}${valStr}`;
    };

    // Table
    autoTable(doc, {
        startY: 80,
        head: [['Description', 'Method', 'Status', 'Amount']],
        body: [
            [
                'Cloud Infrastructure Recurring Payment',
                payment.payment_method,
                payment.status.toUpperCase(),
                formatVal(payment.amount)
            ]
        ],
        headStyles: { fillColor: [99, 102, 241], fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [249, 250, 251] },
        styles: { fontSize: 10, cellPadding: 6 }
    });

    // Summary
    const finalY = doc.lastAutoTable.finalY + 15;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.text('Total Amount Paid:', 120, finalY);
    doc.setTextColor(16, 185, 129); // Emerald-600

    doc.text(formatVal(payment.amount), 190, finalY, { align: 'right' });

    // Footer
    doc.setFontSize(9);
    doc.setTextColor(156, 163, 175); // Gray-400
    doc.text('This is a computer-generated document. No signature required.', 105, 280, { align: 'center' });

    doc.save(`${invoiceNo.toLowerCase()}.pdf`);
};

export const generateInvoicePDF = (invoice) => {
    const doc = new jsPDF();
    const invoiceNo = invoice.invoice_number;

    // Header
    doc.setFontSize(22);
    doc.setTextColor(16, 185, 129); // Emerald-600
    doc.text('COMMERCIAL INVOICE', 105, 20, { align: 'center' });

    // Subheader
    doc.setFontSize(10);
    doc.setTextColor(107, 114, 128); // Gray-500
    doc.text('Taxation & Compliance Ledger Record', 105, 28, { align: 'center' });

    // Divider
    doc.setDrawColor(229, 231, 235);
    doc.line(20, 35, 190, 35);

    // Document Info
    doc.setFontSize(12);
    doc.setTextColor(17, 24, 39);
    doc.setFont('helvetica', 'bold');
    doc.text('Document Metadata', 20, 45);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Invoice #: ${invoiceNo}`, 20, 55);
    doc.text(`Issue Date: ${invoice.invoice_date}`, 20, 62);
    doc.text(`Due Date: ${invoice.due_date}`, 20, 69);

    // Customer Info
    doc.setFont('helvetica', 'bold');
    doc.text('Client Entity', 120, 45);
    doc.setFont('helvetica', 'normal');
    doc.text(invoice.customer_name, 120, 55);

    // Helper for currency formatting symbol
    const currencySymbols = {
        USD: '$',
        EUR: '€',
        GBP: '£',
        INR: '₹',
        SGD: 'S$',
        AED: 'د.إ'
    };
    const currencySign = currencySymbols[invoice.currency] || (invoice.currency || '$');

    // Helper to format values properly by stripping any existing '$' prefix
    const formatVal = (val) => {
        if (!val) return `${currencySign}0.00`;
        const valStr = String(val).replace(/^\$/, '').trim();
        return `${currencySign}${valStr}`;
    };

    // Table
    autoTable(doc, {
        startY: 80,
        head: [['Line Item', 'Status', 'Total Sum']],
        body: [
            [
                'Provisioned Managed Services',
                invoice.status.toUpperCase(),
                `$${parseFloat(invoice.usd_amount || 0).toFixed(2)}`
            ]
        ],
        headStyles: { fillColor: [16, 185, 129], fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [249, 250, 251] },
        styles: { fontSize: 10, cellPadding: 6 }
    });

    // Summary
    const finalY = doc.lastAutoTable.finalY + 15;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.text('Grand Total:', 120, finalY);
    doc.setTextColor(17, 24, 39);
    
    doc.text(`$${parseFloat(invoice.usd_amount || 0).toFixed(2)}`, 190, finalY, { align: 'right' });

    // Footer
    doc.setFontSize(9);
    doc.setTextColor(156, 163, 175);
    doc.text('Certified taxation document mapping to central accounting registers.', 105, 280, { align: 'center' });

    doc.save(`${invoiceNo.toLowerCase()}.pdf`);
};

export const generateExpensePDF = (expense) => {
    const doc = new jsPDF();
    const docNo = `EXP-${String(expense.id).padStart(6, '0')}`;

    // Header
    doc.setFontSize(22);
    doc.setTextColor(99, 102, 241); // Indigo-600
    doc.text('EXPENSE VOUCHER RECORD', 105, 20, { align: 'center' });

    // Subheader
    doc.setFontSize(10);
    doc.setTextColor(107, 114, 128); // Gray-500
    doc.text('Internal Operations Registry & Audit Ledger', 105, 28, { align: 'center' });

    // Divider
    doc.setDrawColor(229, 231, 235);
    doc.line(20, 35, 190, 35);

    // Document Info
    doc.setFontSize(12);
    doc.setTextColor(17, 24, 39);
    doc.setFont('helvetica', 'bold');
    doc.text('Voucher Metadata', 20, 45);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Voucher ID: ${docNo}`, 20, 55);
    doc.text(`Logged Date: ${expense.expense_date}`, 20, 62);
    doc.text(`Ref Number: ${expense.reference_number || 'N/A'}`, 20, 69);

    // Classification Info
    doc.setFont('helvetica', 'bold');
    doc.text('Classification', 120, 45);
    doc.setFont('helvetica', 'normal');
    doc.text(`Expense Type: ${expense.type}`, 120, 55);
    doc.text(`Category: ${expense.category}`, 120, 62);

    // Table
    autoTable(doc, {
        startY: 80,
        head: [['Description / Purpose', 'Amount']],
        body: [
            [
                expense.title + (expense.description ? `\n\nDescription:\n${expense.description}` : ''),
                `$${parseFloat(expense.amount || 0).toFixed(2)}`
            ]
        ],
        headStyles: { fillColor: [99, 102, 241], fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [249, 250, 251] },
        styles: { fontSize: 10, cellPadding: 6 }
    });

    // Summary
    const finalY = doc.lastAutoTable.finalY + 15;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.text('Total Amount:', 120, finalY);
    doc.setTextColor(17, 24, 39);
    
    doc.text(`$${parseFloat(expense.amount || 0).toFixed(2)}`, 190, finalY, { align: 'right' });

    // Footer
    doc.setFontSize(9);
    doc.setTextColor(156, 163, 175);
    doc.text('This document constitutes an official internal operational expense record.', 105, 280, { align: 'center' });

    doc.save(`${docNo.toLowerCase()}.pdf`);
};
