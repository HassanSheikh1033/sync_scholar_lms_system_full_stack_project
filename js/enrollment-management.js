document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const enrollmentList = document.getElementById('enrollmentList');
    const noEnrollments = document.getElementById('noEnrollments');
    const enrollmentForm = document.getElementById('enrollmentForm');
    const enrollmentModal = document.getElementById('enrollmentModal');
    const deleteModal = document.getElementById('deleteModal');
    const saveEnrollmentBtn = document.getElementById('saveEnrollmentBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const courseFilterSelect = document.getElementById('courseFilterSelect');
    const statusFilterSelect = document.getElementById('statusFilterSelect');
    
    // Form fields
    const enrollmentIdField = document.getElementById('enrollmentId');
    const courseSelect = document.getElementById('courseSelect');
    const studentSelect = document.getElementById('studentSelect');
    const statusSelect = document.getElementById('statusSelect');
    const deleteIdField = document.getElementById('deleteId');
    
    // Bootstrap modal instances
    let enrollmentModalInstance;
    let deleteModalInstance;
    
    // Initialize modals
    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id === 'enrollmentModal') {
            enrollmentModalInstance = bootstrap.Modal.getInstance(enrollmentModal);
        } else if (event.target.id === 'deleteModal') {
            deleteModalInstance = bootstrap.Modal.getInstance(deleteModal);
        }
    });
    
    // Load courses and enrollments on page load
    loadCourses();
    loadStudents();
    loadEnrollments();
    
    // Add event listeners
    saveEnrollmentBtn.addEventListener('click', saveEnrollment);
    confirmDeleteBtn.addEventListener('click', deleteEnrollment);
    courseFilterSelect.addEventListener('change', loadEnrollments);
    statusFilterSelect.addEventListener('change', loadEnrollments);
    
    // Reset form when modal is closed
    enrollmentModal.addEventListener('hidden.bs.modal', function () {
        enrollmentForm.reset();
        enrollmentIdField.value = '';
        document.getElementById('enrollmentModalLabel').textContent = 'Add New Enrollment';
    });
    
    // Function to load courses
    async function loadCourses() {
        try {
            const response = await fetch('./api/courses/get_teacher_courses.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                const courseOptions = '<option value="">Select Course</option>' + 
                    data.courses.map(course => `
                        <option value="${course.id}">${course.course_code} - ${course.course_name}</option>
                    `).join('');
                
                courseSelect.innerHTML = courseOptions;
                courseFilterSelect.innerHTML = '<option value="">All Courses</option>' + 
                    data.courses.map(course => `
                        <option value="${course.id}">${course.course_code} - ${course.course_name}</option>
                    `).join('');
            } else {
                showError(data.message || 'Failed to load courses');
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            showError('Error loading courses');
        }
    }
    
    // Function to load students
    async function loadStudents() {
        try {
            const response = await fetch('./api/courses/get_all_students.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                const studentOptions = '<option value="">Select Student</option>' + 
                    data.students.map(student => `
                        <option value="${student.id}">${student.username} - ${student.first_name} ${student.last_name}</option>
                    `).join('');
                
                studentSelect.innerHTML = studentOptions;
            } else {
                showError(data.message || 'Failed to load students');
            }
        } catch (error) {
            console.error('Error loading students:', error);
            showError('Error loading students');
        }
    }
    
    // Function to load enrollments
    async function loadEnrollments() {
        try {
            const courseId = courseFilterSelect.value;
            const status = statusFilterSelect.value;
            
            // Build query parameters
            const params = new URLSearchParams();
            if (courseId) params.append('courseId', courseId);
            if (status) params.append('status', status);
            
            const response = await fetch(`./api/courses/get_enrollments.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                displayEnrollments(data.enrollments);
            } else {
                showError(data.message || 'Failed to load enrollments');
            }
        } catch (error) {
            console.error('Error loading enrollments:', error);
            showError('Error loading enrollments');
        }
    }
    
    // Function to display enrollments
    function displayEnrollments(enrollments) {
        enrollmentList.innerHTML = '';
        noEnrollments.classList.add('d-none');
        
        if (enrollments.length === 0) {
            noEnrollments.classList.remove('d-none');
            return;
        }
        
        enrollments.forEach(enrollment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${enrollment.id}</td>
                <td>${enrollment.course_id}</td>
                <td>${enrollment.course_name}</td>
                <td>${enrollment.student_id}</td>
                <td>${enrollment.student_name}</td>
                <td>
                    <span class="badge ${getStatusBadgeClass(enrollment.status)}">
                        ${enrollment.status}
                    </span>
                </td>
                <td>${formatDate(enrollment.enrollment_date)}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${enrollment.id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${enrollment.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            `;
            
            enrollmentList.appendChild(row);
        });
        
        // Add event listeners to edit and delete buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => editEnrollment(btn.getAttribute('data-id')));
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => showDeleteModal(btn.getAttribute('data-id')));
        });
    }
    
    // Function to save enrollment (create or update)
    async function saveEnrollment() {
        if (!enrollmentForm.checkValidity()) {
            enrollmentForm.reportValidity();
            return;
        }
        
        const enrollmentId = enrollmentIdField.value;
        const isEdit = enrollmentId !== '';
        
        const enrollmentData = {
            course_id: courseSelect.value,
            student_id: studentSelect.value,
            status: statusSelect.value
        };
        
        if (isEdit) {
            enrollmentData.id = enrollmentId;
        }
        
        try {
            saveEnrollmentBtn.disabled = true;
            saveEnrollmentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const url = isEdit ? './api/courses/update_enrollment.php' : './api/courses/enroll_student.php';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(enrollmentData)
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showSuccess(isEdit ? 'Enrollment updated successfully' : 'Student enrolled successfully');
                enrollmentModalInstance.hide();
                loadEnrollments();
            } else {
                showError(data.message || 'Failed to save enrollment');
            }
        } catch (error) {
            console.error('Error saving enrollment:', error);
            showError('Error saving enrollment');
        } finally {
            saveEnrollmentBtn.disabled = false;
            saveEnrollmentBtn.innerHTML = '<i class="fas fa-save"></i> Save Enrollment';
        }
    }
    
    // Function to edit enrollment
    async function editEnrollment(id) {
        try {
            const response = await fetch(`./api/courses/get_enrollment.php?id=${id}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                const enrollment = data.enrollment;
                
                // Fill form with enrollment data
                enrollmentIdField.value = enrollment.id;
                courseSelect.value = enrollment.course_id;
                studentSelect.value = enrollment.student_id;
                statusSelect.value = enrollment.status;
                
                // Update modal title
                document.getElementById('enrollmentModalLabel').textContent = 'Edit Enrollment';
                
                // Show modal
                const modal = new bootstrap.Modal(enrollmentModal);
                modal.show();
            } else {
                showError(data.message || 'Failed to load enrollment details');
            }
        } catch (error) {
            console.error('Error loading enrollment details:', error);
            showError('Error loading enrollment details');
        }
    }
    
    // Function to show delete confirmation modal
    function showDeleteModal(id) {
        deleteIdField.value = id;
        const modal = new bootstrap.Modal(deleteModal);
        modal.show();
    }
    
    // Function to delete enrollment
    async function deleteEnrollment() {
        const id = deleteIdField.value;
        
        try {
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            
            const response = await fetch('./api/courses/delete_enrollment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showSuccess('Enrollment deleted successfully');
                deleteModalInstance.hide();
                loadEnrollments();
            } else {
                showError(data.message || 'Failed to delete enrollment');
            }
        } catch (error) {
            console.error('Error deleting enrollment:', error);
            showError('Error deleting enrollment');
        } finally {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
    }
    
    // Helper function to get status badge class
    function getStatusBadgeClass(status) {
        switch (status.toLowerCase()) {
            case 'active':
                return 'bg-success';
            case 'completed':
                return 'bg-primary';
            case 'dropped':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }
    
    // Helper function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString();
    }
    
    // Function to show success message
    function showSuccess(message) {
        // You can implement this using a toast or alert
        alert(message);
    }
    
    // Function to show error message
    function showError(message) {
        // You can implement this using a toast or alert
        alert(message);
    }
});