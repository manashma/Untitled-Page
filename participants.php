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

    // Retrieve the page_id from the URL
    $pageId = htmlspecialchars($_GET['page_id'] ?? '');

    if (!$pageId) {
        $errorMessage = "Invalid or missing Page ID.";
    } else {
        // Fetch link details using page_id
        $query = "SELECT username, expiry_date FROM links WHERE page_id = :page_id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':page_id' => $pageId]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$link) {
            $errorMessage = "Page not found.";
        } else {
            $creatorUsername = $link['username'];
            $expiryDate = $link['expiry_date'];

            // Check if the link is expired
            if (strtotime($expiryDate) < time()) {
                $errorMessage = "This competition link has expired.";
            }
        }
    }
} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants</title>
    <!-- Include SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.17/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #121212;
            color: #fff;
            margin: 0;
        }
        .form-container, .error-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 3px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            text-align: center;
        }
        input[type="text"] {
            width: 95%;
            padding: 12px;
            margin: 10px auto;
            border: 1px solid rgb(255, 89, 0);
            outline: none;
            color: #000;
            background: #fff;
        }
        button {
            padding: 10px 20px;
            border: none;
            background: rgb(255, 89, 0);
            color: #000;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #1f4037;
            color: #fff;
        }
        .error-container {
            color: #fff;
            font-size: 1.2rem;
        }
        .error-icon {
            font-size: 3rem;
            color: rgb(255, 89, 0);
        }
    </style>
</head>
<body>
    <?php if (!empty($errorMessage)) : ?>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
        </div>
    <?php else : ?>
        <div class="form-container">
            <h1>Participate in the shilling competition</h1>
            <form id="telegramForm">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($creatorUsername); ?>">
                <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($pageId); ?>">
                <input type="text" name="percipient_telegram_username" placeholder="Your Telegram Username" required>
                <button type="submit">Participate</button>
            </form>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.17/dist/sweetalert2.all.min.js"></script>
    <script>
    $('#telegramForm').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();
        $.ajax({
            url: 'add_participants.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    const fullLink = 'https://site.com/c/' + response.link;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Participant added successfully. Link: ' + fullLink,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    $('#telegramForm')[0].reset();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
        });
    });
    </script>
</body>
</html>
