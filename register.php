<?php
/**
 * ScholarFind — Student Registration (Sign Up)
 * Clean, simplified registration with auto-generated ScholarFind ID and password visibility toggle
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$warning = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please re-enter.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if user already exists
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            $warning = "⚠️ An account with this email address ($email) already exists! Please sign in with your password.";
        } else {
            try {
                // Generate Unique Human-Readable ScholarFind ID
                $scholarfind_id = generate_scholarfind_id($pdo, $name);
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (name, email, scholarfind_id, password, role) VALUES (?, ?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $scholarfind_id, $hashed]);
                $new_id = $pdo->lastInsertId();

                // Create clean initial empty profile without fake defaults
                $p_stmt = $pdo->prepare("
                    INSERT INTO student_profiles (user_id, gender, state, education_level, course, percentage, family_income, category)
                    VALUES (?, 'all', '', '', '', 0.00, 0.00, '')
                ");
                $p_stmt->execute([$new_id]);

                $_SESSION['user_id'] = $new_id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['scholarfind_id'] = $scholarfind_id;
                $_SESSION['role'] = 'student';
                $_SESSION['flash_success'] = "🎉 Account created successfully! Your ScholarFind ID is <strong>@" . htmlspecialchars($scholarfind_id) . "</strong>. Please complete your academic details to see matched scholarships.";
                header("Location: profile.php");
                exit;
            } catch (Exception $e) {
                $error = "Failed to create account: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Create Free Account - ScholarFind';
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
    padding: 42px 38px;
    width: 100%;
    max-width: 480px;
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
    width: 52px;
    height: 52px;
    background: var(--primary-light);
    color: var(--primary);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin: 0 auto 16px;
    box-shadow: 0 4px 12px var(--primary-glow);
}

.auth-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.5px;
    margin-bottom: 4px;
}

.auth-subtitle {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 24px;
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
        <h1 class="auth-title">Create Free Account</h1>
        <p class="auth-subtitle">Join thousands of students finding verified scholarships</p>

        <?php if (!empty($warning)): ?>
            <div style="background: var(--warning-light); color: #92400e; border: 1.5px solid rgba(245, 158, 11, 0.4); padding: 14px 16px; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 700; margin-bottom: 20px; text-align: left;">
                <?= htmlspecialchars($warning) ?>
                <div style="margin-top: 8px;">
                    <a href="login.php" style="color: var(--primary); text-decoration: underline; font-weight: 800;">👉 Click here to Sign In →</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="background: var(--danger-light); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px 14px; border-radius: var(--radius-sm); font-size: 0.88rem; font-weight: 700; margin-bottom: 18px; text-align: left;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" style="text-align: left;">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Jesika Gharde" value="<?= htmlspecialchars($name) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-input-wrap">
                        <input type="password" name="password" id="reg-password" class="form-control" placeholder="Min 6 chars" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reg-password', this)" title="Show Password" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="password-input-wrap">
                        <input type="password" name="confirm_password" id="reg-confirm-password" class="form-control" placeholder="Re-enter password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reg-confirm-password', this)" title="Show Password" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 13px; font-size: 1rem; margin-top: 12px;">
                Create Free Account →
            </button>
        </form>

        <div style="margin-top: 20px; font-size: 0.88rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="font-weight: 700;">Sign in here</a>
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
