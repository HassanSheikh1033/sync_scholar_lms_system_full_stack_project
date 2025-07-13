// Roll Number Slip Display JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Load roll number slips on page load
    loadRollNumberSlips();
    
    // Add animation to elements when they come into view
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate__animated:not(.animate__fadeIn)');
        elements.forEach(element => {
            const position = element.getBoundingClientRect();
            if(position.top < window.innerHeight) {
                element.classList.add('animate__fadeIn');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);
    
    // Function to load roll number slips
    async function loadRollNumberSlips() {
        try {
            // Get student ID from URL parameter if available
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('studentId');
            
            // Construct the API URL with student ID parameter if available
            let apiUrl = 'api/rollno/get_rollno_slips.php';
            if (studentId) {
                apiUrl += `?studentId=${studentId}`;
            }
            
            const response = await fetch(apiUrl);
            const result = await response.json();
            
            if (result.status === 'success') {
                displayRollNumberSlips(result.data.rollNoSlips);
            } else {
                console.error('Error loading roll number slips:', result.message);
                document.querySelector('.content-container').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        Error loading roll number slips: ${result.message}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading roll number slips:', error);
            document.querySelector('.content-container').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    An error occurred while loading roll number slips
                </div>
            `;
        }
    }
    
    // Function to display roll number slips
    function displayRollNumberSlips(slips) {
        const container = document.querySelector('.content-container');
        
        if (!slips || slips.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info" role="alert">
                    No roll number slips found
                </div>
            `;
            return;
        }
        
        // Get roll number info from first slip
        const rollNumberInfo = `
            <div class="student-info animate__animated animate__fadeIn animate__delay-1s">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="icon-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-card-heading" viewBox="0 0 16 16">
                                    <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                                    <path d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5v-1z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="info-label">Roll Number</div>
                                <div class="info-value">${slips[0].rollNumber}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar3" viewBox="0 0 16 16">
                                    <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"/>
                                    <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="info-label">Semester</div>
                                <div class="info-value">${slips[0].semester}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="icon-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                    <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                    <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="info-label">Exam Type</div>
                                <div class="info-value">${slips[0].examType.charAt(0).toUpperCase() + slips[0].examType.slice(1)}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm3.854-4.146a.5.5 0 0 0-.708-.708L7.5 10.293 6.146 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l4-4z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="info-label">Status</div>
                                <div class="info-value">${slips[0].status.charAt(0).toUpperCase() + slips[0].status.slice(1)}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Generate HTML for each slip
        const slipsHTML = slips.map(slip => {
            // Determine status badge class
            let statusBadgeClass = '';
            switch(slip.status) {
                case 'verified':
                    statusBadgeClass = 'status-verified';
                    break;
                case 'rejected':
                    statusBadgeClass = 'status-rejected';
                    break;
                default:
                    statusBadgeClass = 'status-pending';
            }

            // Format date
            const uploadDate = new Date(slip.uploadDate);
            const formattedDate = uploadDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            return `
                <div class="slip-card animate__animated animate__fadeIn animate__delay-2s" onclick="openSlipModal('${slip.filePath}', '${slip.examType}', '${slip.semester}')">
                    <div class="slip-header d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">${slip.semester} ${slip.examType.charAt(0).toUpperCase() + slip.examType.slice(1)} Examination</h3>
                        <span class="status-badge ${statusBadgeClass}">${slip.status.charAt(0).toUpperCase() + slip.status.slice(1)}</span>
                    </div>
                    <div class="slip-body">
                        <div class="row mb-4">
                            <div class="col-md-12 mb-4">
                                <h4 class="h6 text-muted mb-3">Roll Number Slip</h4>
                                <div class="embed-responsive">
                                    <iframe src="${slip.filePath}" class="pdf-preview" frameborder="0"></iframe>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h6 text-muted mb-3">Submission Details</h4>
                                <p class="mb-2"><strong>Uploaded:</strong> ${formattedDate}</p>
                                <p class="mb-0"><strong>Comments:</strong> ${slip.comments || 'None'}</p>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h6 text-muted mb-3">Verification Details</h4>
                                <p class="mb-2"><strong>Status:</strong> ${slip.status.charAt(0).toUpperCase() + slip.status.slice(1)}</p>
                                <p class="mb-0"><strong>Verification Date:</strong> ${slip.verificationDate ? new Date(slip.verificationDate).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : 'Pending'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="slip-footer d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0"><small class="text-muted">Generated on: ${formattedDate}</small></p>
                        </div>
                        <div>
                            <a href="${slip.filePath}" class="btn btn-sm btn-outline-primary me-2" download>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-1" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                Download PDF
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" onclick="printPDF('${slip.filePath}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer me-1" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                </svg>
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Generate timeline HTML
        const timelineHTML = generateTimeline(slips[0]);

        // Add modal HTML for slip viewing
        const modalHTML = `
            <div class="modal fade" id="slipModal" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="slipModalLabel">Roll Number Slip</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <iframe id="slipModalFrame" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a id="slipModalDownload" href="" class="btn btn-primary" download>Download</a>
                            <button type="button" class="btn btn-info" onclick="printModalPDF()">Print</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Update container with all HTML
        container.innerHTML = rollNumberInfo + slipsHTML + timelineHTML + modalHTML;
    }

    // Function to generate timeline HTML
    function generateTimeline(slip) {
        const uploadDate = new Date(slip.uploadDate);
        const uploadDateFormatted = uploadDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let verificationDateFormatted = 'Pending';
        if (slip.verificationDate) {
            const verificationDate = new Date(slip.verificationDate);
            verificationDateFormatted = verificationDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        return `
            <div class="timeline animate__animated animate__fadeIn animate__delay-3s">
                <h4 class="mb-4">Roll No Slip Status Timeline</h4>
                <div class="timeline-item">
                    <div class="timeline-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">${uploadDateFormatted}</div>
                        <h5 class="timeline-title">Roll No Slip Generated</h5>
                        <p class="timeline-text">Your roll number slip has been uploaded and is ready for verification.</p>
                    </div>
                </div>
                ${slip.status !== 'pending' ? `
                <div class="timeline-item">
                    <div class="timeline-dot">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">${verificationDateFormatted}</div>
                        <h5 class="timeline-title">Roll No Slip ${slip.status.charAt(0).toUpperCase() + slip.status.slice(1)}</h5>
                        <p class="timeline-text">Your roll number slip has been ${slip.status} by the examination department.</p>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    }

    // Function to print PDF
    window.printPDF = function(pdfPath) {
        const printWindow = window.open(pdfPath, '_blank');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        });
    };
});

// Function to open slip in modal
function openSlipModal(filePath, examType, semester) {
    const modal = new bootstrap.Modal(document.getElementById('slipModal'));
    document.getElementById('slipModalLabel').textContent = `${semester} ${examType.charAt(0).toUpperCase() + examType.slice(1)} Examination`;
    document.getElementById('slipModalFrame').src = filePath;
    document.getElementById('slipModalDownload').href = filePath;
    modal.show();
}

// Function to print PDF from modal
function printModalPDF() {
    const iframe = document.getElementById('slipModalFrame');
    const printWindow = window.open(iframe.src, '_blank');
    printWindow.addEventListener('load', function() {
        printWindow.print();
    });
}