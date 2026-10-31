<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

$idleTimeout = 1800; // 30 minutes

if (isset($_SESSION['last_activity'])) {
    $inactiveTime = time() - $_SESSION['last_activity'];

    if ($inactiveTime >= $idleTimeout) {
        require_once __DIR__ . '/db.php';

        $sessionHash = hash('sha256', session_id());

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE login_activity
             SET status = 'expired',
                 last_activity = NOW()
             WHERE session_hash = ?
               AND status = 'active'"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $sessionHash);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }

        session_destroy();

        header('Location: /my-portfolio/admin-login.php?expired=1');
        exit;
    }
}

$_SESSION['last_activity'] = time();
?>