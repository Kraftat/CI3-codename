<?php

namespace App\Libraries;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Config\Database;

class StripePayment
{
    private $ci;
    public $api_config;

    public function __construct()
    {
        $this->ci = Database::connect();
        $this->initialize();
    }

    public function initialize($branchID = ''): void
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->api_config = $this->ci->table('payment_config')
            ->select('stripe_secret, stripe_demo')
            ->where('branch_id', $branchID)
            ->get()
            ->getRowArray();

        if (empty($this->api_config)) {
            $this->api_config = ['stripe_secret' => '', 'stripe_demo' => ''];
        }

        Stripe::setApiKey($this->api_config['stripe_secret']);
    }

    public function payment(array $data)
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'USD',
                    'unit_amount' => number_format(($data['amount'] * 100), 0, '.', ''),
                    'product_data' => [
                        'name' => $data['description'],
                        'images' => [$data['imagesURL']],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
        ]);
    }

    public function verify($id)
    {
        return Session::retrieve($id);
    }
}
