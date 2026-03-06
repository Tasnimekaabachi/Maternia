<?php
/**
 * TEST TWILIO SMS — Maternia (version simple, sans dépendances)
 * Exécuter : php test_twilio_sms.php
 */

// ── 1. Lire .env et .env.local manuellement ──────────────────────────────────
function loadEnvFile(string $path): void {
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '###')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\"'");
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

loadEnvFile(__DIR__ . '/.env');
loadEnvFile(__DIR__ . '/.env.local'); // .env.local écrase .env (priorité Symfony)

// ── 2. Récupérer les variables ───────────────────────────────────────────────
$sid   = $_ENV['TWILIO_ACCOUNT_SID'] ?? '';
$token = $_ENV['TWILIO_AUTH_TOKEN']  ?? '';
$from  = $_ENV['TWILIO_FROM_NUMBER'] ?? '';

echo "\n=== TWILIO CONFIG CHECK ===\n";
echo "TWILIO_ACCOUNT_SID : " . (empty($sid)   ? "[MANQUANT]"  : substr($sid, 0, 6)   . "***" . substr($sid, -4)   . " OK")   . "\n";
echo "TWILIO_AUTH_TOKEN  : " . (empty($token) ? "[MANQUANT]"  : substr($token, 0, 4) . "***" . substr($token, -4) . " OK") . "\n";
echo "TWILIO_FROM_NUMBER : " . (empty($from)  ? "[MANQUANT]"  : "$from OK")   . "\n";

// Vérifier qu'on utilise bien le BON token (pas l'ancien)
if (!empty($token) && str_starts_with($token, '68f6ff')) {
    echo "\n[ATTENTION] L'ancien Auth Token incorrect est encore charge !\n";
    echo "Verifie que .env.local est bien sauvegarde.\n";
}

if (empty($sid) || empty($token) || empty($from)) {
    echo "\n[ERREUR] Variables manquantes — verifier .env.local\n";
    exit(1);
}

// ── 3. Numéro de destination ─────────────────────────────────────────────────
$to = '+21697076060'; // Ton numero verifie dans Twilio Trial

echo "\n=== ENVOI SMS DE TEST ===\n";
echo "De    : $from\n";
echo "Vers  : $to\n";
echo "Msg   : MATERNIA TEST: configuration Twilio OK\n\n";

// ── 4. Appel API Twilio via cURL ─────────────────────────────────────────────
$url  = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
$body = http_build_query([
    'To'   => $to,
    'From' => $from,
    'Body' => 'MATERNIA TEST: SMS OK. La configuration Twilio fonctionne correctement.',
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => "$sid:$token",
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// ── 5. Résultat ──────────────────────────────────────────────────────────────
echo "HTTP Code : $httpCode\n";

if ($curlErr) {
    echo "[ERREUR cURL] : $curlErr\n";
    exit(1);
}

$data = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300) {
    echo "[SUCCESS] SMS envoye avec succes !\n";
    echo "   SID message : " . ($data['sid']    ?? 'N/A') . "\n";
    echo "   Statut      : " . ($data['status'] ?? 'N/A') . "\n";
    echo "\n=> Verifie ton telephone $to !\n";
} else {
    $code    = $data['code']    ?? '?';
    $message = $data['message'] ?? $response;
    echo "[ECHEC] Twilio HTTP $httpCode\n";
    echo "   Code erreur : $code\n";
    echo "   Message     : $message\n";
    echo "\nAIDE :\n";
    echo "  Code 21608 => numero '$to' non verifie dans Twilio Trial Dashboard\n";
    echo "             => https://console.twilio.com/us1/develop/phone-numbers/manage/verified\n";
    echo "  Code 20003 => SID ou Token incorrect\n";
    echo "  Code 21211 => format numero invalide (doit etre +21697076060)\n";
}

echo "\n";
