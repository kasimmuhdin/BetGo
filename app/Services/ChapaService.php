<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChapaService
{
    protected string $baseUrl;
    protected string $secretKey;


    public function  __construct()
    {
        $this->baseUrl = rtrim(config('chapa.base_url', '/'));

        $this->secretKey = config('chapa.secret_key');
    }
    public function initialize(array $data): array
    {
        $response = Http::timeout(15)->withToken($this->secretKey)->acceptJson()
            ->post("{$this->baseUrl}/v1/transaction/initialize", $data);
        return $response->json() ?? [];
    }

    public function verify(string $txRef): array
    {
        $response = Http::timeout(15)->withToken($this->secretKey)->acceptJson()
            ->get("{$this->baseUrl}/v1/transaction/verify/{$txRef}");

        return $response->json() ?? [];
    }
    public function isValidWebhookSignature(string $rawPayload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }
        $expected = hash_hmac('sha256', $rawPayload, $this->secretKey);
        return hash_equals($expected, $signature);
    }
}
