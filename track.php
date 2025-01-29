<?php

require_once 'connections.php';
// Retrieve page_id from the URL parameter
$pageId = htmlspecialchars($_GET['page'] ?? '');
if (!$pageId) {
    die("Page ID is required.");
}
// Check if the link has expired
$expiryQuery = "SELECT expiry_date FROM links WHERE page_id = :page_id";
$expiryStmt = $pdo->prepare($expiryQuery);
$expiryStmt->execute([':page_id' => $pageId]);
$expiryResult = $expiryStmt->fetch(PDO::FETCH_ASSOC);
if ($expiryResult && strtotime($expiryResult['expiry_date']) < time()) {
    // Delete related data from all tables
    $pdo->prepare("DELETE FROM participants WHERE page_id = :page_id")->execute([':page_id' => $pageId]);
    $pdo->prepare("DELETE FROM link_views WHERE page_id = :page_id")->execute([':page_id' => $pageId]);
    $pdo->prepare("DELETE FROM links WHERE page_id = :page_id")->execute([':page_id' => $pageId]);
    die("This link has expired and all related data has been deleted.");
}
// Query to fetch most frequent referring site along with participant data
$query = "
    SELECT 
        p.username AS admin_username,
        p.page_id,
        p.participant_telegram_username,
        COUNT(DISTINCT lv.ip_address) AS unique_ips,
        SUM(p.total_view) AS total_views,
        l.created_at AS link_created_at,
        lv.referring_site,
        COUNT(lv.referring_site) AS referring_site_count
    FROM participants p
    LEFT JOIN link_views lv ON p.link = lv.link
    LEFT JOIN links l ON p.page_id = l.page_id
    WHERE p.page_id = :page_id
    GROUP BY p.participant_telegram_username, lv.referring_site
    ORDER BY referring_site_count DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([':page_id' => $pageId]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get the most frequent referring site
$mostFrequentReferringSite = '';
$maxCount = 0;
foreach ($participants as $participant) {
    if ($participant['referring_site_count'] > $maxCount) {
        $mostFrequentReferringSite = $participant['referring_site'];
        $maxCount = $participant['referring_site_count'];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Participants</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Page Styling */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212; /* Deep black background */
            color: #f5f5f5; /* Light text for contrast */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #1c1c1c; /* Slightly lighter background for the container */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        /* Header Section */
        h1 {
            text-align: center;
            color: rgb(255, 89, 0); /* Dark orange */
            font-size: 36px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        /* Information Section */
        .info {
            margin: 20px 0;
            font-size: 18px;
            color: #ddd; /* Lighter grey for info text */
            text-align: center;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 18px;
            text-align: left;
            border-bottom: 2px solid #333; /* Darker border */
        }

        th {
            background-color: #252525; /* Dark grey for header */
            color: rgb(255, 89, 0); /* Dark orange header text */
            font-weight: 600;
            text-transform: uppercase;
        }

        td {
            background-color: #1f1f1f; /* Dark background for table data */
            color: #f5f5f5; /* Light text for table data */
            font-size: 16px;
            font-weight: 400;
        }

        td a {
            color: rgb(255, 89, 0); /* Dark orange links */
            text-decoration: none;
        }

        td a:hover {
            text-decoration: underline;
        }

        /* Table Hover Effect */
        tr:hover {
            background-color: #333333; /* Slightly lighter dark background on hover */
        }

        /* No Data Found Message */
        td[colspan="7"] {
            text-align: center;
            font-size: 18px;
            color: #bbb; /* Soft grey when no data is found */
            padding: 20px;
        }

        /* Responsive Design for Table */
        @media screen and (max-width: 768px) {
            table {
                font-size: 14px;
            }
        
            th, td {
                padding: 12px;
            }
        
            .container {
                padding: 20px;
            }
        
            h1 {
                font-size: 28px;
            }
        }

        /* Button Styling */
        button {
            background-color: rgb(255, 89, 0); /* Dark orange button */
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #e07b00; /* Slightly lighter orange on hover */
        }

        /* Search/Input Field Styling */
        input[type="text"], select {
            padding: 12px 18px;
            margin: 10px 0;
            background-color: #333;
            color: #fff;
            border: 1px solid #444;
            border-radius: 6px;
            font-size: 16px;
            width: 100%;
        }

        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: rgb(255, 89, 0);
        }

        /* Font Awesome Icon Styling */
        .icon {
            margin-right: 10px;
            color: rgb(255, 89, 0);
        }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-users icon"></i>Participant Tracking for Page: <?php echo $pageId; ?></h1>

    <div class="info">
        <p><strong>Note:</strong> This page shows participant tracking information based on the page ID provided in the URL. Link was created on: <?php echo $participants[0]['link_created_at'] ?? 'N/A'; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Admin Username</th>
                <th>Page ID</th>
                <th>Participant Telegram Username</th>
                <th>Total Views</th>
                <th>Unique IPs</th>
                <th>Link Created At</th>
                <th>Most Referring Site</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($participants)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No participants found for this page.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($participants as $participant): ?>
                    <tr>
                        <td><i class="fas fa-user icon"></i><?php echo htmlspecialchars($participant['admin_username']); ?></td>
                        <td><?php echo htmlspecialchars($participant['page_id']); ?></td>
                        <td><b>@<?php echo htmlspecialchars($participant['participant_telegram_username']); ?></b></td>
                        <td><i class="fas fa-eye icon"></i><?php echo htmlspecialchars($participant['total_views']); ?></td>
                        <td><i class="fas fa-globe icon"></i><?php echo htmlspecialchars($participant['unique_ips']); ?></td>
                        <td><?php echo isset($participant['link_created_at']) ? date('Y-m-d', strtotime($participant['link_created_at'])) : 'N/A'; ?></td>
                        <td><?php echo ($participant['referring_site'] == $mostFrequentReferringSite) ? htmlspecialchars($participant['referring_site']) : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
