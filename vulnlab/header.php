<?php
require_once __DIR__ . '/../agent.php';

session_start();
if (!isset($_SESSION['swift_auth'])) {
    header("Location: ../swift/dashboard/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIFT | VulnLab</title>
    <style>
        :root {
            --bg: #f3f4f6;
            --surface: #ffffff;
            --border: #e5e7eb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --accent: #1f2937;
            --danger: #ef4444;
        }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: var(--bg); 
            margin: 0; 
            padding: 0; 
            color: var(--text-main);
            line-height: 1.5;
        }
        .navbar { 
            background: var(--surface); 
            padding: 0.75rem 2rem; 
            color: var(--text-main); 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar .brand { font-weight: 700; font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
        .navbar .brand img { height: 32px; }
        .nav-links { display: flex; gap: 1.5rem; }
        .navbar a { 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 500;
            transition: color 0.2s;
        }
        .navbar a:hover, .navbar a.active { color: var(--accent); }
        
        .container { 
            max-width: 900px; 
            margin: 3rem auto; 
            background: var(--surface); 
            padding: 2.5rem; 
            border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
            border: 1px solid var(--border); 
        }
        h1 { color: var(--text-main); font-size: 1.75rem; font-weight: 700; margin-top: 0; margin-bottom: 1.5rem; }
        h1::after { content: ''; display: block; width: 60px; height: 3px; background: var(--accent); margin-top: 8px; border-radius: 2px; }
        
        .warning { 
            background: #fffbeb; 
            border-left: 4px solid #f59e0b; 
            padding: 1.25rem; 
            margin-bottom: 2rem; 
            color: #92400e; 
            font-size: 0.9rem;
            border-radius: 0 4px 4px 0;
        }
        
        code { background: #f3f4f6; padding: 0.2rem 0.4rem; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #1f2937; }
        
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.825rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem; }
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 0.75rem 1rem; 
            border: 1px solid var(--border); 
            border-radius: 6px; 
            font-size: 1rem;
            background: #fff;
            transition: all 0.2s;
        }
        input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1); }
        
        button { 
            background-color: var(--accent); 
            color: white; 
            border: none; 
            padding: 0.75rem 1.5rem; 
            cursor: pointer; 
            border-radius: 6px; 
            font-weight: 600; 
            font-size: 0.9rem;
            transition: opacity 0.2s;
        }
        button:hover { opacity: 0.9; }
        
        pre { 
            background: #111827; 
            color: #10b981; 
            padding: 1.5rem; 
            border-radius: 8px; 
            overflow-x: auto; 
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            margin-top: 1.5rem;
            border: 1px solid #374151;
        }
        
        .result-box {
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid var(--border);
        }
        .footer-note { text-align: center; margin-top: 4rem; color: var(--text-muted); font-size: 0.8rem; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">
        <div class="brand">
            <img src="../swift/dashboard/assets/swift.png" alt="SWIFT" style="filter: invert(1);">
            <span><i>VulnLab</i></span>
        </div>
        <div class="nav-links">
            <a href="/vulnlab" class="<?= basename($_SERVER['PHP_SELF']) == '/vulnlab' ? 'active' : '' ?>">Overview</a>
            <a href="sql_lab" class="<?= basename($_SERVER['PHP_SELF']) == 'sql_lab' ? 'active' : '' ?>">SQLi</a>
            <a href="xss_lab" class="<?= basename($_SERVER['PHP_SELF']) == 'xss_lab' ? 'active' : '' ?>">XSS</a>
            <a href="lfi_lab" class="<?= basename($_SERVER['PHP_SELF']) == 'lfi_lab' ? 'active' : '' ?>">LFI</a>
            <a href="rce_lab" class="<?= basename($_SERVER['PHP_SELF']) == 'rce_lab' ? 'active' : '' ?>">RCE</a>
        </div>
    </div>
    <div class="container">
