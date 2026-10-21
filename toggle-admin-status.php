
<?php
require_once __DIR__ . '/includes/super-admin-auth.php';
require_once __DIR__ . '/includes/db.php';

// Sirf POST requests allow hain.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-admins.php');
    exit;
}

// CSRF security check.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['admin_csrf_token']) ||
    !hash_equals($_SESSION['admin_csrf_token'], $token)
) {
    http_response_code(403);
    exit('Security check failed. Please go back and try again.');
}

// Target ID aur requested status validate karo.
$targetId = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);
$newStatus = filter_input(INPUT_POST, 'new_status', FILTER_VALIDATE_INT);

if (
    $targetId === false ||
    $targetId === null ||
    $targetId < 1 ||
    !in_array($newStatus, [0, 1], true)
) {
    header('Location: manage-admins.php');
    exit;
}

// Apna account disable/enable nahi kar sakte.
if ($targetId === (int) $_SESSION['admin_id']) {
    http_response_code(403);
    exit('You cannot change your own account status.');
}

// Sirf Normal Admin ka status change ho sakta hai.
$stmt = mysqli_prepare(
    $conn,
    "UPDATE admin_users
     SET is_active = ?
     WHERE id = ?
       AND role = 'admin'"
);

if (!$stmt) {
    error_log('Prepare failed in toggle-admin-status.php: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Database error. Please try again.');
}

mysqli_stmt_bind_param($stmt, 'ii', $newStatus, $targetId);

if (!mysqli_stmt_execute($stmt)) {
    error_log('Status update failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    http_response_code(500);
    exit('Unable to update account status. Please try again.');
}

mysqli_stmt_close($stmt);

// Purana token invalidate karke naya token banao.
$_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));

header('Location: manage-admins.php');
exit;
