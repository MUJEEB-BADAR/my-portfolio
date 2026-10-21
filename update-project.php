
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (empty($_SESSION['update_project_csrf_token'])) {
    $_SESSION['update_project_csrf_token'] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function validProjectImagePath($path): bool
{
    return is_string($path)
        && preg_match(
            '#\Auploads/[a-f0-9]{32}\.(?:jpg|png|webp)\z#i',
            $path
        ) === 1
        && is_file(__DIR__ . '/' . $path);
}

$message = '';
$messageType = '';
$id = 0;
$title = '';
$slug = '';
$description = '';
$technologies = '';
$category = 'frontend';
$currentImage = '';
$allowedCategories = ['frontend', 'backend', 'fullstack'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !is_string($_SESSION['update_project_csrf_token']) ||
        !hash_equals($_SESSION['update_project_csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Security check failed. Please reload the form and try again.');
    }

    foreach (
        ['id', 'title', 'slug', 'description', 'technologies', 'category']
        as $field
    ) {
        if (isset($_POST[$field]) && !is_string($_POST[$field])) {
            http_response_code(400);
            exit('Invalid form input.');
        }
    }

    $idValue = trim($_POST['id'] ?? '');
    $id = filter_var($idValue, FILTER_VALIDATE_INT);

    $title = trim($_POST['title'] ?? '');
    $slug = strtolower(trim($_POST['slug'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if ($id === false || $id === null || $id < 1) {
        $message = 'Please select a valid project.';
        $messageType = 'error';
        $id = 0;
    } elseif (
        $title === '' ||
        $slug === '' ||
        $description === '' ||
        $technologies === '' ||
        $category === ''
    ) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (strlen($title) > 150) {
        $message = 'Project title cannot exceed 150 characters.';
        $messageType = 'error';
    } elseif (strlen($slug) > 180) {
        $message = 'Slug cannot exceed 180 characters.';
        $messageType = 'error';
    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        $message = 'Slug must use lowercase letters, numbers and single hyphens only.';
        $messageType = 'error';
    } elseif (strlen($technologies) > 1000) {
        $message = 'Technologies cannot exceed 1000 characters.';
        $messageType = 'error';
    } elseif (!in_array($category, $allowedCategories, true)) {
        $message = 'Please select a valid project category.';
        $messageType = 'error';
    } else {
        // Load the current image before making any changes.
        $check = mysqli_prepare(
            $conn,
            'SELECT image FROM projects WHERE id = ? LIMIT 1'
        );

        if (!$check) {
            error_log('Project check failed: ' . mysqli_error($conn));
            $message = 'Could not verify the project. Please try again.';
            $messageType = 'error';
        } else {
            mysqli_stmt_bind_param($check, 'i', $id);

            if (!mysqli_stmt_execute($check)) {
                error_log('Project check execution failed: ' . mysqli_stmt_error($check));
                $message = 'Could not verify the project. Please try again.';
                $messageType = 'error';
            } else {
                mysqli_stmt_bind_result($check, $dbImage);

                if (mysqli_stmt_fetch($check)) {
                    $currentImage = validProjectImagePath($dbImage)
                        ? $dbImage
                        : '';
                } else {
                    $message = 'Project not found. Please select a project from the list.';
                    $messageType = 'error';
                    $id = 0;
                }
            }

            mysqli_stmt_close($check);
        }

        if ($id > 0 && $message === '') {
            $newImagePath = '';
            $uploadError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;

            if (!is_int($uploadError)) {
                $message = 'Invalid image upload.';
                $messageType = 'error';
            } elseif ($uploadError !== UPLOAD_ERR_NO_FILE) {
                if ($uploadError !== UPLOAD_ERR_OK) {
                    $message = 'Image upload failed. Please select the image again.';
                    $messageType = 'error';
                } elseif (
                    !isset($_FILES['image']['tmp_name'], $_FILES['image']['size']) ||
                    !is_string($_FILES['image']['tmp_name']) ||
                    !is_uploaded_file($_FILES['image']['tmp_name'])
                ) {
                    $message = 'Invalid uploaded image.';
                    $messageType = 'error';
                } elseif (
                    !is_int($_FILES['image']['size']) ||
                    $_FILES['image']['size'] < 1 ||
                    $_FILES['image']['size'] > 5 * 1024 * 1024
                ) {
                    $message = 'Image must be smaller than 5 MB.';
                    $messageType = 'error';
                } else {
                    $tmpFile = $_FILES['image']['tmp_name'];
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tmpFile);
                    $imageInfo = @getimagesize($tmpFile);

                    $allowedMimes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (
                        !is_string($mime) ||
                        !isset($allowedMimes[$mime]) ||
                        $imageInfo === false ||
                        !isset($imageInfo['mime']) ||
                        $imageInfo['mime'] !== $mime
                    ) {
                        $message = 'Only valid JPG, PNG or WebP images are allowed.';
                        $messageType = 'error';
                    } else {
                        $uploadDirectory = __DIR__ . '/uploads';

                        if (
                            !is_dir($uploadDirectory) ||
                            !is_writable($uploadDirectory)
                        ) {
                            $message = 'Uploads folder is missing or not writable.';
                            $messageType = 'error';
                        } else {
                            $filename = bin2hex(random_bytes(16))
                                . '.' . $allowedMimes[$mime];

                            $destination = $uploadDirectory . '/' . $filename;
                            $newImagePath = 'uploads/' . $filename;

                            if (!move_uploaded_file($tmpFile, $destination)) {
                                $newImagePath = '';
                                $message = 'Could not save the uploaded image.';
                                $messageType = 'error';
                            }
                        }
                    }
                }
            }

            if ($message === '') {
                // Preserve the existing image when no new image is selected.
                $imageToSave = $newImagePath !== ''
                    ? $newImagePath
                    : $currentImage;

                $sql = 'UPDATE projects
                        SET title = ?, slug = ?, description = ?,
                            technologies = ?, category = ?, image = ?
                        WHERE id = ?';

                $stmt = mysqli_prepare($conn, $sql);

                if (!$stmt) {
                    error_log('Project update prepare failed: ' . mysqli_error($conn));
                    $message = 'Could not prepare the update. Please try again.';
                    $messageType = 'error';
                } else {
                    mysqli_stmt_bind_param(
                        $stmt,
                        'ssssssi',
                        $title,
                        $slug,
                        $description,
                        $technologies,
                        $category,
                        $imageToSave,
                        $id
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);

                        // Remove the old image only after the DB update succeeds.
                        if (
                            $newImagePath !== '' &&
                            $currentImage !== '' &&
                            $currentImage !== $newImagePath
                        ) {
                            $oldImageFile = __DIR__ . '/' . $currentImage;

                            if (is_file($oldImageFile)) {
                                @unlink($oldImageFile);
                            }
                        }

                        $_SESSION['update_project_csrf_token'] =
                            bin2hex(random_bytes(32));

                        header('Location: update-project.php?updated=1&id=' . $id);
                        exit;
                    }

                    $errorNumber = mysqli_stmt_errno($stmt);
                    error_log('Project update failed: ' . mysqli_stmt_error($stmt));
                    mysqli_stmt_close($stmt);

                    if ($errorNumber === 1062) {
                        $message = 'This slug is already used by another project. Please choose a different slug.';
                    } else {
                        $message = 'Could not update the project. Please try again.';
                    }

                    $messageType = 'error';
                }

                // Clean up the newly uploaded file if the DB update failed.
                if ($message !== '' && $newImagePath !== '') {
                    $newFile = __DIR__ . '/' . $newImagePath;

                    if (is_file($newFile)) {
                        @unlink($newFile);
                    }
                }
            }
        }
    }
} else {
    $requestedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($requestedId !== false && $requestedId !== null && $requestedId > 0) {
        $id = $requestedId;

        $stmt = mysqli_prepare(
            $conn,
            'SELECT title, slug, description, technologies, category, image
             FROM projects WHERE id = ? LIMIT 1'
        );

        if (!$stmt) {
            error_log('Load project prepare failed: ' . mysqli_error($conn));
            $message = 'Could not load the project. Please try again.';
            $messageType = 'error';
            $id = 0;
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $id);

            if (!mysqli_stmt_execute($stmt)) {
                error_log('Load project failed: ' . mysqli_stmt_error($stmt));
                $message = 'Could not load the project. Please try again.';
                $messageType = 'error';
                $id = 0;
            } else {
                mysqli_stmt_bind_result(
                    $stmt,
                    $dbTitle,
                    $dbSlug,
                    $dbDescription,
                    $dbTechnologies,
                    $dbCategory,
                    $dbImage
                );

                if (mysqli_stmt_fetch($stmt)) {
                    $title = $dbTitle;
                    $slug = $dbSlug;
                    $description = $dbDescription;
                    $technologies = $dbTechnologies;
                    $category = $dbCategory;
                    $currentImage = validProjectImagePath($dbImage)
                        ? $dbImage
                        : '';

                    if (!in_array($category, $allowedCategories, true)) {
                        $category = 'frontend';
                    }

                    if (isset($_GET['updated']) && $_GET['updated'] === '1') {
                        $message = 'Project updated successfully!';
                        $messageType = 'success';
                    }
                } else {
                    $message = 'Project not found. Please select a project from the list.';
                    $messageType = 'error';
                    $id = 0;
                }
            }

            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_GET['id'])) {
        $message = 'Please select a valid project.';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project | My Portfolio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --bg: #080d1b;
            --card: rgba(19, 27, 49, .84);
            --text: #f1f5ff;
            --muted: #a6b1ca;
            --blue: #5b8cff;
            --purple: #a855f7;
            --border: rgba(255, 255, 255, .10);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at 10% 0%, rgba(91,140,255,.18), transparent 32%),
                radial-gradient(circle at 90% 15%, rgba(168,85,247,.15), transparent 30%),
                var(--bg);
        }

        .main-wrapper {
            max-width: 760px;
            margin: 0 auto;
            padding: 45px 16px 70px;
        }

        .page-heading {
            text-align: center;
            margin-bottom: 32px;
        }

        .eyebrow {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 50px;
            color: #b9ccff;
            background: rgba(91,140,255,.10);
            border: 1px solid rgba(91,140,255,.25);
            font-size: .83rem;
            margin-bottom: 17px;
        }

        h1 {
            font-size: clamp(2rem, 5vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -1px;
        }

        .gradient-text {
            background: linear-gradient(100deg, #75a1ff, #c084fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-heading p {
            color: var(--muted);
            line-height: 1.7;
            margin: 12px auto 0;
            max-width: 540px;
        }

        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: clamp(22px, 5vw, 38px);
            box-shadow: 0 20px 55px rgba(0,0,0,.24);
        }

        .form-card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-intro {
            color: var(--muted);
            font-size: .92rem;
            margin-bottom: 28px;
        }

        .form-label {
            color: #e4eaff;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .form-control, .form-select {
            color: #f1f5ff;
            background-color: #0c1429;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 10px;
            padding: 12px 14px;
        }

        .form-control::placeholder { color: #8996b1; }

        .form-control:focus, .form-select:focus {
            color: #fff;
            background-color: #0c1429;
            border-color: var(--blue);
            box-shadow: 0 0 0 .2rem rgba(91,140,255,.15);
        }

        .form-select option {
            color: #f1f5ff;
            background: #0c1429;
        }

        textarea.form-control {
            min-height: 170px;
            resize: vertical;
        }

        .form-text { color: #8996b1; }

        .image-preview {
            display: block;
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            object-position: left center;
            background: #0c1429;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 14px;
        }

        .no-image-preview {
            display: flex;
            min-height: 140px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            background: #0c1429;
            border: 1px dashed rgba(255,255,255,.18);
            border-radius: 12px;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .no-image-preview i {
            color: #a78bfa;
            font-size: 2rem;
        }

        .alert-custom {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
            border: 1px solid transparent;
        }

        .alert-success-custom {
            color: #a7f3d0;
            background: rgba(16,185,129,.10);
            border-color: rgba(16,185,129,.28);
        }

        .alert-error-custom {
            color: #fecaca;
            background: rgba(239,68,68,.10);
            border-color: rgba(239,68,68,.28);
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .btn-gradient {
            color: #fff;
            background: linear-gradient(110deg, var(--blue), var(--purple));
            border: 0;
            border-radius: 10px;
            padding: 11px 20px;
            font-weight: 600;
        }

        .btn-gradient:hover { color: #fff; filter: brightness(1.1); }

        .btn-outline-custom {
            color: #e2e8ff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 18px;
        }

        .btn-outline-custom:hover {
            color: #fff;
            background: rgba(255,255,255,.08);
            border-color: rgba(91,140,255,.5);
        }

        footer {
            border-top: 1px solid var(--border);
            color: #8490aa;
            text-align: center;
            padding: 24px 12px;
            font-size: .88rem;
        }

        @media (max-width: 480px) {
            .main-wrapper { padding-top: 38px; }
            .form-actions .btn { width: 100%; }
        }
    </style>
</head>

<body>
<?php require_once __DIR__ . '/includes/admin-header.php'; ?>

<main class="main-wrapper">
    <header class="page-heading">
        <span class="eyebrow">PROJECT MANAGEMENT</span>
        <h1>Update Your <span class="gradient-text">Project</span></h1>
        <p>Edit your project details and replace its preview image if needed.</p>
    </header>

    <section class="form-card">
        <h2>Edit Project Details</h2>
        <p class="form-intro">Update your project information below.</p>

        <?php if ($message !== ''): ?>
            <div class="alert-custom <?= $messageType === 'success'
                ? 'alert-success-custom'
                : 'alert-error-custom' ?>"
                role="status">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="update-project.php"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['update_project_csrf_token']) ?>">

            <div class="mb-4">
                <label for="id" class="form-label">Project ID</label>
                <input type="number" id="id" name="id" class="form-control"
                       value="<?= $id > 0 ? e($id) : '' ?>" readonly required>
                <div class="form-text">Selected automatically from your project list.</div>
            </div>

            <div class="mb-4">
                <label for="title" class="form-label">Project Title *</label>
                <input type="text" id="title" name="title" class="form-control"
                       maxlength="150" value="<?= e($title) ?>"
                       placeholder="Enter project title" required>
            </div>

            <div class="mb-4">
                <label for="slug" class="form-label">Project Slug *</label>
                <input type="text" id="slug" name="slug" class="form-control"
                       maxlength="180" pattern="[a-z0-9]+(-[a-z0-9]+)*"
                       value="<?= e($slug) ?>" placeholder="my-project-name" required>
                <div class="form-text">Use lowercase letters, numbers and hyphens only.</div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Description *</label>
                <textarea id="description" name="description" class="form-control"
                          placeholder="Describe your project..." required><?= e($description) ?></textarea>
            </div>

            <div class="mb-4">
                <label for="technologies" class="form-label">Technologies *</label>
                <input type="text" id="technologies" name="technologies"
                       class="form-control" maxlength="1000"
                       value="<?= e($technologies) ?>"
                       placeholder="PHP, MySQL, HTML, CSS" required>
                <div class="form-text">Separate technologies with commas.</div>
            </div>

            <div class="mb-4">
                <label for="category" class="form-label">Category *</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="frontend" <?= $category === 'frontend' ? 'selected' : '' ?>>Frontend</option>
                    <option value="backend" <?= $category === 'backend' ? 'selected' : '' ?>>Backend</option>
                    <option value="fullstack" <?= $category === 'fullstack' ? 'selected' : '' ?>>Full Stack</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Current Project Image</label>

                <?php if ($currentImage !== ''): ?>
                    <img
                        src="<?= e($currentImage) ?>"
                        alt="Current project image"
                        class="image-preview"
                    >
                <?php else: ?>
                    <div class="no-image-preview">
                        <i class="bi bi-image" aria-hidden="true"></i>
                        <span>No project image uploaded</span>
                    </div>
                <?php endif; ?>

                <label for="image" class="form-label">Replace Project Image (Optional)</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                <div class="form-text">
                    JPG, JPEG, PNG or WebP. Maximum size: 5 MB.
                    Leave empty to keep the current image.
                </div>

                <img
                    id="new-image-preview"
                    class="image-preview mt-3"
                    alt="New image preview"
                    hidden
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-gradient">Update Project</button>
                <a href="view-projects.php" class="btn btn-outline-custom">Cancel</a>
                <a href="index.php" class="btn btn-outline-custom">Home</a>
            </div>
        </form>
    </section>
</main>

<footer>
    &copy; <?= date('Y') ?> Mujeeb Ahmed Badar. Project Management
</footer>

<script>
const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('new-image-preview');

imageInput.addEventListener('change', function () {
    const file = this.files && this.files[0];

    if (!file) {
        imagePreview.removeAttribute('src');
        imagePreview.hidden = true;
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        alert('Please select a JPG, PNG or WebP image.');
        this.value = '';
        imagePreview.removeAttribute('src');
        imagePreview.hidden = true;
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('Image size must be 5 MB or less.');
        this.value = '';
        imagePreview.removeAttribute('src');
        imagePreview.hidden = true;
        return;
    }

    imagePreview.src = URL.createObjectURL(file);
    imagePreview.hidden = false;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
