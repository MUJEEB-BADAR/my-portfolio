
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
.admin-header {
    background: #0b1224;
    border-bottom: 1px solid #273449;
    padding: 16px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
}

.admin-brand {
    color: #a78bfa;
    font-size: 22px;
    font-weight: 700;
    text-decoration: none;
}

.admin-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.admin-nav a {
    color: #cbd5e1;
    text-decoration: none;
    padding: 9px 13px;
    border: 1px solid #334155;
    border-radius: 8px;
    transition: 0.3s;
}

.admin-nav a:hover,
.admin-nav a.active {
    color: #ffffff;
    background: linear-gradient(135deg, #4338ca, #7c3aed);
    border-color: #8b5cf6;
}

.admin-nav a.logout {
    color: #c4b5fd;
    border-color: #8b5cf6;
}

.admin-nav a.logout:hover {
    color: #fff;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

@media (max-width: 600px) {
    .admin-header {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<header class="admin-header">
    <a href="index.php" class="admin-brand">MyPortfolio</a>

    
<nav class="admin-nav">
    <a href="index.php">Home</a>

    <a href="admin-dashboard.php"
       class="<?= $currentPage === 'admin-dashboard.php' ? 'active' : '' ?>">
        Dashboard
    </a>


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

        <a href="active-sessions.php"
           class="<?= $currentPage === 'active-sessions.php' ? 'active' : '' ?>">
            Active Sessions
        </a>

        <a href="login-history.php"
           class="<?= $currentPage === 'login-history.php' ? 'active' : '' ?>">
            Login History
        </a>

        <?php if (
            isset($_SESSION['admin_role']) &&
            $_SESSION['admin_role'] === 'super_admin'
        ): ?>
            <a href="manage-admins.php"
               class="<?= $currentPage === 'manage-admins.php' ? 'active' : '' ?>">
                Manage Admins
            </a>
        <?php endif; ?>

        <a href="admin-logout.php" class="logout">
            Logout
        </a>
    </nav>
</header>