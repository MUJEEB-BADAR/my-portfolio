<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('SITE_NAME', 'MUJEEB AHMED BADAR | Software Engineer | Web Developer');
define('SITE_URL', 'http://localhost/my-portfolio'); // XAMPP folder: htdocs/my-portfolio

define('OWNER_NAME', 'MUJEEB AHMED BADAR');
define('OWNER_EMAIL', 'mujeebmughal458@gmail.com');
define('OWNER_LOCATION', 'Ali Pur Chattha, Pakistan');
define('LINKEDIN_URL', 'https://www.linkedin.com/in/mujeeb-ahmed-badar-60661a248/');

define('CV_PATH', __DIR__ . '/../assets/files/Mujeeb Ahmed Badar.pdf');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}