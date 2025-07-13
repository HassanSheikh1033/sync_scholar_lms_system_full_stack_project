// Roll Number Slip Upload JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Show filename when file is selected
    document.getElementById('rollNoSlip').addEventListener('change', function(e) {
        const fileName = e.target.files[0].name;
        const fileHelp = document.getElementById('fileHelp');
        fileHelp.textContent = 'Selected file: ' + fileName;
    });

    // Handle form submission
    const form = document.getElementById('rollNoSlipForm');
    console.log('Form found:', form);
    
    if (!form) {
        console.error('Form not found!');
        return;
    }
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Form submission intercepted');
        
        // Get form data
        const roll_number = document.getElementById('roll_number').value;
        const examType = document.getElementById('examType').value;
        const semester = document.getElementById('semester').value;
        const rollNoSlip = document.getElementById('rollNoSlip').files[0];
        const comments = document.getElementById('comments').value;
        
        // Debug logging
        console.log('Form data:', {
            roll_number,
            examType,
            semester,
            rollNoSlip: rollNoSlip ? rollNoSlip.name : 'No file',
            comments
        });
        
        // Validate form data
        if (!roll_number || !examType || !semester || !rollNoSlip) {
            alert('Please fill in all required fields and select a PDF file');
            console.log('Validation failed:', { roll_number, examType, semester, hasFile: !!rollNoSlip });
            return;
        }
        
        // Validate file type
        if (rollNoSlip.type !== 'application/pdf') {
            alert('Please upload a PDF file');
            return;
        }
        
        // Validate file size (5MB max)
        if (rollNoSlip.size > 5 * 1024 * 1024) {
            alert('File size exceeds the limit of 5MB');
            return;
        }
        
        // Show loading animation
        const button = document.querySelector('.btn-upload');
        button.classList.remove('animate__pulse', 'animate__infinite');
        button.classList.add('animate__bounceOut');
        button.disabled = true;
        
        try {
            // Create form data
            const formData = new FormData();
            formData.append('roll_number', roll_number);
            formData.append('examType', examType);
            formData.append('semester', semester);
            formData.append('rollNoSlip', rollNoSlip);
            formData.append('comments', comments);
            
            // Debug: Log FormData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value);
            }
            
            // Send request to API
            const response = await fetch('api/rollno/upload_rollno_slip.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            const result = await response.json();
            console.log('Response result:', result);
            
            if (result.status === 'success') {
                // Show success message
                alert('Roll Number Slip uploaded successfully!');
                form.reset();
                document.getElementById('fileHelp').textContent = 'Supported format: PDF (.pdf) only. Max size: 5MB';
            } else {
                // Show error message
                alert('Error: ' + result.message);
                console.error('Upload error:', result);
            }
        } catch (error) {
            // Show error message
            alert('An error occurred while uploading the roll number slip');
            console.error('Upload error:', error);
        } finally {
            // Restore button
            setTimeout(() => {
                button.classList.remove('animate__bounceOut');
                button.classList.add('animate__bounceIn');
                button.disabled = false;
                setTimeout(() => {
                    button.classList.add('animate__pulse', 'animate__infinite');
                }, 1000);
            }, 1000);
        }
    });
});