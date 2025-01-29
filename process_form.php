<?php

require_once 'connections.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieving and sanitizing input
    $name = htmlspecialchars($_POST['name']);
    $platform = htmlspecialchars($_POST['platform']);
    $otherPlatform = htmlspecialchars($_POST['other_platform'] ?? '');
    $username = htmlspecialchars($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $description = htmlspecialchars($_POST['description']);
    $pageId = htmlspecialchars($_POST['pageid']);
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }
    // Insert into database
    $query = "INSERT INTO links (name, platform, other_platform, username, email, description, page_id, expiry_date) 
              VALUES (:name, :platform, :other_platform, :username, :email, :description, :page_id, :expiry_date)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':name' => $name,
        ':platform' => $platform,
        ':other_platform' => $otherPlatform,
        ':username' => $username,
        ':email' => $email,
        ':description' => $description,
        ':page_id' => $pageId,
        ':expiry_date' => $expiryDate,
    ]);
    echo json_encode(['status' => 'success', 'message' => 'Link created successfully']);
}


?>
