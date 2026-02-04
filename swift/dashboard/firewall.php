<?php
require_once __DIR__ . '/../boot.php';

session_start();

if (!isset($_SESSION['swift_auth'])) {
    header("Location: login.php");
    exit;
}

$pdo = \Swift\Core\Database::getInstance();
$message = '';
$error = '';

// Fetch Recent WAF Events (Malicious Logs)
$logs = [];
try {
    $stmt = $pdo->query("SELECT * FROM swift_logs WHERE classification = 'malicious' ORDER BY id DESC LIMIT 50");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Database Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firewall Logs | SWIFT</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Google+Sans+Code:wght@400;500;600;700&family=Google+Sans:wght@400;500;700&display=swap');
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0a0a0a;
            --surface: #141414;
            --border: #262626;
            --text-primary: #e5e5e5;
            --text-secondary: #a3a3a3;
            --accent: #f97316;
            --danger: #ef4444;
            --success: #22c55e;
            --font-main: 'Google Sans', sans-serif;
            --font-code: 'Google Sans Code', monospace;
            --radius: 4px;
        }

        body.theme-light {
            --bg: #f8f9fa;
            --surface: #ffffff;
            --border: #dee2e6;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent: #2563eb;
        }

        body.theme-light header { 
            background: linear-gradient(to bottom, rgba(248, 249, 250, 1) 0%, rgba(248, 249, 250, 0) 100%);
            border-bottom: none;
        }
        body.theme-light footer { background: #ffffff; border-top: 1px solid var(--border); }
        body.theme-light .brand img, body.theme-light .brand-footer img { filter: brightness(0); }

        body {
            font-family: var(--font-main);
            background-color: var(--bg);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: linear-gradient(to bottom, rgba(10, 10, 10, 1) 0%, rgba(10, 10, 10, 0) 100%);
            padding: 1.5rem 2rem 3rem 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--text-primary);
            text-decoration: none;
        }

        .container {
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            padding: 1rem 2rem;
            box-sizing: border-box;
        }

        .card {
            background: var(--surface);
            padding: 2.5rem;
            border-left: 4px solid var(--accent);
            display: flex;
            flex-direction: column;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 2rem 0;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
        }
        .alert-success { background: var(--success); border: none; color: var(--bg); }
        .alert-error { background: var(--danger); border: none; color: var(--bg); }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        th {
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        td { color: var(--text-primary); }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .back-link:hover { color: var(--accent); }

        /* Dropdown & Hamburger */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: var(--surface);
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.4);
            z-index: 1000;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-top: 0.5rem;
        }
        .dropdown-content a {
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
        }
        .dropdown-content a:hover { background-color: var(--border); }
        .show { display: block; }
        .hamburger-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.1rem; padding: 0.5rem; }
        
        footer { margin-top: auto; padding: 2rem; border-top: 1px solid var(--border); text-align: center; color: var(--text-secondary); font-size: 0.75rem; background: var(--surface); }
    </style>
</head>
<body class="theme-dark">
    <header>
        <a href="index.php" class="brand"><img src="assets/swift.png" alt="SWIFT" height="45"> <span style="margin-left: 0.5rem; font-size: 0.75rem; font-weight: 400; color: var(--text-secondary);">Security Intelligence Platform</span></a>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 0.75rem; color: var(--text-secondary);">Firewall</div>
            <button onclick="toggleTheme()" id="theme-toggle" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1rem;"><i class="fas fa-moon" id="theme-icon"></i></button>
            <div class="dropdown">
                <button onclick="toggleDropdown()" class="hamburger-btn"><i class="fas fa-bars"></i></button>
                <div id="myDropdown" class="dropdown-content">
                    <a href="firewall.php"><i class="fas fa-shield-halved"></i> Firewall Logs</a>
                    <a href="settings.php"><i class="fas fa-gear"></i> Settings</a>
                    <a href="docs.php"><i class="fas fa-book"></i> Documentation</a>
                    <a href="info.php"><i class="fas fa-circle-info"></i> Project Info</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: -1.5rem;">
        <a href="index.php" class="back-link"><i class="fas fa-chevron-left"></i> Back to Live Monitor</a>

        <div class="card">
            <h1 class="page-title">Firewall Event Logs</h1>

            <?php if ($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (empty($logs)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <i class="fas fa-shield-cat" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i><br>
                    No recent blocking events found.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>IP Address</th>
                            <th>Method</th>
                            <th>Target URI</th>
                            <th>Detection Tags</th>
                            <th>Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $row): ?>
                            <tr>
                                <td style="color: var(--text-secondary); white-space: nowrap;"><?= htmlspecialchars($row['timestamp']) ?></td>
                                <td style="font-family: var(--font-code);"><?= htmlspecialchars($row['ip']) ?></td>
                                <td style="font-family: var(--font-code); color: var(--accent);"><?= htmlspecialchars($row['request_method']) ?></td>
                                <td style="font-family: var(--font-code); word-break: break-all;"><?= htmlspecialchars($row['uri']) ?></td>
                                <td style="font-size: 0.85rem; color: var(--danger); font-weight: 500;"><?= htmlspecialchars($row['detection_tags']) ?></td>
                                <td style="font-weight: 700; color: var(--danger);"><?= htmlspecialchars($row['risk_score']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="brand-footer"><img src="assets/swift.png" alt="SWIFT" style="height:60px;"></div>
        <div>&copy; <?= date('Y') ?> Smart Web Intelligence Framework & Tracker. All rights reserved.</div>
    </footer>

    <script>
        function initTheme() {
            const savedTheme = localStorage.getItem('swift-theme') || 'dark';
            if (savedTheme === 'light') {
                document.body.classList.remove('theme-dark');
                document.body.classList.add('theme-light');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }
        }
        function toggleTheme() {
            const isLight = document.body.classList.toggle('theme-light');
            document.body.classList.toggle('theme-dark', !isLight);
            localStorage.setItem('swift-theme', isLight ? 'light' : 'dark');
            document.getElementById('theme-icon').className = isLight ? 'fas fa-sun' : 'fas fa-moon';
        }
        function toggleDropdown() { document.getElementById("myDropdown").classList.toggle("show"); }
        window.onclick = function(event) {
            if (!event.target.matches('.hamburger-btn') && !event.target.matches('.fa-bars')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) openDropdown.classList.remove('show');
                }
            }
        }
        initTheme();
    </script>
</body>
</html>
