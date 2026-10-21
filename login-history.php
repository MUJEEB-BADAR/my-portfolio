
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$fromDate = (string) ($_GET['from_date'] ?? '');
$toDate = (string) ($_GET['to_date'] ?? '');

$allowedStatuses = [
    'active',
    'logged_out',
    'expired',
    'disabled'
];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

function validDate(string $date): bool
{
    if ($date === '') {
        return false;
    }

    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    $errors = DateTime::getLastErrors();

    return $parsed !== false
        && $parsed->format('Y-m-d') === $date
        && (
            $errors === false
            || (
                $errors['warning_count'] === 0
                && $errors['error_count'] === 0
            )
        );
}

if (!validDate($fromDate)) {
    $fromDate = '';
}

if (!validDate($toDate)) {
    $toDate = '';
}

if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
    $fromDate = '';
    $toDate = '';
}

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(username LIKE ? OR ip_address LIKE ?)';
    $searchTerm = '%' . $search . '%';

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
    $types .= 's';
}

if ($fromDate !== '') {
    $where[] = 'login_at >= ?';
    $params[] = $fromDate . ' 00:00:00';
    $types .= 's';
}

if ($toDate !== '') {
    $endDate = new DateTime($toDate);
    $endDate->modify('+1 day');

    $where[] = 'login_at < ?';
    $params[] = $endDate->format('Y-m-d') . ' 00:00:00';
    $types .= 's';
}

$sql = "SELECT
            username,
            ip_address,
            user_agent,
            login_at,
            last_activity,
            logout_at,
            status
        FROM login_activity";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY login_at DESC LIMIT 200';

$records = false;
$pageError = '';

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $pageError = 'Unable to prepare the login history query.';
} else {
    if (!empty($params)) {
        $bindParams = [$types];

        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array(
            'mysqli_stmt_bind_param',
            array_merge([$stmt], $bindParams)
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        $records = mysqli_stmt_get_result($stmt);

        if ($records === false) {
            $pageError = 'Unable to retrieve login history.';
        }
    } else {
        $pageError = 'Unable to load login history. Please try again.';
    }

    mysqli_stmt_close($stmt);
}

$recordCount = $records ? mysqli_num_rows($records) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History | MyPortfolio Admin</title>

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
            font-family: Arial, sans-serif;
        }

        .page-container {
            width: 92%;
            max-width: 1500px;
            margin: 35px auto;
        }

        .page-title {
            color: #f8fafc;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-description {
            color: #94a3b8;
            margin-bottom: 25px;
        }

        .panel {
            background: #111c31;
            border: 1px solid #263449;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .form-label {
            color: #cbd5e1;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            background-color: #0b1224;
            color: #f1f5f9;
            border: 1px solid #334155;
            min-height: 43px;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #0b1224;
            color: #fff;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.18);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .form-select option {
            background: #0b1224;
            color: #fff;
        }

        .btn-search {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
            min-height: 43px;
            font-weight: 600;
        }

        .btn-search:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-reset {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 43px;
            padding: 0 18px;
            color: #cbd5e1;
            text-decoration: none;
            border: 1px solid #475569;
            border-radius: 7px;
        }

        .btn-reset:hover {
            background: #1e293b;
            color: #fff;
        }

        .records-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .records-heading h2 {
            font-size: 20px;
            margin: 0;
            color: #f8fafc;
        }

        .record-count {
            color: #c4b5fd;
            background: #292044;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .table-responsive {
            border: 1px solid #29364b;
            border-radius: 10px;
        }

        .table {
            margin: 0;
            vertical-align: middle;
            min-width: 1100px;
        }

        .table thead th {
            background: #0b1224;
            color: #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 12px;
            white-space: nowrap;
            border-bottom: 1px solid #334155;
        }

        .table tbody td {
            background: #111c31;
            color: #e2e8f0;
            border-bottom: 1px solid #263449;
            padding: 14px 12px;
            font-size: 13px;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover td {
            background: #17243b;
        }

        .username {
            font-weight: 700;
            color: #c4b5fd;
        }

        .muted {
            color: #94a3b8;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-active {
            color: #86efac;
            background: #143a2b;
        }

        .status-logged_out {
            color: #cbd5e1;
            background: #29364b;
        }

        .status-expired {
            color: #fde68a;
            background: #493719;
        }

        .status-disabled {
            color: #fca5a5;
            background: #4b2028;
        }

        .empty-state {
            text-align: center;
            padding: 45px 20px !important;
            color: #94a3b8 !important;
        }

        .alert-error {
            background: #451a24;
            border: 1px solid #7f1d1d;
            color: #fecaca;
            padding: 14px 16px;
            border-radius: 9px;
        }

        .page-footer {
            color: #64748b;
            font-size: 12px;
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .page-container {
                width: 95%;
                margin-top: 22px;
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

    <h1 class="page-title">Login History</h1>
    <p class="page-description">
        Review administrator sign-ins, session activity and logout records.
    </p>

    <section class="panel">
        <form method="GET" action="login-history.php">
            <div class="row g-3">

                <div class="col-lg-4 col-md-6">
                    <label for="search" class="form-label">
                        Search Username or IP
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        maxlength="150"
                        placeholder="Enter username or IP address"
                        value="<?= e($search) ?>"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="logged_out" <?= $status === 'logged_out' ? 'selected' : '' ?>>Logged Out</option>
                        <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
                        <option value="disabled" <?= $status === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label for="from_date" class="form-label">From Date</label>
                    <input
                        type="date"
                        class="form-control"
                        id="from_date"
                        name="from_date"
                        value="<?= e($fromDate) ?>"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <label for="to_date" class="form-label">To Date</label>
                    <input
                        type="date"
                        class="form-control"
                        id="to_date"
                        name="to_date"
                        value="<?= e($toDate) ?>"
                    >
                </div>

                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-search flex-grow-1">
                        Search
                    </button>
                    <a href="login-history.php" class="btn-reset">
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </section>

    <section class="panel">

        <div class="records-heading">
            <h2>Administrator Activity</h2>
            <span class="record-count">
                <?= $recordCount ?> record(s) shown
            </span>
        </div>

        <?php if ($pageError !== ''): ?>

            <div class="alert-error">
                <?= e($pageError) ?>
            </div>

        <?php elseif ($recordCount === 0): ?>

            <div class="empty-state">
                <h5>No login records found</h5>
                <p class="mb-0">
                    Try changing your search, status or date filters.
                </p>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Admin Username</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                            <th>Last Activity</th>
                            <th>Logout Time</th>
                            <th>Status</th>
                            <th>Device / Browser</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($records)): ?>
                            <?php
                            $rowStatus = (string) ($row['status'] ?? '');
                            $statusClass = in_array(
                                $rowStatus,
                                $allowedStatuses,
                                true
                            ) ? $rowStatus : 'logged_out';
                            ?>

                            <tr>
                                <td>
                                    <span class="username">
                                        <?= e($row['username'] ?? 'Unknown') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($row['ip_address'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= e($row['login_at'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= e($row['last_activity'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= !empty($row['logout_at'])
                                        ? e($row['logout_at'])
                                        : '<span class="muted">—</span>' ?>
                                </td>

                                <td>
                                    <span class="status-badge status-<?= e($statusClass) ?>">
                                        <?= e(ucwords(str_replace('_', ' ', $rowStatus))) ?>
                                    </span>
                                </td>

                                <td style="min-width: 200px; max-width: 280px; overflow-wrap: anywhere;">
                                    <?= !empty($row['user_agent'])
                                        ? e($row['user_agent'])
                                        : 'N/A' ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <p class="page-footer">
                A maximum of 200 records are displayed per search, newest first.
                Older login history is not deleted by this limit.
            </p>

        <?php endif; ?>

    </section>

</main>

</body>
</html>