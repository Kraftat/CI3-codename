<?php

namespace App\Libraries;

use Midtrans\Config;
use Midtrans\Snap;
use CodeIgniter\Database\BaseConnection;

class MidtransPayment
{
    protected $db;
    protected $apiConfig;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
        $this->initialize();
    }

    public function initialize($branchID = ''): void
    {
        if (empty($branchID)) {
            $branchID = service('session')->get('branch_id'); // Adjust if using a different method to get logged in branch ID
        }

        $this->apiConfig = $this->db->table('payment_config')
            ->select('midtrans_client_key, midtrans_server_key, midtrans_sandbox')
            ->where('branch_id', $branchID)
            ->get()
            ->getRowArray();

        if (empty($this->apiConfig)) {
            $this->apiConfig = [
                'midtrans_client_key' => '',
                'midtrans_server_key' => '',
                'midtrans_sandbox' => ''
            ];
        }

        // Set your Merchant Server Key
        Config::$serverKey = $this->apiConfig['midtrans_server_key'];
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = $this->apiConfig['midtrans_sandbox'] != 1;
        // Set sanitization on (default)
        Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        Config::$is3ds = true;
    }

    public function getSnapToken($amount, $orderId)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ]
        ];

        try {
            // Get Snap Payment Page URL
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            log_message('error', 'Midtrans Snap token generation failed: ' . $e->getMessage());
            return null;
        }
    }
}
