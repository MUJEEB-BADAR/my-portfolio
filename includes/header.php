<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription
    ?? 'Portfolio of a Full Stack Web Developer specializing in PHP, JavaScript and responsive web development.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($pageTitle) ?></title>
    <meta name="description"
          content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#0b1020">

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top site-nav">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <span class="brand-mark"></span>
            <?= e(OWNER_NAME) ?><span class="text-accent"></span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= isActive('index.php') ?>"
                       href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#skills">Skills</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="projects.php">Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php#education">Education</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php#experience">Experience</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-outline-accent btn-sm px-3"
                       href="contact.php">Let's Talk</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>