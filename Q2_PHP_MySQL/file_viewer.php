<?php
/**
 * Safe File Serving Endpoint
 * Prevents direct filesystem path exposure, directory traversal,
 * and ensures safe browser rendering or file download.
 */

require_once __DIR__ . '/db.php';

// Validate file ID parameter
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    die("Invalid file identifier.");
}

$fileId = intval($_GET['id']);
$isDownload = isset($_GET['download']) && $_GET['download'] === '1';

// Query file metadata from database using prepared statement
$stmt = $conn->prepare("SELECT file_name, stored_name, file_type FROM student_files WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die("File record not found in database.");
}

$fileData = $result->fetch_assoc();
$stmt->close();

$storedName = basename($fileData['stored_name']);
$originalName = $fileData['file_name'];
$mimeType = $fileData['file_type'];

$fullFilePath = UPLOAD_DIR . $storedName;

// Security check: Verify file exists on disk
if (!file_exists($fullFilePath) || !is_file($fullFilePath)) {
    http_response_code(404);
    die("Error: The requested physical file does not exist on disk.");
}

// Security check: Ensure realpath strictly resides inside UPLOAD_DIR
$realBase = realpath(UPLOAD_DIR);
$realFile = realpath($fullFilePath);
if ($realFile === false || strpos($realFile, $realBase) !== 0) {
    http_response_code(403);
    die("Access denied: Invalid file path.");
}

// Allowed MIME types whitelist
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'application/pdf' => 'pdf'
];

if (!array_key_exists($mimeType, $allowedMimes)) {
    // Default fallback to generic stream if unrecognized
    $mimeType = 'application/octet-stream';
}

// Clear any buffered output before sending binary headers
if (ob_get_level()) {
    ob_end_clean();
}

// Set security and content headers
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullFilePath));

// Set disposition: 'inline' for browser view/preview, 'attachment' for download
$disposition = $isDownload ? 'attachment' : 'inline';
// Sanitize original filename for header
$safeFilename = str_replace(['"', "\r", "\n"], '', basename($originalName));
header('Content-Disposition: ' . $disposition . '; filename="' . $safeFilename . '"');
header('Cache-Control: private, max-age=86400');

// Stream file content to user
readfile($fullFilePath);
exit;
