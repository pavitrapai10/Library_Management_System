// This will run when the page has finished loading
document.addEventListener('DOMContentLoaded', function () {

    // Utility function to check if the email format is valid
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Utility function to check password strength
    function isValidPassword(password) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return passwordRegex.test(password);
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
            } else if (!isValidEmail(username)) {
                alert("Please enter a valid email address!");
                event.preventDefault();
            }
        });
    }

    // Validate the registration form before submitting
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirmPassword').value.trim();

            if (username === '' || password === '' || confirmPassword === '') {
                alert("All fields are required!");
                event.preventDefault();
            } else if (!isValidEmail(username)) {
                alert("Please enter a valid email address!");
                event.preventDefault();
            } else if (!isValidPassword(password)) {
                alert("Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.");
                event.preventDefault();
            } else if (password !== confirmPassword) {
                alert("Passwords do not match!");
                event.preventDefault();
            }
        });
    }

    // Function to display error messages dynamically (for example, after login failure)
    function showErrorMessage(message) {
        const errorDiv = document.getElementById('error-message');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    // Example to show/hide additional form fields based on user selection (e.g. user type)
    const userTypeSelect = document.getElementById('userType');
    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', function () {
            const userType = this.value;
            const adminSection = document.getElementById('adminSection');
            if (adminSection) {
                adminSection.style.display = (userType === 'admin') ? 'block' : 'none';
            }
        });
    }

    // Fetch user data for the admin dashboard without reloading the page (AJAX)
    function fetchUserData() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_users.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                const users = JSON.parse(xhr.responseText);
                const usersList = document.getElementById('usersList');
                if (usersList) {
                    usersList.innerHTML = '';
                    users.forEach(function (user) {
                        const li = document.createElement('li');
                        li.textContent = user.username + ' - ' + user.first_name + ' ' + user.last_name;
                        usersList.appendChild(li);
                    });
                }
            }
        };
        xhr.send();
    }

    // Call the function when the page loads or on specific user interaction (e.g., button click)
    window.onload = fetchUserData;

    // Example for smooth scroll to a section (for example, scroll to form)
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
