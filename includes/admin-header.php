
<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$isSuperAdmin = (
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'super_admin'
);

$projectPages = [
    'add-project.php',
    'view-projects.php',
    'update-project.php'
];

$isProjectPage = in_array($currentPage, $projectPages, true);
?>

<style>
.admin-header {
    position: relative;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    padding: 16px 5%;
    margin-bottom: 30px;
    background: rgba(11, 18, 36, .96);
    border-bottom: 1px solid rgba(139, 164, 255, .16);
    box-shadow: 0 8px 25px rgba(0, 0, 0, .16);
}

.admin-brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    color: #ffffff;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -.5px;
    text-decoration: none;
}

.admin-brand:hover {
    color: #ffffff;
}

.brand-icon {
    display: flex;
    width: 38px;
    height: 38px;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    color: #ffffff;
    background: linear-gradient(135deg, #5b8cff, #a855f7);
    font-size: 19px;
    font-weight: 800;
}

.admin-brand span:last-child {
    background: linear-gradient(100deg, #8db3ff, #c084fc);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.admin-nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    flex-wrap: wrap;
}

.admin-nav a,
.projects-dropdown summary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 13px;
    border: 1px solid transparent;
    border-radius: 9px;
    color: #b9c5da;
    background: transparent;
    font-family: inherit;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    transition: .25s ease;
}

.admin-nav a:hover,
.admin-nav a.active,
.projects-dropdown[open] summary {
    color: #ffffff;
    background: rgba(91, 140, 255, .13);
    border-color: rgba(91, 140, 255, .25);
}

.projects-dropdown {
    position: relative;
}

.projects-dropdown summary {
    list-style: none;
}

.projects-dropdown summary::-webkit-details-marker {
    display: none;
}

.dropdown-arrow {
    font-size: .7rem;
    transition: transform .2s ease;
}

.projects-dropdown[open] .dropdown-arrow {
    transform: rotate(180deg);
}

.projects-menu {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    z-index: 200;
    display: flex;
    min-width: 190px;
    flex-direction: column;
    gap: 4px;
    padding: 8px;
    border: 1px solid #334155;
    border-radius: 12px;
    background: #111c31;
    box-shadow: 0 15px 35px rgba(0, 0, 0, .35);
}

.projects-menu a {
    justify-content: flex-start;
    width: 100%;
    padding: 10px 12px;
}

.admin-nav a.website-link {
    color: #a9c3ff;
    border-color: rgba(91, 140, 255, .25);
}

.admin-nav a.website-link:hover {
    color: #ffffff;
    background: rgba(91, 140, 255, .15);
}

.admin-nav a.logout {
    color: #fca5a5;
    border-color: rgba(248, 113, 113, .25);
}

.admin-nav a.logout:hover {
    color: #ffffff;
    background: rgba(239, 68, 68, .15);
    border-color: rgba(248, 113, 113, .45);
}

.admin-nav a:focus-visible,
.projects-dropdown summary:focus-visible {
    outline: 2px solid #8b5cf6;
    outline-offset: 3px;
}

@media (max-width: 900px) {
    .admin-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 15px;
        padding: 17px 4%;
    }

    .admin-nav {
        justify-content: flex-start;
        width: 100%;
    }
}

@media (max-width: 576px) {
    .admin-header {
        padding: 15px 4%;
    }

    .admin-nav {
        gap: 6px;
    }

    .admin-nav a,
    .projects-dropdown summary {
        padding: 9px 10px;
        font-size: .82rem;
    }

    .projects-menu {
        left: 0;
    }
}
</style>

<header class="admin-header">

    <a href="admin-dashboard.php" class="admin-brand">
        <span class="brand-icon">M</span>
        <span>MyPortfolio</span>
    </a>

    <nav class="admin-nav" aria-label="Admin navigation">

        <a href="admin-dashboard.php"
           class="<?= $currentPage === 'admin-dashboard.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <details class="projects-dropdown" <?= $isProjectPage ? 'open' : '' ?>>
            <summary class="<?= $isProjectPage ? 'active' : '' ?>">
                Projects
                <span class="dropdown-arrow">▼</span>
            </summary>

            <div class="projects-menu">
                <a href="add-project.php"
                   class="<?= $currentPage === 'add-project.php' ? 'active' : '' ?>">
                    Add Project
                </a>

                <a href="view-projects.php"
                   class="<?= $currentPage === 'view-projects.php' ? 'active' : '' ?>">
                    View Projects
                </a>

                <a href="update-project.php"
                   class="<?= $currentPage === 'update-project.php' ? 'active' : '' ?>">
                    Update Project
                </a>
            </div>
        </details>

        <?php if ($isSuperAdmin): ?>

            <a href="active-sessions.php"
               class="<?= $currentPage === 'active-sessions.php' ? 'active' : '' ?>">
                Active Sessions
            </a>

            <a href="login-history.php"
               class="<?= $currentPage === 'login-history.php' ? 'active' : '' ?>">
                Login History
            </a>

            <a href="manage-admins.php"
               class="<?= $currentPage === 'manage-admins.php' ? 'active' : '' ?>">
                Manage Admins
            </a>

        <?php endif; ?>

        <a href="index.php" class="website-link">
            View Website ↗
        </a>

        <a href="admin-logout.php" class="logout">
            Logout
        </a>

    </nav>

</header>
