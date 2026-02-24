<?php
$page_title = 'Create Account';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main class="container-fluid py-5" style="min-height: calc(100vh - 200px); display: flex; align-items: center;">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="card border-secondary">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <div style="font-size: 60px; margin-bottom: 20px;">🎬</div>
                            <h2 class="card-title">Join StreamHive</h2>
                            <p class="text-muted small">Create your account to get started</p>
                        </div>

                        <form id="register-form" onsubmit="return handleRegister(event)">
                            <div class="mb-4">
                                <label for="username" class="form-label fw-500">Username</label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Choose a username" required minlength="3">
                                <small class="text-muted">3-20 characters</small>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-500">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="you@example.com" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-500">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="••••••••" required minlength="6">
                                <small class="text-muted">At least 6 characters</small>
                            </div>

                            <div class="mb-4">
                                <label for="confirm-password" class="form-label fw-500">Confirm Password</label>
                                <input type="password" class="form-control form-control-lg" id="confirm-password" name="confirm-password" placeholder="••••••••" required>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                                <label class="form-check-label" for="agree">
                                    I agree to the <a href="#" class="text-primary">Terms of Service</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg mb-3 fw-600">
                                Create Account
                            </button>
                        </form>

                        <hr class="border-secondary my-4">

                        <p class="text-center text-muted mb-3">
                            Already have an account?
                            <a href="login.php" class="text-primary fw-bold text-decoration-none">Sign in</a>
                        </p>
                    </div>
                </div>

                <p class="text-center text-muted small mt-4">
                    This is a demo. Registration is simulated.
                </p>
            </div>
        </div>
    </div>
</main>

<script>
    function handleRegister(event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (username.length < 3) {
            showToast('Username must be at least 3 characters', 'warning');
            return false;
        }

        if (password !== confirmPassword) {
            showToast('Passwords do not match', 'danger');
            return false;
        }

        if (password.length < 6) {
            showToast('Password must be at least 6 characters', 'warning');
            return false;
        }

        localStorage.setItem('userEmail', email);
        localStorage.setItem('username', username);
        localStorage.setItem('isLoggedIn', 'true');

        showToast('✓ Account created successfully!', 'success');

        setTimeout(() => {
            window.location.href = 'index.php';
        }, 1500);

        return false;
    }
</script>

<?php include '../app/includes/footer.php'; ?>
