
<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';

if (!empty($_SESSION['admin_id'])) {
    $sessionHash = hash('sha256', session_id());

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE login_activity
         SET status = 'logged_out',
             logout_at = NOW(),
             last_activity = NOW()
         WHERE session_hash = ?
           AND status = 'active'"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $sessionHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
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

header('Location: admin-login.php');
exit;
