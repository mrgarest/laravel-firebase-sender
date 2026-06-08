<?php

namespace Garest\FirebaseSender\Support;

use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Garest\FirebaseSender\DTO\GoogleAccessToken;
use Garest\FirebaseSender\DTO\ServiceAccountData;
use Illuminate\Support\Facades\Cache;

class GoogleApi
{
    /**
     * Handles sending bulk messages via HTTP.
     *
     * @param string $projectId
     * @param string $accessToken
     * @param array $messages
     * 
     * @return array
     */
    public function poolMessage(string $projectId, string $accessToken, array $messages): array
    {
        try {
            return Http::pool(function (Pool $pool) use ($projectId, $accessToken, $messages) {
                $requests = [];
                foreach ($messages as $index => $message) {
                    $requests["msg_{$index}"] = $pool->as("msg_{$index}")
                        ->withToken($accessToken)
                        ->withHeaders(['Content-Type' => 'application/json; UTF-8'])
                        ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                            'message' => $message
                        ]);
                }
                return $requests;
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Receives authorization tokens.
     *
     * @param ServiceAccountData $account
     * @return GoogleAccessToken|null
     */
    public function getAccessToken(ServiceAccountData $account): ?GoogleAccessToken
    {
        $cashEnabled = (bool) config('firebase-sender.cache.google_access_token');

        $cacheKey = 'fcm_auth_token_' . md5($account->projectId);
        if (!$cashEnabled) {
            $data = Cache::get($cacheKey, null);
            if (!$data) return GoogleAccessToken::fromArray($data);
        }

        $now = Carbon::now()->timestamp;
        $tokenPayload = [
            'iss' => $account->clientEmail,
            'sub' => $account->clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode($tokenPayload));

        $signatureInput = "$header.$payload";
        $privateKey = openssl_pkey_get_private($account->privateKey);
        openssl_sign($signatureInput, $signature, $privateKey, "SHA256");
        $signature = $this->base64UrlEncode($signature);

        $jwt = "$signatureInput.$signature";

        $response = Http::asForm()->post("https://oauth2.googleapis.com/token", [
            "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion" => $jwt
        ]);

        if ($response->failed()) return null;

        $data = $response->json();

        $data = new GoogleAccessToken(
            accessToken: $data['access_token'],
            expiresAt: Carbon::now()->addSeconds($data['expires_in']),
            tokenType: $data['token_type']
        );

        if (!$cashEnabled) {
            Cache::put($cacheKey, $data->toArray(), $data->expiresAt->copy()->subSeconds(60));
        }

        return $data;
    }

    protected function base64UrlEncode(string $data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
