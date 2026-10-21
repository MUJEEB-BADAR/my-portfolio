
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Expire sessions with no activity for 30 minutes.
$expireSql = "UPDATE login_activity
              SET status = 'expired'
              WHERE status = 'active'
                AND last_activity < NOW() - INTERVAL 30 MINUTE";

if (!mysqli_query($conn, $expireSql)) {
    error_log('Active session expiry update failed: ' . mysqli_error($conn));
}

// Show active sessions belonging to existing, enabled admin accounts.
$sql = "SELECT
            la.username,
            la.ip_address,
            la.user_agent,
            la.login_at,
            la.last_activity
        FROM login_activity AS la
        INNER JOIN admin_users AS au
            ON au.id = la.admin_id
        WHERE la.status = 'active'
          AND la.last_activity >= NOW() - INTERVAL 30 MINUTE
          AND au.is_active = 1
          AND au.role IN ('admin', 'super_admin')
        ORDER BY la.last_activity DESC";

$result = mysqli_query($conn, $sql);

$pageError = $result === false;
$sessionCount = $result ? mysqli_num_rows($result) : 0;

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Active Sessions | MyPortfolio Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #0f172a;
            color: #e2e8f0;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .page-container {
            width: 92%;
            max-width: 1500px;
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

        .panel {
            background: #111c31;
            border: 1px solid #263449;
            border-radius: 14px;
            padding: 24px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary h2 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: #f8fafc;
        }

        .count {
            display: inline-block;
            background: #292044;
            color: #c4b5fd;
            padding: 8px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .table-responsive {
            border: 1px solid #29364b;
            border-radius: 10px;
        }

        .table {
            margin: 0;
            min-width: 1000px;
            vertical-align: middle;
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            --bs-table-border-color: #263449;
            --bs-table-hover-bg: #17243b;
            --bs-table-hover-color: #fff;
        }

        .table thead th {
            background: #0b1224;
            color: #cbd5e1;
            padding: 15px 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
            border-bottom: 1px solid #334155;
        }

        .table tbody td {
            padding: 15px 12px;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .username {
            color: #c4b5fd;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            background: #143a2b;
            color: #86efac;
            border: 1px solid #226442;
            border-radius: 20px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .device {
            min-width: 220px;
            max-width: 320px;
            color: #cbd5e1;
            overflow-wrap: anywhere;
            line-height: 1.5;
        }

        .empty {
            text-align: center;
            padding: 45px 20px;
            color: #94a3b8;
        }

        .empty h3 {
            color: #e2e8f0;
            font-size: 19px;
            margin-bottom: 8px;
        }

        .error-message {
            background: #451a24;
            border: 1px solid #7f1d1d;
            color: #fecaca;
            padding: 15px;
            border-radius: 9px;
        }

        .footer-note {
            color: #64748b;
            font-size: 12px;
            margin: 16px 0 0;
        }

        @media (max-width: 768px) {
            .page-container {
                width: 95%;
                margin: 22px auto;
            }

            .panel {
                padding: 16px;
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

    <h1 class="page-title">Active Sessions</h1>

    <p class="subtitle">
        Review recent login activity for enabled administrator accounts.
    </p>

    <section class="panel">

        <div class="summary">
            <h2>Current Session Overview</h2>

            <span class="count">
                <?= $pageError ? 'Unavailable' : $sessionCount . ' active session(s)' ?>
            </span>
        </div>

        <?php if ($pageError): ?>

            <div class="error-message">
                Unable to load active sessions. Please check the database
                connection and try again.
            </div>

        <?php elseif ($sessionCount === 0): ?>

            <div class="empty">
                <h3>No active sessions found</h3>
                <p class="mb-0">
                    No enabled administrator accounts have a session with
                    activity recorded in the last 30 minutes.
                </p>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Admin Username</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                            <th>Last Activity</th>
                            <th>Status</th>
                            <th>Device / Browser</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <span class="username">
                                        <?= e($row['username'] ?? 'Unknown') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($row['ip_address'] ?? 'Unknown') ?>
                                </td>

                                <td>
                                    <?= e($row['login_at'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= e($row['last_activity'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <span class="status">Active</span>
                                </td>

                                <td>
                                    <div class="device">
                                        <?= e($row['user_agent'] ?? 'Unknown device') ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

        <p class="footer-note">
            Sessions inactive for more than 30 minutes are marked expired.
            Disabled or deleted admin accounts are not listed as active.
        </p>

    </section>
</main>

</body>
</html>
