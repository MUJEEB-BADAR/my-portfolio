
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Only accept valid POST deletion requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete'])) {
    header('Location: view-projects.php');
    exit;
}

// Verify CSRF token.
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['project_csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    header('Location: view-projects.php?delete_error=security');
    exit;
}

// Validate project ID.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: view-projects.php?delete_error=invalid');
    exit;
}

// Get the project's image path before deleting the database record.
$selectSql = "SELECT image FROM projects WHERE id = ?";
$selectStmt = mysqli_prepare($conn, $selectSql);

if (!$selectStmt) {
    error_log('Delete project image lookup prepare failed: ' . mysqli_error($conn));
    header('Location: view-projects.php?delete_error=failed');
    exit;
}

mysqli_stmt_bind_param($selectStmt, 'i', $id);

if (!mysqli_stmt_execute($selectStmt)) {
    error_log('Delete project image lookup failed: ' . mysqli_stmt_error($selectStmt));
    mysqli_stmt_close($selectStmt);

    header('Location: view-projects.php?delete_error=failed');
    exit;
}

mysqli_stmt_bind_result($selectStmt, $imagePath);
$projectExists = mysqli_stmt_fetch($selectStmt);
mysqli_stmt_close($selectStmt);

if (!$projectExists) {
    header('Location: view-projects.php?delete_error=not_found');
    exit;
}

// Delete the project using a prepared statement.
$deleteSql = "DELETE FROM projects WHERE id = ?";
$deleteStmt = mysqli_prepare($conn, $deleteSql);

if (!$deleteStmt) {
    error_log('Delete project prepare failed: ' . mysqli_error($conn));
    header('Location: view-projects.php?delete_error=failed');
    exit;
}

mysqli_stmt_bind_param($deleteStmt, 'i', $id);

if (!mysqli_stmt_execute($deleteStmt)) {
    error_log('Delete project execution failed: ' . mysqli_stmt_error($deleteStmt));
    mysqli_stmt_close($deleteStmt);

    header('Location: view-projects.php?delete_error=failed');
    exit;
}

$deleted = mysqli_stmt_affected_rows($deleteStmt);
mysqli_stmt_close($deleteStmt);

// Only clean up the image after a successful database deletion.
if ($deleted === 1) {
    if (
        is_string($imagePath) &&
        preg_match(
            '#\Auploads/[a-f0-9]{32}\.(?:jpg|jpeg|png|webp)\z#i',
            $imagePath
        ) === 1
    ) {
        $uploadDirectory = realpath(__DIR__ . '/uploads');
        $imageFile = realpath(__DIR__ . '/' . $imagePath);

        // Ensure the resolved image is inside the uploads directory.
        if (
            $uploadDirectory !== false &&
            $imageFile !== false &&
            is_file($imageFile) &&
            dirname($imageFile) === $uploadDirectory
        ) {
            if (!unlink($imageFile)) {
                error_log('Could not delete project image: ' . $imageFile);
            }
        }
    }

    // Rotate the project CSRF token after successful deletion.
    $_SESSION['project_csrf_token'] = bin2hex(random_bytes(32));

    header('Location: view-projects.php?deleted=1');
    exit;
}

header('Location: view-projects.php?delete_error=failed');
exit;
