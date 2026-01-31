<?php include 'header.php'; ?>

<h1>Welcome to VulnLab</h1>
<div class="warning">
    <strong><i class="fas fa-exclamation-triangle"></i> SAFE ENVIRONMENT:</strong> This application contains intentionally vulnerable simulations. It is designed to demonstrate the detection and classification capabilities of the <strong>SWIFT</strong> security agent in a controlled manner.
</div>

<p style="color: var(--text-muted); margin-bottom: 2rem;">Select a vulnerability laboratory from the menu above to simulate and analyze common web attacks.</p>

<h3 style="font-weight: 600; font-size: 1.1rem; margin-bottom: 1rem;">Simulation Workflow</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
        <div style="color: var(--accent); font-weight: 700; margin-bottom: 0.5rem;">01. SIMULATE</div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Submit a malicious payload like SQLi, XSS, or Command Injection.</p>
    </div>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
        <div style="color: var(--accent); font-weight: 700; margin-bottom: 0.5rem;">02. INTERCEPT</div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">The SWIFT agent intercept and parses the request in real-time.</p>
    </div>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
        <div style="color: var(--accent); font-weight: 700; margin-bottom: 0.5rem;">03. ANALYZE</div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">View the classification and risk score on the <a href="../swift/dashboard/index.php" target="_blank" style="color: var(--accent); font-weight: 700;">SWIFT Dashboard</a>.</p>
    </div>
</div>

<div class="footer-note">
    &copy; <?= date('Y') ?> SWIFT Security Framework • Authorized Access Only
</div>

</body>
</html>
