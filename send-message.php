<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'success' => false,
        'message' => 'Only POST requests are accepted.'
    ]);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/x-www-form-urlencoded') !== 0
    && stripos($contentType, 'multipart/form-data') !== 0) {
    respond(415, [
        'success' => false,
        'message' => 'Unsupported request format.'
    ]);
}

$token = $_POST['csrf_token'] ?? '';

if (!is_string($token)
    || !hash_equals($_SESSION['csrf_token'], $token)) {
    respond(403, [
        'success' => false,
        'message' => 'Your session expired. Refresh the page and try again.'
    ]);
}

/* Bots that fill the hidden field are silently rejected. */
$honeypot = $_POST['website'] ?? '';

if (!is_string($honeypot) || trim($honeypot) !== '') {
    respond(200, [
        'success' => true,
        'message' => 'Thank you. Your message has been received.'
    ]);
}

/* Basic session-based cooldown; use server-side rate limiting in production. */
$now = time();
$lastSubmission = (int) ($_SESSION['last_contact_submission'] ?? 0);

if ($now - $lastSubmission < 15) {
    respond(429, [
        'success' => false,
        'message' => 'Please wait a few seconds before sending another message.'
    ]);
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!is_string($name) || !is_string($email)
    || !is_string($subject) || !is_string($message)) {
    respond(422, [
        'success' => false,
        'message' => 'Invalid form data.'
    ]);
}

if (
    $name === '' || textLength($name) > 80 ||
    $subject === '' || textLength($subject) > 120 ||
    $message === '' || textLength($message) > 5000 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 254
) {
    respond(422, [
        'success' => false,
        'message' => 'Please enter valid details in all required fields.'
    ]);
}

/* Prevent CR/LF header injection. */
if (preg_match('/[\r\n]/', $email)
    || preg_match('/[\r\n]/', $subject)) {
    respond(422, [
        'success' => false,
        'message' => 'Invalid email or subject.'
    ]);
}

/*
 * Configure a real domain-based mailbox and working PHP mail transport
 * before enabling email delivery.
 */
if (
    OWNER_EMAIL === 'your-email@example.com'
    || !filter_var(OWNER_EMAIL, FILTER_VALIDATE_EMAIL)
) {
    respond(503, [
        'success' => false,
        'message' => 'Contact email is not configured yet.'
    ]);
}

$siteHost = parse_url(SITE_URL, PHP_URL_HOST);
$siteHost = is_string($siteHost) ? $siteHost : 'localhost';

/* Use a verified mailbox on your own domain for the From header. */
$fromAddress = 'no-reply@' . $siteHost;

if (
    $siteHost === 'localhost'
    || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)
) {
    respond(503, [
        'success' => false,
        'message' => 'Configure your website domain and email transport first.'
    ]);
}

$safeSubject = str_replace(
    ["\r", "\n"],
    '',
    $subject
);

$body = "New portfolio contact message\n\n"
    . "Name: " . $name . "\n"
    . "Email: " . $email . "\n"
    . "Subject: " . $safeSubject . "\n\n"
    . "Message:\n" . $message . "\n";

$headers = [
    'From' => OWNER_NAME . ' Website <' . $fromAddress . '>',
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
    'X-Mailer' => 'PHP/' . PHP_VERSION
];

try {
    $sent = mail(
        OWNER_EMAIL,
        'Portfolio enquiry: ' . $safeSubject,
        $body,
        $headers
    );
} catch (Throwable $exception) {
    error_log('Portfolio contact mail failed: ' . $exception->getMessage());
    $sent = false;
}

if (!$sent) {
    error_log('Portfolio contact email could not be queued.');
    respond(500, [
        'success' => false,
        'message' => 'Email delivery is unavailable. Please try again later.'
    ]);
}

$_SESSION['last_contact_submission'] = $now;

respond(200, [
    'success' => true,
    'message' => 'Your message was submitted successfully.'
]);