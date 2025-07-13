document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const courseList = document.getElementById('courseList');
    const noCourses = document.getElementById('noCourses');
    const courseForm = document.getElementById('courseForm');
    const courseModal = document.getElementById('courseModal');
    const deleteModal = document.getElementById('deleteModal');
    const saveCourseBtn = document.getElementById('saveCourseBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Form fields
    const courseIdField = document.getElementById('courseId');
    const courseCodeField = document.getElementById('courseCode');
    const courseNameField = document.getElementById('courseName');
    const descriptionField = document.getElementById('description');
    const semesterField = document.getElementById('semester');
    const creditsField = document.getElementById('credits');
    const deleteIdField = document.getElementById('deleteId');
    
    // Bootstrap modal instances
    let courseModalInstance;
    let deleteModalInstance;
    
    // Initialize modals
    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id === 'courseModal') {
            courseModalInstance = bootstrap.Modal.getInstance(courseModal);
        } else if (event.target.id === 'deleteModal') {
            deleteModalInstance = bootstrap.Modal.getInstance(deleteModal);
        }
    });
    
    // Load courses on page load
    loadCourses();
    
    // Add event listeners
    saveCourseBtn.addEventListener('click', saveCourse);
    confirmDeleteBtn.addEventListener('click', deleteCourse);
    
    // Reset form when modal is closed
    courseModal.addEventListener('hidden.bs.modal', function () {
        courseForm.reset();
        courseIdField.value = '';
        document.getElementById('courseModalLabel').textContent = 'Add New Course';
    });
    
    // Function to load courses
    async function loadCourses() {
        try {
            const response = await fetch('./api/courses/get_teacher_courses.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                displayCourses(data.courses);
            } else {
                showError(data.message || 'Failed to load courses');
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            showError('Error loading courses');
        }
    }
    
    // Function to display courses
    function displayCourses(courses) {
        courseList.innerHTML = '';
        noCourses.classList.add('d-none');
        
        if (courses.length === 0) {
            noCourses.classList.remove('d-none');
            return;
        }
        
        courses.forEach(course => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${course.id}</td>
                <td>${course.course_code}</td>
                <td>${course.course_name}</td>
                <td>${course.description || '-'}</td>
                <td>${course.semester}</td>
                <td>${course.credits}</td>
                <td>${formatDate(course.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${course.id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${course.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            `;
            
            courseList.appendChild(row);
        });
        
        // Add event listeners to edit and delete buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => editCourse(btn.getAttribute('data-id')));
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => showDeleteModal(btn.getAttribute('data-id')));
        });
    }
    
    // Function to save course (create or update)
    async function saveCourse() {
        if (!courseForm.checkValidity()) {
            courseForm.reportValidity();
            return;
        }
        
        const courseId = courseIdField.value;
        const isEdit = courseId !== '';
        
        const courseData = {
            course_code: courseCodeField.value,
            course_name: courseNameField.value,
            description: descriptionField.value,
            semester: semesterField.value,
            credits: creditsField.value
        };
        
        if (isEdit) {
            courseData.id = courseId;
        }
        
        try {
            saveCourseBtn.disabled = true;
            saveCourseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const url = isEdit ? './api/courses/update_course.php' : './api/courses/add_course.php';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(courseData)
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showSuccess(isEdit ? 'Course updated successfully' : 'Course added successfully');
                courseModalInstance.hide();
                loadCourses();
            } else {
                showError(data.message || 'Failed to save course');
            }
        } catch (error) {
            console.error('Error saving course:', error);
            showError('Error saving course');
        } finally {
            saveCourseBtn.disabled = false;
            saveCourseBtn.innerHTML = '<i class="fas fa-save"></i> Save Course';
        }
    }
    
    // Function to edit course
    async function editCourse(id) {
        try {
            const response = await fetch(`./api/courses/get_course.php?id=${id}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                const course = data.course;
                
                // Fill form with course data
                courseIdField.value = course.id;
                courseCodeField.value = course.course_code;
                courseNameField.value = course.course_name;
                descriptionField.value = course.description || '';
                semesterField.value = course.semester;
                creditsField.value = course.credits;
                
                // Update modal title
                document.getElementById('courseModalLabel').textContent = 'Edit Course';
                
                // Show modal
                const modal = new bootstrap.Modal(courseModal);
                modal.show();
            } else {
                showError(data.message || 'Failed to load course details');
            }
        } catch (error) {
            console.error('Error loading course details:', error);
            showError('Error loading course details');
        }
    }
    
    // Function to show delete confirmation modal
    function showDeleteModal(id) {
        deleteIdField.value = id;
        const modal = new bootstrap.Modal(deleteModal);
        modal.show();
    }
    
    // Function to delete course
    async function deleteCourse() {
        const id = deleteIdField.value;
        
        try {
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            
            const response = await fetch('./api/courses/delete_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showSuccess('Course deleted successfully');
                deleteModalInstance.hide();
                loadCourses();
            } else {
                showError(data.message || 'Failed to delete course');
            }
        } catch (error) {
            console.error('Error deleting course:', error);
            showError('Error deleting course');
        } finally {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
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