<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'connections.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieving and sanitizing input
        $name = htmlspecialchars($_POST['name'] ?? '');
        $platform = htmlspecialchars($_POST['platform'] ?? '');
        $otherPlatform = htmlspecialchars($_POST['other_platform'] ?? '');
        $username = htmlspecialchars($_POST['username'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $description = htmlspecialchars($_POST['description'] ?? '');
        $pageId = htmlspecialchars($_POST['pageid'] ?? '');
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit;
        }

        // Validate required fields
        if (empty($name) || empty($platform) || empty($username) || empty($email) || empty($description) || empty($pageId)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
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
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
?>
