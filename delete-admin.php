
<?php
require_once __DIR__ . '/includes/super-admin-auth.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-admins.php');
    exit;
}

// CSRF protection.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['admin_csrf_token']) ||
    !hash_equals($_SESSION['admin_csrf_token'], $token)
) {
    http_response_code(403);
    exit('Security check failed. Please return and try again.');
}

// Validate target admin ID.
$targetId = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);

if ($targetId === false || $targetId === null || $targetId < 1) {
    header('Location: manage-admins.php');
    exit;
}

// Prevent deleting your own account.
if ($targetId === (int) $_SESSION['admin_id']) {
    http_response_code(403);
    exit('You cannot delete your own account.');
}

mysqli_begin_transaction($conn);

try {
    // Lock the target account and verify its role.
    $checkStmt = mysqli_prepare(
        $conn,
        "SELECT role
         FROM admin_users
         WHERE id = ?
         LIMIT 1
         FOR UPDATE"
    );

    if (!$checkStmt) {
        throw new Exception('Could not prepare account verification.');
    }

    mysqli_stmt_bind_param($checkStmt, 'i', $targetId);

    if (!mysqli_stmt_execute($checkStmt)) {
        mysqli_stmt_close($checkStmt);
        throw new Exception('Could not verify admin account.');
    }

    mysqli_stmt_bind_result($checkStmt, $targetRole);
    $accountFound = mysqli_stmt_fetch($checkStmt);
    mysqli_stmt_close($checkStmt);

    if (!$accountFound) {
        throw new Exception('Admin account not found.');
    }

    if ($targetRole !== 'admin') {
        throw new Exception('Super Admin accounts cannot be deleted.');
    }

    // Mark this admin's active sessions as terminated.
    $sessionStmt = mysqli_prepare(
        $conn,
        "UPDATE login_activity
         SET status = 'disabled',
             logout_at = NOW()
         WHERE admin_id = ?
           AND status = 'active'"
    );

    if (!$sessionStmt) {
        throw new Exception('Could not prepare session update.');
    }

    mysqli_stmt_bind_param($sessionStmt, 'i', $targetId);

    if (!mysqli_stmt_execute($sessionStmt)) {
        mysqli_stmt_close($sessionStmt);
        throw new Exception('Could not update active sessions.');
    }

    mysqli_stmt_close($sessionStmt);

    // Preserve login history before deleting the admin.
    $historyStmt = mysqli_prepare(
        $conn,
        "UPDATE login_activity
         SET admin_id = NULL
         WHERE admin_id = ?"
    );

    if (!$historyStmt) {
        throw new Exception('Could not prepare login history update.');
    }

    mysqli_stmt_bind_param($historyStmt, 'i', $targetId);

    if (!mysqli_stmt_execute($historyStmt)) {
        mysqli_stmt_close($historyStmt);
        throw new Exception('Could not preserve login history.');
    }

    mysqli_stmt_close($historyStmt);

    // Delete only a Normal Admin.
    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM admin_users
         WHERE id = ?
           AND role = 'admin'"
    );

    if (!$deleteStmt) {
        throw new Exception('Could not prepare account deletion.');
    }

    mysqli_stmt_bind_param($deleteStmt, 'i', $targetId);

    if (!mysqli_stmt_execute($deleteStmt)) {
        mysqli_stmt_close($deleteStmt);
        throw new Exception('Could not delete admin account.');
    }

    $deletedRows = mysqli_stmt_affected_rows($deleteStmt);
    mysqli_stmt_close($deleteStmt);

    if ($deletedRows !== 1) {
        throw new Exception('Admin account was not deleted.');
    }

    mysqli_commit($conn);

    // Rotate CSRF token after success.
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));

    header('Location: manage-admins.php?deleted=1');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);

    error_log('Admin deletion failed: ' . $e->getMessage());

    http_response_code(500);
    exit(
        'Could not delete the admin account. No database changes were committed. Please check the server error log.'
    );
}