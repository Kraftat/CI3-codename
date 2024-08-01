<?php

namespace App\Libraries;

use Config\Services;

class Msg91
{
    protected $authKey;
    protected $senderID;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $branchID = service('session')->get('branch_id') ?? 'default_branch_id'; // Adjust if using a different method to get logged in branch ID

        $msg91 = $db->table('sms_credential')
            ->where('sms_api_id', 3)
            ->where('branch_id', $branchID)
            ->get()
            ->getRowArray();

        $this->authKey  = $msg91['field_one'] ?? '';
        $this->senderID = $msg91['field_two'] ?? '';
    }

    public function send(string $to, string $message, string $dlt_te_id = ''): ?bool
    {
        $message = urlencode($message);

        $route = "4";

        $postData = [
            "sender"    => $this->senderID,
            'DLT_TE_ID' => $dlt_te_id,
            'route'     => $route,
            "country"   => "91",
            "unicode"   => 1,
            'sms'       => [['message' => $message, 'to' => [$to]]],
        ];

        $url = "http://api.msg91.com/api/v2/sendsms";

        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', $url, [
            'headers' => [
                'authkey' => $this->authKey,
                'content-type' => 'application/json',
            ],
            'json' => $postData,
        ]);

        $responseBody = $response->getBody();
        $result = json_decode($responseBody);

        return isset($result->type) && $result->type === "success";
    }
}
