document.addEventListener('DOMContentLoaded', function () {

    // Validate the registration form before submitting
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirmPassword').value.trim();
            const email = document.getElementById('email').value.trim();

            const usernameRegex = /^[a-zA-Z0-9]{4,20}$/;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            const emailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;

            // Username validation
            if (!usernameRegex.test(username)) {
                alert("Username must be 4-20 characters long and contain only letters and numbers.");
                event.preventDefault();
            }
            // Email validation
            else if (!emailRegex.test(email)) {
                alert("Please enter a valid Gmail address (e.g., example@gmail.com).");
                event.preventDefault();
            }
            // Password validation
            else if (!passwordRegex.test(password)) {
                alert("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.");
                event.preventDefault();
            }
            // Confirm password validation
            else if (password !== confirmPassword) {
                alert("Passwords do not match!");
                event.preventDefault();
            }
        });
    }

    // Validate the login form before submitting
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (username === '' || password === '') {
                alert("Both fields are required!");
                event.preventDefault();
            }
        });
    }

    // Show/hide additional form fields based on user selection
    const userTypeSelect = document.getElementById('userType');
    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', function () {
            const userType = this.value;
            const adminSection = document.getElementById('adminSection');
            if (adminSection) {
                if (userType === 'admin') {
                    adminSection.style.display = 'block';
                } else {
                    adminSection.style.display = 'none';
                }
            }
        });
    }

    // Example for smooth scroll to a section
    const scrollButton = document.querySelector('.scroll-to-form');
    if (scrollButton) {
        scrollButton.addEventListener('click', function () {
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                formContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }



    
});


