<?php

require_once 'connections.php';

// Log incoming POST request
file_put_contents('debug.log', "POST Data: " . print_r($_POST, true), FILE_APPEND);

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'Invalid request method.']));
}

$creatorUsername = htmlspecialchars($_POST['username'] ?? '');
$pageId = htmlspecialchars($_POST['page_id'] ?? '');
$telegramUsername = htmlspecialchars($_POST['percipient_telegram_username'] ?? '');

if (!$telegramUsername || !$pageId) {
    die(json_encode(['status' => 'error', 'message' => 'Missing required fields.']));
}

try {
    // Check if the Telegram username already exists for this page
    $telegramUsernameCheckQuery = "SELECT COUNT(*) FROM participants WHERE participant_telegram_username = :telegram_username AND page_id = :page_id";
    $telegramUsernameCheckStmt = $pdo->prepare($telegramUsernameCheckQuery);
    $telegramUsernameCheckStmt->execute([':telegram_username' => $telegramUsername, ':page_id' => $pageId]);

    if ($telegramUsernameCheckStmt->fetchColumn() > 0) {
        die(json_encode(['status' => 'error', 'message' => 'Telegram username already exists.']));
    }

    // Generate a unique 6-character link
    do {
        $newLink = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
        $linkCheckQuery = "SELECT COUNT(*) FROM participants WHERE link = :link";
        $linkCheckStmt = $pdo->prepare($linkCheckQuery);
        $linkCheckStmt->execute([':link' => $newLink]);
        $exists = $linkCheckStmt->fetchColumn();
    } while ($exists > 0);

    // Insert participant data
    $insertQuery = "INSERT INTO participants (username, page_id, participant_telegram_username, link) VALUES (:username, :page_id, :telegram_username, :link)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->execute([
        ':username' => $creatorUsername,
        ':page_id' => $pageId,
        ':telegram_username' => $telegramUsername,
        ':link' => $newLink
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Participant added successfully.', 'link' => $newLink]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]));
}
?>
