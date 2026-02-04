<?php
require_once __DIR__ . '/../boot.php';

session_start();

if (!isset($_SESSION['swift_auth'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About SWIFT</title>
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

        body.theme-light {
            --bg: #f8f9fa;
            --surface: #ffffff;
            --border: #dee2e6;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent: #2563eb;
        }

        body.theme-light .card { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
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
            z-index: 1;
        }

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

        .section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i { color: var(--accent); }

        .content-text {
            line-height: 1.6;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .link-card {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 1rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: var(--text-primary);
        }

        .link-card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .link-card i { font-size: 1.25rem; color: var(--accent); }

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

        .flex { display: flex; }
        .items-center { align-items: center; }
        .gap-4 { gap: 1rem; }
        .text-xs { font-size: 0.75rem; }
        .text-secondary { color: var(--text-secondary); }

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
<body class="theme-dark">
    <header>
        <a href="index" class="brand"><img src="assets/swift.png" alt="SWIFT" height="45"> <span class="text-xs text-secondary" style="font-weight: 400; margin-left: 0.5rem;">Security Intelligence Platform</span></a>
        <div class="flex items-center gap-4">
            <div class="text-xs text-secondary">Information</div>
            <button onclick="toggleTheme()" id="theme-toggle" title="Toggle Theme" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1rem; transition: color 0.2s; padding: 0.5rem;">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <div class="dropdown">
                <button onclick="toggleDropdown()" class="hamburger-btn" id="hamburger-trigger" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div id="myDropdown" class="dropdown-content">
                    <a href="firewall.php"><i class="fas fa-shield-halved"></i> Firewall Logs</a>
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
                <div class="card-title">Project Information</div>
            </div>
            
            <h1 class="page-title" style="margin-bottom: 2rem;">Intelligence Overview</h1>

            <p class="content-text" style="color: var(--text-secondary); margin-bottom: 3rem;">Smart Web Intelligence Framework & Tracker (SWIFT) is a lightweight, high-performance security monitoring solution designed for forensic-level telemetry collection and behavioral threat analysis.</p>

            <div class="section">
                <div class="section-title"><i class="fas fa-user-ninja"></i> Engineering</div>
                <div class="content-text">
                    Architected and developed by <strong>Rosemary</strong> (@Rosemary1337).<br>
                    Focusing on advanced agentic coding and security intelligence solutions for modern web infrastructures.
                </div>
            </div>

            <div class="section">
                <div class="section-title"><i class="fas fa-certificate"></i> Open Source License</div>
                <div class="content-text">
                    SWIFT is released under the <strong>MIT License</strong>. It is free, open-source software built for the security community to enhance transparent web application monitoring.
                </div>
            </div>

            <div class="section">
                <div class="section-title"><i class="fas fa-code-branch"></i> External Resources</div>
                <div class="link-grid">
                    <a href="https://github.com/Rosemary1337/Swift" target="_blank" class="link-card">
                        <i class="fab fa-github"></i>
                        <div>
                            <div style="font-weight: 600;">GitHub Repository</div>
                            <div style="font-size: 0.7rem; color: var(--text-secondary);">Source code & issues</div>
                        </div>
                    </a>
                    <a href="#" class="link-card">
                        <i class="fas fa-book"></i>
                        <div>
                            <div style="font-weight: 600;">Technical Docs</div>
                            <div style="font-size: 0.7rem; color: var(--text-secondary);">Deployment guidelines</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="brand-footer"><img src="assets/swift.png" alt="SWIFT" style="height:60px;"></div>
        <div>&copy; <?php echo date('Y'); ?> Smart Web Intelligence Framework & Tracker. All rights reserved.</div>
        <div style="margin-top: 0.5rem; opacity: 0.5;"><i class="fa-brands fa-github"></i> Available on <a href="https://github.com/Rosemary1337/Swift" style="text-decoration: none; color: var(--accent);">GitHub</a></div>
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
</body>
</html>
