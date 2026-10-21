
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
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
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
    <title>Admin Login | My Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            color: #f1f5ff;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at 10% 0%, #5b8cff30, transparent 35%),
                radial-gradient(circle at 90% 15%, #a855f725, transparent 30%),
                #080d1b;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 35px;
            border: 1px solid #ffffff1a;
            border-radius: 20px;
            background: #131b31df;
            box-shadow: 0 20px 55px #0005;
        }

        .brand {
            color: white;
            text-align: center;
            font-size: 1.7rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .brand span {
            background: linear-gradient(100deg, #75a1ff, #c084fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .muted {
            color: #a6b1ca;
            text-align: center;
            margin-bottom: 28px;
        }

        .form-label {
            color: #e4eaff;
            font-weight: 600;
        }

        .form-control {
            color: white;
            background: #080d1b;
            border: 1px solid #ffffff22;
            border-radius: 10px;
            padding: 12px 14px;
        }

        .form-control:focus {
            color: white;
            background: #080d1b;
            border-color: #5b8cff;
            box-shadow: 0 0 0 .2rem #5b8cff30;
        }

        .form-control::placeholder { color: #75819c; }

        .btn-gradient {
            width: 100%;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            background: linear-gradient(110deg, #5b8cff, #a855f7);
        }

        .home-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #a6b1ca;
            text-decoration: none;
        }

        .home-link:hover { color: white; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">My<span>Portfolio</span></div>
    <p class="muted">Admin Login — Manage your projects</p>

    <?php if ($message !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin-login.php">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username"
                   class="form-control" required maxlength="100"
                   autocomplete="username">
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn-gradient">Login Securely</button>
    </form>

    <a href="index.php" class="home-link">← Back to Portfolio</a>
</div>
</body>
</html>