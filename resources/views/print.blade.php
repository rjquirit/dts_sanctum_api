<?php
// Get tracking number from route parameter
$trackingNumber = $track ?? '';
$preview = false;

if (empty($trackingNumber)) {
    die('Tracking number is required');
}

// Construct API URL
$apiUrl = "/api/docmain/track/" . urlencode($trackingNumber);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Tracking Report - <?php echo htmlspecialchars($trackingNumber); ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        
        .document-info {
            margin-bottom: 25px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table th,
        .info-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #000;
        }
        
        .info-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .routes-section {
            margin-top: 30px;
        }
        
        .routes-section h3 {
            margin-bottom: 15px;
            font-size: 14px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .route-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .route-table th,
        .route-table td {
            padding: 6px;
            text-align: left;
            border: 1px solid #000;
            font-size: 11px;
        }
        
        .route-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 15px;
            font-size: 10px;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            font-size: 16px;
        }
        
        .error {
            color: #d32f2f;
            text-align: center;
            padding: 20px;
            border: 2px solid #d32f2f;
            background-color: #ffebee;
        }

        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .no-print button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }
        
        .no-print button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php if ($preview): ?>
    <div class="no-print">
        <button onclick="window.print()">Print Document</button>
        <button onclick="window.close()">Close Preview</button>
    </div>
    <?php endif; ?>

    <div class="loading" id="loading">
        Loading document data...
    </div>

    <div id="report-content" style="display: none;">
        <div class="header">
            <h1>DOCUMENT TRACKING REPORT</h1>
            <h2>Tracking Number: <span id="doc-tracking"></span></h2>
        </div>

        <div class="document-info">
            <h3>Document Information</h3>
            <table class="info-table">
                <tr>
                    <th style="width: 30%;">Document ID</th>
                    <td id="doc-id"></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td id="doc-description"></td>
                </tr>
                <tr>
                    <th>Document Type</th>
                    <td id="doc-type"></td>
                </tr>
                <tr>
                    <th>Origin</th>
                    <td id="doc-origin"></td>
                </tr>
                <tr>
                    <th>Origin Section</th>
                    <td id="doc-origin-section"></td>
                </tr>
                <tr>
                    <th>Date Posted</th>
                    <td id="doc-date-posted"></td>
                </tr>
                <tr>
                    <th>Date Accepted</th>
                    <td id="doc-date-accepted"></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td id="doc-status"></td>
                </tr>
            </table>
        </div>

        <div class="routes-section">
            <h3>Document Routing History</h3>
            <table class="route-table">
                <thead>
                    <tr>
                        <th>Action ID</th>
                        <th>From</th>
                        <th>From Section</th>
                        <th>To</th>
                        <th>To Section</th>
                        <th>Date Forwarded</th>
                        <th>Received By</th>
                        <th>Date Accepted</th>
                    </tr>
                </thead>
                <tbody id="routes-tbody">
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Generated on: <span id="print-date"></span></p>
            <p>Document Tracking System - Regional Office</p>
        </div>
    </div>

    <div id="error-message" class="error" style="display: none;">
        <h3>Error Loading Document</h3>
        <p id="error-text"></p>
    </div>

    <script>
        // Set current date
        document.getElementById('print-date').textContent = new Date().toLocaleString();

        // Function to format date
        function formatDate(dateString) {
            if (!dateString || dateString.includes('-000001')) {
                return 'N/A';
            }
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }

        // Function to load and display document data
        async function loadDocumentData() {
            try {
                const response = await fetch('<?php echo $apiUrl; ?>');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                // Populate document information
                document.getElementById('doc-tracking').textContent = data.document.doc_tracking || 'N/A';
                document.getElementById('doc-id').textContent = data.document.doc_id || 'N/A';
                document.getElementById('doc-description').textContent = data.document.docs_description || 'N/A';
                document.getElementById('doc-type').textContent = data.document.doctype.doctype_description || 'N/A';
                document.getElementById('doc-origin').textContent = data.document.origin_fname || 'N/A';
                document.getElementById('doc-origin-section').textContent = data.document.origin_section.section_description || 'N/A';
                document.getElementById('doc-date-posted').textContent = formatDate(data.document.datetime_posted);
                document.getElementById('doc-date-accepted').textContent = formatDate(data.document.datetime_accepted);
                document.getElementById('doc-status').textContent = data.document.done ? 'Completed' : 'In Progress';

                // Populate routes
                const routesTableBody = document.getElementById('routes-tbody');
                routesTableBody.innerHTML = '';

                data.routes.forEach(route => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${route.action_id}</td>
                        <td>${route.route_from}</td>
                        <td>${route.from_section.section_description}</td>
                        <td>${route.received_by}</td>
                        <td>${route.to_section.section_description}</td>
                        <td>${formatDate(route.datetime_forwarded)}</td>
                        <td>${route.received_by}</td>
                        <td>${formatDate(route.datetime_route_accepted)}</td>
                    `;
                    routesTableBody.appendChild(row);
                });

                // Hide loading, show content
                document.getElementById('loading').style.display = 'none';
                document.getElementById('report-content').style.display = 'block';

                // Auto-print if not in preview mode
                <?php if (!$preview): ?>
                setTimeout(() => {
                    window.print();
                }, 1000);
                <?php endif; ?>

            } catch (error) {
                console.error('Error loading document data:', error);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-text').textContent = 'Failed to load document data: ' + error.message;
            }
        }

        // Load data when page is ready
        document.addEventListener('DOMContentLoaded', loadDocumentData);
    </script>
</body>
</html>