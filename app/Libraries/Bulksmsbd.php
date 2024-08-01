<?php

namespace App\Libraries;

use CodeIgniter\Database\ConnectionInterface;

class Bulksmsbd
{
    protected $sender_id;
    protected $api_key;
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
        $branchID = session('is_superadmin_loggedin') ? service('request')->getPost('branch_id') : session('branch_id');

        $bulksms = $this->db->table('sms_credential')
            ->where(['sms_api_id' => 7, 'branch_id' => $branchID])
            ->get()
            ->getRowArray();
        $this->sender_id = $bulksms['field_one'] ?? '';
        $this->api_key = $bulksms['field_two'] ?? '';
    }

    public function send(string $to, string $message): bool
    {
        $url = "https://bulksmsbd.net/api/smsapi";
        $data = [
            "api_key" => $this->api_key,
            "senderid" => $this->sender_id,
            "number" => $to,
            "message" => $message
        ];

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($curlHandle);
        curl_close($curlHandle);

        return true;
    }
}
