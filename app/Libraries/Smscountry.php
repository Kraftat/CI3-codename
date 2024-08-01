<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class Smscountry
{
    protected string $username;
    protected string $password;
    protected string $senderId;
    protected BaseConnection $db;

    public function __construct()
    {
        helper('custom'); // Load your custom helper where is_superadmin_loggedin and get_loggedin_branch_id are defined
        $this->db = Database::connect();
        $branchID = is_superadmin_loggedin() ? service('request')->getPost('branch_id') : get_loggedin_branch_id();

        $smscountry = $this->db->table('sms_credential')
            ->select('field_one, field_two, field_three')
            ->where(['sms_api_id' => 6, 'branch_id' => $branchID])
            ->get()
            ->getRowArray();

        $this->username = $smscountry['field_one'] ?? '';
        $this->password = $smscountry['field_two'] ?? '';
        $this->senderId = $smscountry['field_three'] ?? '';
    }

    public function send(string $to, string $message): bool
    {
        $url = "http://api.smscountry.com/SMSCwebservice_bulk.aspx";
        $mtype = "N";
        $dr = "Y";
        $message = urlencode($message);

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, sprintf(
            'User=%s&passwd=%s&mobilenumber=%s&message=%s&sid=%s&mtype=%s&DR=%s',
            $this->username,
            $this->password,
            $to,
            $message,
            $this->senderId,
            $mtype,
            $dr
        ));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curlHandle);
        $curlError = curl_errno($curlHandle);
        $curlErrorMessage = curl_error($curlHandle);

        curl_close($curlHandle);

        if ($curlError !== 0) {
            log_message('error', 'Curl error: ' . $curlErrorMessage);
            return false;
        }

        return true;
    }
}
