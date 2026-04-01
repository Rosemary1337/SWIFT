<?php 
require_once __DIR__ . '/../swift/boot.php'; 

// Handle Upload BEFORE any output
$inspectionResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $agent = new \Swift\Core\SecurityAgent();
        $result = $agent->inspectUpload($_FILES['file']);
        
        if ($result['is_malicious']) {
            $logId = $agent->quarantine($_FILES['file'], $result);
            if ($logId) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $token = bin2hex(random_bytes(16));
                $_SESSION['swift_blocked_id'] = $logId;
                $_SESSION['swift_blocked_token'] = $token;

                // Redirect to 403 immediately with token
                header("Location: /swift2/swift/dashboard/403.php?token=" . $token);
                exit;
            } else {
                $inspectionResult = [
                    'success' => false,
                    'error' => 'Threat detected, but failed to quarantine the file. Check storage permissions.'
                ];
            }
        } else {
            $inspectionResult = [
                'success' => true,
                'filename' => $_FILES['file']['name']
            ];
        }
    } else {
        $inspectionResult = [
            'success' => false,
            'error' => 'No file selected or upload error occurred.'
        ];
    }
}

include 'header.php'; 
?>

<h1>File Upload Lab</h1>
<p style="color: var(--text-muted);">This laboratory allows you to test file uploads against the SWIFT security agent. Malicious files will be automatically quarantined and logged.</p>

<div class="result-box">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Select File to Upload</label>
            <input type="file" name="file">
        </div>
        <button type="submit"><i class="fas fa-upload"></i> Upload File</button>
    </form>
</div>

<?php if ($inspectionResult): ?>
    <h3 style="margin-top: 2rem; font-size: 1.1rem;">Agent Inspection Result</h3>
    <div style="background: #fff; padding: 1.5rem; border: 1px solid var(--border); border-radius: 8px;">
        <div class="text-xs text-muted uppercase tracking-widest mb-2" style="font-weight: 700;">SWIFT Security Scan</div>
        <div style="font-family: var(--font-code); font-size: 0.9rem;">
            <?php if ($inspectionResult['success']): ?>
                <span style="color: #10b981;"><i class="fas fa-check-circle"></i> File passed security scan.</span><br>
                <p class="text-muted" style="font-size: 0.85rem;">The file <code><?= htmlspecialchars($inspectionResult['filename']) ?></code> is clean and could be processed safely.</p>
            <?php else: ?>
                <span style="color: var(--danger);"><?= htmlspecialchars($inspectionResult['error']) ?></span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
