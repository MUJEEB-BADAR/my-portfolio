

<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Ensure a CSRF token exists for project deletion.
if (empty($_SESSION['project_csrf_token'])) {
    $_SESSION['project_csrf_token'] = bin2hex(random_bytes(32));
}

// Deletion feedback.
$deleteMessage = '';
$deleteMessageType = '';

if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $deleteMessage = 'Project deleted successfully.';
    $deleteMessageType = 'success';
} elseif (isset($_GET['delete_error'])) {
    $deleteMessageType = 'error';

    switch ($_GET['delete_error']) {
        case 'security':
            $deleteMessage = 'Security verification failed. Please refresh and try again.';
            break;
        case 'invalid':
            $deleteMessage = 'Invalid project ID.';
            break;
        case 'not_found':
            $deleteMessage = 'Project was not found or has already been deleted.';
            break;
        default:
            $deleteMessage = 'Could not delete the project. Please try again.';
            break;
    }
}

// Search and category filters.
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$allowedCategories = ['frontend', 'backend', 'fullstack'];

if (!in_array($category, $allowedCategories, true)) {
    $category = '';
}

// Pagination settings.
$perPage = 6;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

if (!$page || $page < 1) {
    $page = 1;
}

// Build shared filter conditions.
$where = " WHERE 1=1";
$params = [];
$types = '';

if ($search !== '') {
    $where .= " AND (title LIKE ? OR description LIKE ? OR technologies LIKE ?)";
    $searchTerm = '%' . $search . '%';

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if ($category !== '') {
    $where .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

// Bind dynamic parameters safely.
$bindDynamicParams = static function (
    mysqli_stmt $statement,
    string $paramTypes,
    array &$values
): bool {
    if ($paramTypes === '') {
        return true;
    }

    $bindArguments = [$paramTypes];

    foreach ($values as $key => &$value) {
        $bindArguments[] = &$value;
    }

    unset($value);

    return call_user_func_array(
        [$statement, 'bind_param'],
        $bindArguments
    );
};

// Count all matching projects before pagination.
$countSql = "SELECT COUNT(*) AS total FROM projects" . $where;
$countStmt = mysqli_prepare($conn, $countSql);
$totalProjects = 0;

if ($countStmt) {
    $countParams = $params;

    if (
        $bindDynamicParams($countStmt, $types, $countParams) &&
        mysqli_stmt_execute($countStmt)
    ) {
        $countResult = mysqli_stmt_get_result($countStmt);

        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $totalProjects = (int) ($countRow['total'] ?? 0);
        }
    } else {
        error_log('Project count failed: ' . mysqli_stmt_error($countStmt));
    }

    mysqli_stmt_close($countStmt);
} else {
    error_log('Project count prepare failed: ' . mysqli_error($conn));
}

// Calculate pages and keep the requested page within range.
$totalPages = max(1, (int) ceil($totalProjects / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Load only the projects for the current page.
$sql = "SELECT * FROM projects" . $where . " ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
$result = false;

if ($stmt) {
    $queryParams = $params;
    $queryParams[] = $perPage;
    $queryParams[] = $offset;
    $queryTypes = $types . 'ii';

    if (
        $bindDynamicParams($stmt, $queryTypes, $queryParams) &&
        mysqli_stmt_execute($stmt)
    ) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        error_log('Project query failed: ' . mysqli_stmt_error($stmt));
    }
} else {
    error_log('Project query prepare failed: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Projects | Portfolio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --bg: #080d1b;
            --card: rgba(19, 27, 49, 0.82);
            --text: #f1f5ff;
            --muted: #a6b1ca;
            --blue: #5b8cff;
            --purple: #a855f7;
            --border: rgba(255, 255, 255, 0.10);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at 10% 0%, rgba(91, 140, 255, .18), transparent 32%),
                radial-gradient(circle at 90% 15%, rgba(168, 85, 247, .15), transparent 30%),
                var(--bg);
        }

        .navbar-custom {
            background: rgba(8, 13, 27, .82);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(14px);
        }

        .brand {
            color: #fff;
            font-weight: 800;
            letter-spacing: .4px;
            text-decoration: none;
        }

        .brand span {
            color: var(--blue);
        }

        .btn-gradient {
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
            background: linear-gradient(110deg, var(--blue), var(--purple));
            transition: transform .2s, box-shadow .2s;
        }

        .btn-gradient:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(91, 140, 255, .25);
        }

        .btn-outline-custom {
            color: #e2e8ff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 17px;
        }

        .btn-outline-custom:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
            border-color: rgba(91, 140, 255, .5);
        }

        .page-header {
            text-align: center;
            padding: 65px 0 40px;
        }

        .eyebrow {
            display: inline-block;
            color: #a9c3ff;
            background: rgba(91, 140, 255, .10);
            border: 1px solid rgba(91, 140, 255, .25);
            border-radius: 50px;
            padding: 7px 14px;
            font-size: .85rem;
            margin-bottom: 18px;
        }

        .page-header h1 {
            font-size: clamp(2rem, 5vw, 3.3rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 14px;
        }

        .gradient-text {
            background: linear-gradient(100deg, #75a1ff, #c084fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-header p {
            color: var(--muted);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .project-count {
            color: var(--muted);
            font-size: .9rem;
            margin-bottom: 18px;
        }

        .project-card {
            height: 100%;
            padding: 27px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--card);
            box-shadow: 0 12px 35px rgba(0, 0, 0, .12);
            backdrop-filter: blur(12px);
            transition: transform .25s, border-color .25s, box-shadow .25s;
            overflow-wrap: anywhere;
        }

        
.project-image-wrap {
    width: 100%;
    height: 200px;
    margin-bottom: 22px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #151f3a, #252047);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.project-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.project-card:hover .project-image {
    transform: scale(1.04);
}

.project-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 10px;
    color: #a6b1ca;
    font-size: 0.9rem;
}

.project-image-placeholder span {
    font-size: 2rem;
}

@media (max-width: 576px) {
    .project-image-wrap {
        height: 180px;
    }
}


        .project-card:hover {
            transform: translateY(-6px);
            border-color: rgba(91, 140, 255, .55);
            box-shadow: 0 18px 40px rgba(0, 0, 0, .25);
        }

        .project-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 43px;
            height: 43px;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, var(--blue), var(--purple));
        }

        .project-card h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 13px;
            line-height: 1.45;
        }

        .project-description {
            color: var(--muted);
            font-size: .95rem;
            line-height: 1.8;
            white-space: pre-line;
            margin-bottom: 22px;
        }

        .project-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}

.btn-edit-project,
.btn-delete-project {
    flex: 1;
    text-align: center;
    text-decoration: none;
    border-radius: 9px;
    padding: 10px 12px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

.btn-edit-project {
    color: #c3d4ff;
    border: 1px solid rgba(91, 140, 255, .4);
    background: rgba(91, 140, 255, .1);
}

.btn-edit-project:hover {
    color: #fff;
    background: rgba(91, 140, 255, .25);
}

.btn-delete-project {
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, .35);
    background: rgba(239, 68, 68, .08);
}

.btn-delete-project:hover {
    color: #fff;
    background: #dc2626;
}

        .tech-label {
            color: #d6def4;
            font-size: .8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tech-tag {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid rgba(91, 140, 255, .22);
            border-radius: 7px;
            color: #c3d4ff;
            background: rgba(91, 140, 255, .09);
            font-size: .78rem;
        }

        .category-label {
            display: inline-block;
            color: #d8c4ff;
            background: rgba(168, 85, 247, .12);
            border: 1px solid rgba(168, 85, 247, .22);
            border-radius: 30px;
            padding: 5px 10px;
            font-size: .75rem;
            margin-bottom: 14px;
            text-transform: capitalize;
        }

        .empty-state {
            text-align: center;
            padding: 55px 20px;
            border: 1px dashed rgba(255, 255, 255, .18);
            border-radius: 18px;
            background: rgba(19, 27, 49, .55);
        }

        .empty-state h2 {
            font-size: 1.4rem;
            margin-bottom: 12px;
        }

        .empty-state p {
            color: var(--muted);
            margin-bottom: 22px;
        }

        footer {
            color: #8490aa;
            border-top: 1px solid var(--border);
            margin-top: 70px;
            padding: 25px 0;
            text-align: center;
            font-size: .88rem;
        }

        @media (max-width: 576px) {
            .page-header {
                padding: 45px 0 30px;
            }

            .project-card {
                padding: 22px;
            }

            .navbar-actions {
                gap: 8px;
            }

            .navbar-actions .btn {
                padding: 8px 11px;
                font-size: .85rem;
            }

            .pagination .page-link {
    color: #dbe5ff;
    background: #131b31;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 8px;
    padding: 9px 14px;
}

.pagination .page-link:hover {
    color: #fff;
    background: #26385f;
}

.pagination .page-item.active .page-link {
    color: #fff;
    background: linear-gradient(110deg, #5b8cff, #a855f7);
    border-color: transparent;
}

.pagination .page-item.disabled .page-link {
    color: #75819c;
    background: #0c1325;
    pointer-events: none;
}
        }
    </style>
</head>

<body>
<?php require_once __DIR__ . '/includes/admin-header.php'; ?>


<main class="container pb-4">

    <header class="page-header">
        <span class="eyebrow">MY WORK &amp; PROJECTS</span>

        <h1>Explore My <span class="gradient-text">Projects</span></h1>

        <p>
            A collection of projects showcasing my development skills,
            technologies, and practical learning.
        </p>
    </header>
    <?php if ($deleteMessage !== ''): ?>
    <div class="alert <?= $deleteMessageType === 'success'
        ? 'alert-success'
        : 'alert-danger' ?>"
        role="status">
        <?= htmlspecialchars($deleteMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="GET" action="view-projects.php" class="mb-4">
    <div class="row g-3 align-items-end">

        <div class="col-12 col-md-6">
            <label for="search" class="form-label">Search Projects</label>
            <input
                type="search"
                name="search"
                id="search"
                class="form-control"
                placeholder="Search title, description or technology..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                style="background:#111a30;color:#f1f5ff;border:1px solid #34415d;"
            >
        </div>

        <div class="col-12 col-md-4">
            <label for="category" class="form-label">Category</label>
            <select
                name="category"
                id="category"
                class="form-select"
                style="background:#111a30;color:#f1f5ff;border:1px solid #34415d;"
            >
                <option value="">All Categories</option>
                <option value="frontend" <?= $category === 'frontend' ? 'selected' : '' ?>>Frontend</option>
                <option value="backend" <?= $category === 'backend' ? 'selected' : '' ?>>Backend</option>
                <option value="fullstack" <?= $category === 'fullstack' ? 'selected' : '' ?>>Full Stack</option>
            </select>
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-gradient flex-grow-1">
                Search
            </button>
            <a href="view-projects.php" class="btn btn-outline-custom">
                Reset
            </a>
        </div>

    </div>
</form>

    <?php if ($result): ?>

        <div class="project-count">
            <?= (int) $totalProjects ?>
            project(s) in this collection
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>

            <div class="row g-4">

                <?php
                $number = 1;

                while ($row = mysqli_fetch_assoc($result)):
                    $title = $row['title'] ?? 'Untitled Project';
                    $description = $row['description'] ?? '';
                    $technologies = $row['technologies'] ?? '';
                    $category = $row['category'] ?? 'Project';
                    
$imagePath = $row['image'] ?? '';

$showImage = (
    is_string($imagePath) &&
    preg_match(
        '#\Auploads/[a-f0-9]{32}\.(?:jpg|jpeg|png|webp)\z#i',
        $imagePath
    ) === 1 &&
    is_file(__DIR__ . '/' . $imagePath)
);

                ?>

                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="project-card">

<div class="project-image-wrap">
    <?php if ($showImage): ?>
        <img
            src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
            class="project-image"
            loading="lazy"
        >
    <?php else: ?>
        <div class="project-image-placeholder">
            <span aria-hidden="true">&#128196;</span>
            <span>No image available</span>
        </div>
    <?php endif; ?>
</div>

                            <div class="project-number">
                                <?php echo str_pad((string) $number, 2, '0', STR_PAD_LEFT); ?>
                            </div>

                            <div>
                                <span class="category-label">
                                    <?php
                                    echo htmlspecialchars(
                                        ucwords(str_replace('-', ' ', $category)),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </span>
                            </div>

                            <h2>
                                <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                            </h2>

                            <p class="project-description"><?php
                                echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
                            ?></p>

                            <div class="tech-label">TECHNOLOGIES USED</div>

                            <div class="tech-tags">
                                <?php
                                $techList = array_filter(
                                    array_map('trim', explode(',', $technologies))
                                );

                                if (!empty($techList)):
                                    foreach ($techList as $tech):
                                ?>
                                    <span class="tech-tag">
                                        <?php echo htmlspecialchars($tech, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <span class="tech-tag">Not specified</span>
                                <?php endif; ?>
                            </div>
                                                        <div class="project-actions">

                                <a href="update-project.php?id=<?= (int) $row['id'] ?>"
   class="btn-edit-project">
    Edit / Update
</a>

                                <form action="delete-project.php" method="POST">
                                      <input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars(
        $_SESSION['project_csrf_token'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int) $row['id'] ?>">

                                    <button type="submit"
        name="delete"
        class="btn-delete-project"
        style="width: 100%;"
        onclick="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">
    Delete
</button>

                                </form>

                            </div>

                        </article>
                    </div>

                <?php
                    $number++;
                endwhile;
                ?>

            </div>

<?php if ($totalPages > 1): ?>
    <nav class="mt-5" aria-label="Project pages">
        <ul class="pagination justify-content-center flex-wrap gap-1">

            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="?<?= htmlspecialchars(http_build_query([
                       'search' => $search,
                       'category' => $category,
                       'page' => max(1, $page - 1)
                   ]), ENT_QUOTES, 'UTF-8') ?>">
                    Previous
                </a>
            </li>

            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
                    <a class="page-link"
                       href="?<?= htmlspecialchars(http_build_query([
                           'search' => $search,
                           'category' => $category,
                           'page' => $pageNumber
                       ]), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $pageNumber ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="?<?= htmlspecialchars(http_build_query([
                       'search' => $search,
                       'category' => $category,
                       'page' => min($totalPages, $page + 1)
                   ]), ENT_QUOTES, 'UTF-8') ?>">
                    Next
                </a>
            </li>

        </ul>
    </nav>
<?php endif; ?>

        <?php else: ?>

            <section class="empty-state">
                <h2>No Projects Yet</h2>
                <p>Your projects will appear here after you add them.</p>
                <a href="add-project.php" class="btn btn-gradient">
                    Add Your First Project
                </a>
            </section>

        <?php endif; ?>

    <?php else: ?>

        <section class="empty-state">
            <h2>Projects Could Not Be Loaded</h2>
            <p>Please check your database connection and try again.</p>
            <a href="index.php" class="btn btn-outline-custom">Back to Home</a>
        </section>

    <?php endif; ?>

</main>

<footer>
    <div class="container">
        &copy; <?php echo date('Y'); ?> Mujeeb Ahmed Badar. Project Management
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>