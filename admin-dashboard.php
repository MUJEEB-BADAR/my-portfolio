
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Get project statistics.
$stats = [
    'total' => 0,
    'frontend' => 0,
    'backend' => 0,
    'fullstack' => 0,
];

$statsSql = "SELECT
    COUNT(*) AS total,
    COALESCE(SUM(category = 'frontend'), 0) AS frontend,
    COALESCE(SUM(category = 'backend'), 0) AS backend,
    COALESCE(SUM(category = 'fullstack'), 0) AS fullstack
    FROM projects";

$statsResult = mysqli_query($conn, $statsSql);

if ($statsResult) {
    $statsRow = mysqli_fetch_assoc($statsResult);

    if ($statsRow) {
        foreach ($stats as $key => $value) {
            $stats[$key] = (int) ($statsRow[$key] ?? 0);
        }
    }

    mysqli_free_result($statsResult);
} else {
    error_log('Dashboard statistics query failed: ' . mysqli_error($conn));
}

 // Get active admin account count for Super Admin.
$activeAdmins = 0;

$inactiveAdmins = 0;
$totalAdminAccounts = 0;

if (
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'super_admin'
) {
    $activeAdminsSql = "SELECT COUNT(*) AS total
                        FROM admin_users
                        WHERE is_active = 1";

    $activeAdminsResult = mysqli_query($conn, $activeAdminsSql);

    if ($activeAdminsResult) {
        $activeAdminsRow = mysqli_fetch_assoc($activeAdminsResult);
        $activeAdmins = (int) ($activeAdminsRow['total'] ?? 0);

        mysqli_free_result($activeAdminsResult);
    } else {
        error_log(
            'Dashboard active admins query failed: ' . mysqli_error($conn)
        );
    }
    
    $inactiveAdminsSql = "SELECT COUNT(*) AS total
                          FROM admin_users
                          WHERE is_active = 0";

    $inactiveAdminsResult = mysqli_query($conn, $inactiveAdminsSql);

    if ($inactiveAdminsResult) {
        $inactiveAdminsRow = mysqli_fetch_assoc($inactiveAdminsResult);
        $inactiveAdmins = (int) ($inactiveAdminsRow['total'] ?? 0);

        mysqli_free_result($inactiveAdminsResult);
    } else {
        error_log(
            'Dashboard inactive admins query failed: ' . mysqli_error($conn)
        );
    }
    
    $totalAccountsSql = "SELECT COUNT(*) AS total FROM admin_users";

    $totalAccountsResult = mysqli_query($conn, $totalAccountsSql);

    if ($totalAccountsResult) {
        $totalAccountsRow = mysqli_fetch_assoc($totalAccountsResult);
        $totalAdminAccounts = (int) ($totalAccountsRow['total'] ?? 0);

        mysqli_free_result($totalAccountsResult);
    } else {
        error_log(
            'Dashboard total admin accounts query failed: ' . mysqli_error($conn)
        );
    }


}

/* Get recent admin login activity for Super Admin. */
$recentAdminActivity = [];

if (
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'super_admin'
) {
    $activitySql = "SELECT
                        username,
                        login_at,
                        ip_address,
                        status
                    FROM login_activity
                    ORDER BY login_at DESC
                    LIMIT 5";

    $activityResult = mysqli_query($conn, $activitySql);

    if ($activityResult) {
        while ($activityRow = mysqli_fetch_assoc($activityResult)) {
            $recentAdminActivity[] = $activityRow;
        }

        mysqli_free_result($activityResult);
    } else {
        error_log(
            'Dashboard recent admin activity query failed: '
            . mysqli_error($conn)
        );
    }
}

// Get the five most recently added projects.
$recentProjects = [];

$recentSql = "SELECT id, title, slug, category, image, created_at
              FROM projects
              ORDER BY created_at DESC, id DESC
              LIMIT 5";

$recentResult = mysqli_query($conn, $recentSql);

if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentProjects[] = $row;
    }

    mysqli_free_result($recentResult);
} else {
    error_log('Dashboard recent projects query failed: ' . mysqli_error($conn));
}

function dashboardEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Dashboard | My Portfolio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --bg: #080d1b;
            --card: #131b31;
            --text: #f1f5ff;
            --muted: #a6b1ca;
            --blue: #5b8cff;
            --purple: #a855f7;
            --border: rgba(255, 255, 255, .10);
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
                radial-gradient(circle at 10% 0%, #5b8cff25, transparent 32%),
                radial-gradient(circle at 90% 10%, #a855f720, transparent 30%),
                var(--bg);
        }

        .dashboard {
            padding-top: 42px;
            padding-bottom: 55px;
        }

        .welcome-panel {
            padding: 30px;
            margin-bottom: 28px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: linear-gradient(120deg, #172747, #21183c);
        }

        .eyebrow {
            color: #a9c3ff;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .welcome-panel h1 {
            margin: 12px 0;
            font-size: clamp(1.8rem, 4vw, 2.7rem);
            font-weight: 800;
        }

        .welcome-panel p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .section-title {
            margin-bottom: 18px;
            font-size: 1.25rem;
            font-weight: 750;
        }

        .stat-card {
            height: 100%;
            padding: 23px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: rgba(19, 27, 49, .88);
            transition: transform .2s, border-color .2s;
        }


.stat-link {
    display: block;
    height: 100%;
    color: inherit;
    text-decoration: none;
}

.stat-link:focus-visible {
    outline: 2px solid #a78bfa;
    outline-offset: 4px;
    border-radius: 16px;
}


        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #5b8cff80;
        }

        .stat-label {
            color: var(--muted);
            font-size: .9rem;
        }

        .stat-number {
            margin: 12px 0 5px;
            font-size: 2.2rem;
            font-weight: 800;
        }

        .stat-icon {
            display: inline-flex;
            width: 43px;
            height: 43px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: #dbe5ff;
            background: #5b8cff20;
            font-size: 1.25rem;
        }

        .quick-card {
            display: flex;
            height: 100%;
            flex-direction: column;
            align-items: flex-start;
            padding: 23px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: rgba(19, 27, 49, .88);
        }

        .quick-card h3 {
            margin: 16px 0 8px;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .quick-card p {
            flex: 1;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.7;
        }

        .btn-gradient {
            display: inline-block;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 9px;
            padding: 10px 15px;
            font-size: .9rem;
            font-weight: 650;
            background: linear-gradient(110deg, var(--blue), var(--purple));
        }

        .btn-gradient:hover {
            color: white;
            filter: brightness(1.1);
        }

        .btn-outline-custom {
            display: inline-block;
            color: #dbe5ff;
            text-decoration: none;
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 9px 14px;
            font-size: .9rem;
        }

        .btn-outline-custom:hover {
            color: white;
            background: #ffffff0d;
        }

        
.recent-panel {
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: 16px;
    background: rgba(19, 27, 49, .88);
}

/* Space between Recent Projects and Admin Activity */
.recent-panel + section {
    margin-top: 42px;
}

.activity-table-wrapper {
    margin-top: 0;
}

.activity-footer {
    margin-top: 20px;
}


        .recent-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
        }

        .recent-row:last-child {
            border-bottom: none;
        }

        .recent-image {
            width: 76px;
            height: 58px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 9px;
            background: #202b47;
        }

        .recent-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .recent-placeholder {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 1.3rem;
        }

        .recent-info {
            min-width: 0;
            flex: 1;
        }

        .recent-info h3 {
            margin: 0 0 6px;
            overflow-wrap: anywhere;
            font-size: .98rem;
            font-weight: 700;
        }

        .recent-info p {
            margin: 0;
            color: var(--muted);
            font-size: .82rem;
            text-transform: capitalize;
        }

        .empty-message {
            padding: 30px 20px;
            color: var(--muted);
            text-align: center;
        }

        .dashboard-footer {
            padding-top: 20px;
            margin-top: 35px;
            border-top: 1px solid var(--border);
            color: #8490aa;
            text-align: center;
            font-size: .85rem;
        }

        
/* Responsive dashboard styles */
@media (max-width: 576px) {
    .dashboard {
        padding-top: 25px;
        padding-bottom: 35px;
    }

    .welcome-panel {
        padding: 23px;
    }

    .stat-card,
    .quick-card {
        padding: 19px;
    }

    .recent-row {
        gap: 11px;
        padding: 15px;
    }

    .recent-image {
        width: 64px;
        height: 54px;
    }
}

/* Recent project actions */
.recent-row > .d-flex {
    flex-shrink: 0;
}

@media (max-width: 480px) {
    .recent-row {
        flex-wrap: wrap;
    }

    .recent-info {
        flex: 1 1 calc(100% - 80px);
    }

    .recent-row > .d-flex {
        display: flex;
        width: 100%;
        padding-left: 75px;
    }

    .recent-row > .d-flex a {
        flex: 1;
        text-align: center;
    }
}

/* Recent Admin Activity */
.activity-table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #2b3852;
    border-radius: 14px;
    background: #111c31;
    scrollbar-width: thin;
    scrollbar-color: #526487 #111c31;
}

.activity-table {
    width: 100%;
    min-width: 780px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    color: var(--text);
    vertical-align: middle;
}

.activity-table thead th {
    padding: 18px 26px;
    background: #0b1224;
    color: #a9c3ff;
    border-bottom: 1px solid #2b3852;
    font-size: .76rem;
    font-weight: 700;
    letter-spacing: .7px;
    text-transform: uppercase;
    white-space: nowrap;
}

.activity-table tbody td {
    padding: 21px 26px;
    background: #111c31;
    color: #e2e8f0;
    border-bottom: 1px solid #263449;
    font-size: .9rem;
    white-space: nowrap;
    transition: background .2s ease;
}

.activity-table tbody tr:last-child td {
    border-bottom: none;
}

.activity-table tbody tr:hover td {
    background: #192740;
}

.activity-username {
    display: inline-block;
    min-width: 110px;
    color: #d2c2ff;
    font-weight: 700;
}

.activity-ip {
    display: inline-block;
    min-width: 125px;
    color: #a6b1ca;
    font-family: Consolas, monospace;
    font-size: .84rem;
}

.activity-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 105px;
    padding: 8px 13px;
    border: 1px solid transparent;
    border-radius: 20px;
    font-size: .76rem;
    font-weight: 700;
    white-space: nowrap;
}

.activity-status-active {
    color: #86efac;
    background: #143a2b;
    border-color: #276749;
}

.activity-status-logged_out {
    color: #cbd5e1;
    background: #29364b;
    border-color: #475569;
}

.activity-status-expired {
    color: #fde68a;
    background: #493719;
    border-color: #806323;
}

.activity-status-disabled {
    color: #fca5a5;
    background: #4b2028;
    border-color: #7f303d;
}

.activity-status-unknown {
    color: #cbd5e1;
    background: #29364b;
    border-color: #475569;
}

/* Activity section footer */
.activity-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 20px;
    padding: 0 2px;
}

.activity-footer .stat-label {
    line-height: 1.7;
}

/* Small screens */
@media (max-width: 576px) {
    .activity-table thead th {
        padding: 17px 22px;
    }

    .activity-table tbody td {
        padding: 20px 22px;
    }

    .activity-footer {
        align-items: stretch;
        gap: 12px;
        margin-top: 16px;
    }

    .activity-footer a {
        text-align: center;
    }
}


        
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/admin-header.php'; ?>

<main class="container dashboard">

    
<section class="welcome-panel">
    <span class="eyebrow">Control Center</span>

    <h1>
        Welcome,
        <?= dashboardEscape($_SESSION['admin_username'] ?? 'Admin') ?>!
    </h1>

    <p>
        Manage your portfolio projects, review your work and keep
        your website content organized from one place.
    </p>

    <div class="mt-3">
        <span class="stat-label">Account role:</span>

        <span class="badge rounded-pill text-bg-primary ms-1">
            <?= dashboardEscape(
                ucwords(str_replace(
                    '_',
                    ' ',
                    $_SESSION['admin_role'] ?? 'admin'
                ))
            ) ?>
        </span>
    </div>
</section>


    <section class="mb-5">
        <h2 class="section-title">Project Overview</h2>

        <div class="row g-3">
            
<div class="col-12 col-sm-6 col-xl-3">
    <a href="view-projects.php"
       class="stat-link"
       aria-label="View all projects">
        <div class="stat-card">
            <span class="stat-icon" aria-hidden="true">&#128196;</span>
            <div class="stat-label mt-3">Total Projects</div>
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">All saved projects</div>
        </div>
    </a>
</div>


            
<div class="col-12 col-sm-6 col-xl-3">
    <a href="view-projects.php?category=frontend"
       class="stat-link"
       aria-label="View frontend projects">
        <div class="stat-card">
            <span class="stat-icon" aria-hidden="true">&#127912;</span>
            <div class="stat-label mt-3">Frontend</div>
            <div class="stat-number"><?= $stats['frontend'] ?></div>
            <div class="stat-label">Frontend projects</div>
        </div>
    </a>
</div>


            
<div class="col-12 col-sm-6 col-xl-3">
    <a href="view-projects.php?category=backend"
       class="stat-link"
       aria-label="View backend projects">
        <div class="stat-card">
            <span class="stat-icon" aria-hidden="true">&#9881;</span>
            <div class="stat-label mt-3">Backend</div>
            <div class="stat-number"><?= $stats['backend'] ?></div>
            <div class="stat-label">Backend projects</div>
        </div>
    </a>
</div>


            
<div class="col-12 col-sm-6 col-xl-3">
    <a href="view-projects.php?category=fullstack"
       class="stat-link"
       aria-label="View full stack projects">
        <div class="stat-card">
            <span class="stat-icon" aria-hidden="true">&#128187;</span>
            <div class="stat-label mt-3">Full Stack</div>
            <div class="stat-number"><?= $stats['fullstack'] ?></div>
            <div class="stat-label">Full stack projects</div>
        </div>
    </a>
</div>

        </div>
        
    </section>
<?php if (
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'super_admin'
): ?>
    <section class="mb-5">
        <h2 class="section-title">Account Overview</h2>

        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">&#128101;</span>
                    <div class="stat-label mt-3">Active Admins</div>
                    <div class="stat-number"><?= $activeAdmins ?></div>
                    <div class="stat-label">Active admin accounts</div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">&#128683;</span>
                    <div class="stat-label mt-3">Inactive Admins</div>
                    <div class="stat-number"><?= $inactiveAdmins ?></div>
                    <div class="stat-label">Disabled admin accounts</div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <span class="stat-icon" aria-hidden="true">&#128100;</span>
                    <div class="stat-label mt-3">Total Accounts</div>
                    <div class="stat-number"><?= $totalAdminAccounts ?></div>
                    <div class="stat-label">All admin accounts</div>
                </div>
            </div>


        </div>
    </section>
<?php endif; ?>
    <section class="mb-5">
        <h2 class="section-title">Quick Actions</h2>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="quick-card">
                    <span class="stat-icon" aria-hidden="true">&#43;</span>
                    <h3>Add a Project</h3>
                    <p>Add a new project with its description, technologies, category and image.</p>
                    <a href="add-project.php" class="btn-gradient">Add Project</a>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="quick-card">
                    <span class="stat-icon" aria-hidden="true">&#128065;</span>
                    <h3>Manage Projects</h3>
                    <p>Search, filter, edit and delete existing portfolio projects.</p>
                    <a href="view-projects.php" class="btn-gradient">View Projects</a>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="quick-card">
                    <span class="stat-icon" aria-hidden="true">&#127760;</span>
                    <h3>Public Portfolio</h3>
                    <p>Open your public website to see how visitors experience your portfolio.</p>
                    <a href="index.php" class="btn-gradient">
    View Website
</a>
                </div>
            </div>
            
<?php if (
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'super_admin'
): ?>
    <div class="col-12 col-md-4">
        <div class="quick-card">
            <span class="stat-icon" aria-hidden="true">&#128101;</span>
            <h3>Manage Admins</h3>
            <p>Create admin accounts, review existing accounts and manage their access.</p>
            <a href="manage-admins.php" class="btn-gradient">Manage Admins</a>
        </div>
    </div>
<?php endif; ?>

        </div>
    </section>

    <section>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <h2 class="section-title mb-0">Recent Projects</h2>
            <a href="view-projects.php" class="btn-outline-custom">View All Projects</a>
        </div>

        <div class="recent-panel">
            <?php if (!empty($recentProjects)): ?>

                <?php foreach ($recentProjects as $project): ?>
                    <?php
                    $recentImage = $project['image'] ?? '';

                    $validRecentImage = (
                        is_string($recentImage) &&
                        preg_match(
                            '#\Auploads/[a-f0-9]{32}\.(?:jpg|jpeg|png|webp)\z#i',
                            $recentImage
                        ) === 1 &&
                        is_file(__DIR__ . '/' . $recentImage)
                    );
                    ?>

                    <div class="recent-row">
                        <div class="recent-image">
                            <?php if ($validRecentImage): ?>
                                <img
                                    src="<?= dashboardEscape($recentImage) ?>"
                                    alt="<?= dashboardEscape($project['title'] ?? 'Project') ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="recent-placeholder" aria-hidden="true">&#128196;</div>
                            <?php endif; ?>
                        </div>

                        
<div class="recent-info">
    <h3><?= dashboardEscape($project['title'] ?? 'Untitled Project') ?></h3>
    <p>
        <?= dashboardEscape(ucwords(str_replace('_', ' ', $project['category'] ?? 'Uncategorized'))) ?>
    </p>
    <p>
        Added:
        <?php
        $createdAt = $project['created_at'] ?? '';
        $timestamp = is_string($createdAt) ? strtotime($createdAt) : false;
        echo $timestamp !== false ? date('d M Y', $timestamp) : 'Date unavailable';
        ?>
    </p>
</div>


                        
<div class="d-flex flex-wrap gap-2">
    <a
    href="project-details.php?slug=<?= dashboardEscape(rawurlencode((string) ($project['slug'] ?? ''))) ?>"
    class="btn-outline-custom"
>View Project</a>

    <a
        href="update-project.php?id=<?= (int) $project['id'] ?>"
        class="btn-outline-custom"
    >Edit</a>
</div>

                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="empty-message">
                    No projects have been added yet.
                    <div class="mt-3">
                        <a href="add-project.php" class="btn-gradient">Add Your First Project</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (
        isset($_SESSION['admin_role']) &&
        $_SESSION['admin_role'] === 'super_admin'
    ): ?>
        <section class="mb-5" style="margin-top: 50px !important;">
            <h2 class="section-title">Recent Admin Activity</h2>

            <div class="recent-panel">
                <?php if (!empty($recentAdminActivity)): ?>
                    <div class="activity-table-wrapper">
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Login Time</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAdminActivity as $activity): ?>
                                    <?php
                                    $activityStatus = (string) ($activity['status'] ?? '');
                                    $validActivityStatuses = [
                                        'active',
                                        'logged_out',
                                        'expired',
                                        'disabled'
                                    ];
                                    ?>
                                    <tr>
                                        <td>
    <span class="activity-username">
        <?= dashboardEscape($activity['username'] ?? 'Unknown') ?>
    </span>
</td>
                                        <td>
                                            <?= dashboardEscape(
                                                $activity['login_at'] ?? 'N/A'
                                            ) ?>
                                        </td>
                                        <td>
    <span class="activity-ip">
        <?= dashboardEscape($activity['ip_address'] ?? 'N/A') ?>
    </span>
</td>
                                        
<td>
    <?php
    $validActivityStatuses = [
        'active',
        'logged_out',
        'expired',
        'disabled'
    ];

    $activityStatus = (string) ($activity['status'] ?? '');
    $statusClass = in_array(
        $activityStatus,
        $validActivityStatuses,
        true
    ) ? $activityStatus : 'unknown';
    ?>

    <span class="activity-status activity-status-<?= dashboardEscape($statusClass) ?>">
        <?= dashboardEscape(
            $statusClass === 'unknown'
                ? 'Unknown'
                : ucwords(str_replace('_', ' ', $activityStatus))
        ) ?>
    </span>
</td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        No admin login activity found.
                    </div>
                <?php endif; ?>
            </div>

            <div class="activity-footer">
    <span class="stat-label">
        Showing the 5 most recent login records
    </span>

    <a href="login-history.php" class="btn-outline-custom">
        View Full Login History
    </a>
</div>
        </section>
    <?php endif; ?>

    <footer class="dashboard-footer">
        &copy; <?= date('Y') ?> Mujeeb Ahmed Badar. Admin Dashboard
    </footer>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
