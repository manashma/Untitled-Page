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

    // Get the link from the URL
    $shortLink = htmlspecialchars($_GET['link'] ?? '');

    if (!$shortLink) {
        die("Invalid or missing link.");
    }

    // Fetch the participant's data using the link
    $query = "SELECT username, participant_telegram_username FROM participants WHERE link = :link LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':link' => $shortLink]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participant) {
        die("Link not found.");
    }

    $username = $participant['username'];
    $telegramUsername = $participant['participant_telegram_username'];

    // Get the user's IP address
    $userIp = $_SERVER['REMOTE_ADDR'];

    // Get the referring site (the page where the user clicked the link)
    $referringSite = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct';

    // Extract the domain from the referring site (without path or query parameters)
    if ($referringSite !== 'Direct') {
        $parsedUrl = parse_url($referringSite);
        $referringSite = $parsedUrl['host'] ?? 'Unknown';
    }

    // Check if the IP has viewed this link in the last 7 days
    $viewCheckQuery = "
        SELECT id, last_viewed 
        FROM link_views 
        WHERE link = :link AND ip_address = :ip_address AND last_viewed >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        LIMIT 1";
    $viewCheckStmt = $pdo->prepare($viewCheckQuery);
    $viewCheckStmt->execute([':link' => $shortLink, ':ip_address' => $userIp]);
    $recentView = $viewCheckStmt->fetch(PDO::FETCH_ASSOC);

    if (!$recentView) {
        // Insert or update the view with the referring site
        $insertViewQuery = "
            INSERT INTO link_views (link, participant_telegram_username, ip_address, last_viewed, referring_site)
            VALUES (:link, :telegram_username, :ip_address, NOW(), :referring_site)
            ON DUPLICATE KEY UPDATE last_viewed = NOW(), referring_site = :referring_site";
        $insertViewStmt = $pdo->prepare($insertViewQuery);
        $insertViewStmt->execute([
            ':link' => $shortLink,
            ':telegram_username' => $telegramUsername,
            ':ip_address' => $userIp,
            ':referring_site' => $referringSite,
        ]);

        // Update the total_view column in the participants table
        $updateViewQuery = "
            UPDATE participants 
            SET total_view = total_view + 1 
            WHERE link = :link AND participant_telegram_username = :telegram_username";
        $updateViewStmt = $pdo->prepare($updateViewQuery);
        $updateViewStmt->execute([
            ':link' => $shortLink,
            ':telegram_username' => $telegramUsername,
        ]);
    }

    // Redirect to the target page
    header("Location: https://crudoprotocol.com/?utm_source=shilling_contest");
    exit;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
