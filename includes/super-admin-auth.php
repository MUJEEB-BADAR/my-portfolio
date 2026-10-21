
<?php
require_once __DIR__ . '/auth.php';

// Sirf Super Admin ko management pages access karne do.
if (
    !isset($_SESSION['admin_role']) ||
    $_SESSION['admin_role'] !== 'super_admin'
) {
    http_response_code(403);
    exit(
        'Access denied. Only the Super Admin can access this page.'
    );
}
?>
