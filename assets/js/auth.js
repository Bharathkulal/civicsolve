const API_URL = 'backend/auth/';

function showError(elementId, message) {
    const el = document.getElementById(elementId);
    el.textContent = message;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3000);
}

function showSuccess(elementId, message) {
    const el = document.getElementById(elementId);
    el.textContent = message;
    el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const data = {
                email: formData.get('email'),
                password: formData.get('password')
            };

            try {
                const response = await fetch(API_URL + 'login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = result.redirect;
                } else {
                    showError('login-error', result.message || 'Invalid credentials');
                }
            } catch (error) {
                showError('login-error', 'Connection error. Please try again.');
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            
            if (formData.get('password') !== formData.get('confirm_password')) {
                showError('register-error', 'Passwords do not match');
                return;
            }

            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password')
            };

            try {
                const response = await fetch(API_URL + 'register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const raw = await response.text();
                let result = null;

                try {
                    result = JSON.parse(raw);
                } catch (parseError) {
                    console.error('Non-JSON register response:', raw);
                    showError('register-error', 'Server returned invalid response. Check PHP/DB config.');
                    return;
                }

                console.log('Registration result:', result);
                
                if (result.success) {
                    showSuccess('register-success', 'Registration successful! Redirecting to login...');
                    setTimeout(() => window.location.href = 'login.html', 1500);
                } else {
                    showError('register-error', result.message || 'Registration failed');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('register-error', 'Connection error: ' + error.message);
            }
        });
    }
});