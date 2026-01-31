<?php include 'header.php'; ?>

<h1>Reflected XSS Lab</h1>
<p style="color: var(--text-muted);">This page demonstrate a search or greeting feature. It echoes back user input without sanitization, allowing for Cross-Site Scripting.</p>

<div class="result-box">
    <form method="GET">
        <div class="form-group">
            <label>Input Parameter</label>
            <input type="text" name="name" placeholder="e.g., <script>alert(1)</script>" value="">
        </div>
        <button type="submit"><i class="fas fa-paper-plane"></i> Submit Payload</button>
    </form>
</div>

<?php
if (isset($_GET['name'])) {
    $name = $_GET['name'];
    echo "<h3 style='margin-top: 2rem; font-size: 1.1rem;'>Server Output</h3>";
    
    echo "<div style='background: #fff; padding: 2rem; border: 1px solid var(--border); border-radius: 8px; text-align: center;'>";
    echo "<div style='font-size: 1.25rem; font-weight: 500;'>Hello, <span style='color: var(--accent);'>" . $name . "</span>!</div>"; 
    echo "</div>";
    
    echo "<div class='footer-note'>If you see a popup or the page behavior changes, the XSS payload was successfully executed.</div>";
}
?>

</body>
</html>
