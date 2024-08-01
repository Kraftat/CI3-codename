<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Config\BaseConfig;
class SystemUpdateModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
        $this->user_agent = service('user_agent');
    }
    public function get_update_info()
    {
        $purchaseCode = $this->getPurchaseCode();
        $curl = curl_init();
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_USERAGENT => $this->agent->agent_string(), CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_TIMEOUT => 30, CURLOPT_URL => UPDATE_INFO_URL, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => ['item' => 'school', 'current_version' => $this->get_current_db_version(), 'purchase_code' => $purchaseCode['purchase_code']]]);
        $result = curl_exec($curl);
        $error = '';
        if (!$curl || !$result) {
            $error = 'Curl Error - Contact your hosting provider with the following error as reference: Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
        }
        curl_close($curl);
        if ($error != '') {
            return $error;
        }
        return $result;
    }
    public function getPurchaseCode()
    {
        $array = array('purchase_username' => "", 'purchase_code' => "");
        $file = APPPATH . 'config/purchase_key.php';
        if (file_exists($file)) {
            @chmod($file, FILE_WRITE_MODE);
            $purchase = file_get_contents($file);
            $purchase = json_decode($purchase);
            if (is_array($purchase)) {
                $array['purchase_username'] = trim($purchase[0]);
                $array['purchase_code'] = trim($purchase[1]);
            }
        }
        return $array;
    }
    public function get_current_db_version()
    {
        $builder->limit(1);
        return $builder->get('migrations')->row()->version;
    }
    public function getIP()
    {
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote == "::1" ? "127.0.0.1" : $remote;
        }
        return $ip;
    }
    public function is_connected($host = 'www.google.com')
    {
        $connected = @fsockopen($host, 80);
        //website, port  (try 80 or 443)
        if ($connected) {
            $is_conn = true;
            //action when connected
            fclose($connected);
        } else {
            $is_conn = false;
            //action in connection failure
        }
        return $is_conn;
    }
    public function upgrade_database_silent()
    {
        $this->load->config('migration');
        $this->library('migration', array('migration_enabled' => true, 'migration_type' => config('MyConfig')->migration_type, 'migration_table' => config('MyConfig')->migration_table, 'migration_auto_latest' => config('MyConfig')->migration_auto_latest, 'migration_version' => config('MyConfig')->migration_version, 'migration_path' => config('MyConfig')->migration_path));
        if ($this->migration->current() === false) {
            return array('success' => 0, 'message' => $this->migration->error_string());
        } else {
            return array('success' => 1);
        }
    }
    public function addonChk()
    {
        $builder->select('prefix,version');
        $addons = $builder->get('addon')->getResult();
        $data = [];
        $result = "";
        if (!empty($addons)) {
            foreach ($addons as $key => $value) {
                $data[] = $value->prefix . "-" . $value->version;
            }
        }
        if (is_array($data) && !empty($data)) {
            $result = implode("||", $data);
        }
        return $result;
    }
}



