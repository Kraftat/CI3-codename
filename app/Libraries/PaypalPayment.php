<?php

namespace App\Libraries;

use Config\Services;

class PaypalPayment
{
    private $clientId;
    private $clientSecret;
    private $sandbox;
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = Services::curlrequest();
    }

    public function initialize(string $clientId, string $clientSecret, bool $sandbox = true): void
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->sandbox = $sandbox;
    }

    private function getApiUrl(): string
    {
        return $this->sandbox ? "https://api-m.sandbox.paypal.com" : "https://api.paypal.com";
    }

    private function getAccessToken()
    {
        $url = $this->getApiUrl() . "/v1/oauth2/token";
        $headers = [
            "Accept: application/json",
            "Accept-Language: en_US",
        ];
        $data = [
            'grant_type' => 'client_credentials'
        ];

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $data
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['access_token'])) {
            return $result['access_token'];
        }

        log_message('error', 'PayPal Access Token Error: ' . $response->getBody());
        return false;
    }

    public function payment(array $data): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $url = $this->getApiUrl() . "/v1/payments/payment";

        $paymentData = [
            "intent" => "sale",
            "payer" => [
                "payment_method" => "paypal"
            ],
            "transactions" => [
                [
                    "amount" => [
                        "total" => $data['amount'],
                        "currency" => $data['currency']
                    ],
                    "description" => $data['description']
                ]
            ],
            "redirect_urls" => [
                "return_url" => $data['returnUrl'],
                "cancel_url" => $data['cancelUrl']
            ]
        ];

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken,
        ];

        $response = $this->httpClient->request('POST', $url, [
            'headers' => $headers,
            'json' => $paymentData
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['links'])) {
            foreach ($result['links'] as $link) {
                if ($link['rel'] == 'approval_url') {
                    return ['status' => 'redirect', 'url' => $link['href']];
                }
            }
        } else {
            log_message('error', 'PayPal Payment Error: ' . $response->getBody());
        }

        return null;
    }

    public function success(string $paymentId, string $payerId): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $url = $this->getApiUrl() . sprintf('/v1/payments/payment/%s/execute', $paymentId);

        $data = [
            "payer_id" => $payerId,
        ];

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken,
        ];

        $response = $this->httpClient->request('POST', $url, [
            'headers' => $headers,
            'json' => $data
        ]);

        return json_decode($response->getBody(), true);
    }
}
