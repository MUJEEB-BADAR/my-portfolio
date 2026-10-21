
<?php
require_once __DIR__ . '/session.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}