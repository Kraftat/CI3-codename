<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;

class Bulk
{
    protected $username;
    protected $password;
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
        $branchID = service('request')->getPost('branch_id') ?: session('branch_id');

        $bulksms = $this->db->table('sms_credential')
            ->where(['sms_api_id' => 4, 'branch_id' => $branchID])
            ->get()
            ->getRowArray();
        $this->username = $bulksms['field_one'] ?? '';
        $this->password = $bulksms['field_two'] ?? '';
    }

    public function send(string $to, string $message): bool
    {
        $username = $this->username;
        $password = $this->password;
        $messages = [
            ['to' => $to, 'body' => $message],
        ];

        $result = $this->sendMessage(json_encode($messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true', $username, $password);

        return $result['http_status'] == 201;
    }

    protected function sendMessage(string $postBody, string $url, string $username, string $password): array
    {
        $curlHandle = curl_init();
        $headers = [
            'Content-Type:application/json',
            'Authorization:Basic ' . base64_encode(sprintf('%s:%s', $username, $password)),
        ];
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postBody);
        // Allow cUrl functions 20 seconds to execute
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 20);
        // Wait 10 seconds while trying to connect
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
        $output = [];
        $output['server_response'] = curl_exec($curlHandle);
        $curl_info = curl_getinfo($curlHandle);
        $output['http_status'] = $curl_info['http_code'];
        $output['error'] = curl_error($curlHandle);
        curl_close($curlHandle);
        return $output;
    }
}
