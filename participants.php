<?php

require_once 'connections.php';

// Retrieve the page_id from the URL
$pageId = $_GET['page_id'];

if (!$pageId) {
    $errorMessage = "Invalid or missing Page ID.";
} else {
    $participantQuery = "SELECT link FROM participants WHERE page_id = :page_id LIMIT 1";
    $participantStmt = $pdo->prepare($participantQuery);
    $participantStmt->execute([':page_id' => $pageId]);
    $participant = $participantStmt->fetch(PDO::FETCH_ASSOC);

    if ($participant) {
        $expiryQuery = "SELECT expiry_date FROM links WHERE page_id = :page_id";
        $expiryStmt = $pdo->prepare($expiryQuery);
        $expiryStmt->execute([':page_id' =>$pageId]);
        $expiryResult = $expiryStmt->fetch(PDO::FETCH_ASSOC);
    
        if ($expiryResult && strtotime($expiryResult['expiry_date']) < time()) {
            // Step 2: Delete related data from all tables using the link
            $pdo->prepare("DELETE FROM participants WHERE page_id = :page_id")->execute([':page_id' => $pageId]);
            $pdo->prepare("DELETE FROM link_views WHERE link = :link")->execute([':link' => $participant['link']]);
            $pdo->prepare("DELETE FROM links WHERE link = :link")->execute([':link' => $participant['link']]);
            
            die("This link has expired and all related data has been deleted.");
        }
    }
    $query = "SELECT username, expiry_date, description FROM links WHERE page_id = :page_id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':page_id' => $pageId]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$link) {
        $errorMessage = "Page not found.";
    } else {
        $creatorUsername = $link['username'];
        $expiryDate = $link['expiry_date'];
        $description = $link['description'];
        
        // Check if the link is expired
        if (strtotime($expiryDate) < time()) {
            $errorMessage = "This competition link has expired.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.17/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('/img.jpeg') no-repeat center center/cover;
            color: #fff;
            position: relative;
        }       

        /* Add a blur effect using an overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: inherit;
            filter: blur(5px);
            z-index: -1;
        }  
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            width: 800px;
            text-align: center;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .description {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        .countdown-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .countdown {
            font-size: 2rem;
            font-weight: bold;
            color: rgb(255, 89, 0);
            margin-top: 10px;
            letter-spacing: 1px;
            display: flex;
            gap: 10px;
        }
        .countdown .digit {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 60px;
            font-size: 2rem;
            background: #222;
            border-radius: 10px;
            color: white;
            text-align: center;
            line-height: 60px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .countdown .digit .flip {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            transform-origin: 50% 50%;
            backface-visibility: hidden;
        }
        @keyframes flipIn {
            0% {
                transform: rotateX(90deg);
                opacity: 0;
            }
            50% {
                transform: rotateX(0deg);
                opacity: 1;
            }
            100% {
                transform: rotateX(-90deg);
                opacity: 0;
            }
        }
        @keyframes flipIn {
            0% {
                transform: rotateX(90deg);
                opacity: 0;
            }
            50% {
                transform: rotateX(0deg);
                opacity: 1;
            }
            100% {
                transform: rotateX(-90deg);
                opacity: 0;
            }
        }
        input[type="text"] {
            width: 90%;
            padding: 12px;
            margin: 10px auto;
            border: 1px solid rgb(255, 89, 0);
            outline: none;
            background-color: #fff;
            color: #000;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus {
            border-color: #fff;
            box-shadow: 0 0 10px rgba(255, 89, 0, 0.7);
        }
        button {
            padding: 12px 25px;
            border: none;
            background: rgb(255, 89, 0);
            color: #fff;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: rgb(255, 109, 0);
        }
        .logo {
            margin: 0 auto 20px;
            width: 180px;
            height: auto;
            object-fit: contain;
        }
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes countdownEffect {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        .logo {
            margin: 10px 0 0 0;
            display: block;
            margin: 0 auto 15px; /* Center horizontally and add spacing */
            width: 190px; /* Set width */
            height: 100px; /* Set height */
            object-fit: contain; /* Ensure the image scales properly without distortion */
        }
    </style>
</head>
<body>
    <?php if (!empty($errorMessage)) : ?>
        <div class="container">
            <h2>Error</h2>
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
        </div>
    <?php else : ?>
        <div class="container">
            <img src="/icon_blur.png" alt="Logo" class="logo">
            <h1>Shilling Competition</h1>
            <p><b>Description:</b><?php echo htmlspecialchars($description); ?></p>
            <div class="countdown-wrapper">
                <div class="countdown">
                    <div class="digit"><span class="flip">00</span></div>
                    <div class="digit"><span class="flip">00</span></div>
                    <div class="digit"><span class="flip">00</span></div>
                    <div class="digit"><span class="flip">00</span></div>
                </div>
            </div>
            <form id="telegramForm">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($creatorUsername); ?>">
                <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($pageId); ?>">
                <input type="text" name="percipient_telegram_username" placeholder="Your Telegram Username" required>
                <button type="submit">Participate</button>
            </form>
        </div>
        <script>
            function startCountdown(expiryDate) {
                const countdownElement = document.querySelectorAll(".countdown .digit .flip");

                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = new Date(expiryDate).getTime() - now;

                    if (distance < 0) {
                        countdownElement.forEach(el => el.innerHTML = "00");
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    const timeUnits = [days, hours, minutes, seconds];
                    timeUnits.forEach((timeUnit, index) => {
                        const formattedTime = timeUnit.toString().padStart(2, '0');
                        const currentDigit = countdownElement[index];

                        // Flip only if the value has changed
                        if (currentDigit.innerHTML !== formattedTime) {
                            const flipElement = currentDigit;
                            flipElement.innerHTML = formattedTime;
                            flipElement.classList.remove('flip');
                            void flipElement.offsetWidth; // Trigger reflow
                            flipElement.classList.add('flip');
                        }
                    });
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }

            startCountdown("<?php echo $expiryDate; ?>");
        </script>
    <?php endif; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.17/dist/sweetalert2.all.min.js"></script>
    <script>
    $('#telegramForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/add_participants.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your link: https://crudoprotocol.com/l/' + response.link,
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
