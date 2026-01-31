<?php include 'header.php'; ?>

<h1>SQL Injection Lab</h1>
<p style="color: var(--text-muted);">This form simulate a user search feature. It is intentionally vulnerable to SQL Injection to test detection rules.</p>

<div class="result-box">
    <form method="GET">
        <div class="form-group">
            <label>Query User ID</label>
            <input type="text" name="id" placeholder="e.g., 1 OR 1=1" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
        </div>
        <button type="submit"><i class="fas fa-search"></i> Execute Query</button>
    </form>
</div>

<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    echo "<h3 style='margin-top: 2rem; font-size: 1.1rem;'>Database Response</h3>";
    
    echo "<div style='background: #fff; padding: 1.5rem; border: 1px solid var(--border); border-radius: 8px;'>";
    echo "<div class='text-xs text-muted uppercase tracking-widest mb-2' style='font-weight: 700;'>Raw Query Execution</div>";
    echo "<code>SELECT * FROM users WHERE id = " . $id . "</code>";
    
    echo "<div class='text-xs text-muted uppercase tracking-widest mt-4 mb-2' style='font-weight: 700;'>Output Stream</div>";
    echo "<div style='font-family: var(--font-code); font-size: 0.9rem;'>";
    if (preg_match('/^\d+$/', $id)) {
        echo "<span style='color: #10b981;'><i class='fas fa-check-circle'></i> Record found for User #" . htmlspecialchars($id) . "</span>";
    } elseif (stripos($id, 'union') !== false || stripos($id, 'or') !== false || stripos($id, 'select') !== false) {
        echo "<span style='color: var(--danger); font-weight: 700;'><i class='fas fa-bug'></i> Exception: [PDO_MYSQL_SIM] User successfully bypassed authentication logic.</span><br>";
        echo "<div style='margin-top: 10px; background: #fee2e2; padding: 10px; border-radius: 4px; border: 1px solid #fecaca;'>";
        echo "<strong>Dumped Data:</strong> admin:$2y$10$..., rosemary:toor, guest:guest123";
        echo "</div>";
    } else {
        echo "<span class='text-muted'>Query returned 0 results.</span>";
    }
    echo "</div>";
    echo "</div>";
}
?>

</body>
</html>
