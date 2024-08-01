<?php

namespace App\Libraries;

class Textlocal
{
    private $apiKey;

    private $senderID;

    public function __construct()
    {
        $ci = \Config\Services::request();
        $branchID = is_superadmin_loggedin() ? $ci->getPost('branch_id') : get_loggedin_branch_id();

        $db = \Config\Database::connect();
        $query = $db->table('sms_credential')
                    ->select('field_one, field_two')
                    ->where('sms_api_id', 5)
                    ->where('branch_id', $branchID)
                    ->get();
        $smscountry = $query->getRowArray();

        $this->senderID = isset($smscountry['field_one']) ? $smscountry['field_one'] : '';
        $this->apiKey = isset($smscountry['field_two']) ? $smscountry['field_two'] : '';
    }

    public function sendSms($numbers, $message): bool
    {
        // apiKey
        $apiKey = urlencode($this->apiKey);

        // Message details
        $sender = urlencode($this->senderID);
        $message = rawurlencode((string) $message);

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Process your response here
        $r = json_decode($response);
        return $r->status == 'success';
    }
}
