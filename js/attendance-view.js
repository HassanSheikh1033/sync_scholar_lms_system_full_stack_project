document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const filterForm = document.getElementById('filterForm');
    const courseSelect = document.getElementById('courseSelect');
    const statusSelect = document.getElementById('statusSelect');
    const resetButton = document.getElementById('resetFilters');
    const attendanceList = document.getElementById('attendanceList');
    const noRecords = document.getElementById('noRecords');
    const uploadButton = document.getElementById('uploadButton');
    const csvFile = document.getElementById('csvFile');
    const uploadModal = document.getElementById('uploadModal');
    const uploadCourseSelect = document.getElementById('uploadCourseSelect');

    // Load courses for the logged-in teacher
    loadCourses();

    // Add event listeners
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadAttendance();
    });

    resetButton.addEventListener('click', function() {
        filterForm.reset();
        loadAttendance();
    });

    uploadButton.addEventListener('click', function() {
        uploadCSV();
    });

    // Function to load courses
    async function loadCourses() {
        try {
            const response = await fetch('./handlers/get_teacher_courses.php');
            const data = await response.json();

            if (data.status === 'success') {
                const courseOptions = '<option value="">Select Course</option>' + 
                    data.courses.map(course => `
                        <option value="${course.id}">${course.course_code} - ${course.course_name}</option>
                    `).join('');
                
                courseSelect.innerHTML = courseOptions;
                uploadCourseSelect.innerHTML = courseOptions;

                // Log debug information
                console.log('Courses loaded successfully:', data.debug);
            } else {
                console.error('Error loading courses:', data);
                showError(data.message || 'Failed to load courses');
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            showError('Error loading courses');
        }
    }

    // Function to load attendance records
    async function loadAttendance() {
        const courseId = courseSelect.value;
        const status = statusSelect.value;

        if (!courseId) {
            showError('Please select a course');
            return;
        }

        try {
            // Build query parameters
            const params = new URLSearchParams();
            params.append('courseId', courseId);
            if (status) params.append('status', status);

            const response = await fetch(`api/attendance/get_attendance.php?${params.toString()}`);
            const data = await response.json();

            if (data.status === 'success') {
                displayAttendance(data.data.attendance);
            } else {
                showError(data.message || 'Failed to load attendance records');
            }
        } catch (error) {
            console.error('Error loading attendance:', error);
            showError('Error loading attendance records');
        }
    }

    // Function to upload CSV file
    async function uploadCSV() {
        const file = csvFile.files[0];
        const courseId = uploadCourseSelect.value;
    
        if (!file) {
            showError('Please select a CSV file');
            return;
        }
    
        if (!courseId) {
            showError('Please select a course');
            return;
        }
    
        // Check file type
        if (!file.name.endsWith('.csv')) {
            showError('Please select a valid CSV file');
            return;
        }
    
        try {
            // Read the CSV file
            const text = await file.text();
            const lines = text.split('\n');
            
            // Check if file has headers
            if (lines.length < 2) {
                showError('CSV file must contain headers and at least one row of data');
                return;
            }
    
            // Get headers
            const headers = lines[0].trim().split(',');
            
            // Check if required headers exist
            const requiredHeaders = ['student_id', 'status', 'remarks'];
            const missingHeaders = requiredHeaders.filter(h => !headers.includes(h));
            
            if (missingHeaders.length > 0) {
                showError(`Missing required columns: ${missingHeaders.join(', ')}`);
                return;
            }
    
            let modifiedLines;
            // Only add course_id if it doesn't already exist
            if (!headers.includes('course_id')) {
                // Add course_id to each row
                modifiedLines = lines.map((line, index) => {
                    if (index === 0) {
                        // Add course_id to headers
                        return 'course_id,' + line;
                    }
                    // Add course_id to data rows
                    return courseId + ',' + line;
                });
            } else {
                // If course_id already exists, just use the file as is
                modifiedLines = lines;
            }
    
            // Create new file with course_id
            const modifiedFile = new File([modifiedLines.join('\n')], file.name, {
                type: 'text/csv'
            });
    
            const formData = new FormData();
            formData.append('file', modifiedFile);
    
            uploadButton.disabled = true;
            uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    
            const response = await fetch('api/attendance/upload_attendance_csv.php', {
                method: 'POST',
                body: formData
            });
    
            const data = await response.json();
    
            if (data.status === 'success') {
                showSuccess('Attendance data uploaded successfully');
                // Close modal and reset form
                const modal = bootstrap.Modal.getInstance(uploadModal);
                modal.hide();
                document.getElementById('uploadForm').reset();
                // Reload attendance data
                loadAttendance();
            } else {
                console.error('Upload error details:', data);
                showError(data.message || 'Failed to upload attendance data');
            }
        } catch (error) {
            console.error('Error uploading CSV:', error);
            showError('Error uploading CSV file');
        } finally {
            uploadButton.disabled = false;
            uploadButton.innerHTML = '<i class="fas fa-upload"></i> Upload';
        }
    }

    // Function to display attendance records
    function displayAttendance(attendance) {
        attendanceList.innerHTML = '';
        noRecords.classList.add('d-none');

        if (attendance.length === 0) {
            noRecords.classList.remove('d-none');
            return;
        }

        attendance.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${record.rollNumber}</td>
                <td>${record.studentName}</td>
                <td>${getCourseName(record.courseId)}</td>
                <td>
                    <span class="badge ${getStatusBadgeClass(record.status)}">
                        ${record.status.charAt(0).toUpperCase() + record.status.slice(1)}
                    </span>
                </td>
                <td>${record.remarks || '-'}</td>
            `;
            attendanceList.appendChild(row);
        });
    }

    // Helper function to get status badge class
    function getStatusBadgeClass(status) {
        switch (status.toLowerCase()) {
            case 'present':
                return 'bg-success';
            case 'absent':
                return 'bg-danger';
            case 'late':
                return 'bg-warning';
            default:
                return 'bg-secondary';
        }
    }

    // Helper function to get course name
    function getCourseName(courseId) {
        const option = courseSelect.querySelector(`option[value="${courseId}"]`);
        return option ? option.textContent : 'Unknown Course';
    }

    // Helper function to show error message
    function showError(message) {
        // Create error alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert at the top of the main content
        const mainContent = document.querySelector('main');
        mainContent.insertBefore(alertDiv, mainContent.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Helper function to show success message
    function showSuccess(message) {
        // Create success alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert at the top of the main content
        const mainContent = document.querySelector('main');
        mainContent.insertBefore(alertDiv, mainContent.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});