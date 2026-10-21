

<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

if (empty($_SESSION['project_csrf_token'])) {
    $_SESSION['project_csrf_token'] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars(
        is_string($value) ? $value : '',
        ENT_QUOTES,
        'UTF-8'
    );
}

$message = '';
$messageType = 'info';

$title = '';
$slug = '';
$description = '';
$technologies = '';
$category = 'fullstack';

if (isset($_GET['added']) && $_GET['added'] === '1') {
    $message = 'Project added successfully!';
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !is_string($_SESSION['project_csrf_token'] ?? null) ||
        !hash_equals($_SESSION['project_csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Security check failed. Please reload the form and try again.');
    }

    $fields = ['title', 'slug', 'description', 'technologies', 'category'];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
            $message = 'Invalid form input.';
            $messageType = 'danger';
            break;
        }
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $category = trim($_POST['category'] ?? 'fullstack');

    if ($message !== '') {
        // Keep the input error message.

    } elseif (
        $title === '' ||
        $slug === '' ||
        $description === '' ||
        $technologies === ''
    ) {
        $message = 'Please fill in all required fields.';
        $messageType = 'danger';

    } elseif (
        strlen($title) > 150 ||
        strlen($slug) > 180 ||
        strlen($description) > 20000 ||
        strlen($technologies) > 1000
    ) {
        $message = 'One or more fields exceed the allowed length.';
        $messageType = 'danger';

    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        $message = 'Slug must contain lowercase letters, numbers and single hyphens only.';
        $messageType = 'danger';

    } elseif (!in_array($category, ['frontend', 'backend', 'fullstack'], true)) {
        $message = 'Please select a valid project category.';
        $messageType = 'danger';

    } else {
        $imagePath = '';
        $uploadedFilePath = '';
        $uploadError = '';

        // Process an image only if the user selected one.
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];

            if (!isset($file['error']) || is_array($file['error'])) {
                $uploadError = 'Invalid image upload.';
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $uploadError = 'Image upload failed. Please try again.';
            } elseif (
                !isset($file['size'], $file['tmp_name']) ||
                !is_string($file['tmp_name']) ||
                !is_uploaded_file($file['tmp_name'])
            ) {
                $uploadError = 'Invalid uploaded image.';
            } elseif ($file['size'] < 1 || $file['size'] > 5 * 1024 * 1024) {
                $uploadError = 'Image size must be between 1 byte and 5 MB.';
            } else {
                $imageInfo = @getimagesize($file['tmp_name']);
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (
                    $imageInfo === false ||
                    !isset($allowedTypes[$mimeType]) ||
                    ($imageInfo['mime'] ?? '') !== $mimeType
                ) {
                    $uploadError = 'Only valid JPG, PNG and WebP images are allowed.';
                } else {
                    $uploadDirectory = __DIR__ . '/uploads';

                    if (
                        !is_dir($uploadDirectory) ||
                        !is_writable($uploadDirectory)
                    ) {
                        $uploadError = 'Upload folder is missing or not writable.';
                    } else {
                        try {
                            $filename = bin2hex(random_bytes(16))
                                . '.' . $allowedTypes[$mimeType];

                            $destination = $uploadDirectory . '/' . $filename;

                            if (move_uploaded_file($file['tmp_name'], $destination)) {
                                $imagePath = 'uploads/' . $filename;
                                $uploadedFilePath = $destination;
                            } else {
                                $uploadError = 'Could not save the image. Please try again.';
                            }
                        } catch (Throwable $exception) {
                            error_log('Image filename generation failed.');
                            $uploadError = 'Could not process the image. Please try again.';
                        }
                    }
                }
            }
        }

        if ($uploadError !== '') {
            $message = $uploadError;
            $messageType = 'danger';
        } else {
            $sql = "INSERT INTO projects
                    (title, slug, description, technologies, category, image, is_featured)
                    VALUES (?, ?, ?, ?, ?, ?, 1)";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                error_log('Add project prepare failed: ' . mysqli_error($conn));
                $message = 'Could not add project. Please try again.';
                $messageType = 'danger';
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    'ssssss',
                    $title,
                    $slug,
                    $description,
                    $technologies,
                    $category,
                    $imagePath
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);

                    $_SESSION['project_csrf_token'] = bin2hex(random_bytes(32));

                    header('Location: add-project.php?added=1');
                    exit;
                }

                $errorCode = mysqli_stmt_errno($stmt);

                error_log('Add project failed. MySQL error code: ' . $errorCode);

                if ($uploadedFilePath !== '' && is_file($uploadedFilePath)) {
                    if (!@unlink($uploadedFilePath)) {
                        error_log('Could not clean up uploaded image after database failure.');
                    }
                }

                if ($errorCode === 1062) {
                    $message = 'This slug already exists. Please use another.';
                } else {
                    $message = 'Could not add project. Please try again.';
                }

                $messageType = 'danger';
                mysqli_stmt_close($stmt);
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
    <title>Add Project | Mujeeb Ahmed Badar</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/style.css">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 10%, rgba(37,99,235,.16), transparent 35%),
                radial-gradient(circle at 85% 85%, rgba(124,58,237,.16), transparent 35%),
                #080d19;
            color: #f1f5f9;
        }

        .add-project-section {
            min-height: 100vh;
            padding: 45px 15px 70px;
        }

        .project-container {
            max-width: 780px;
            margin: 0 auto;
        }

        .project-heading {
            text-align: center;
            margin-bottom: 32px;
        }

        .eyebrow {
            display: inline-block;
            padding: 7px 14px;
            margin-bottom: 15px;
            border: 1px solid rgba(129,140,248,.3);
            border-radius: 30px;
            background: rgba(99,102,241,.1);
            color: #c4b5fd;
            font-size: .85rem;
            letter-spacing: 1px;
        }

        .project-heading h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            margin-bottom: 12px;
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .project-heading p {
            color: #a7b0c0;
            line-height: 1.7;
        }

        .project-form-card {
            padding: clamp(22px, 5vw, 38px);
            border: 1px solid rgba(148,163,184,.2);
            border-radius: 22px;
            background: rgba(15,23,42,.88);
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #f8fafc;
        }

        .form-subtitle, .field-help {
            color: #94a3b8;
        }

        .form-subtitle {
            font-size: .92rem;
            margin-bottom: 28px;
        }

        .project-form .form-label {
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .project-form .form-control,
        .project-form .form-select {
            min-height: 48px;
            padding: 12px 14px;
            color: #f8fafc;
            background-color: #0b1220;
            border: 1px solid #334155;
            border-radius: 11px;
            box-shadow: none;
        }

        .project-form textarea.form-control {
            min-height: 145px;
            resize: vertical;
        }

        .project-form .form-control::placeholder {
            color: #64748b;
        }

        .project-form .form-control:focus,
        .project-form .form-select:focus {
            color: #fff;
            background-color: #0b1220;
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129,140,248,.15);
        }

        .project-form .form-select option {
            color: #f8fafc;
            background: #0b1220;
        }

        .field-help {
            display: block;
            margin-top: 7px;
            font-size: .82rem;
        }

        .project-alert {
            border-radius: 12px;
            padding: 14px 16px;
        }

        .project-alert.alert-success {
            color: #bbf7d0;
            background: rgba(22,163,74,.12);
            border: 1px solid rgba(74,222,128,.3);
        }

        .project-alert.alert-danger {
            color: #fecaca;
            background: rgba(220,38,38,.12);
            border: 1px solid rgba(248,113,113,.3);
        }

        .project-alert.alert-info {
            color: #bae6fd;
            background: rgba(14,165,233,.12);
            border: 1px solid rgba(56,189,248,.3);
        }

        .project-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .project-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            min-height: 47px;
            padding: 12px 20px;
            border-radius: 11px;
            font-weight: 700;
            text-decoration: none;
            transition: transform .2s, filter .2s;
        }

        .project-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        .project-btn-primary {
            border: 0;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
        }

        .project-btn-secondary {
            color: #e2e8f0;
            background: rgba(30,41,59,.7);
            border: 1px solid #475569;
        }

        .project-footer {
            text-align: center;
            color: #64748b;
            font-size: .85rem;
            margin-top: 25px;
        }

        @media (max-width: 575px) {
            .add-project-section { padding: 35px 12px 45px; }
            .project-actions { flex-direction: column; }
            .project-btn { width: 100%; }
        }
    </style>
</head>

<body style="margin: 0; padding: 0;">
<?php require_once __DIR__ . '/includes/admin-header.php'; ?>

<main class="add-project-section">
    <div class="project-container">

        <header class="project-heading">
            <span class="eyebrow">PORTFOLIO MANAGEMENT</span>
            <h1>Add New Project</h1>
            <p>Showcase your work by adding a new project to your software development portfolio.</p>
        </header>

        <section class="project-form-card">
            <h2 class="form-title">Project Information</h2>
            <p class="form-subtitle">
                Fill in the details below. Fields marked with * are required.
            </p>

            <?php if ($message !== ''): ?>
                <div
                    class="alert alert-<?= e($messageType) ?> project-alert"
                    role="alert"
                ><?= e($message) ?></div>
            <?php endif; ?>

            <form method="POST" class="project-form" enctype="multipart/form-data" novalidate>
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['project_csrf_token']) ?>"
                >

                <div class="mb-4">
                    <label for="title" class="form-label">Project Title *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control"
                        maxlength="150"
                        placeholder="e.g. Student Management System"
                        value="<?= e($title) ?>"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="slug" class="form-label">Project Slug *</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        class="form-control"
                        maxlength="180"
                        pattern="[a-z0-9]+(-[a-z0-9]+)*"
                        placeholder="student-management-system"
                        value="<?= e($slug) ?>"
                        required
                    >
                    <small class="field-help">
                        Use lowercase letters, numbers and hyphens. Slugs must be unique.
                    </small>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Project Description *</label>
                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        rows="5"
                        placeholder="Explain what your project does..."
                        required
                    ><?= e($description) ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="technologies" class="form-label">Technologies Used *</label>
                    <input
                        type="text"
                        id="technologies"
                        name="technologies"
                        class="form-control"
                        maxlength="1000"
                        placeholder="HTML5, CSS3, JavaScript, PHP, MySQL"
                        value="<?= e($technologies) ?>"
                        required
                    >
                    <small class="field-help">
                        Separate each technology with a comma.
                    </small>
                </div>

                <div class="mb-2">
                    <label for="category" class="form-label">Project Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="frontend" <?= $category === 'frontend' ? 'selected' : '' ?>>Frontend</option>
                        <option value="backend" <?= $category === 'backend' ? 'selected' : '' ?>>Backend</option>
                        <option value="fullstack" <?= $category === 'fullstack' ? 'selected' : '' ?>>Full Stack</option>
                    </select>
                </div>
<div class="mb-4">
    <label for="image" class="form-label">Project Image</label>

    <input
        type="file"
        id="image"
        name="image"
        class="form-control"
        accept=".jpg,.jpeg,.png,.webp"
    >

    <small class="field-help">
        Upload a JPG, PNG or WebP image. Maximum file size will be validated on the server.
    </small>
</div>
                <div class="project-actions">
                    <button type="submit" class="project-btn project-btn-primary">
                        ＋ Save Project
                    </button>

                    <a href="index.php" class="project-btn project-btn-secondary">
                        ← Back to Home
                    </a>

                    <a href="view-projects.php" class="project-btn project-btn-secondary">
                        View Projects
                    </a>
                </div>
            </form>
        </section>

        <footer class="project-footer">
            © <?= date('Y') ?> Mujeeb Ahmed Badar · Portfolio Management
        </footer>
    </div>
</main>
</body>
</html>