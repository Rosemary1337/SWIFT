<?php
require_once __DIR__ . '/../boot.php';

session_start();

if (!isset($_SESSION['swift_auth'])) {
    header("Location: login.php");
    exit;
}

$pdo = \Swift\Core\Database::getInstance();

try {
    $stmt = $pdo->query("SELECT `key` FROM swift_settings LIMIT 1");
} catch (Exception $e) {
}

$pdo->exec("CREATE TABLE IF NOT EXISTS swift_settings (
    skey VARCHAR(50) PRIMARY KEY,
    svalue TEXT
)");

try {
    $pdo->query("SELECT skey FROM swift_settings LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("DROP TABLE IF EXISTS swift_settings");
    $pdo->exec("CREATE TABLE IF NOT EXISTS swift_settings (
        skey VARCHAR(50) PRIMARY KEY,
        svalue TEXT
    )");
}

$message = '';
$error = '';

$stmt = $pdo->query("SELECT * FROM swift_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['skey']] = $row['svalue'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groq_api_key = $_POST['groq_api_key'] ?? '';
    $log_retention_days = $_POST['log_retention_days'] ?? '3';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO swift_settings (skey, svalue) VALUES ('groq_api_key', ?) ON DUPLICATE KEY UPDATE svalue = ?");
        $stmt->execute([$groq_api_key, $groq_api_key]);
        
        $stmt = $pdo->prepare("INSERT INTO swift_settings (skey, svalue) VALUES ('log_retention_days', ?) ON DUPLICATE KEY UPDATE svalue = ?");
        $stmt->execute([$log_retention_days, $log_retention_days]);

        $message = "Settings updated successfully.";
        $settings['groq_api_key'] = $groq_api_key;
        $settings['log_retention_days'] = $log_retention_days;
    } catch (Exception $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIFT System Settings</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Google+Sans+Code:ital,wght@0,300..800;1,300..800&family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap');
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
            --warning: #f59e0b;
            --success: #22c55e;
            --font-main: 'Google Sans', 'Inter', -apple-system, sans-serif;
            --font-code: 'Google Sans Code', monospace;
            --radius: 4px;
        }

        body.theme-blue {
            --accent: #3b82f6;
        }

        body.theme-light {
            --bg: #f8f9fa;
            --surface: #ffffff;
            --border: #dee2e6;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent: #2563eb;
        }

        body.theme-light .card { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        body.theme-light input[type="password"], body.theme-light input[type="text"], body.theme-light select {
            background: #ffffff;
            color: #212529;
        }
        body.theme-light header { 
            background: linear-gradient(to bottom, rgba(248, 249, 250, 1) 0%, rgba(248, 249, 250, 0) 100%);
            border-bottom: none;
            backdrop-filter: none;
        }
        body.theme-light footer { background: #ffffff; border-top: 1px solid var(--border); }
        body.theme-light .brand img, body.theme-light .brand-footer img { filter: brightness(0); }

        body {
            font-family: var(--font-main);
            background-color: var(--bg);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            font-size: 13px;
            letter-spacing: -0.01em;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Utilities */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .gap-4 { gap: 1rem; }
        .text-xs { font-size: 0.75rem; }
        .text-secondary { color: var(--text-secondary); }

        header {
            background: linear-gradient(to bottom, rgba(10, 10, 10, 1) 0%, rgba(10, 10, 10, 0) 100%);
            border-bottom: none;
            padding: 1.5rem 2rem 3rem 2rem; 
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none; 
        }

        header > * { pointer-events: auto; } 

        .brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.125rem;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            text-decoration: none;
        }

        .brand span {
            margin-top: 5px;
            font-style: italic;
        }

        .container {
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            padding: 1rem 2rem;
            box-sizing: border-box;
        }

        /* Large Card Layout */
        .card {
            background: var(--surface);
            padding: 2.5rem;
            border-left: 4px solid var(--accent);
            display: flex;
            flex-direction: column;
            margin-bottom: 2rem;
        }

        .card-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
        }

        .card-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            margin: 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-weight: 500;
            font-size: 0.85rem;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--accent); }

        /* Form Styling */
        .form-section {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 768px) {
            .form-section { grid-template-columns: 1fr; gap: 1rem; }
        }

        .section-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .section-info p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.5;
            font-size: 0.85rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input[type="password"], input[type="text"] {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            color: var(--text-primary);
            font-family: var(--font-code);
            font-size: 0.9rem;
            width: 100%;
            box-sizing: border-box;
        }

        input:focus { outline: none; border-color: var(--accent); }

        .btn-save {
            background: var(--accent);
            color: #ffffff;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius);
            font-family: var(--font-main);
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            width: fit-content;
            align-self: flex-start;
            transition: transform 0.1s, opacity 0.2s;
        }

        .btn-save:hover { opacity: 0.9; }
        .btn-save:active { transform: scale(0.97); }

        /* Notifications */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
        }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid var(--success); color: var(--success); }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); }

        /* Footer matching index.php */
        footer {
            margin-top: auto;
            padding: 2rem;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.75rem;
            background: var(--surface);
        }
        
        footer .brand-footer {
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }

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
            overflow: hidden;
            animation: fadeInDropdown 0.2s ease-out;
        }

        @keyframes fadeInDropdown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-content a {
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            transition: background 0.2s;
        }

        .dropdown-content a:hover {
            background-color: var(--border);
        }

        .dropdown-content a i {
            width: 16px;
            text-align: center;
            color: var(--accent);
        }

        .show { display: block; }

        .hamburger-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hamburger-btn:hover {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <header>
        <a href="index" class="brand"><img src="assets/swift.png" alt="SWIFT" height="45"> <span class="text-xs text-secondary" style="font-weight: 400; margin-left: 0.5rem;">Security Intelligence Platform</span></a>
        <div class="flex items-center gap-4">
            <div class="text-xs text-secondary" id="load-status">Settings</div>
            <button onclick="toggleTheme()" id="theme-toggle" title="Toggle Theme" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1rem; transition: color 0.2s; padding: 0.5rem;">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <div class="dropdown">
                <button onclick="toggleDropdown()" class="hamburger-btn" id="hamburger-trigger" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div id="myDropdown" class="dropdown-content">
                    <a href="settings"><i class="fas fa-gear"></i> Settings</a>
                    <a href="docs"><i class="fas fa-book"></i> Documentation</a>
                    <a href="info"><i class="fas fa-circle-info"></i> Project Info</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: -1.5rem;">
        <a href="index" class="back-link"><i class="fas fa-chevron-left"></i> Back to Live Monitor</a>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Configuration</div>
            </div>
            
            <h1 class="page-title" style="margin-bottom: 2rem;">System Settings</h1>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="display: flex; flex-direction: column;">
                <!-- AI Section -->
                <div class="form-section">
                    <div class="section-info">
                        <h3>Artificial Intelligence</h3>
                        <p>Configure telemetry analysis engines and large language model providers.</p>
                    </div>
                    <div class="input-group">
                        <label for="groq_api_key">Groq Cloud API Key</label>
                        <input type="password" name="groq_api_key" id="groq_api_key" 
                               value="<?php echo htmlspecialchars($settings['groq_api_key'] ?? ''); ?>" 
                               placeholder="gsk_************************************************">
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                            Acquire credentials from <a href="https://console.groq.com/keys" target="_blank" style="color: var(--accent); text-decoration: none;">Groq Console</a>.
                        </p>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border); margin-bottom: 3rem;"></div>

                <!-- Data Management Section -->
                <div class="form-section">
                    <div class="section-info">
                        <h3>Data Management</h3>
                        <p>Control telemetry storage and automated cleanup routines to maintain system performance.</p>
                    </div>
                    <div class="input-group">
                        <label for="log_retention_days">Log Retention Period (Days)</label>
                        <select name="log_retention_days" id="log_retention_days" 
                                style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; color: var(--text-primary); font-family: var(--font-main); font-size: 0.9rem; width: 100%; appearance: none; cursor: pointer;">
                            <?php 
                            $current_retention = $settings['log_retention_days'] ?? '3';
                            for ($i = 1; $i <= 7; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($current_retention == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> <?php echo ($i == 1) ? 'Day' : 'Days'; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                            Security logs older than this period will be automatically purged from the database.
                        </p>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> Save System Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme Management
        function initTheme() {
            const savedTheme = localStorage.getItem('swift-theme') || 'dark';
            if (savedTheme === 'light') {
                document.body.classList.add('theme-light');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }
        }

        function toggleTheme() {
            const isLight = document.body.classList.toggle('theme-light');
            localStorage.setItem('swift-theme', isLight ? 'light' : 'dark');
            document.getElementById('theme-icon').className = isLight ? 'fas fa-sun' : 'fas fa-moon';
        }

        function toggleDropdown() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.hamburger-btn') && !event.target.matches('.fa-bars')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        initTheme();
    </script>
    <footer>
        <div class="brand-footer"><img src="assets/swift.png" alt="SWIFT" style="height:60px;"></div>
        <div>&copy; <?php echo date('Y'); ?> Smart Web Intelligence Framework & Tracker. All rights reserved.</div>
        <div style="margin-top: 0.5rem; opacity: 0.5;"><i class="fa-brands fa-github"></i> Available on <a href="https://github.com/Rosemary1337/Swift" style="text-decoration: none; color: var(--accent);">GitHub</a></div>
    </footer>
</body>
</html>
