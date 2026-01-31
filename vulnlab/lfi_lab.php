<?php include 'header.php'; ?>

<h1>Local File Inclusion (LFI) Lab</h1>
<p style="color: var(--text-muted);">This page simulate a dynamic file loader. It includes local files based on a parameter, which can be manipulated to read sensitive system files.</p>

<div class="result-box" style="margin-bottom: 2rem;">
    <div style="font-weight: 600; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Quick Links</div>
    <div class="nav-links">
        <a href="?page=intro.txt" style="text-decoration: underline;">intro.txt</a>
        <a href="?page=about.txt" style="text-decoration: underline;">about.txt</a>
    </div>
</div>

<?php
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    echo "<h3 style='font-size: 1.1rem; margin-bottom: 1rem;'>File Content: <code>" . htmlspecialchars($page) . "</code></h3>";
    
    echo "<div style='background: #fff; border: 1px solid var(--border); padding: 1.5rem; border-radius: 8px;'>";
    
    if (strpos($page, '../') !== false || stripos($page, '/etc/passwd') !== false) {
        echo "<div class='text-xs text-muted uppercase tracking-widest mb-2' style='font-weight: 700; color: var(--danger);'>Warning: Local File Inclusion Detected</div>";
        echo "<pre style='margin-top: 0;'>root:x:0:0:root:/root:/bin/bash\ndaemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin\nbin:x:2:2:bin:/bin:/usr/sbin/nologin\nsys:x:3:3:sys:/dev:/usr/sbin/nologin\nsync:x:4:65534:sync:/bin:/bin/sync\ngames:x:5:60:games:/usr/games:/usr/sbin/nologin</pre>";
    } elseif ($page == 'intro.txt') {
        echo "<div style='color: var(--text-main);'>Welcome to the SWIFT integration laboratory. This environment is designed for payload testing.</div>";
    } elseif ($page == 'about.txt') {
        echo "<div style='color: var(--text-main);'>SWIFT is a lightweight Security Intelligence Framework for PHP applications.</div>";
    } else {
        echo "<div style='color: var(--text-muted);'>File inclusion error: 404 Not Found.</div>";
    }
    
    echo "</div>";
}
?>

</body>
</html>
