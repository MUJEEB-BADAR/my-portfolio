
<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin-dashboard.php');
    exit;
}


$message = '';

if (isset($_GET['expired']) && $_GET['expired'] === '1') {
    $message = 'Your session expired due to inactivity. Please log in again.';
}

if (isset($_GET['disabled']) && $_GET['disabled'] === '1') {
    $message = 'Your admin account is disabled. Please contact the Super Admin.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!is_string($username) || !is_string($password)) {
    $username = '';
    $password = '';
} else {
    $username = trim($username);
}
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if ($username === '' || $password === '') {
        $message = 'Please enter your username and password.';
    } else {
        // Pichlay 15 minutes ki failed attempts count karo.
        $countSql = "SELECT COUNT(*)
                     FROM failed_login_attempts
                     WHERE username = ?
                     AND ip_address = ?
                     AND attempted_at >= NOW() - INTERVAL 15 MINUTE";

        $countStmt = mysqli_prepare($conn, $countSql);

        if (!$countStmt) {
            $message = 'A database error occurred. Please try again.';
        } else {
            mysqli_stmt_bind_param($countStmt, 'ss', $username, $ipAddress);
            mysqli_stmt_execute($countStmt);
            mysqli_stmt_bind_result($countStmt, $failedAttempts);
            mysqli_stmt_fetch($countStmt);
            mysqli_stmt_close($countStmt);

            if ($failedAttempts >= 5) {
                $message = 'Too many failed attempts. Please try again after 15 minutes.';
            } else {
                // Account ka role aur active status bhi check karo.
                $sql = "SELECT id, username, password_hash, role, is_active
                        FROM admin_users
                        WHERE username = ?
                        LIMIT 1";

                $stmt = mysqli_prepare($conn, $sql);

                if (!$stmt) {
                    $message = 'A database error occurred. Please try again.';
                } else {
                    mysqli_stmt_bind_param($stmt, 's', $username);
                    mysqli_stmt_execute($stmt);

                    mysqli_stmt_bind_result(
                        $stmt,
                        $adminId,
                        $adminUsername,
                        $passwordHash,
                        $adminRole,
                        $isActive
                    );

                    $userFound = mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    $validPassword = (
                        $userFound &&
                        password_verify($password, $passwordHash)
                    );

                    if ($validPassword && (int) $isActive !== 1) {
                        $message = 'Your admin account is disabled. Please contact the Super Admin.';

                        // Disabled account ki attempt bhi record karo.
                        $insertSql = "INSERT INTO failed_login_attempts
                                      (username, ip_address)
                                      VALUES (?, ?)";

                        $insertStmt = mysqli_prepare($conn, $insertSql);

                        if ($insertStmt) {
                            mysqli_stmt_bind_param(
                                $insertStmt,
                                'ss',
                                $username,
                                $ipAddress
                            );
                            mysqli_stmt_execute($insertStmt);
                            mysqli_stmt_close($insertStmt);
                        }
                    } elseif (
                        $validPassword &&
                        in_array($adminRole, ['super_admin', 'admin'], true)
                    ) {
                        session_regenerate_id(true);

                        $_SESSION['admin_id'] = (int) $adminId;
                        $_SESSION['admin_username'] = $adminUsername;
                        $_SESSION['admin_role'] = $adminRole;

                        unset($_SESSION['last_activity']);

                        $sessionHash = hash('sha256', session_id());

                        $userAgent = substr(
                            $_SERVER['HTTP_USER_AGENT'] ?? '',
                            0,
                            2000
                        );

                        // Successful login par failed attempts clear karo.
                        $deleteSql = "DELETE FROM failed_login_attempts
                                      WHERE username = ?
                                      AND ip_address = ?";

                        $deleteStmt = mysqli_prepare($conn, $deleteSql);

                        if ($deleteStmt) {
                            mysqli_stmt_bind_param(
                                $deleteStmt,
                                'ss',
                                $adminUsername,
                                $ipAddress
                            );
                            mysqli_stmt_execute($deleteStmt);
                            mysqli_stmt_close($deleteStmt);
                        }
                        // Login history record banao.
                        $activitySql = "INSERT INTO login_activity
                            (admin_id, username, session_hash, ip_address, user_agent)
                            VALUES (?, ?, ?, ?, ?)";
                        $activityStmt = mysqli_prepare($conn, $activitySql);
                        if ($activityStmt) {
                            mysqli_stmt_bind_param(
                                $activityStmt,
                                'issss',
                                $adminId,
                                $adminUsername,
                                $sessionHash,
                                $ipAddress,
                                $userAgent
                            );
                            mysqli_stmt_execute($activityStmt);
                            mysqli_stmt_close($activityStmt);
                        }
                        header('Location: admin-dashboard.php');
                        exit;
                    } else {
                        // Ghalat credentials ya invalid role.
                        $insertSql = "INSERT INTO failed_login_attempts
                                      (username, ip_address)
                                      VALUES (?, ?)";
                        $insertStmt = mysqli_prepare($conn, $insertSql);
                        if ($insertStmt) {
                            mysqli_stmt_bind_param(
                                $insertStmt,
                                'ss',
                                $username,
                                $ipAddress
                            );
                            mysqli_stmt_execute($insertStmt);
                            mysqli_stmt_close($insertStmt);
                        }
                        $message = 'Invalid username or password.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | MyPortfolio Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<style>
    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        margin: 0;
        padding: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "Segoe UI", Arial, sans-serif;
        color: #f8fafc;
        background:
            radial-gradient(circle at 10% 10%, rgba(91, 140, 255, .16), transparent 35%),
            radial-gradient(circle at 90% 90%, rgba(168, 85, 247, .13), transparent 35%),
            #080d1b;
    }

    .login-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
        max-width: 1000px;
        min-height: 590px;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 24px;
        background: #101a30;
        box-shadow: 0 30px 90px rgba(0, 0, 0, .4);
    }

    /* Left branding panel */
    .brand-panel {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 55px 45px;
        overflow: hidden;
        background:
            linear-gradient(145deg, rgba(39, 73, 139, .75), rgba(19, 27, 52, .98));
    }

    .brand-panel::before {
        content: "";
        position: absolute;
        width: 300px;
        height: 300px;
        right: -120px;
        bottom: -100px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 50%;
        box-shadow:
            0 0 0 35px rgba(255,255,255,.025),
            0 0 0 70px rgba(255,255,255,.02);
    }

    .brand-logo {
        position: relative;
        z-index: 1;
        color: #ffffff;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -.8px;
    }

    .brand-logo span {
        color: #8fb3ff;
    }

    .brand-content {
        position: relative;
        z-index: 1;
        max-width: 390px;
    }

    .brand-tag {
        display: inline-block;
        margin-bottom: 22px;
        padding: 8px 13px;
        border: 1px solid rgba(147, 183, 255, .28);
        border-radius: 30px;
        color: #c4d8ff;
        background: rgba(255,255,255,.06);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .brand-content h1 {
        margin-bottom: 18px;
        color: #ffffff;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -1.5px;
    }

    .brand-content h1 span {
        color: #8fb3ff;
    }

    .brand-content p {
        margin: 0;
        color: #c0cce1;
        font-size: .98rem;
        line-height: 1.8;
    }

    .brand-footer {
        position: relative;
        z-index: 1;
        color: #a8b7d0;
        font-size: .82rem;
    }

    /* Right login panel */
    .login-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 55px 45px;
        background: linear-gradient(145deg, #17233c, #101a30);
    }

    .login-content {
        width: 100%;
        max-width: 350px;
    }

    .login-heading {
        margin-bottom: 9px;
        color: #ffffff;
        font-size: 1.9rem;
        font-weight: 800;
        letter-spacing: -.7px;
    }

    .login-description {
        margin-bottom: 34px;
        color: #a5b4cc;
        font-size: .9rem;
    }

    .form-label {
        margin-bottom: 9px;
        color: #e2e8f0;
        font-size: .88rem;
        font-weight: 650;
    }

    .form-control {
        min-height: 49px;
        padding: 12px 15px;
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 10px;
        color: #f8fafc;
        background: rgba(8, 13, 27, .85);
        transition: .25s ease;
    }

    .form-control:focus {
        border-color: #818cf8;
        color: #ffffff;
        background: #080d1b;
        box-shadow: 0 0 0 3px rgba(91, 140, 255, .16);
    }

    .form-control::placeholder {
        color: #75819c;
    }


.password-wrapper {
    position: relative;
}

.password-wrapper .form-control {
    padding-right: 48px;
}

.toggle-password {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    padding: 5px;
    border: none;
    color: #94a3b8;
    background: transparent;
    cursor: pointer;
    font-size: 1.1rem;
    line-height: 1;
}

.toggle-password:hover {
    color: #8fb3ff;
}

.toggle-password:focus-visible {
    outline: 2px solid #8fb3ff;
    border-radius: 4px;
}


    .btn-gradient {
        width: 100%;
        min-height: 50px;
        border: 0;
        border-radius: 10px;
        color: #ffffff;
        background: linear-gradient(110deg, #5b8cff, #a855f7);
        font-size: .95rem;
        font-weight: 750;
        box-shadow: 0 8px 22px rgba(91, 140, 255, .2);
        transition: .25s ease;
    }

    .btn-gradient:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(168, 85, 247, .25);
    }

    .home-link {
        display: block;
        margin-top: 27px;
        color: #a5b4cc;
        text-align: center;
        font-size: .88rem;
        text-decoration: none;
        transition: .2s ease;
    }

    .home-link:hover {
        color: #c4b5fd;
    }

    .alert {
        border-radius: 10px;
        font-size: .85rem;
    }

    .btn-gradient:focus-visible,
    .home-link:focus-visible {
        outline: 2px solid #c4b5fd;
        outline-offset: 4px;
    }

    @media (max-width: 768px) {
        body {
            padding: 18px;
        }

        .login-wrapper {
            grid-template-columns: 1fr;
            max-width: 480px;
        }

        .brand-panel {
            min-height: 240px;
            padding: 30px;
        }

        .brand-content {
            margin-top: 35px;
        }

        .brand-content h1 {
            font-size: 2rem;
        }

        .brand-footer {
            margin-top: 30px;
        }

        .login-panel {
            padding: 38px 30px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            transition: none !important;
        }
    }
.input-icon-wrapper {
    position: relative;
}

.input-icon-wrapper > i {
    position: absolute;
    top: 50%;
    left: 15px;
    transform: translateY(-50%);
    color: #8294b5;
    font-size: 1rem;
    pointer-events: none;
    transition: color 0.2s ease;
}

.input-icon-wrapper:focus-within > i {
    color: #8fb3ff;
}

.input-icon-wrapper .form-control {
    padding-left: 43px;
}

.password-wrapper .input-icon-wrapper > i {
    left: 15px;
}

.password-wrapper .form-control {
    padding-right: 48px;
}


.btn-gradient:disabled {
    opacity: 0.8;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}


</style>


</head>

<body>

<div class="login-wrapper">

    <!-- Left Branding Panel -->
    <section class="brand-panel">

        <div class="brand-logo">
            My<span>Portfolio</span>
        </div>

        <div class="brand-content">
            <div class="brand-tag">ADMINISTRATION PORTAL</div>

            <h1>
                Manage Your
                <span>Digital Portfolio.</span>
            </h1>

            <p>
                Welcome to your portfolio management dashboard.
                Sign in securely to manage projects and website content.
            </p>
        </div>

        <div class="brand-footer">
            &copy; <?= date('Y'); ?> Mujeeb Ahmed Badar. All rights reserved.
        </div>

    </section>

    <!-- Right Login Panel -->
    <section class="login-panel">

        <div class="login-content">

            <h2 class="login-heading">Welcome Back!</h2>

            <p class="login-description">
                Sign in to access your admin dashboard.
            </p>
            <?php if ($message !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="admin-login.php"><div class="mb-3">
    <label for="username" class="form-label">
        Username
    </label>
    <div class="input-icon-wrapper">
        <i class="bi bi-person"></i>
        <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            required
            maxlength="100"
            autocomplete="username"
            placeholder="Enter your username"
        >
    </div>
</div>
<div class="mb-4">
    <label for="password" class="form-label">
        Password
    </label>
    <div class="password-wrapper">
        <div class="input-icon-wrapper">
            <i class="bi bi-lock"></i>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
            >
        </div>
        <button
            type="button"
            class="toggle-password"
            id="togglePassword"
            aria-label="Show password"
            aria-pressed="false"
        >
            👁
        </button>
    </div>
</div>
                <button type="submit" class="btn-gradient">
                    Login Securely
                </button>
            </form>
            <a href="index.php" class="home-link">
                &larr; Back to Portfolio
            </a>
        </div>
    </section>
</div>
<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
        const isPasswordHidden = passwordInput.type === 'password';

        passwordInput.type = isPasswordHidden ? 'text' : 'password';

        this.textContent = isPasswordHidden ? '🙈' : '👁';

        this.setAttribute(
            'aria-label',
            isPasswordHidden ? 'Hide password' : 'Show password'
        );

        this.setAttribute('aria-pressed', String(isPasswordHidden));
    });
const loginForm = document.querySelector('.login-content form');
const loginButton = loginForm.querySelector('.btn-gradient');
loginForm.addEventListener('submit', function () {
    if (loginForm.dataset.submitting === 'true') {
        return;
    }
    loginForm.dataset.submitting = 'true';
    loginButton.disabled = true;
    loginButton.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"
              aria-hidden="true"></span>
        Logging in...
    `;
});
</script>
</body>
</html>
</body>
</html>
</html>