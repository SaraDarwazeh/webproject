<?php
$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StreamHive</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.js" rel="stylesheet">
    <style>
        body { background-color: #0f0f0f; }
    </style>
</head>
<body class="bg-dark">

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(32, 201, 151, 0.08), rgba(142, 68, 173, 0.08));">
    <div style="width: 100%; max-width: 420px; padding: 20px;">
        <div style="background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 40px; text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: 25px;">[ ]</div>
            <h1 style="font-size: 2rem; margin-bottom: 10px; color: white;">Welcome</h1>
            <p style="color: #999; margin-bottom: 35px;">Sign in to your account</p>

            <form onsubmit="handleLogin(event)">
                <div style="margin-bottom: 20px; text-align: left;">
                    <label style="display: block; color: #ccc; font-weight: 600; margin-bottom: 8px; font-size: 0.95rem;">Email Address</label>
                    <input type="email" placeholder="you@example.com" required style="width: 100%; padding: 12px 15px; background-color: #0f0f0f; border: 1px solid #333; border-radius: 6px; color: white; font-size: 1rem; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 25px; text-align: left;">
                    <label style="display: block; color: #ccc; font-weight: 600; margin-bottom: 8px; font-size: 0.95rem;">Password</label>
                    <input type="password" placeholder="•••••••••" required style="width: 100%; padding: 12px 15px; background-color: #0f0f0f; border: 1px solid #333; border-radius: 6px; color: white; font-size: 1rem; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 25px; display: flex; align-items: center;">
                    <input type="checkbox" id="remember" style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;">
                    <label for="remember" style="color: #bbb; cursor: pointer; font-size: 0.95rem;">Remember me</label>
                </div>

                <button type="submit" style="width: 100%; padding: 14px; background: #20c997; color: #000; border: none; border-radius: 6px; font-weight: 700; font-size: 1rem; cursor: pointer; margin-bottom: 15px;">Sign In</button>
            </form>

            <hr style="border: none; border-top: 1px solid #333; margin: 25px 0;">

            <p style="color: #999; margin-bottom: 20px;">Don't have an account?</p>
            <a href="register.php" style="display: inline-block; padding: 12px 24px; background: #8e44ad; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">Create One</a>

            <div style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #333;">
                <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.9rem;">Back to Home</a>
            </div>

            <p style="color: #666; font-size: 0.85rem; margin-top: 20px;">Demo mode: Use any email/password</p>
        </div>
    </div>
</div>

<script src="assets/js/ajax.js"></script>
<script>
    function handleLogin(event) {
        event.preventDefault();
        localStorage.setItem('isLoggedIn', 'true');
        showToast('Login successful!', 'success');
        setTimeout(() => window.location.href = 'index.php', 1500);
        return false;
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
