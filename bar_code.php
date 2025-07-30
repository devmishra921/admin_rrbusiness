<?php
/**
 * scan_barcode.php  — Parse GS1‑128 / DataMatrix scan string
 * ---------------------------------------------------------
 * ‣ Accepts the raw scan string posted from an <input name="scan"> field.
 * ‣ Extracts GS1 Application Identifiers:
 *      01  GTIN‑14     (mandatory)
 *      13  Packaging date (YYMMDD)
 *      17  Expiry date    (YYMMDD)
 *      10  Batch / Lot    (variable‑length, up to Group‑Separator)
 *      3923  Price (₹, 2 decimal implied)
 * ‣ Saves the parsed data into `inventory_scans` table (replace/modify as required).
 * ‣ Shows the captured values back to the user for confirmation.
 *
 *  ▸  Required:
 *      • db_connect.php  – should create a $pdo (PHP PDO object) for MySQL.
 *      • A scanner configured to send the FNC1 (GS) character as ASCII 29 (\x1D) and append an <ENTER>.
 *
 *  Table suggestion:
 *      CREATE TABLE inventory_scans (
 *          id INT AUTO_INCREMENT PRIMARY KEY,
 *          gtin BIGINT UNSIGNED NOT NULL,
 *          pack_date DATE NULL,
 *          exp_date DATE NULL,
 *          batch_no VARCHAR(40) NULL,
 *          mrp_rupees DECIMAL(10,2) NULL,
 *          scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 *      );
 */

session_start();
// --- 1. (Optional) Protect the page behind an admin login
// if (!isset($_SESSION['admin_id'])) { header('Location: login.html'); exit; }

require_once 'db_connect.php';  // ensure this defines $pdo (PDO connection)

$parsed = null; // will hold the parsed array if POSTed
$error  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = trim($_POST['scan'] ?? '');

    // 1. Validate: minimum must contain AI 01 (GTIN)
    if ($raw === '' || !preg_match('/01\d{14}/', $raw)) {
        $error = 'Invalid / empty scan string. Please scan again.';
    } else {
        $parsed = parse_gs1_string($raw);

        // 2. Persist to DB (optional – comment out if not needed)
        if ($parsed) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO inventory_scans (gtin, pack_date, exp_date, batch_no, mrp_rupees) VALUES (?,?,?,?,?)'
                );
                $stmt->execute([
                    $parsed['gtin'] ?? null,
                    $parsed['pack_date'] ?? null,
                    $parsed['exp_date'] ?? null,
                    $parsed['batch'] ?? null,
                    $parsed['price'] ?? null,
                ]);
            } catch (PDOException $e) {
                $error = 'DB Insert error: ' . $e->getMessage();
            }
        } else {
            $error = 'Could not parse GS1 data – check barcode content.';
        }
    }
}

/**
 * Parse GS1‑128 / GS1 DataMatrix string into an associative array.
 * Returns null on failure.
 */
function parse_gs1_string(string $raw): ?array
{
    $out = [];

    if (preg_match('/01(\d{14})/', $raw, $m)) {
        $out['gtin'] = $m[1];
    }
    if (preg_match('/13(\d{6})/', $raw, $m)) {
        $out['pack_date'] = yymmdd_to_iso($m[1]);
    }
    if (preg_match('/17(\d{6})/', $raw, $m)) {
        $out['exp_date'] = yymmdd_to_iso($m[1]);
    }
    if (preg_match('/10([^\x1d]+)/', $raw, $m)) { // until the first Group‑Separator (FNC1)
        $out['batch'] = $m[1];
    }
    if (preg_match('/3923(\d{6})/', $raw, $m)) {
        $out['price'] = intval($m[1]) / 100; // e.g. "000150" ➝ 150.00
    }

    return $out ?: null;
}

/** Convert YYMMDD ➝ YYYY-MM-DD */
function yymmdd_to_iso(string $yy): string
{
    $y = substr($yy, 0, 2);
    $m = substr($yy, 2, 2);
    $d = substr($yy, 4, 2);
    // assume 2000‑2099 range; adjust if needed
    $year = intval($y) + 2000;
    return sprintf('%04d-%02d-%02d', $year, $m, $d);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scan GS1 Barcode | R.R. Business</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        input#scan { font-size: 1.5rem; padding: .5rem 1rem; width: 100%; }
        table { border-collapse: collapse; margin-top: 1rem; }
        td, th { border: 1px solid #ccc; padding: .4rem .8rem; }
        .err { color: #b00020; margin-top: 1rem; }
        .ok  { color: #006400; margin-top: 1rem; }
    </style>
    <script>
        // Auto‑focus scan box on every page load
        window.addEventListener('DOMContentLoaded', () => {
            const scanBox = document.getElementById('scan');
            if (scanBox) {
                scanBox.focus();
                // clear after successful scan display
                <?php if ($parsed): ?> scanBox.value = ""; <?php endif; ?>
            }
        });
    </script>
</head>
<body>
<h2>Scan GS1 Barcode</h2>
<form method="post" autocomplete="off">
    <input type="text" name="scan" id="scan" placeholder="Place cursor and scan barcode…" required>
</form>

<?php if ($error): ?>
    <div class="err">⚠️ <?= htmlspecialchars($error) ?></div>
<?php elseif ($parsed): ?>
    <div class="ok">✅ Scan captured & stored!</div>
    <table>
        <tr><th>GTIN‑14</th><td><?= htmlspecialchars($parsed['gtin'] ?? '-') ?></td></tr>
        <tr><th>Pack Date</th><td><?= htmlspecialchars($parsed['pack_date'] ?? '-') ?></td></tr>
        <tr><th>Expiry Date</th><td><?= htmlspecialchars($parsed['exp_date'] ?? '-') ?></td></tr>
        <tr><th>Batch No.</th><td><?= htmlspecialchars($parsed['batch'] ?? '-') ?></td></tr>
        <tr><th>MRP (₹)</th><td><?= htmlspecialchars(number_format($parsed['price'] ?? 0, 2)) ?></td></tr>
    </table>
<?php endif; ?>

<p><em>Tip:</em> Ensure your scanner sends an <strong>ENTER</strong> / carriage‑return at the end of the data string, so the form auto‑submits.</p>
</body>
</html>
