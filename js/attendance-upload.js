document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const courseSelect = document.getElementById('courseSelect');
    const dateInput = document.getElementById('dateInput');
    const attendanceTable = document.getElementById('attendanceTable');
    const submitButton = document.getElementById('submitAttendance');
    const studentList = document.getElementById('studentList');

    // Load courses for the logged-in teacher
    loadCourses();

    // Add event listeners
    courseSelect.addEventListener('change', loadStudents);
    submitButton.addEventListener('click', submitAttendance);

    // Function to load courses
    async function loadCourses() {
        try {
            const response = await fetch('api/courses/get_teacher_courses.php');
            const data = await response.json();

            if (data.status === 'success') {
                courseSelect.innerHTML = '<option value="">Select Course</option>';
                data.courses.forEach(course => {
                    courseSelect.innerHTML += `
                        <option value="${course.id}">${course.course_code} - ${course.course_name}</option>
                    `;
                });
            } else {
                showError('Failed to load courses');
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            showError('Error loading courses');
        }
    }

    // Function to load students for selected course
    async function loadStudents() {
        const courseId = courseSelect.value;
        if (!courseId) {
            studentList.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`api/courses/get_course_students.php?courseId=${courseId}`);
            const data = await response.json();

            if (data.status === 'success') {
                studentList.innerHTML = '';
                data.students.forEach(student => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${student.roll_number}</td>
                        <td>${student.first_name} ${student.last_name}</td>
                        <td>
                            <select class="attendance-status" data-student-id="${student.id}">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="attendance-remarks" data-student-id="${student.id}" placeholder="Remarks">
                        </td>
                    `;
                    studentList.appendChild(row);
                });
            } else {
                showError('Failed to load students');
            }
        } catch (error) {
            console.error('Error loading students:', error);
            showError('Error loading students');
        }
    }

    // Function to submit attendance
    async function submitAttendance() {
        const courseId = courseSelect.value;
        const date = dateInput.value;

        if (!courseId || !date) {
            showError('Please select a course and date');
            return;
        }

        // Get attendance data
        const attendanceData = [];
        const statusSelects = document.querySelectorAll('.attendance-status');
        const remarksInputs = document.querySelectorAll('.attendance-remarks');

        statusSelects.forEach((select, index) => {
            attendanceData.push({
                studentId: select.dataset.studentId,
                status: select.value,
                remarks: remarksInputs[index].value
            });
        });

        // Prepare data for submission
        const data = {
            courseId: parseInt(courseId),
            date: date,
            attendanceData: attendanceData,
            markedBy: getCurrentUserId() // You need to implement this function
        };

        try {
            const response = await fetch('api/attendance/upload_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.status === 'success') {
                showSuccess('Attendance recorded successfully');
                // Clear form
                dateInput.value = '';
                courseSelect.value = '';
                studentList.innerHTML = '';
            } else {
                showError(result.message || 'Failed to record attendance');
            }
        } catch (error) {
            console.error('Error submitting attendance:', error);
            showError('Error submitting attendance');
        }
    }

    // Helper function to get current user ID
    function getCurrentUserId() {
        // This should be implemented based on your authentication system
        // For now, returning a placeholder
        return 1; // Replace with actual user ID
    }

    // Helper function to show error message
    function showError(message) {
        // Implement your error display logic here
        alert(message); // Replace with better UI feedback
    }

    // Helper function to show success message
    function showSuccess(message) {
        // Implement your success display logic here
        alert(message); // Replace with better UI feedback
    }
}); 