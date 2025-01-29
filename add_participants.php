<?php
// Database configuration
$dbHost = 'localhost';
$dbName = 'shorten';
$dbUser = 'root';
$dbPass = '';

try {
    // Establishing PDO connection
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve the data from the POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $creatorUsername = htmlspecialchars($_POST['username'] ?? '');
        $pageId = htmlspecialchars($_POST['page_id'] ?? '');
        $telegramUsername = htmlspecialchars($_POST['percipient_telegram_username'] ?? '');

        if (!$telegramUsername || !$pageId) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
            exit;
        }

        // Check if the participant's telegram username already exists
        $telegramUsernameCheckQuery = "SELECT COUNT(*) FROM participants WHERE participant_telegram_username = :telegram_username";
        $telegramUsernameCheckStmt = $pdo->prepare($telegramUsernameCheckQuery);
        $telegramUsernameCheckStmt->execute([':telegram_username' => $telegramUsername]);
        if ($telegramUsernameCheckStmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Telegram username already exists.']);
            exit;
        }

        // Check if the link already exists
        $linkCheckQuery = "SELECT COUNT(*) FROM participants WHERE link = :link";
        $linkCheckStmt = $pdo->prepare($linkCheckQuery);

        // Generate a 6-character random link (letters only)
        do {
            $newLink = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
            $linkCheckStmt->execute([':link' => $newLink]);
            $exists = $linkCheckStmt->fetchColumn();
        } while ($exists > 0);

        // Insert participant data into the database
        $insertQuery = "INSERT INTO participants (username, page_id, participant_telegram_username, link) VALUES (:username, :page_id, :telegram_username, :link)";
        $insertStmt = $pdo->prepare($insertQuery);

        $insertStmt->execute([
            ':username' => $creatorUsername,
            ':page_id' => $pageId,
            ':telegram_username' => $telegramUsername,
            ':link' => $newLink
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Participant added successfully.', 'link' => $newLink]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
