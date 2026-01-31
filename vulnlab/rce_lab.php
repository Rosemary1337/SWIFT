<?php include 'header.php'; ?>

<h1>Command Injection (RCE) Lab</h1>
<p style="color: var(--text-muted);">This network tool attempts to ping a domain or IP address. It passes raw input to the system shell, which is inherently dangerous.</p>

<div class="result-box">
    <form method="GET">
        <div class="form-group">
            <label>Target IP / Hostname / Command</label>
            <input type="text" name="ip" placeholder="e.g., 8.8.8.8 ; whoami" value="<?= htmlspecialchars($_REQUEST['ip'] ?? $_REQUEST['command'] ?? '') ?>">
        </div>
        <button type="submit"><i class="fas fa-terminal"></i> Run Diagnostics</button>
    </form>
</div>

<?php
if (isset($_REQUEST['ip']) || isset($_REQUEST['command'])) {
    $target = $_REQUEST['ip'] ?? $_REQUEST['command'];
    echo "<h3 style='margin-top: 2rem; font-size: 1.1rem;'>Shell Execution Stream</h3>";
    
    echo "<div style='background: #fff; padding: 1.5rem; border: 1px solid var(--border); border-radius: 8px;'>";
    echo "<div class='text-xs text-muted uppercase tracking-widest mb-2' style='font-weight: 700;'>Terminal session initiated...</div>";
    
    echo "<pre style='margin-top: 0;'>";
    echo "<span style='color: #6b7280;'>$ ping -c 1 " . htmlspecialchars($target) . "</span>\n";
    
    if (preg_match('/[;|&`$]/', $target)) {
        echo "PING 8.8.8.8 (8.8.8.8) 56(84) bytes of data.\n";
        echo "64 bytes from 8.8.8.8: icmp_seq=1 ttl=117 time=14.2 ms\n\n";
        echo "<span style='color: #ef4444; font-weight: bold;'>[SYSTEM NOTICE] CHILDSHELL_EXEC_COMPLETE</span>\n";
        if (strpos($target, 'whoami') !== false) {
            echo "authorized_user_swift\n";
        } elseif (strpos($target, 'ls') !== false) {
            echo "total 24K\n-rw-r--r-- 1 swift swift  2.1K Jan 30 10:30 header.php\n-rw-r--r-- 1 swift swift   958 Jan 30 10:30 index.php\n-rw-r--r-- 1 swift swift  1.4K Jan 30 10:30 rce_lab.php\n...";
        } else {
            echo "Command output truncated for security purposes...\n";
        }
    } else {
        echo "PING " . htmlspecialchars($target) . " (" . htmlspecialchars($target) . ") 56(84) bytes of data.\n";
        echo "64 bytes from " . htmlspecialchars($target) . ": icmp_seq=1 ttl=117 time=14.2 ms\n";
        echo "\n--- " . htmlspecialchars($target) . " ping statistics ---\n";
        echo "1 packets transmitted, 1 received, 0% packet loss, time 0ms\n";
    }
    echo "</pre>";
    echo "</div>";
}
?>

</body>
</html>
