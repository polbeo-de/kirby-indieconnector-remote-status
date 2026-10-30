<?php
declare(strict_types=1);
namespace Polbeo\KirbyIndieConnectorRemoteStatus\Console;

final class StatusClient
{
    public function get(array $source, int $year, int $month): array
    {
        $path = 'indieconnector-remote-status/v1/status'; $timestamp = (string) time(); $nonce = bin2hex(random_bytes(16));
        $signed = implode("\n", ['GET', $path, $timestamp, $nonce, hash('sha256', '')]);
        $signature = hash_hmac('sha256', $signed, $source['secret']);
        $url = rtrim($source['baseUrl'], '/') . '/' . $path . '?year=' . $year . '&month=' . $month;
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => ['Accept: application/json', 'X-Remote-Key: ' . $source['keyId'], 'X-Remote-Timestamp: ' . $timestamp, 'X-Remote-Nonce: ' . $nonce, 'X-Remote-Signature: ' . $signature]]);
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); curl_close($curl);
        $decoded = is_string($body) ? json_decode($body, true) : null;
        if ($status !== 200 || !is_array($decoded)) throw new \RuntimeException('Remote source unavailable');
        return $decoded;
    }
}
