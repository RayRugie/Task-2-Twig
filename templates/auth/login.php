<?php
/**
 * Login Template - PHP version
 */

$content = '
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle text-primary" style="font-size: 3rem;"></i>
                    <h2 class="fw-bold mt-3">Welcome Back</h2>
                    <p class="text-muted">Sign in to your account</p>
                </div>

                <form method="POST" action="/login" id="loginForm" novalidate>
                    <input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($csrf_token) . '">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                value="' . htmlspecialchars($form_data['username'] ?? '') . '"
                                required
                                autocomplete="username"
                                placeholder="Enter your username or email"
                            >
                        </div>
                        <div class="invalid-feedback" id="username-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="password-error"></div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>

                <div class="text-center">
                    <p class="mb-0">
                        Don\'t have an account? 
                        <a href="/register" class="text-decoration-none">Sign up here</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Demo Credentials -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>Demo Credentials
                </h6>
                <p class="card-text small text-muted mb-2">
                    <strong>Admin:</strong> admin / admin123<br>
                    <strong>User:</strong> john.doe / password123
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("loginForm");
    const submitBtn = document.getElementById("submitBtn");
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    // Toggle password visibility
    togglePassword.addEventListener("click", function() {
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);
        
        const icon = this.querySelector("i");
        icon.classList.toggle("bi-eye");
        icon.classList.toggle("bi-eye-slash");
    });

    // Form validation
    form.addEventListener("submit", function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        let isValid = true;
        
        // Validate username
        const username = document.getElementById("username").value.trim();
        if (!username) {
            showError("username", "Username or email is required");
            isValid = false;
        }
        
        // Validate password
        const password = document.getElementById("password").value;
        if (!password) {
            showError("password", "Password is required");
            isValid = false;
        }
        
        if (isValid) {
            // Show loading state
            submitBtn.innerHTML = "<i class=\"bi bi-hourglass-split me-2\"></i>Signing in...";
            submitBtn.disabled = true;
            
            // Submit form
            form.submit();
        }
    });

    function showError(field, message) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + "-error");
        
        input.classList.add("is-invalid");
        errorDiv.textContent = message;
    }

    function clearErrors() {
        const inputs = form.querySelectorAll(".form-control");
        inputs.forEach(input => {
            input.classList.remove("is-invalid");
        });
        
        const errorDivs = form.querySelectorAll(".invalid-feedback");
        errorDivs.forEach(div => {
            div.textContent = "";
        });
    }
});
</script>';

// Include the base template
include TEMPLATES_PATH . '/base.php';
?>
