<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function isActive(string $page): string
{
    $current = basename(
        $_SERVER['SCRIPT_NAME'] ?? 'index.php'
    );

    return $current === $page ? 'active' : '';
}