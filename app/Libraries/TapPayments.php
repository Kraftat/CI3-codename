<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Config\Services;
use Exception;

require_once APPPATH . 'ThirdParty/tappayments/vendor/autoload.php';

class TapPayments {
    protected $request;
    protected $db;
    protected $api_config;
    protected $curl;

    public function __construct() {
        $this->request = Services::request(); // Get the CI service instance for requests
        $this->db = Database::connect(); // Connect to the database
        $this->curl = curl_init(); // Initialize CURL
    }

    public function initialize($branch_id = ''): void {
        if (empty($branch_id)) {
            $branch_id = $this->request->getSession()->get('branch_id'); // Adjust to get logged-in branch ID correctly
        }

        $query = $this->db->table('payment_config')
                          ->select('tap_secret_key, tap_public_key, tap_merchant_id')
                          ->where('branch_id', $branch_id)
                          ->get();
        $this->api_config = $query->getRowArray();

        if (empty($this->api_config) || empty($this->api_config['tap_secret_key'])) {
            log_message('error', 'Tap Payments configuration not found for branch_id: ' . $branch_id);
            throw new Exception('Configuration for Tap Payments not found.');
        }
    }

    public function createCharge($data) {
        if (empty($this->api_config['tap_secret_key'])) {
            throw new Exception('Tap Payments API key is not initialized.');
        }

        $url = "https://api.tap.company/v2/charges/";
        $headers = [
            'Authorization: Bearer ' . $this->api_config['tap_secret_key'],
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($this->curl);
        $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            throw new Exception("Error from Tap Payments: " . $response);
        }

        return json_decode($response, true);
    }

    public function verifyTransaction(string $charge_id) {
        if (empty($this->api_config['tap_secret_key'])) {
            throw new Exception('Tap Payments API key is not initialized.');
        }

        $url = 'https://api.tap.company/v2/charges/' . $charge_id;
        $headers = [
            'Authorization: Bearer ' . $this->api_config['tap_secret_key'],
            "Accept: application/json"
        ];

        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($this->curl);
        $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            throw new Exception("Error from Tap Payments: " . $response);
        }

        return json_decode($response, true);
    }

    public function __destruct() {
        curl_close($this->curl); // Properly close the CURL session
    }
}
