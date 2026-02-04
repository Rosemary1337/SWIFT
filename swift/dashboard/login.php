<?php
require_once __DIR__ . '/../boot.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === SWIFT_CONFIG['security']['dashboard_password']) {
        $_SESSION['swift_auth'] = true;
        header("Location: index");
        exit;
    } else {
        $error = "Access Denied: Invalid Credentials";
    }
}

if (isset($_SESSION['swift_auth'])) {
    header("Location: index");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIFT Intelligence | Access Control</title>
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

        .login-container {
            background: var(--surface);
            padding: 1.5rem;
            width: 100%;
            max-width: 400px;
            border-radius: 4px;
            border: 1px solid var(--border);
            border-top: 4px solid var(--accent);
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
            margin-top: 0.25rem;
            margin-bottom: 2rem;
            font-family: 'Google Sans Code', monospace;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .input-group {
            position: relative;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-family: 'Google Sans Code', monospace;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input { 
            width: 100%; 
            padding: 0.875rem; 
            background: #000; 
            border: 1px solid var(--border); 
            color: var(--text-primary); 
            border-radius: 4px; 
            font-family: 'Google Sans', sans-serif;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent);
        }

        button { 
            width: 100%; 
            padding: 0.875rem; 
            background: var(--accent); 
            color: #000; 
            border: none; 
            border-radius: 4px; 
            font-weight: 700; 
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer; 
            transition: opacity 0.2s;
            margin-top: 0.5rem;
        }

        button:hover { 
            opacity: 0.9; 
        }

        .error-msg {
            background: var(--danger);
            border: none;
            color: var(--bg);
            padding: 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

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
    <div class="login-container">
        <h1 class="title">Security Intelligence</h1>
        <div class="subtitle">Enter your Access Key</div>

        <?php if (isset($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="input-group">
                <input type="password" name="password" placeholder="••••••••••••" autofocus required>
            </div>
            <button type="submit">Authenticate</button>
        </form>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Security Web Intelligence Framework & Tracker
    </footer>

</body>
</html>
