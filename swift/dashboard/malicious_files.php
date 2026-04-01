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

// Handle Delete/Cleanup
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT quarantine_path FROM swift_quarantine WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    
    if ($file) {
        if (file_exists($file['quarantine_path'])) {
            unlink($file['quarantine_path']);
        }
        $pdo->prepare("DELETE FROM swift_quarantine WHERE id = ?")->execute([$id]);
        $message = "Malicious file record and quarantined file removed successfully.";
    }
}

// Fetch Quarantined Files
$files = [];
try {
    $stmt = $pdo->query("
        SELECT q.*, l.ip, l.risk_score, l.detection_tags, l.timestamp 
        FROM swift_quarantine q 
        JOIN swift_logs l ON q.log_id = l.id 
        ORDER BY q.id DESC
    ");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Database Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malicious Files | SWIFT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Google+Sans+Code:wght@400;500;600;700&family=Google+Sans:wght@400;500;700&display=swap');
        
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

        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 1rem 2rem;
            box-sizing: border-box;
        }

        .card {
            background: var(--surface);
            padding: 2rem;
            border-left: 4px solid var(--danger);
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid var(--success); color: var(--success); }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
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
            font-size: 0.7rem;
            letter-spacing: 0.05em;
        }

        .filename { font-weight: 600; color: var(--text-primary); }
        .path { font-family: var(--font-code); font-size: 0.75rem; color: var(--text-secondary); }
        .risk-badge { 
            padding: 2px 8px; 
            background: var(--danger); 
            color: white; 
            border-radius: 4px; 
            font-weight: 700; 
            font-size: 0.7rem;
        }

        .btn-delete {
            background: none;
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 4px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
        }
        .btn-delete:hover { background: var(--danger); color: white; }

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

        footer { margin-top: auto; padding: 2rem; border-top: 1px solid var(--border); text-align: center; color: var(--text-secondary); font-size: 0.75rem; }
    </style>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="assets/swift.png" alt="SWIFT" height="40">
        </a>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">Quarantine Management</div>
    </header>

    <div class="container" style="margin-top: -1.5rem;">
        <a href="index.php" class="back-link"><i class="fas fa-chevron-left"></i> Back to Dashboard</a>

        <div class="card">
            <h1 class="page-title"><i class="fas fa-shield-virus"></i> Malicious Files & Quarantines</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (empty($files)): ?>
                <div style="text-align: center; padding: 4rem; color: var(--text-secondary);">
                    <i class="fas fa-check-shield" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.2;"></i><br>
                    No malicious files detected yet. Keep your web app safe!
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Time Detected</th>
                            <th>Original Filename</th>
                            <th>Source IP</th>
                            <th>Risk Score</th>
                            <th>Detection Tags</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $row): ?>
                            <tr>
                                <td style="color: var(--text-secondary);"><?= htmlspecialchars($row['timestamp']) ?></td>
                                <td>
                                    <div class="filename"><?= htmlspecialchars($row['original_name']) ?></div>
                                    <div class="path"><?= htmlspecialchars(basename($row['quarantine_path'])) ?></div>
                                </td>
                                <td style="font-family: var(--font-code);"><?= htmlspecialchars($row['ip']) ?></td>
                                <td><span class="risk-badge"><?= $row['risk_score'] ?></span></td>
                                <td style="color: var(--danger); font-size: 0.75rem;"><?= htmlspecialchars($row['detection_tags']) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Permanently delete this quarantined file?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i> Purge</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div>&copy; <?= date('Y') ?> SWIFT Security Intelligence. All rights reserved.</div>
    </footer>
</body>
</html>
