<?php

namespace App\Libraries;

use Twilio\Rest\Client;
use Config\Services;
use Config\Database;

class Twilio
{
    protected Client $client;
    protected $account_sid;
    protected $auth_token;
    protected $number;

    public function __construct()
    {
        $ci = Services::request(); // Use CI4 Services to get request service
        $branchID = is_superadmin_loggedin() ? $ci->getPost('branch_id') : get_loggedin_branch_id();

        $db = Database::connect();
        $query = $db->table('sms_credential')
                    ->select('field_one, field_two, field_three')
                    ->where('sms_api_id', 1)
                    ->where('branch_id', $branchID)
                    ->get();
        $twilio = $query->getRowArray();

        $this->account_sid = $twilio['field_one'] ?? ''; // Use null coalescing operator
        $this->auth_token  = $twilio['field_two'] ?? '';
        $this->number      = $twilio['field_three'] ?? '';

        // Initialize the client with Twilio credentials
        $this->client = new Client($this->account_sid, $this->auth_token);
    }

    public function sms($to, $body)
    {
        // Send an SMS using Twilio's REST API
        $message = $this->client->messages->create(
            $to,
            [
                'from' => $this->number,
                'body' => $body
            ]
        );
        return $message->sid; // Return the message SID to confirm successful sending
    }
}
