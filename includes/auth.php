
<?php
require_once __DIR__ . '/session.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: /my-portfolio/admin-login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$adminId = (int) $_SESSION['admin_id'];

// Har protected request par account ka role aur status dobara check karo.
$sql = "SELECT username, role, is_active
        FROM admin_users
        WHERE id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(503);
    exit('Unable to verify your admin account. Please try again later.');
}

mysqli_stmt_bind_param($stmt, 'i', $adminId);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $dbUsername,
    $dbRole,
    $dbIsActive
);

$accountFound = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Account delete, disable, ya role invalid ho to access rok do.
if (
    !$accountFound ||
    (int) $dbIsActive !== 1 ||
    !in_array($dbRole, ['super_admin', 'admin'], true)
) {
    $sessionHash = hash('sha256', session_id());

    $activityStmt = mysqli_prepare(
        $conn,
        "UPDATE login_activity
         SET status = 'disabled',
             logout_at = NOW(),
             last_activity = NOW()
         WHERE session_hash = ?
           AND status = 'active'"
    );

    if ($activityStmt) {
        mysqli_stmt_bind_param(
            $activityStmt,
            's',
            $sessionHash
        );
        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);
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

    header('Location: /my-portfolio/admin-login.php?disabled=1');
    exit;
}

// Database se current role session mein update rakho.
$_SESSION['admin_username'] = $dbUsername;
$_SESSION['admin_role'] = $dbRole;

// Current session ki activity update karo.
$sessionHash = hash('sha256', session_id());

$activitySql = "UPDATE login_activity
                SET last_activity = NOW()
                WHERE session_hash = ?
                  AND status = 'active'";

$activityStmt = mysqli_prepare($conn, $activitySql);

if ($activityStmt) {
    mysqli_stmt_bind_param(
        $activityStmt,
        's',
        $sessionHash
    );
    mysqli_stmt_execute($activityStmt);
    mysqli_stmt_close($activityStmt);
}
?>
