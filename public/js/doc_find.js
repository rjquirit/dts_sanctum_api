document.addEventListener('DOMContentLoaded', function() {
    const trackingInput = document.getElementById('trackingNumber');
    const searchBtn = document.getElementById('searchBtn');
    const printBtn = document.getElementById('printBtn');
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('errorMessage');
    const documentContent = document.getElementById('documentContent');
    const documentDetailsContent = document.getElementById('documentDetailsContent');
    const documentTimeline = document.getElementById('documentTimeline');
    const barcodeContainer = document.getElementById('barcodeContainer');
    const qrCodeContainer = document.getElementById('qrCodeContainer');

    let currentDocument = null;

    // Search button event listener
    searchBtn.addEventListener('click', function() {
        const trackingNumber = trackingInput.value.trim();
        if (trackingNumber) {
            searchDocument(trackingNumber);
        } else {
            showError('Please enter a tracking number');
        }
    });

    // Enter key support for search input
    trackingInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // Print button event listener
    printBtn.addEventListener('click', function() {
        if (currentDocument) {
            printDocument();
        } else {
            showError('No document to print');
        }
    });

    // Search document function
    function searchDocument(trackingNumber) {
        showLoading(true);
        hideError();
        hideDocumentContent();

        // Make API call to your specific endpoint
        fetch(`/api/docmain/track/${trackingNumber}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            showLoading(false);
            if (data.document) {
                currentDocument = data;
                displayDocument(data);
                generateCodes(trackingNumber);
            } else {
                showError('Document not found or invalid response format');
            }
        })
        .catch(error => {
            showLoading(false);
            showError('Error searching document: ' + error.message);
            console.error('Search error:', error);
        });
    }

    // Display document details
    function displayDocument(data) {
        const document = data.document;
        
        // Populate document details
        documentDetailsContent.innerHTML = `
            <div class="detail-row">
                <div class="detail-label">Document ID:</div>
                <div class="detail-value">${document.doc_id || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Tracking Number:</div>
                <div class="detail-value"><strong>${document.doc_tracking || 'N/A'}</strong></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">From:</div>
                <div class="detail-value">${document.origin_fname || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Office/School:</div>
                <div class="detail-value">${document.origin_school || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Origin Section:</div>
                <div class="detail-value">${document.origin_section ? document.origin_section.section_description : 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date Posted:</div>
                <div class="detail-value">${formatDateTime(document.datetime_posted)}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Document Type:</div>
                <div class="detail-value">${document.doctype ? document.doctype.doctype_description : 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Description:</div>
                <div class="detail-value">${document.docs_description || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Actions Needed:</div>
                <div class="detail-value">
                    <span class="badge badge-${getActionsBadgeClass(document.actions_needed)}">${document.actions_needed || 'N/A'}</span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="badge badge-${getStatusBadgeClass(document.done)}">${document.done == 1 ? 'Completed' : 'In Progress'}</span>
                </div>
            </div>
        `;

        // Populate timeline
        populateTimeline(data.routes || []);

        showDocumentContent();
    }

    // Populate timeline with route information
    function populateTimeline(routes) {
        if (!routes || routes.length === 0) {
            documentTimeline.innerHTML = '<p class="text-center text-muted">No route information available</p>';
            return;
        }

        let timelineHTML = '';
        routes.forEach((route, index) => {
            const isActive = !route.route_accomplished; // Mark unaccomplished routes as active
            const isAccepted = route.datetime_route_accepted !== "-000001-11-30T00:00:00.000000Z";
            const hasActions = route.actions_datetime !== "-000001-11-30T00:00:00.000000Z";
            
            // Determine the status based on route progress
            let statusText = 'Forwarded';
            let statusClass = 'forwarded';
            
            if (hasActions) {
                statusText = 'Actions Taken';
                statusClass = 'completed';
            } else if (isAccepted) {
                statusText = 'Received';
                statusClass = 'received';
            } else {
                statusText = 'Pending';
                statusClass = 'pending';
            }
            
            timelineHTML += `
                <div class="timeline-item ${isActive ? 'active' : ''} ${statusClass}">
                    <div class="timeline-date">${formatDateTime(route.datetime_forwarded)}</div>
                    <div class="timeline-title">${statusText}</div>
                    <div class="timeline-details">
                        <strong>From:</strong> ${route.route_from || 'N/A'}<br>
                        <strong>From Section:</strong> ${route.route_fromsection || 'N/A'}<br>
                        <strong>To Section:</strong> ${route.route_tosection || 'N/A'}<br>
                        <strong>Purpose:</strong> ${route.route_purpose || 'N/A'}<br>
                        ${route.fwd_remarks ? `<strong>Forwarding Remarks:</strong> ${route.fwd_remarks}<br>` : ''}
                        ${isAccepted ? `<strong>Received:</strong> ${formatDateTime(route.datetime_route_accepted)}<br>` : ''}
                        ${route.received_by ? `<strong>Received By:</strong> ${route.received_by}<br>` : ''}
                        ${route.accepting_remarks ? `<strong>Receiving Remarks:</strong> ${route.accepting_remarks}<br>` : ''}
                        ${hasActions ? `<strong>Actions Date:</strong> ${formatDateTime(route.actions_datetime)}<br>` : ''}
                        ${route.actions_taken ? `<strong>Actions Taken:</strong> ${route.actions_taken}<br>` : ''}
                        ${route.acted_by ? `<strong>Acted By:</strong> ${route.acted_by}<br>` : ''}
                        ${route.end_remarks ? `<strong>End Remarks:</strong> ${route.end_remarks}` : ''}
                    </div>
                </div>
            `;
        });

        documentTimeline.innerHTML = timelineHTML;
    }

    // Generate barcode and QR code
    function generateCodes(trackingNumber) {
        // Generate Barcode
        generateBarcode(trackingNumber);
        
        // Generate QR Code
        generateQRCode(trackingNumber);
    }

    // Generate barcode using JsBarcode library
    function generateBarcode(data) {
        // Check if JsBarcode is available
        if (typeof JsBarcode !== 'undefined') {
            const canvas = document.createElement('canvas');
            barcodeContainer.innerHTML = '';
            barcodeContainer.appendChild(canvas);
            
            try {
                JsBarcode(canvas, data, {
                    format: "CODE128",
                    width: 2,
                    height: 60,
                    displayValue: true,
                    fontSize: 14,
                    margin: 10
                });
            } catch (error) {
                barcodeContainer.innerHTML = '<p class="text-danger">Error generating barcode</p>';
                console.error('Barcode generation error:', error);
            }
        } else {
            // Fallback - load JsBarcode from CDN
            loadScript('https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js')
                .then(() => generateBarcode(data))
                .catch(() => {
                    barcodeContainer.innerHTML = '<p class="text-danger">Failed to load barcode library</p>';
                });
        }
    }

    // Generate QR code using qrcode-generator library (lighter alternative)
    function generateQRCode(data) {
        qrCodeContainer.innerHTML = '';
        
        try {
            // Use qrcode-generator library (lighter than qrcode.js)
            if (typeof qrcode !== 'undefined') {
                const qr = qrcode(0, 'M');
                qr.addData(data);
                qr.make();
                
                // Create QR code as SVG for better print quality
                const qrSvg = qr.createSvgTag({
                    cellSize: 4,
                    margin: 4,
                    scalable: true
                });
                
                qrCodeContainer.innerHTML = qrSvg;
            } else {
                // Fallback: Create simple QR code using online service for display
                const qrImg = document.createElement('img');
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data)}`;
                qrImg.style.width = '150px';
                qrImg.style.height = '150px';
                qrImg.alt = `QR Code: ${data}`;
                qrCodeContainer.appendChild(qrImg);
            }
        } catch (error) {
            console.error('QR code generation error:', error);
            // Fallback: Create simple QR code using online service
            const qrImg = document.createElement('img');
            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data)}`;
            qrImg.style.width = '150px';
            qrImg.style.height = '150px';
            qrImg.alt = `QR Code: ${data}`;
            qrImg.onerror = function() {
                qrCodeContainer.innerHTML = '<p style="color: #666; font-size: 12px;">QR Code: ' + data + '</p>';
            };
            qrCodeContainer.appendChild(qrImg);
        }
    }

    // Load external script dynamically
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    // Utility functions
    function showLoading(show) {
        loading.style.display = show ? 'block' : 'none';
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        setTimeout(() => hideError(), 5000); // Auto-hide after 5 seconds
    }

    function hideError() {
        errorMessage.style.display = 'none';
    }

    function showDocumentContent() {
        documentContent.style.display = 'block';
    }

    function hideDocumentContent() {
        documentContent.style.display = 'none';
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            return dateString; // Return original if parsing fails
        }
    }

    function getStatusBadgeClass(done) {
        return done == 1 ? 'success' : 'warning';
    }

    function getActionsBadgeClass(actions) {
        if (!actions) return 'secondary';
        
        const actionsLower = actions.toLowerCase();
        if (actionsLower.includes('action')) {
            return 'primary';
        } else if (actionsLower.includes('information')) {
            return 'info';
        } else if (actionsLower.includes('urgent')) {
            return 'danger';
        } else {
            return 'secondary';
        }
    }

    function printDocument() {
        const printContainer = document.getElementById('printContainer');
        const printContent = document.getElementById('printContent');
        const printHeader = document.getElementById('printHeader');

        if (!printContainer || !currentDocument) {
            showError('Print container not found or no document loaded');
            return;
        }

        // Create print header
        printHeader.innerHTML = `
            <div class="print-header">
                <h2>Document Tracking Report</h2>
                <p><strong>Tracking Number:</strong> ${currentDocument.document.doc_tracking || 'N/A'}</p>
                <p><strong>Generated on:</strong> ${new Date().toLocaleString()}</p>
            </div>
        `;

        // Clone content for printing
        printContent.innerHTML = `
            <div class="print-codes">
                ${barcodeContainer.innerHTML}
                ${qrCodeContainer.innerHTML}
            </div>
            <div class="print-details">
                <h3>Document Details</h3>
                ${documentDetailsContent.innerHTML}
            </div>
            <div class="print-timeline">
                <h3>Document Route</h3>
                ${documentTimeline.innerHTML}
            </div>
        `;

        // Add print-specific styles
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body * {
                    visibility: hidden;
                }
                #printContainer, #printContainer * {
                    visibility: visible;
                }
                #printContainer {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 20px;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #ccc;
                }
                .print-codes {
                    display: flex;
                    justify-content: space-around;
                    margin-bottom: 20px;
                }
                .print-details, .print-timeline {
                    margin-bottom: 20px;
                }
                .timeline-item {
                    page-break-inside: avoid;
                }
                .btn-print {
                    display: none;
                }
            }
        `;
        document.head.appendChild(style);

        // Trigger print
        window.print();

        // Remove the style element after printing
        setTimeout(() => {
            document.head.removeChild(style);
        }, 1000);
    }

    // Remove the unused generatePrintHTML function since we're now using direct print
    // function generatePrintHTML() { ... } - REMOVED

    // Load barcode and QR code libraries on page load
    Promise.all([
        loadScript('https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js'),
        loadScript('https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js').catch(() => {
            // Fallback to qrcode-generator library
            return loadScript('https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js');
        })
    ]).catch(error => {
        console.warn('Some code generation libraries failed to load, using fallbacks:', error);
    });
});