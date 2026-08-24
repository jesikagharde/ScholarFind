<?php
/**
 * ScholarFind — Student & Admin Authentication (Sign In)
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';

if (is_logged_in()) {
    header("Location: " . (is_admin() ? "admin/index.php" : "dashboard.php"));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email address and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['scholarfind_id'] = $user['scholarfind_id'] ?? '';
            $_SESSION['role'] = $user['role'];

            $_SESSION['flash_success'] = "Welcome back, " . htmlspecialchars($user['name']) . "!";
            header("Location: " . ($user['role'] === 'admin' ? "admin/index.php" : "dashboard.php"));
            exit;
        } else {
            $error = "Invalid email address or password. Please try again.";
        }
    }
}

$page_title = 'Sign In - ScholarFind';
require_once 'includes/header.php';
?>

<style>
.auth-wrapper {
    min-height: calc(100vh - 220px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.auth-glass-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 44px 38px;
    width: 100%;
    max-width: 440px;
    box-shadow: var(--shadow-card-hover);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.auth-glass-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--primary-gradient);
}

.auth-brand-badge {
    width: 54px;
    height: 54px;
    background: var(--primary-light);
    color: var(--primary);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    margin: 0 auto 16px;
    box-shadow: 0 4px 12px var(--primary-glow);
}

.auth-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.5px;
    margin-bottom: 4px;
}

.auth-subtitle {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 26px;
}

/* Password Toggle Wrapper */
.password-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-wrap input {
    padding-right: 44px;
}

.password-toggle-btn {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    color: var(--text-muted);
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    transition: var(--transition);
}

.password-toggle-btn:hover {
    color: var(--primary);
}
</style>

<div class="auth-wrapper">
    <div class="auth-glass-box">
        <div class="auth-brand-badge">🎓</div>
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in to check matched scholarships & deadlines</p>

        <?php if (!empty($error)): ?>
            <div style="background: var(--danger-light); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px 14px; border-radius: var(--radius-sm); font-size: 0.88rem; font-weight: 700; margin-bottom: 18px; text-align: left;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" style="text-align: left;">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="password-input-wrap">
                    <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('login-password', this)" title="Show Password" aria-label="Toggle password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 13px; font-size: 1rem; margin-top: 14px;">
                Sign In to ScholarFind →
            </button>
        </form>

        <div style="margin-top: 24px; font-size: 0.88rem; color: var(--text-muted);">
            Don't have an account yet? <a href="register.php" style="font-weight: 700;">Sign up for free</a>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const eyeOpenSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const eyeClosedSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-eye-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = eyeClosedSvg;
        btn.title = 'Hide Password';
    } else {
        input.type = 'password';
        btn.innerHTML = eyeOpenSvg;
        btn.title = 'Show Password';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
