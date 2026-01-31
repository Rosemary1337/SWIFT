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
    <title>SWIFT Dashboard</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Google+Sans+Code:ital,wght@0,300..800;1,300..800&family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap');
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --card-shadow: none;
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

        body.theme-light .ai-brief { background: var(--surface); }
        body.theme-light .card { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        body.theme-light .modal { background: #ffffff; }
        body.theme-light th { background: #f8f9fa; }
        body.theme-light .code-block { background: #f1f3f5; color: #1e293b; }
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
        }


        /* Utilities */
        .w-full { width: 100%; }
        .h-full { height: 100%; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-sm { font-size: 0.8125rem; }
        .text-xs { font-size: 0.75rem; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .text-secondary { color: var(--text-secondary); }

        /* Layout */
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
        }

        .brand span {
            margin-top: 5px;
            font-style: italic;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 1rem;
        }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1rem;
        }

        .col-span-12 { grid-column: span 12; }
        .col-span-8 { grid-column: span 8; }
        .col-span-4 { grid-column: span 4; }
        .col-span-3 { grid-column: span 3; }

        @media (max-width: 1024px) {
            .col-span-8, .col-span-4, .col-span-3 { grid-column: span 12; }
        }

        /* Cards */
        .card {
            background: var(--surface);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            box-shadow: var(--card-shadow);
        }

        .card.accent-border { border-left: 4px solid var(--accent); } 

        .card-header {
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1;
            margin-top: 0.25rem;
        }

        /* AI Brief */
        .ai-brief {
            background-color: var(--surface);
        }
        
        .ai-content {
            line-height: 1.6;
            color: var(--text-primary);
            font-size: 0.9rem;
            font-weight: 400;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem; 
        }

        th {
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--bg);
        }

        td {
            padding: 0.75rem 1rem; 
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 4px; 
            font-size: 0.7rem;
            font-weight: 600;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge.normal { color: #ffffff; background: var(--success); border: none; }
        .badge.suspicious { color: #ffffff; background: var(--warning); border: none; }
        .badge.malicious { color: #ffffff; background: var(--danger); border: none; }

        .method { font-family: var(--font-code); color: var(--text-secondary); margin-right: 0.5rem; }
        .uri { font-family: var(--font-code); color: var(--text-primary); }
        .ip-cell { display: flex; align-items: center; gap: 0.5rem; }
        .country-tag { 
            font-size: 0.65rem; 
            color: var(--accent);
            padding: 2px 6px; 
            font-weight: 600;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--accent);
            color: #ffffff !important;
            border: none !important;
            padding: 0.4rem 1rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .btn-primary:active { transform: scale(0.97); }

        /* Pagination */
        .pagination { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem; padding-bottom: 1rem; }
        .page-btn {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
            min-width: 30px;
            text-align: center;
        }
        .page-btn:hover { background: var(--border); color: var(--text-primary); }
        .page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        /* Tabs */
        .tabs { display: flex; border-bottom: 1px solid var(--border); margin-bottom: 1rem; }
        .tab { padding: 0.75rem 1.5rem; cursor: pointer; color: var(--text-secondary); border-bottom: 2px solid transparent; font-weight: 500; }
        .tab:hover { color: var(--text-primary); }
        .tab.active { color: var(--accent); border-bottom-color: var(--accent); }

        /* Modal */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; justify-content: center; align-items: center; z-index: 100; backdrop-filter: blur(4px); }
        .modal { 
            background: var(--surface); width: 800px; max-width: 90%; max-height: 90vh; 
            border: 1px solid var(--border); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
            overflow-y: auto; display: flex; flex-direction: column;
            scrollbar-width: none; 
            -ms-overflow-style: none; 
        }
        .modal::-webkit-scrollbar { display: none; }
        
        .modal-header { padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); }
        .modal-close { cursor: pointer; color: var(--text-secondary); font-size: 1.5rem; }
        .modal-body { padding: 1.5rem; }
        .code-block { 
            background: #111; padding: 1rem; border-radius: 4px; border: 1px solid var(--border); 
            font-family: var(--font-code); font-size: 0.85rem; color: #dddee9ff; 
            overflow-x: auto; white-space: pre-wrap; word-break: break-all;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .code-block::-webkit-scrollbar { display: none; }
        
        .kv-row { display: flex; border-bottom: 1px solid var(--border); padding: 0.5rem 0; }
        .kv-key { width: 150px; color: var(--text-secondary); font-weight: 500; }
        .kv-value { color: var(--text-primary); flex: 1; word-break: break-all; }

        /* Top Attackers Widget */
        .attacker-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
        .attacker-row:last-child { border-bottom: none; }
        .attacker-ip { font-family: var(--font-code); font-weight: 600; color: var(--danger); }
        .attacker-stat { font-size: 0.85rem; color: var(--text-secondary); text-align: right; }

        /* Footer */
        footer {
            margin-top: 4rem;
            padding: 2rem;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.75rem;
            background: var(--surface);
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
        <div class="brand"><img src="assets/swift.png" alt="SWIFT" height="45"> <span class="text-xs text-secondary" style="font-weight: 400; margin-left: 0.5rem;">Security Intelligence Platform</span>
            <span class="badge" style="margin-left: 10px; background: var(--accent); color: white; border: none; font-size: 0.6rem;">v1.1</span></div>
        <div class="flex items-center gap-4">
            <div class="text-xs text-secondary" id="load-status">Connecting...</div>
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

    <div class="container">
        <h2 style="margin-bottom: 1.5rem; font-weight: 500; color: var(--text-primary); letter-spacing: -0.02em;">Analytics for <span style="color: var(--accent);"><?php echo $_SERVER['HTTP_HOST']; ?></span></h2>
        
        <div class="grid mb-6">
            <div class="card col-span-3" style="border-left: 3px solid var(--accent);">
                <div class="card-title">24h Traffic</div>
                <div class="stat-value" style="color: var(--accent);" id="stat-total">0</div>
            </div>
            <div class="card col-span-3" style="border-left: 3px solid var(--danger);">
                <div class="card-title">Threats Detected</div>
                <div class="stat-value" style="color: var(--danger);" id="stat-threats">0</div>
            </div>
            <div class="card col-span-3" style="border-left: 3px solid var(--warning);">
                <div class="card-title">Avg Risk Score</div>
                <div class="stat-value" style="color: var(--warning);" id="stat-risk">0</div>
            </div>
            <div class="card col-span-3" style="border-left: 3px solid var(--accent);">
                <div class="card-title">IP Address</div>
                <div class="stat-value" style="color: var(--accent);" id="stat-ips">0</div>
            </div>

            <div class="card col-span-8">
                <div class="card-header">
                    <div class="card-title">Traffic Analysis</div>
                </div>
                <div class="flex items-center">
                    <div style="height: 350px; width: 75%;">
                        <canvas id="trafficChart"></canvas>
                    </div>
                    <div style="width: 25%; padding-left: 1.5rem; border-left: 1px solid var(--border); height: 350px; overflow-y: auto;">
                        <div class="text-xs text-secondary uppercase tracking-wider mb-4">Traffic Activity</div>
                        <div id="traffic-list" class="flex flex-col gap-3">
                            <div class="text-secondary text-xs">Awaiting data...</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card col-span-4">
                <div class="tabs" style="margin-bottom: 0.5rem; border-bottom: none;">
                    <div class="tab active" onclick="switchSidebar('threats', this)">Threats</div>
                    <div class="tab" onclick="switchSidebar('attackers', this)">Attackers</div>
                </div>
                
                <div id="sidebar-threats" class="h-full flex items-center justify-center">
                    <div style="height: 250px; width: 60%;">
                        <canvas id="threatChart"></canvas>
                    </div>
                    <div style="width: 40%; padding-left: 1rem; display: flex; flex-direction: column; justify-content: center;">
                         <div class="text-xs text-secondary uppercase tracking-wider mb-1">Active Threats</div>
                         <div id="threat-stat-count" class="font-code text-danger font-bold" style="font-size: 2.25rem;">0</div>
                         <div class="text-xs text-secondary mt-1">Total Detected</div>
                    </div>
                </div>

                <div id="sidebar-attackers" style="display: none;">
                    <div id="top-attackers-list" class="flex flex-col gap-2">
                        <div class="text-secondary text-sm">Loading...</div>
                    </div>
                </div>
            </div>

            <div class="card col-span-4">
                <div class="card-header">
                    <div class="card-title">Attack Vectors</div>
                </div>
                <div style="height: 200px; width: 100%;">
                    <canvas id="vectorChart"></canvas>
                </div>
            </div>

            <div class="card col-span-4">
                <div class="card-header">
                    <div class="card-title">Targeted Endpoints</div>
                </div>
                <div id="top-endpoints-list" class="flex flex-col gap-2">
                    <div class="text-secondary text-xs">Loading...</div>
                </div>
            </div>

            <div class="card col-span-4 ai-brief">
                <div class="card-header">
                    <div class="card-title">AI Quick Analysis</div>
                    <button onclick="fetchAI()" id="ai-trigger" class="btn-primary" style="height: 24px; font-size: 0.65rem; padding: 0 0.75rem;">
                        <i class="fas fa-magic" style="margin-right: 4px;"></i> ANALYZE WITH AI
                    </button>
                </div>
                <div id="ai-content" class="ai-content" style="font-size: 0.8rem; overflow-y: auto; max-height: 180px;">
                    <div class="text-secondary text-xs" style="text-align: center; padding: 2rem;">Click "Analyze" to generate a security briefing.</div>
                </div>
            </div>

            <div class="card col-span-12">
                <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
                    <div class="card-title">Security Telemetry Live Stream</div>
                    <div class="flex gap-2" style="margin-left: auto;">
                        <div class="flex items-center" style="position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 10px; color: var(--text-secondary); font-size: 0.7rem;"></i>
                            <input type="text" id="log-search" placeholder="Filter logs..." class="text-xs" style="padding: 0.4rem 0.4rem 0.4rem 1.8rem; border: 1px solid var(--border); border-radius: 4px; background: var(--bg); color: var(--text-primary); width: 150px;" oninput="filterLogs()">
                        </div>
                        <div class="flex items-center" style="gap: 0.5rem; color: var(--text-secondary); font-size: 0.75rem;">
                            <span style="margin-left: 10px;">Show:</span>
                            <select id="row-count" class="text-xs" style="padding: 0.3rem; border: 1px solid var(--border); border-radius: 4px; background: var(--bg); color: var(--text-primary); outline: none; cursor: pointer; margin-right: 15px;" onchange="fetchData()">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <button onclick="window.location.href='api.php?action=export'" class="btn-primary" style="height: 28px; font-size: 0.7rem;">
                            <i class="fas fa-download" style="margin-right: 6px;"></i> EXPORT
                        </button>
                    </div>
                </div>
                
                <div class="tabs">
                    <div class="tab active" onclick="switchFilter('all', this)">All Traffic</div>
                    <div class="tab" onclick="switchFilter('threats', this)" style="color: var(--danger);">Threats Only</div>
                    <div class="tab" onclick="switchFilter('safe', this)" style="color: var(--success);">Safe Logs</div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 14%;">Timestamp</th>
                                <th style="width: 13%;">Source / Location</th>
                                <th style="width: 43%;">Request (Method + URI)</th>
                                <th style="width: 10%;">Result</th>
                                <th style="width: 8%;">Score</th>
                                <th style="width: 12%;">Tags</th>
                            </tr>
                        </thead>
                        <tbody id="logs-body">
                        </tbody>
                    </table>
                </div>
                <div id="pagination-controls" class="pagination">
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="brand-footer"><img src="assets/swift.png" alt="SWIFT" style="height:60px;"></div>
        <div>&copy; <?php echo date('Y'); ?> Smart Web Intelligence Framework & Tracker. All rights reserved.</div>
        <div style="margin-top: 0.5rem; opacity: 0.5;"><i class="fa-brands fa-github"></i> Available on <a href="https://github.com/Rosemary1337/Swift" style="text-decoration: none; color: var(--accent);">GitHub</a></div>
    </footer>

    <div class="modal-overlay" id="logModal" onclick="if(event.target === this) closeLogModal()">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Log Details</div>
                <div class="modal-close" onclick="closeLogModal()">&times;</div>
            </div>
            <div class="modal-body" id="modal-content">
                Loading...
            </div>
        </div>
    </div>

    <script>
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
            updateChartTheme(isLight);
        }

        function updateChartTheme(isLight) {
            const gridColor = isLight ? 'rgba(0,0,0,0.05)' : '#262626';
            const textColor = isLight ? '#6c757d' : '#a3a3a3';
            const accentColor = isLight ? '#2563eb' : '#f97316';
            const accentBg = isLight ? 'rgba(37, 99, 235, 0.1)' : 'rgba(249, 115, 22, 0.05)';

            if (trafficChart) {
                trafficChart.options.scales.y.grid.color = gridColor;
                trafficChart.options.scales.x.ticks.color = textColor;
                trafficChart.options.scales.y.ticks.color = textColor;
                trafficChart.data.datasets[0].borderColor = accentColor;
                trafficChart.data.datasets[0].backgroundColor = accentBg;
                trafficChart.update();
            }
            if (threatChart) {
                threatChart.options.plugins.legend.labels.color = textColor;
                threatChart.update();
            }
            if (vectorChart) {
                vectorChart.options.scales.x.ticks.color = textColor;
                vectorChart.options.scales.y.ticks.color = textColor;
                vectorChart.data.datasets[0].backgroundColor = accentColor;
                vectorChart.update();
            }
        }

        initTheme();

        const trafficCtx = document.getElementById('trafficChart').getContext('2d');
        const threatCtx = document.getElementById('threatChart').getContext('2d');
        const vectorCtx = document.getElementById('vectorChart').getContext('2d');

        const trafficChart = new Chart(trafficCtx, {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'Requests', data: [], borderColor: '#f97316', tension: 0.4, pointRadius: 0, fill: true, backgroundColor: 'rgba(249, 115, 22, 0.05)' }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#262626' } }, x: { grid: { display: false } } } }
        });

        const threatChart = new Chart(threatCtx, {
            type: 'pie',
            data: { 
                labels: ['Safe', 'Suspicious', 'Critical'], 
                datasets: [{ 
                    data: [0, 0, 0], 
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'], 
                    borderWidth: 0,
                    hoverOffset: 10
                }] 
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 10
                },
                plugins: { 
                    legend: { 
                        position: 'bottom', 
                        labels: { color: '#a3a3a3', usePointStyle: true, padding: 20 } 
                    } 
                } 
            }
        });

        const vectorChart = new Chart(vectorCtx, {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Attacks', data: [], backgroundColor: '#f97316', borderRadius: 4 }] },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }
        });

        updateChartTheme(document.body.classList.contains('theme-light'));

        let currentPage = 1;
        let currentFilter = 'all';
        let currentLogs = []; 
        let allLogs = []; 

        const geoCache = {};
        const pendingGeo = new Set();

        async function fetchGeo(ip) {
            if (geoCache[ip] || pendingGeo.has(ip)) return;
            if (ip === '::1' || ip === '127.0.0.1') {
                geoCache[ip] = 'Local';
                return;
            }

            pendingGeo.add(ip);
            try {
                const res = await fetch(`//ipwho.is/${ip}`);
                const data = await res.json();
                if (data && data.success) {
                    geoCache[ip] = data.country || 'Unknown';
                    renderLogs(currentLogs); 
                }
            } catch (e) {
                console.error("Geo error", e);
            } finally {
                pendingGeo.delete(ip);
            }
        }

        function updateCharts() {
            const textColor = '#8b949e';
            const gridColor = '#30363d';

            if (trafficChart) {
                trafficChart.options.scales.y.grid.color = gridColor;
                if(trafficChart.options.scales.x.ticks) trafficChart.options.scales.x.ticks.color = textColor;
                if(trafficChart.options.scales.y.ticks) trafficChart.options.scales.y.ticks.color = textColor;
                trafficChart.update();
            }
            if (threatChart) {
                threatChart.options.plugins.legend.labels.color = textColor;
                threatChart.update();
            }
            if (vectorChart) {
                if(vectorChart.options.scales.x.ticks) vectorChart.options.scales.x.ticks.color = textColor;
                if(vectorChart.options.scales.y.ticks) vectorChart.options.scales.y.ticks.color = textColor;
                vectorChart.update();
            }
        }

        updateCharts();

        async function fetchData(page = 1) {
            try {
                currentPage = page;
                const limit = document.getElementById('row-count').value || 25;
                const res = await fetch(`api.php?action=stats&filter=${currentFilter}&limit=${limit}&page=${currentPage}`);
                const data = await res.json();
                
                currentLogs = data.recent_logs;
                allLogs = [...currentLogs];
                filterLogs();
                renderPagination(data.pagination);

                document.getElementById('stat-total').innerText = data.total_24h;
                document.getElementById('stat-threats').innerText = data.threats_24h;
                document.getElementById('stat-risk').innerText = data.avg_risk;
                document.getElementById('stat-ips').innerText = data.unique_ips || 0;

                if (data.top_attackers) {
                    const attackerList = document.getElementById('top-attackers-list');
                    if (data.top_attackers.length > 0) {
                        attackerList.innerHTML = data.top_attackers.map(attacker => {
                            const country = geoCache[attacker.ip];
                            if (!country) fetchGeo(attacker.ip);
                            return `
                                <div class="attacker-row">
                                    <div class="attacker-ip">
                                        ${attacker.ip}
                                        <div class="text-secondary" style="font-size: 0.65rem;">${country || 'Detecting...'}</div>
                                    </div>
                                    <div>
                                        <div class="badge malicious" style="margin-bottom: 2px;">Risk: ${attacker.total_risk}</div>
                                        <div class="attacker-stat">${attacker.attack_count} attacks</div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        attackerList.innerHTML = '<div class="text-secondary text-sm">No significant threats detected yet.</div>';
                    }
                }

                if (data.top_endpoints) {
                    const endpointList = document.getElementById('top-endpoints-list');
                    if(data.top_endpoints.length > 0) {
                        endpointList.innerHTML = data.top_endpoints.map(ep => `
                             <div class="attacker-row">
                                <div class="uri" style="font-size: 0.8rem;">${ep.uri.substring(0, 35)}</div>
                                <div class="badge suspicious">${ep.count} Hits</div>
                            </div>
                        `).join('');
                    } else {
                        endpointList.innerHTML = '<div class="text-secondary text-sm">No attacks targeted specific endpoints yet.</div>';
                    }
                }

                if (data.chart_traffic && trafficChart) {
                    trafficChart.data.labels = data.chart_traffic.map(item => item.hour);
                    trafficChart.data.datasets[0].data = data.chart_traffic.map(item => item.count);
                    trafficChart.update();
                    if (data.chart_traffic.length > 0) {
                        const trafficList = document.getElementById('traffic-list');
                        const history = [...data.chart_traffic].slice(-6).reverse();
                        trafficList.innerHTML = history.map(item => `
                            <div class="flex justify-between items-center pb-2 border-b border-white border-opacity-5">
                                <span class="font-code text-secondary" style="font-size: 0.85rem;">${item.hour}</span>
                                <span class="font-code text-accent font-bold" style="font-size: 1.1rem;">${item.count} <small class="text-secondary" style="font-size: 0.6rem; font-weight: normal;">REQ</small></span>
                            </div>
                        `).join('');
                    }
                }

                if (data.chart_threats && threatChart && data.chart_threats.length > 0) {
                    let counts = { 'normal': 0, 'suspicious': 0, 'malicious': 0 };
                    data.chart_threats.forEach(item => { counts[item.classification] = item.count; });
                    threatChart.data.datasets[0].data = [counts.normal, counts.suspicious, counts.malicious];
                    threatChart.update();

                    let totalThreats = (counts.suspicious || 0) + (counts.malicious || 0);
                    document.getElementById('threat-stat-count').innerText = totalThreats;
                }

                if (data.attack_vectors && vectorChart) {
                    vectorChart.data.labels = data.attack_vectors.map(item => item.detection_tags || 'Generic');
                    vectorChart.data.datasets[0].data = data.attack_vectors.map(item => item.count);
                    vectorChart.update();
                }
                
                document.getElementById('load-status').innerText = 'Last updated: ' + new Date().toLocaleTimeString();

            } catch (e) {
                console.error("Fetch error", e);
                document.getElementById('load-status').innerHTML = '<span style="color:var(--danger)">Connection Offline (Check Console)</span>';
            }
        }
        
        function renderPagination(pagination) {
            const container = document.getElementById('pagination-controls');
            if (!pagination || pagination.total_pages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = `
                <button class="page-btn" onclick="fetchData(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;
            const range = 2; 
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - range && i <= pagination.current_page + range)) {
                    html += `<button class="page-btn ${i === pagination.current_page ? 'active' : ''}" onclick="fetchData(${i})">${i}</button>`;
                } else if (i === pagination.current_page - range - 1 || i === pagination.current_page + range + 1) {
                    html += `<span class="text-secondary" style="padding: 0 0.25rem;">...</span>`;
                }
            }

            html += `
                <button class="page-btn" onclick="fetchData(${pagination.current_page + 1})" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            container.innerHTML = html;
        }

        async function fetchAI() {
             const btn = document.getElementById('ai-trigger');
             const content = document.getElementById('ai-content');
             
             if(btn.disabled) return;
             
             btn.disabled = true;
             btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
             content.innerHTML = '<div class="text-secondary text-xs" style="text-align: center; padding: 2rem;">Synthesizing telemetry data...</div>';

             try {
                const res = await fetch('api.php?action=ai_summary');
                const data = await res.json();
                if(data.summary) {
                     content.innerHTML = `<p>${data.summary}</p>`;
                } else {
                     content.innerText = "Not enough data for AI analysis yet.";
                }
            } catch (e) { 
                console.error(e); 
                content.innerText = "Analysis failed. Please check API configuration.";
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic" style="margin-right: 4px;"></i> Analyze';
            }
        }

        function switchSidebar(tab, element) {
            document.querySelectorAll('.card .tabs .tab').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
            
            if (tab === 'threats') {
                document.getElementById('sidebar-threats').style.display = 'block';
                document.getElementById('sidebar-attackers').style.display = 'none';
            } else {
                document.getElementById('sidebar-threats').style.display = 'none';
                document.getElementById('sidebar-attackers').style.display = 'block';
            }
        }

        function switchFilter(filter, element) {
            currentFilter = filter;
            document.querySelectorAll('.table-container .tabs .tab').forEach(t => t.classList.remove('active')); 
            const tabsContainer = element.parentElement;
            tabsContainer.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
            fetchData(); 
        }

        function filterLogs() {
            const query = document.getElementById('log-search').value.toLowerCase();
            const filtered = allLogs.filter(log => {
                return log.ip.includes(query) || 
                       log.uri.toLowerCase().includes(query) || 
                       log.method.toLowerCase().includes(query);
            });
            renderLogs(filtered);
        }

        function renderLogs(logs) {
            const tbody = document.getElementById('logs-body');
            if (logs.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="6" class="text-secondary" style="text-align:center; padding: 2rem;">No logs found</td></tr>';
                 return;
            }
            tbody.innerHTML = logs.map((log, index) => {
                const country = geoCache[log.ip];
                if (!country) fetchGeo(log.ip);

                return `
                    <tr onclick='openLogModal(${index})' style="cursor: pointer;" title="Click for details">
                    <td class="text-secondary">${log.timestamp}</td>
                    <td>
                        <div class="ip-cell">
                            <span class="text-secondary" style="font-family: var(--font-code);">${log.ip}</span>
                            ${country ? `<span class="country-tag">${country}</span>` : ''}
                        </div>
                    </td>
                    <td>
                        <span class="method">${log.method}</span>
                        <span class="uri">${log.uri.substring(0, 60)}${log.uri.length > 60 ? '...' : ''}</span>
                    </td>
                    <td><span class="badge ${log.classification}">${log.classification.toUpperCase()}</span></td>
                    <td class="font-bold">${log.risk_score}</td>
                    <td class="text-secondary text-xs">${log.detection_tags}</td>
                </tr>
                `;
            }).join('');
        }

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function openLogModal(index) {
            const log = currentLogs[index];
            if (!log) return;

            const modal = document.getElementById('logModal');
            const content = document.getElementById('modal-content');
            
            let headers = {};
            try { headers = JSON.parse(log.headers); } catch(e) { headers = { error: "Invalid JSON" }; }
            
            let payload = log.payload;
            try { 
                const pObj = JSON.parse(log.payload); 
                payload = JSON.stringify(pObj, null, 2);
            } catch(e) {} 

            content.innerHTML = `
                <div class="kv-row"><div class="kv-key">Timestamp</div><div class="kv-value">${escapeHtml(log.timestamp)}</div></div>
                <div class="kv-row">
                    <div class="kv-key">IP Address</div>
                    <div class="kv-value">
                        ${escapeHtml(log.ip)} 
                        ${geoCache[log.ip] ? `<span class="country-tag" style="margin-left: 0.5rem;">${escapeHtml(geoCache[log.ip])}</span>` : ''}
                    </div>
                </div>
                <div class="kv-row"><div class="kv-key">Request</div><div class="kv-value"><span class="badge normal">${escapeHtml(log.method)}</span> ${escapeHtml(log.uri)}</div></div>
                <div class="kv-row"><div class="kv-key">Classification</div><div class="kv-value"><span class="badge ${escapeHtml(log.classification)}">${escapeHtml(log.classification.toUpperCase())}</span> (Risk: ${escapeHtml(log.risk_score)})</div></div>
                <div class="kv-row"><div class="kv-key">Tags</div><div class="kv-value">${escapeHtml(log.detection_tags || 'None')}</div></div>
                
                <div class="mb-2" style="margin-top: 1rem; color: var(--text-secondary); font-weight: 500;">Payload / Query Params</div>
                <div class="code-block">${escapeHtml(payload || "No Payload")}</div>

                <div class="mb-2" style="margin-top: 1rem; color: var(--text-secondary); font-weight: 500;">Request Headers</div>
                <div class="code-block">${escapeHtml(JSON.stringify(headers, null, 2))}</div>
            `;
            
            modal.style.display = 'flex';
        }

        function closeLogModal() {
            document.getElementById('logModal').style.display = 'none';
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

        setInterval(() => fetchData(currentPage), 5000); 
        fetchData();
    </script>
</body>
</html>
