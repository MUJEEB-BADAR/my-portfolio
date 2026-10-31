<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mail-config.php';
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function textLength(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'success' => false,
        'message' => 'Only POST requests are accepted.'
    ]);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (
    stripos($contentType, 'application/x-www-form-urlencoded') !== 0 &&
    stripos($contentType, 'multipart/form-data') !== 0
) {
    respond(415, [
        'success' => false,
        'message' => 'Unsupported request format.'
    ]);
}

$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    respond(403, [
        'success' => false,
        'message' => 'Your session expired. Refresh the page and try again.'
    ]);
}

$honeypot = $_POST['website'] ?? '';

if (!is_string($honeypot) || trim($honeypot) !== '') {
    respond(200, [
        'success' => true,
        'message' => 'Thank you. Your message has been received.'
    ]);
}

$now = time();
$lastSubmission = (int) ($_SESSION['last_contact_submission'] ?? 0);

if ($now - $lastSubmission < 15) {
    respond(429, [
        'success' => false,
        'message' => 'Please wait a few seconds before sending another message.'
    ]);
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

if (
    !is_string($name) ||
    !is_string($email) ||
    !is_string($subject) ||
    !is_string($message)
) {
    respond(422, [
        'success' => false,
        'message' => 'Invalid form data.'
    ]);
}

$name = trim($name);
$email = trim($email);
$subject = trim($subject);
$message = trim($message);

if (
    $name === '' ||
    textLength($name) > 80 ||
    $subject === '' ||
    textLength($subject) > 120 ||
    $message === '' ||
    textLength($message) > 5000 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 254
) {
    respond(422, [
        'success' => false,
        'message' => 'Please enter valid details in all required fields.'
    ]);
}

if (
    preg_match('/[\r\n]/', $email) ||
    preg_match('/[\r\n]/', $subject)
) {
    respond(422, [
        'success' => false,
        'message' => 'Invalid email or subject.'
    ]);
}
// Save the submission time before sending the email.
$_SESSION['last_contact_submission'] = time();
session_write_close();

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = MAIL_PORT;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom(MAIL_USERNAME, OWNER_NAME);
    $mail->addAddress(OWNER_EMAIL);
    $mail->addReplyTo($email, $name);

    $mail->isHTML(false);
    $mail->Subject = 'Portfolio enquiry: ' . $subject;

    $mail->Body =
        "New portfolio contact message\n\n" .
        "Name: {$name}\n" .
        "Email: {$email}\n" .
        "Subject: {$subject}\n\n" .
        "Message:\n{$message}\n";

    $mail->send();
    respond(200, [
        'success' => true,
        'message' => 'Your message has been sent successfully. Thank you for contacting me.'
    ]);

} catch (Throwable $exception) {
    error_log('Portfolio contact email failed: ' . $exception->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Your message could not be sent. Please try again later.'
    ]);
}
