/**
 * Generic utility to export JSON data to a CSV file that can be opened in Excel.
 * @param {Array} data - The array of objects to export
 * @param {String} fileName - Desired name for the exported file
 * @param {Object} columnMap - Mapping of Object Keys to Human Readable Headers { key: 'Header' }
 */
export const exportToExcel = (data, fileName, columnMap) => {
    if (!data || !data.length) {
        alert('No data available for export.');
        return;
    }

    const headers = Object.values(columnMap);
    const keys = Object.keys(columnMap);

    const csvRows = [];
    
    // Add headers
    csvRows.push(headers.map(h => `"${h.replace(/"/g, '""')}"`).join(','));

    // Add data rows
    for (const row of data) {
        const values = keys.map(key => {
            let val = row[key];
            
            // Handle nested objects if necessary (like customer.name)
            if (key.includes('.')) {
                val = key.split('.').reduce((obj, k) => (obj ? obj[k] : ''), row);
            }
            
            if (val === null || val === undefined) val = '';
            
            // Format for CSV
            const escaped = ('' + val).replace(/"/g, '""');
            return `"${escaped}"`;
        });
        csvRows.push(values.join(','));
    }

    const csvContent = "\uFEFF" + csvRows.join('\n'); // Add BOM for Excel UTF-8 support
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    link.setAttribute('href', url);
    link.setAttribute('download', `${fileName}_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
