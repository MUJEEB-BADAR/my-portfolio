
<?php
require_once __DIR__ . '/includes/super-admin-auth.php';

if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

$sql = "SELECT id, username, role, is_active, created_at
        FROM admin_users
        ORDER BY
            CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END,
            id ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    error_log('Manage admins query failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Could not load admin accounts. Please check the database configuration.');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins | MyPortfolio</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #0f172a;
            color: #e2e8f0;
            font-family: Arial, sans-serif;
        }

        .page-container {
            width: 92%;
            max-width: 1400px;
            margin: 35px auto;
        }

        .page-title {
            color: #f8fafc;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #94a3b8;
            margin-bottom: 25px;
        }

        .notice {
            padding: 14px 16px;
            margin-bottom: 20px;
            border: 1px solid #166534;
            border-radius: 9px;
            background: #123b2b;
            color: #a7f3d0;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .button {
            display: inline-block;
            padding: 11px 17px;
            border-radius: 8px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: opacity .2s;
        }

        .button:hover {
            color: #fff;
            opacity: .9;
        }

        .table-wrap {
            overflow-x: auto;
            background: #111c31;
            border: 1px solid #263449;
            border-radius: 12px;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #263449;
        }

        th {
            background: #0b1224;
            color: #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
        }

        tbody tr:hover td {
            background: #17243b;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .active {
            background: #143a2b;
            color: #86efac;
        }

        .disabled {
            background: #451d2a;
            color: #fecdd3;
        }

        .role {
            background: #252454;
            color: #c7d2fe;
        }

        .empty {
            padding: 28px;
            color: #94a3b8;
            text-align: center;
        }

        .action-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .action-form {
            display: inline-flex;
            margin: 0;
        }

        .action-button {
            padding: 8px 12px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: #4f46e5;
            color: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .action-button:hover {
            filter: brightness(1.12);
        }

        .delete-button {
            background: #b42336;
        }

        .protected {
            color: #94a3b8;
            font-size: 13px;
        }

        .username {
            color: #c4b5fd;
            font-weight: 700;
        }

        .created-at {
            white-space: nowrap;
            color: #cbd5e1;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .page-container {
                width: 95%;
                margin: 22px auto;
            }

            .page-title {
                font-size: 25px;
            }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/admin-header.php'; ?>

<main class="page-container">

    <h1 class="page-title">Manage Admins</h1>

    <p class="subtitle">
        Manage administrator accounts, permissions and account status.
    </p>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <div class="notice">
            Admin account deleted successfully. Login history has been preserved.
        </div>
    <?php endif; ?>

    <div class="actions">
        <a class="button" href="create-admin.php">
            + Create New Admin
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($admin = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int) $admin['id'] ?></td>

                        <td class="username">
                            <?= e($admin['username']) ?>
                        </td>

                        <td>
                            <span class="badge role">
                                <?= $admin['role'] === 'super_admin'
                                    ? 'Super Admin'
                                    : 'Normal Admin' ?>
                            </span>
                        </td>

                        <td>
                            <?php if ((int) $admin['is_active'] === 1): ?>
                                <span class="badge active">Active</span>
                            <?php else: ?>
                                <span class="badge disabled">Disabled</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($admin['role'] === 'admin'): ?>

                                <div class="action-group">

                                    <form
                                        class="action-form"
                                        method="POST"
                                        action="toggle-admin-status.php"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['admin_csrf_token']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="admin_id"
                                            value="<?= (int) $admin['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="new_status"
                                            value="<?= (int) $admin['is_active'] === 1 ? '0' : '1' ?>"
                                        >

                                        <button type="submit" class="action-button">
                                            <?= (int) $admin['is_active'] === 1
                                                ? 'Disable'
                                                : 'Enable' ?>
                                        </button>
                                    </form>

                                    <form
                                        class="action-form"
                                        method="POST"
                                        action="delete-admin.php"
                                        onsubmit="return confirm('Permanently delete this admin account? Its login history will be retained.');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['admin_csrf_token']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="admin_id"
                                            value="<?= (int) $admin['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="action-button delete-button"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </div>

                            <?php else: ?>

                                <span class="protected">Protected account</span>

                            <?php endif; ?>
                        </td>

                        <td class="created-at">
                            <?= e($admin['created_at']) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="6" class="empty">
                        No administrator accounts found.
                    </td>
                </tr>

            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>

<?php
mysqli_free_result($result);
?>
