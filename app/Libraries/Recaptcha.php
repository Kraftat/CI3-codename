<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;

class Recaptcha
{
    private string $siteKey;
    private string $secretKey;
    private string $language;

    const SIGN_UP_URL = 'https://www.google.com/recaptcha/admin';
    const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const API_URL = 'https://www.google.com/recaptcha/api.js';

    public function __construct(array $api_keys = [])
    {
        if (!empty($api_keys)) {
            $this->siteKey = $api_keys['site_key'];
            $this->secretKey = $api_keys['secret_key'];
        }

        $this->language = 'en';

        if (empty($this->siteKey) || empty($this->secretKey)) {
            die("To use reCAPTCHA you must get an API key from <a href='" . self::SIGN_UP_URL . "'>" . self::SIGN_UP_URL . "</a>");
        }
    }

    private function submitHTTPGet(array $data): string|false
    {
        $url = self::SITE_VERIFY_URL . '?' . http_build_query($data);

        if (ini_get('allow_url_fopen')) {
            return file_get_contents($url);
        } else {
            $curl = Services::curlrequest();
            return $curl->get($url)->getBody();
        }
    }

    public function verifyResponse($response, $remoteIp = null): array
    {
        $remoteIp = $remoteIp ?? Services::request()->getIPAddress();

        if (empty($response)) {
            return [
                'success' => false,
                'error-codes' => 'missing-input',
            ];
        }

        $getResponse = $this->submitHTTPGet([
            'secret' => $this->secretKey,
            'remoteip' => $remoteIp,
            'response' => $response,
        ]);

        $responses = json_decode($getResponse, true);

        $status = $responses['success'] ?? false;
        $error = $responses['error-codes'] ?? 'invalid-input-response';

        return [
            'success' => $status,
            'error-codes' => $status ? null : $error,
        ];
    }

    public function getScriptTag(array $parameters = []): string
    {
        $default = [
            'render' => 'onload',
            'hl' => $this->language,
        ];

        $result = array_merge($default, $parameters);

        return sprintf('<script type="text/javascript" src="%s?%s" async defer></script>',
            self::API_URL, http_build_query($result));
    }

    public function getWidget(array $parameters = []): string
    {
        $default = [
            'data-sitekey' => $this->siteKey,
            'data-theme' => 'light',
            'data-type' => 'image',
            'data-size' => 'normal',
        ];

        $result = array_merge($default, $parameters);

        $html = '';
        foreach ($result as $key => $value) {
            $html .= sprintf('%s="%s" ', $key, $value);
        }

        return '<div class="g-recaptcha" ' . $html . '></div>';
    }
}
