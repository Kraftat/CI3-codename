<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class CustomSms
{
    private $apiURL;
    protected $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;

        if (is_superadmin_loggedin()) {
            $branchID = service('request')->getPost('branch_id');
        } else {
            $branchID = get_loggedin_branch_id();
        }

        $smscountry = $this->db->table('sms_credential')->where(['sms_api_id' => 8, 'branch_id' => $branchID])->get()->getRowArray();
        $this->apiURL = $smscountry['field_one'] ?? '';
    }

    public function send($numbers, $message, $dlt_template_id = '')
    {
        $message = rawurlencode($message);
        $url = $this->apiURL;
        $url = str_replace('[app_number]', $numbers, $url);
        $url = str_replace('[app_message]', $message, $url);
        $url = str_replace('[dlt_template_id]', $dlt_template_id, $url);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return true;
    }
}
