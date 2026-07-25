<?php

declare(strict_types=1);

// Run after importing database/schema.sql and database/seed.sql, then starting:
// C:\xampp\php\php.exe -S 127.0.0.1:8080 -t backend/public

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8080';

function request(string $method, string $url, ?array $body = null, ?string $token = null): array
{
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body === null ? null : json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if ($raw === false) {
        throw new RuntimeException(curl_error($ch));
    }
    $json = json_decode($raw, true);
    if ($status >= 400 || !($json['success'] ?? false)) {
        throw new RuntimeException("$method $url failed: $raw");
    }
    return $json['data'] ?? [];
}

$login = request('POST', "$baseUrl/auth/login", [
    'email' => 'admin@lamshaml.com',
    'password' => 'Admin@123',
]);
$token = $login['token'];
request('GET', "$baseUrl/auth/me", null, $token);
request('GET', "$baseUrl/admin/dashboard", null, $token);
request('GET', "$baseUrl/search?name=" . urlencode('احمد عبدالرحمن سالم'));

echo "API smoke test passed\n";
