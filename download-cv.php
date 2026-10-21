<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

if (!is_file(CV_PATH) || !is_readable(CV_PATH)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('CV is not available yet. Please add your PDF file.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Mujeeb-Ahmed-Badar-CV.pdf"');
header('Content-Length: ' . filesize(CV_PATH));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile(CV_PATH);
exit;