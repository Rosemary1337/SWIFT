<?php
// Retrieve details passed from the agent
if (!isset($_GET['reason'])) {
    header("Location: ?reason=" . urlencode("Auto-Blocked: Malicious Activity Detected"));
    exit;
}
$reason = $_GET['reason'];
$requestId = $_GET['id'] ?? 'N/A';
$ip = $_SERVER['REMOTE_ADDR'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden | SWIFT Firewall</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Google+Sans+Code:wght@400;500;600;700&family=Google+Sans:wght@400;500;700&display=swap');

        :root {
            --bg: #0a0a0a;
            --surface: #141414;
            --border: #262626;
            --text-primary: #e5e5e5;
            --text-secondary: #a3a3a3;
            --accent: #f97316; /* Industrial Orange */
            --danger: #ef4444;
        }

        body { 
            font-family: 'Google Sans', sans-serif; 
            background-color: var(--bg); 
            color: var(--text-primary);
            height: 100vh; 
            width: 100vw;
            margin: 0; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
        }

        .container {
            background: var(--surface);
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            border-radius: 4px;
            border: 1px solid var(--border);
            border-top: 4px solid var(--danger);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .brand img {
            height: 65px;
        }

        .title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.02em;
        }

        .subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            margin-bottom: 2rem;
            font-family: 'Google Sans Code', monospace;
            line-height: 1.5;
        }

        .details {
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1rem;
            text-align: left;
            /* No margin bottom needed as there is no button below */
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
            gap: 2rem;
        }
        
        .detail-row:last-child { margin-bottom: 0; }

        .label { color: var(--text-secondary); font-family: 'Google Sans Code', monospace; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
        .value { color: var(--danger); font-family: 'Google Sans Code', monospace; font-weight: 600; text-align: right; }

        footer {
            margin-top: 2rem;
            color: var(--text-secondary);
            font-size: 0.75rem;
            opacity: 0.6;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="brand">
        <img src="assets/swift.png" alt="SWIFT Logo">
    </div>

    <div class="container">
        <h1 class="title">Request Blocked</h1>
        <div class="subtitle">Your request was flagged by the firewall due to a detected security threat.</div>

        <div class="details">
            <div class="detail-row">
                <span class="label">REASON</span>
                <span class="value"><?= htmlspecialchars($reason) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">IP ADDRESS</span>
                <span class="value"><?= $ip ?></span>
            </div>
            <div class="detail-row">
                <span class="label">REQUEST ID</span>
                <span class="value"><?= htmlspecialchars($requestId) ?></span>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Security Web Intelligence Framework & Tracker
    </footer>

</body>
</html>
