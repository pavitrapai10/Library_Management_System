// This will run when the page has finished loading
document.addEventListener('DOMContentLoaded', function() {

    // Validate the login form before submitting
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (username === '' || password === '') {
                alert("Both fields are required!");
                event.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }

    // Validate the registration form before submitting
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (username === '' || password === '' || confirmPassword === '') {
                alert("All fields are required!");
                event.preventDefault(); // Prevent form submission
            } else if (password !== confirmPassword) {
                alert("Passwords do not match!");
                event.preventDefault(); // Prevent form submission
            }
        });
    }

    // Function to display error messages dynamically (for example, after login failure)
    function showErrorMessage(message) {
        const errorDiv = document.getElementById('error-message');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block'; // Make error message visible
        }
    }

    // Example to show/hide additional form fields based on user selection (e.g. user type)
    const userTypeSelect = document.getElementById('userType');
    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', function() {
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

    // Fetch user data for the admin dashboard without reloading the page (AJAX)
    function fetchUserData() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_users.php', true);  // Endpoint that returns user data in JSON format
        xhr.onload = function() {
            if (xhr.status === 200) {
                const users = JSON.parse(xhr.responseText);
                const usersList = document.getElementById('usersList');
                if (usersList) {
                    usersList.innerHTML = ''; // Clear previous list
                    users.forEach(function(user) {
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
        scrollButton.addEventListener('click', function() {
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                formContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

});
