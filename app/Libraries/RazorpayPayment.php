<?php

namespace App\Libraries;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\BaseConfig;

class RazorpayPayment {

    private $apiConfig;
    private $db;

    public function __construct(BaseConnection $db, BaseConfig $config) {
        $this->db = $db;
        $this->config = $config;
        $this->initialize();
    }

    public function initialize($branchID = null): void {
        if (empty($branchID)) {
            $branchID = session()->get('branch_id'); // Assuming you store branch_id in session
        }

        $query = $this->db->table('payment_config')->select('razorpay_key_id, razorpay_key_secret')->where('branch_id', $branchID)->get();
        $this->apiConfig = $query->getRowArray() ?: ['razorpay_key_id' => '', 'razorpay_key_secret' => ''];
    }

    public function payment(array $data): string {
        $api = new Api($this->apiConfig['razorpay_key_id'], $this->apiConfig['razorpay_key_secret']);
        $orderData = [
            'receipt'         => $data['invoice_no'],
            'amount'          => ($data['amount'] + $data['fine']) * 100, // Amount in paise
            'currency'        => 'INR',
            'payment_capture' => 1 // Auto capture
        ];

        $razorpayOrder = $api->order->create($orderData);
        return $razorpayOrder['id'];
    }

    public function verify(array $attributes): bool|string {
        $api = new Api($this->apiConfig['razorpay_key_id'], $this->apiConfig['razorpay_key_secret']);
        try {
            $api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (SignatureVerificationError $signatureVerificationError) {
            return 'Razorpay Error: ' . $signatureVerificationError->getMessage();
        }
    }
}
