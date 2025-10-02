document.addEventListener('DOMContentLoaded', function() {
    const trackingInput = document.getElementById('trackingNumber');
    const searchBtn = document.getElementById('searchBtn');
    const scanBtn = document.getElementById('scanBtn');
    const printBtn = document.getElementById('printBtn');
    const grabBtn = document.getElementById('grabBtn');                                                                                                                 
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('errorMessage');
    const documentContent = document.getElementById('documentContent');
    const documentDetailsContent = document.getElementById('documentDetailsContent');
    const documentTimeline = document.getElementById('documentTimeline');
    const barcodeContainer = document.getElementById('barcodeContainer');
    const qrCodeContainer = document.getElementById('qrCodeContainer');

    // Scanner elements
    const scannerModal = document.getElementById('scannerModal');
    const closeScannerBtn = document.getElementById('closeScannerBtn');
    const toggleScannerBtn = document.getElementById('toggleScannerBtn');
    const switchCameraBtn = document.getElementById('switchCameraBtn');
    const scannerVideo = document.getElementById('scanner-video');
    const scannerResult = document.getElementById('scannerResult');

    let currentDocument = null;
    let stream = null;
    let scanning = false;
    let currentCamera = 'environment'; // 'user' for front, 'environment' for back
    let scanInterval = null;

    // Check for tracking number in URL parameters on page load
    const urlParams = new URLSearchParams(window.location.search);
    const trackingNumber = urlParams.get('tracking');
    
    if (trackingNumber) {
        trackingInput.value = trackingNumber;
        // Small delay to ensure DOM is fully loaded before triggering search
        setTimeout(() => {
            searchDocument(trackingNumber);
        }, 100);
    }

    // Search button event listener
    searchBtn.addEventListener('click', function() {
        const trackingNumber = trackingInput.value.trim();
        if (trackingNumber) {
            searchDocument(trackingNumber);
            // Update URL without page reload
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tracking', trackingNumber);
            window.history.pushState({}, '', newUrl);
        } else {
            showError('Please enter a tracking number');
        }
    });

    // Scan button event listener
    scanBtn.addEventListener('click', function() {
        openScanner();
    });

    // Scanner modal event listeners
    closeScannerBtn.addEventListener('click', closeScanner);
    scannerModal.addEventListener('click', function(e) {
        if (e.target === scannerModal) {
            closeScanner();
        }
    });

    // Toggle scanner
    toggleScannerBtn.addEventListener('click', function() {
        if (scanning) {
            stopScanning();
        } else {
            startScanning();
        }
    });

    // grabDocument button event listener
    grabBtn.addEventListener('click', function() {
        const trackingNumber = document.getElementById('documentid').value.trim();
        if (trackingNumber) {
            grabDocument(trackingNumber)
                .then(response => {
                    showSuccess('Document grabbed successfully. Check Incoming Documents.');
                })
                .catch(error => {
                    showError(error.message);
                });
        } else {
            showError('Please enter a tracking number');
        }
    });

    // Switch camera
    switchCameraBtn.addEventListener('click', function() {
        currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
        if (scanning) {
            stopScanning();
            setTimeout(() => startScanning(), 500);
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

    // Scanner Functions
    function openScanner() {
        scannerModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        updateScannerResult('Initializing camera...', '');
        
        // Auto-start scanner after modal opens
        setTimeout(() => {
            startScanning();
        }, 300);
    }

    function closeScanner() {
        stopScanning();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        scannerModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    async function startScanning() {
        try {
            updateScannerResult('Starting camera...', '');
            
            // Stop existing stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Request camera access
            const constraints = {
                video: {
                    facingMode: currentCamera,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            scannerVideo.srcObject = stream;
            
            // Wait for video to load
            await new Promise((resolve) => {
                scannerVideo.addEventListener('loadedmetadata', resolve, { once: true });
            });

            scanning = true;
            toggleScannerBtn.innerHTML = '<i class="fas fa-stop"></i> Stop Scanner';
            toggleScannerBtn.classList.add('active');
            updateScannerResult('Scanner active - Position code in camera view', '');

            // Start continuous scanning
            startContinuousScanning();

        } catch (error) {
            console.error('Error starting camera:', error);
            handleCameraError(error);
        }
    }

    function stopScanning() {
        scanning = false;
        
        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }
        
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        scannerVideo.srcObject = null;
        
        toggleScannerBtn.innerHTML = '<i class="fas fa-play"></i> Start Scanner';
        toggleScannerBtn.classList.remove('active');
        updateScannerResult('Scanner stopped', '');
    }

    function startContinuousScanning() {
        if (!scanning) return;

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        scanInterval = setInterval(() => {
            if (!scanning || !scannerVideo.videoWidth || !scannerVideo.videoHeight) {
                return;
            }

            // Set canvas size to match video
            canvas.width = scannerVideo.videoWidth;
            canvas.height = scannerVideo.videoHeight;

            // Draw current video frame to canvas
            context.drawImage(scannerVideo, 0, 0, canvas.width, canvas.height);

            // Try to scan QR code first
            scanQRCode(canvas).then(result => {
                if (result) {
                    handleScanResult(result, 'QR Code');
                    return;
                }
                
                // If no QR code found, try barcode scanning
                return scanBarcode(canvas);
            }).then(result => {
                if (result) {
                    handleScanResult(result, 'Barcode');
                }
            }).catch(error => {
                // Ignore scanning errors - they're expected when no code is visible
            });

        }, 500); // Scan every 500ms
    }

    // QR Code scanning using jsQR library
    async function scanQRCode(canvas) {
        try {
            // Use jsQR if available
            if (typeof jsQR !== 'undefined') {
                const context = canvas.getContext('2d');
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                return code ? code.data : null;
            }
            
            // Fallback: Use ZXing library
            if (typeof ZXing !== 'undefined') {
                const codeReader = new ZXing.BrowserQRCodeReader();
                try {
                    const result = await codeReader.decodeFromCanvas(canvas);
                    return result.text;
                } catch (error) {
                    return null;
                }
            }
            
            return null;
        } catch (error) {
            return null;
        }
    }

    // Barcode scanning using QuaggaJS
    async function scanBarcode(canvas) {
        return new Promise((resolve) => {
            if (typeof Quagga === 'undefined') {
                resolve(null);
                return;
            }

            try {
                Quagga.decodeSingle({
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "code_39_reader",
                            "code_39_vin_reader",
                            "codabar_reader",
                            "upc_reader",
                            "upc_e_reader",
                            "i2of5_reader"
                        ]
                    },
                    locate: true,
                    src: canvas.toDataURL()
                }, function(result) {
                    if (result && result.codeResult) {
                        resolve(result.codeResult.code);
                    } else {
                        resolve(null);
                    }
                });
            } catch (error) {
                resolve(null);
            }
        });
    }

    function handleScanResult(code, type) {
        if (!code || code.trim() === '' || !scanning) {
            return;
        }

        console.log(`${type} detected:`, code);
        
        // Stop scanning to prevent multiple detections
        scanning = false;
        
        // Update the search input
        trackingInput.value = code.trim();
        
        // Show success message
        updateScannerResult(`✅ ${type} detected: ${code}`, 'success');
        
        // Auto-close scanner and trigger search after a short delay
        setTimeout(() => {
            closeScanner();
            searchBtn.click();
        }, 1500);
    }

    function updateScannerResult(message, type = '') {
        if (scannerResult) {
            scannerResult.textContent = message;
            scannerResult.className = `scanner-result ${type}`;
        }
    }

    function handleCameraError(error) {
        let message = 'Camera access denied or unavailable.';
        
        if (error.name === 'NotAllowedError') {
            message = 'Camera permission denied. Please allow camera access and try again.';
        } else if (error.name === 'NotFoundError') {
            message = 'No camera found on this device.';
        } else if (error.name === 'NotSupportedError') {
            message = 'Camera not supported in this browser. Please use Chrome, Firefox, or Safari.';
        } else if (error.name === 'NotReadableError') {
            message = 'Camera is being used by another application.';
        } else if (error.name === 'OverconstrainedError') {
            message = 'Camera constraints not satisfied. Trying with basic settings...';
            // Retry with basic constraints
            setTimeout(() => {
                currentCamera = 'environment';
                startScanning();
            }, 1000);
            return;
        }
        
        updateScannerResult(message, 'error');
    }

    // Load required libraries
    function loadScannerLibraries() {
        const libraries = [
            {
                url: 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js',
                name: 'jsQR'
            },
            {
                url: 'https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js',
                name: 'Quagga'
            }
        ];

        return Promise.allSettled(libraries.map(lib => loadScript(lib.url)));
    }

    // Your existing search document function
    function searchDocument(trackingNumber) {
        showLoading(true);
        hideError();
        hideDocumentContent();

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
        documentDetailsContent.innerHTML = `
            <div class="detail-row">
                <div class="detail-label">Document ID:</div>
                <div class="detail-value">${document.doc_id || 'N/A'}</div>
                <input type="hidden" id="documentid" name="documentid" value="${document.doc_id || 'N/A'}">
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

        populateTimeline(data.routes || []);
        showDocumentContent();
    }

    function populateTimeline(routes) {
        if (!routes || routes.length === 0) {
            documentTimeline.innerHTML = '<p class="text-center text-muted">No route information available</p>';
            return;
        }

        let timelineHTML = '';
        routes.forEach((route, index) => {
            const isActive = !route.route_accomplished;
            const isAccepted = route.datetime_route_accepted !== "-000001-11-30T00:00:00.000000Z";
            const hasActions = route.actions_datetime !== "-000001-11-30T00:00:00.000000Z";
            
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
                        ${hasActions ? `<strong>Actions Date:</strong> ${formatDateTime(route.actions_datetime)=="Jan 1, 1970, 12:00 AM" ? 'Not yet Accepted':formatDateTime(route.actions_datetime)}<br>` : ''}
                        ${route.actions_taken ? `<strong>Actions Taken:</strong> ${route.actions_taken}<br>` : ''}
                        ${route.acted_by ? `<strong>Acted By:</strong> ${route.acted_by}<br>` : ''}
                        ${route.end_remarks ? `<strong>End Remarks:</strong> ${route.end_remarks}` : ''}
                    </div>
                </div>
            `;
        });

        documentTimeline.innerHTML = timelineHTML;
    }

    // Utility functions
    function generateCodes(trackingNumber) {
        generateBarcode(trackingNumber);
        generateQRCode(trackingNumber);
    }

    function generateBarcode(data) {
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
            }
        } else {
            loadScript('https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js')
                .then(() => generateBarcode(data))
                .catch(() => {
                    barcodeContainer.innerHTML = '<p class="text-danger">Failed to load barcode library</p>';
                });
        }
    }

    function generateQRCode(data) {
        qrCodeContainer.innerHTML = '';
        
        try {
            if (typeof qrcode !== 'undefined') {
                const qr = qrcode(0, 'M');
                qr.addData(data);
                qr.make();
                
                const qrSvg = qr.createSvgTag({
                    cellSize: 4,
                    margin: 4,
                    scalable: true
                });
                
                qrCodeContainer.innerHTML = qrSvg;
            } else {
                const qrImg = document.createElement('img');
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data)}`;
                qrImg.style.width = '150px';
                qrImg.style.height = '150px';
                qrImg.alt = `QR Code: ${data}`;
                qrCodeContainer.appendChild(qrImg);
            }
        } catch (error) {
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

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function showLoading(show) {
        loading.style.display = show ? 'block' : 'none';
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        setTimeout(() => hideError(), 5000);
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
            return dateString;
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

        printHeader.innerHTML = `
            <div class="print-header">
                <h2>Document Tracking Report</h2>
                <p><strong>Tracking Number:</strong> ${currentDocument.document.doc_tracking || 'N/A'}</p>
                <p><strong>Generated on:</strong> ${new Date().toLocaleString()}</p>
            </div>
        `;

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

        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body * { visibility: hidden; }
                #printContainer, #printContainer * { visibility: visible; }
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
                .print-details, .print-timeline { margin-bottom: 20px; }
                .timeline-item { page-break-inside: avoid; }
                .btn-print { display: none; }
            }
        `;
        document.head.appendChild(style);

        window.print();

        setTimeout(() => {
            document.head.removeChild(style);
        }, 1000);
    }

    // Initialize scanner libraries on load
    loadScannerLibraries().then(() => {
        console.log('Scanner libraries loaded');
    }).catch(error => {
        console.warn('Some scanner libraries failed to load:', error);
    });

    // Load barcode and QR code generation libraries
    Promise.all([
        loadScript('https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js'),
        loadScript('https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js').catch(() => {
            return loadScript('https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js');
        })
    ]).catch(error => {
        console.warn('Some code generation libraries failed to load:', error);
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
});

    async function grabDocument(actionId) {
        if (!isOnline()) {
            throw new Error('Cannot keep document while offline');
        }
    
        const token = localStorage.getItem('auth_token');
        if (!token) {
            throw new Error('Authentication required');
        }
    
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }
    
            // Log the request details for debugging
            console.log('Sending keep request:', {
                url: `/api/documents/routes/${actionId}/grab`,
                actionId
            });
    
            const response = await fetch(`/api/documents/routes/${actionId}/grab`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'include', // Include cookies
                body: JSON.stringify({
                    actionid: parseInt(actionId)
                })
            });
    
            // Log the raw response for debugging
            console.log('Raw response:', response);
    
            const responseData = await response.json();
            console.log('Response data:', responseData);
    
            if (!response.ok) {
                throw new Error(responseData.message || responseData.error || 'Failed to grab document');
            }
    
            return responseData;
    
        } catch (error) {
            console.error('Error in grabbing Document:', error);
            throw new Error(`Error grabbing document: ${error.message}`);
        }
    }

    async function isOnline() {
        return navigator.onLine;
    }

    async function showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed end-0 m-3';
        toast.style.top = '70px'; // 👈 move below navbar (adjust as needed)
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
