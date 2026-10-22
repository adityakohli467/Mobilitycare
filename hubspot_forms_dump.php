<?php
/**
 * HubSpot Forms diagnostic dumper.
 *
 * Fetches all forms (fields, required flags, validation rules) via the
 * HubSpot Forms API v3 and writes them to hubspot_forms.json so they can
 * be reviewed.
 *
 * USAGE (PowerShell):
 *   $env:HUBSPOT_TOKEN = "your-private-app-token"
 *   php hubspot_forms_dump.php
 *
 * The Private App must have the "forms" scope.
 * NOTE: hubspot_forms.json may contain form config only (no submissions),
 * but treat it as sensitive and do not commit it.
 */

$token = getenv('HUBSPOT_TOKEN');
if (!$token) {
    fwrite(STDERR, "ERROR: Set the HUBSPOT_TOKEN environment variable first.\n");
    fwrite(STDERR, "  PowerShell:  \$env:HUBSPOT_TOKEN = \"your-token\"; php hubspot_forms_dump.php\n");
    exit(1);
}

$baseUrl = 'https://api.hubapi.com/marketing/v3/forms/';
$after   = null;
$forms   = [];

do {
    $url = $baseUrl . '?limit=50' . ($after ? '&after=' . urlencode($after) : '');

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        fwrite(STDERR, "cURL error: {$err}\n");
        exit(1);
    }

    if ($status !== 200) {
        fwrite(STDERR, "HubSpot API returned HTTP {$status}:\n{$response}\n");
        exit(1);
    }

    $data = json_decode($response, true);
    if (!empty($data['results'])) {
        $forms = array_merge($forms, $data['results']);
    }

    $after = $data['paging']['next']['after'] ?? null;
} while ($after);

$outFile = __DIR__ . '/hubspot_forms.json';
file_put_contents($outFile, json_encode($forms, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Fetched " . count($forms) . " form(s).\n";
echo "Written to: {$outFile}\n\n";

// Quick human-readable summary.
foreach ($forms as $form) {
    $name = $form['name'] ?? '(unnamed)';
    $id   = $form['id'] ?? '?';
    echo "- {$name}  [id: {$id}]\n";
}
