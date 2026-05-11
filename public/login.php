<?php
$page_title = 'Login';
require_once '../app/controllers/auth_controller.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');

    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

// Already logged in?
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StreamHive</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/streamhive/public/assets/css/style.css">
</head>
<body class="bg-dark text-white">

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none">
                    <i class="fas fa-play-circle" style="color: var(--primary); font-size: 2.5rem;"></i>
                    <h2 class="mt-2 mb-1" style="color: white;">Welcome Back</h2>
                </a>
                <p class="text-muted">Sign in to your StreamHive account</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert" style="background: rgba(231,76,60,0.15); border-color: rgba(231,76,60,0.3); color: #e74c3c;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="login-form">
                <div class="mb-4">
                    <label class="form-label fw-500">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-500">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3 fw-600">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <hr style="border-color: rgba(255,255,255,0.1);">

            <p class="text-center text-muted mb-3">Don't have an account?</p>
            <a href="register.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </a>

            <div class="text-center mt-4">
                <a href="index.php" class="text-muted text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
