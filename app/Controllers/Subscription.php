<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SaasModel;
/**
 * @package : Ramom school management system (Saas)
 * @version : 3.1
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Subscription.php
 * @copyright : Reserved RamomCoder Team
 */
class Subscription extends MyController

{
    /**
     * @var mixed
     */
    public $Sslcommerz;
    /**
     * @var App\Models\SaasModel
     */
    public $saas;

    /**
     * @var App\Models\SubscriptionModel
     */
    public $subscription;

    public $load;

    public $session;

    public $saasModel;

    public $input;

    public $subscriptionModel;

    public $validation;

    public $paypal_payment;

    public $applicationModel;

    public $stripe_payment;

    public $razorpay_payment;

    public $sslcommerz;

    public $midtrans_payment;

    public $paytm_kit_lib;

    public $db;

    private $globalPaymentID = 9999;

    public function __construct()
    {
        \Config\Database::connect();
        new \App\Models\SaasModel(); // Ensure you have a SaasModel
        new \App\Models\SubscriptionModel();
        $this->paypal_payment = service('paypalPayment');
        $this->stripe_payment = service('stripePayment');
        $this->razorpay_payment = service('razorpayPayment');
        $this->Sslcommerz = service('sslcommerz');
        $this->midtrans_payment = service('midtransPayment');
        // $this->Tap_payments = service('tapPayments');
        if (!is_admin_loggedin()) {
            session()->set('redirect_url', current_url());
            redirect(base_url('authentication'), 'refresh');
        }
    }

    public function index()
    {
        $id = get_loggedin_branch_id();
        $school = $this->saasModel->getSchool($id);
        $this->data['school'] = $school;
        $this->data['currency_symbol'] = $this->subscriptionModel->getCurrency()->currency_symbol;
        $this->data['schoolID'] = $id;
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'subscription/index';
        $this->data['main_menu'] = 'subscription';
        echo view('layout/index', $this->data);
    }

    public function list()
    {
        $id = get_loggedin_branch_id();
        $school = $this->saasModel->getSchool($id);
        $this->data['school'] = $school;
        $this->data['schoolID'] = $id;
        $this->data['currency_symbol'] = $this->subscriptionModel->getCurrency()->currency_symbol;
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'subscription/list';
        $this->data['main_menu'] = 'subscription';

        // New logic for fetching subscription packages
        $getType = $this->request->getGet('type');
        $getType = (empty($getType)) ? 'all' : $getType;

        $getPeriodType = $saasModel->getPeriodType();
        unset($getPeriodType['']);

        $sql = "SELECT * FROM `saas_package` WHERE `status` = '1' AND `free_trial` = '0'";
        if ($getType !== 'all') {
            $sql .= " AND `period_type` = " . $db->escape($getType);
        }

        $packages = $db->query($sql)->getResultArray();

        $builder = $db->table('permission_modules');
        $builder->select('*');
        $builder->where('permission_modules.in_module', 1);
        $builder->orderBy('permission_modules.prefix', 'asc');

        $modules = $builder->get()->getResult();

        // Pass the fetched data to the view
        $this->data['getType'] = $getType;
        $this->data['getPeriodType'] = $getPeriodType;
        $this->data['packages'] = $packages;
        $this->data['modules'] = $modules;

        echo view('layout/index', $this->data);
    }

    public function renew()
    {
        $getType = urldecode((string) $this->request->getGet('id'));
        if (preg_match('/^[1-9]\d*$/', $getType)) {
            $package = $this->subscriptionModel->getPlanDetails($getType);
            if (empty($package)) {
                return redirect()->to(base_url('subscription/list'));
            }

            $this->data['currency_symbol'] = $this->subscriptionModel->getCurrency()->currency_symbol;
            $this->data['getUser'] = $this->subscriptionModel->getAdminDetails();
            $this->data['package'] = $package;
            $this->data['title'] = translate('subscription');
            $this->data['sub_page'] = 'subscription/renew';
            $this->data['main_menu'] = 'package';
            echo view('layout/index', $this->data);
        }
        return null;
    }

    public function checkout()
    {
        if (!is_admin_loggedin()) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $payVia = $this->request->getPost('pay_via');
            $this->validation->setRules(['pay_via' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
            if ($payVia == 'payumoney') {
                $this->validation->setRules(['payer_name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['phone' => ["label" => translate('phone'), "rules" => 'trim|required']]);
            }

            if ($payVia == 'sslcommerz') {
                $this->validation->setRules(['sslcommerz_name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sslcommerz_email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['sslcommerz_address' => ["label" => translate('address'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sslcommerz_postcode' => ["label" => translate('postcode'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sslcommerz_state' => ["label" => translate('state'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sslcommerz_phone' => ["label" => translate('phone'), "rules" => 'trim|required']]);
            }

            if ($this->validation->run() !== false) {
                $packageID = $this->request->getPost('plan_id');
                $getSubscriptions = $this->subscriptionModel->getSubscriptions();
                $getPlanDetails = $this->subscriptionModel->getPlanDetails($packageID, true);
                if (empty($getPlanDetails)) {
                    set_alert('error', "Invaild Plan.");
                    $array = ['status' => 'success', 'url' => base_url('subscription/index')];
                    echo json_encode($array);
                    exit;
                }

                $getAdminDetails = $this->subscriptionModel->getAdminDetails();
                $amount = floatval($getPlanDetails['price'] - $getPlanDetails['discount']);
                // //subscription expiry date calculation
                // $period_value = $getPlanDetails['period_value'];
                // $periodType = $getPlanDetails['period_type'];
                // if ($periodType == 2) {
                //     $strtotimeType = "day";
                // }
                // if ($periodType == 3) {
                //     $strtotimeType = "month";
                // }
                // if ($periodType == 4) {
                //     $strtotimeType = "year";
                // }
                // if ($periodType ==  1) {
                //     $expireDate = null;
                // } else {
                //     $expireDate = date("Y-m-d", strtotime("+$period_value $strtotimeType"));
                // }
                // Subscription expiry date calculation
                $currentDate = date("Y-m-d");
                $existingExpireDate = $getSubscriptions['expire_date'] ?? $currentDate;
                $periodValue = $getPlanDetails['period_value'];
                $periodType = $getPlanDetails['period_type'];
                $strtotimeType = $periodType == 2 ? "days" : ($periodType == 3 ? "months" : "years");
                $expireDate = $existingExpireDate > $currentDate ? date("Y-m-d", strtotime(sprintf('+%s %s', $periodValue, $strtotimeType), strtotime((string) $existingExpireDate))) : date("Y-m-d", strtotime(sprintf('+%s %s', $periodValue, $strtotimeType)));
                $params = ['package_id' => $packageID, 'current_package_id' => $getSubscriptions['package_id'], 'current_subscriptions_id' => $getSubscriptions['id'], 'name' => $getAdminDetails['name'], 'expire_date' => $expireDate, 'email' => $getAdminDetails['email'], 'mobile_no' => $getAdminDetails['mobileno'], 'amount' => $amount, 'discount' => $getPlanDetails['discount'], 'currency' => $this->subscriptionModel->getCurrency()->currency_symbol];
                if ($payVia == 'paypal') {
                    $url = base_url("subscription/paypal");
                    session()->set("params", $params);
                }

                if ($payVia == 'stripe') {
                    $url = base_url("subscription/stripe");
                    session()->set("params", $params);
                }

                if ($payVia == 'payumoney') {
                    $payerData = ['name' => $this->request->getPost('payer_name'), 'email' => $this->request->getPost('email'), 'phone' => $this->request->getPost('phone')];
                    $params['payer_data'] = $payerData;
                    $url = base_url("subscription/payumoney");
                    session()->set("params", $params);
                }

                if ($payVia == 'paystack') {
                    $url = base_url("subscription/paystack");
                    session()->set("params", $params);
                }

                if ($payVia == 'razorpay') {
                    $url = base_url("subscription/razorpay");
                    session()->set("params", $params);
                }

                if ($payVia == 'sslcommerz') {
                    $params['tran_id'] = "SSLC" . uniqid();
                    $params['cus_name'] = $this->request->getPost('sslcommerz_name');
                    $params['cus_email'] = $this->request->getPost('sslcommerz_email');
                    $params['cus_address'] = $this->request->getPost('sslcommerz_address');
                    $params['cus_postcode'] = $this->request->getPost('sslcommerz_postcode');
                    $params['cus_state'] = $this->request->getPost('sslcommerz_state');
                    $params['cus_phone'] = $this->request->getPost('sslcommerz_phone');
                    $url = base_url("subscription/sslcommerz");
                    session()->set("params", $params);
                }

                if ($payVia == 'jazzcash') {
                    $url = base_url("subscription/jazzcash");
                    session()->set("params", $params);
                }

                if ($payVia == 'midtrans') {
                    $url = base_url("subscription/midtrans");
                    session()->set("params", $params);
                }

                if ($payVia == 'flutterwave') {
                    $url = base_url("subscription/flutterwave");
                    session()->set("params", $params);
                }

                if ($payVia == 'payhere') {
                    $url = base_url("subscription/payhere");
                    session()->set("params", $params);
                }

                if ($payVia == 'toyyibPay') {
                    $url = base_url("subscription/toyyibpay");
                    session()->set("params", $params);
                }

                if ($payVia == 'paytm') {
                    $url = base_url("subscription/paytm");
                    session()->set("params", $params);
                }

                if ($payVia == 'tap') {
                    $url = base_url("subscription/tap");
                    session()->set("params", $params);
                }

                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function paypal()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (empty($params) || !is_array($params)) {
            set_alert('error', 'Payment parameters are missing or invalid.');
            redirect($_SERVER['HTTP_REFERER']);
        }

        if (empty($config['paypal_client_id']) || empty($config['paypal_client_secret'])) {
            set_alert('error', 'PayPal configuration is incomplete.');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $sandbox = (bool) $config['paypal_sandbox'];
        $data = ['cancelUrl' => base_url('subscription/cancel_payment'), 'returnUrl' => base_url('subscription/success_payment'), 'reference_no' => $params['reference_no'], 'name' => $params['name'], 'description' => "School Subscription fees deposit via PayPal, Reference No - " . $params['reference_no'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
        $this->paypal_payment->initialize($config['paypal_client_id'], $config['paypal_client_secret'], $sandbox);
        $response = $this->paypal_payment->payment($data);
        if (!empty($response) && isset($response['status']) && $response['status'] == 'redirect') {
            redirect($response['url']);
        } else {
            log_message('error', 'PayPal payment error: ' . json_encode($response));
            set_alert('error', 'Payment initialization failed. Please try again later.');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function success_payment()
    {
        $paymentId = $this->request->getGet('paymentId');
        $payerId = $this->request->getGet('PayerID');
        $params = session()->get('params');
        session()->unset_userdata("params");
        if (empty($params) || !is_array($params)) {
            set_alert('error', 'Payment parameters are missing or invalid.');
            return redirect()->to(base_url('subscription/index'));
        }

        $redirectUrl = base_url('subscription/index/' . $params['reference_no']);
        if (empty($paymentId) || empty($payerId)) {
            set_alert('error', 'Payment verification failed. Required parameters are missing.');
            redirect($redirectUrl);
        }

        $config = $this->getPaymentConfig();
        $this->paypal_payment->initialize($config['paypal_client_id'], $config['paypal_client_secret'], $config['paypal_sandbox']);
        $response = $this->paypal_payment->success($paymentId, $payerId);
        log_message('info', 'PayPal verification response: ' . json_encode($response));
        if (isset($response['state']) && $response['state'] == 'approved') {
            $refId = $response['transactions'][0]['related_resources'][0]['sale']['id'];
            $amount = floatval($response['transactions'][0]['amount']['total']);
            $currency = $response['transactions'][0]['amount']['currency'];
            $params['amount'] = floatval($params['amount']);
            // Confirm that $params['amount'] is already set properly
            $params['payment_id'] = $refId;
            $params['payment_method'] = 6;
            // Payment method identifier for Tap Payments
            $this->subscriptionModel->savePaymentData($params);
            // Calling the model function to handle the data
            set_alert('success', 'Subscription renewal successful.');
            redirect($redirectUrl);
        } else {
            set_alert('error', "Transaction Failed");
            redirect($redirectUrl);
        }

        set_alert('error', "Transaction Failed");
        redirect($redirectUrl);
        return null;
    }

    public function stripe()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['stripe_secret'] == "") {
                set_alert('error', 'Stripe config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $data = ['imagesURL' => $this->applicationModel->getBranchImage(get_loggedin_branch_id(), 'logo-small'), 'success_url' => base_url("subscription/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'cancel_url' => base_url("subscription/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'name' => $params['name'], 'description' => "School subscription fee deposit through online.", 'amount' => $params['amount'], 'currency' => $params['currency']];
                $this->stripe_payment->initialize($this->globalPaymentID);
                $response = $this->stripe_payment->payment($data);
                $data['sessionId'] = $response['id'];
                $data['stripe_publishiable'] = $config['stripe_publishiable'];
                echo view('layout/stripe', $data);
            }
        }
    }

    public function stripe_success()
    {
        $sessionId = $this->request->getGet('session_id');
        $params = session()->get('params');
        if (!empty($sessionId) && !empty($params)) {
            try {
                $this->stripe_payment->initialize($this->globalPaymentID);
                $response = $this->stripe_payment->verify($sessionId);
                if (isset($response->payment_status) && $response->payment_status == 'paid') {
                    $amount = floatval($response->amount_total) / 100;
                    $refId = $response->payment_intent;
                    // transition history save in database
                    $params['amount'] = $amount;
                    $params['payment_id'] = $refId;
                    $params['payment_method'] = 7;
                    $this->subscriptionModel->savePaymentData($params);
                    set_alert('success', translate('payment_successfull'));
                    return redirect()->to(base_url('subscription/index'));
                }
                // payment failed: display message to customer
                set_alert('error', "Something went wrong!");
                return redirect()->to(base_url('subscription/index'));
            } catch (\Exception $ex) {
                set_alert('error', $ex->getMessage());
                redirect(site_url('subscription/index'));
            }
        }
        return null;
    }

    public function paystack()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        $email = empty($params['email']) ? "example@email.com" : $params['email'];
        if (!empty($params)) {
            if ($config['paystack_secret_key'] == "") {
                set_alert('error', 'Paystack config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $result = [];
                $amount = $params['amount'] * 100;
                $ref = app_generate_hash();
                $callbackUrl = base_url() . 'subscription/verify_paystack_payment/' . $ref;
                $postdata = ['email' => $email, 'amount' => $amount, "reference" => $ref, "callback_url" => $callbackUrl];
                $url = "https://api.paystack.co/transaction/initialize";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
                //Post Fields
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $headers = ['Authorization: Bearer ' . $config['paystack_secret_key'], 'Content-Type: application/json'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $request = curl_exec($ch);
                curl_close($ch);
                //
                if ($request) {
                    $result = json_decode($request, true);
                }

                $redir = $result['data']['authorization_url'];
                header("Location: " . $redir);
            }
        }
    }

    public function verify_paystack_payment($ref)
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        // null session data
        session()->set("params", "");
        $result = [];
        $url = 'https://api.paystack.co/transaction/verify/' . $ref;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['paystack_secret_key']]);
        $request = curl_exec($ch);
        curl_close($ch);
        //
        if ($request) {
            $result = json_decode($request, true);
            // print_r($result);
            if ($result) {
                if ($result['data']) {
                    //something came in
                    if ($result['data']['status'] == 'success') {
                        $params['payment_id'] = $ref;
                        $params['payment_method'] = 9;
                        $this->subscriptionModel->savePaymentData($params);
                        set_alert('success', translate('payment_successfull'));
                        return redirect()->to(base_url('subscription/index'));
                    }
                    // the transaction was not successful, do not deliver value'
                    // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                    set_alert('error', "Transaction Failed");
                    return redirect()->to(base_url('subscription/index'));
                }
                //echo $result['message'];
                set_alert('error', "Transaction Failed");
                return redirect()->to(base_url('subscription/index'));
            }
            //print_r($result);
            //die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('subscription/index'));
        }
        //var_dump($request);
        //die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('subscription/index'));
    }

    /* PayUmoney Payment */
    public function payumoney()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['payumoney_key'] == "" || $config['payumoney_salt'] == "") {
                set_alert('error', 'PayUmoney config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                // api config
                $apiLink = $config['payumoney_demo'] == 1 ? "https://test.payu.in/_payment" : "https://secure.payu.in/_payment";
                $key = $config['payumoney_key'];
                $salt = $config['payumoney_salt'];
                // generate transaction id
                $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
                // payumoney details
                $amount = floatval($params['amount']);
                $payerName = $params['payer_data']['name'];
                $payerEmail = $params['payer_data']['email'];
                $payerPhone = $params['payer_data']['phone'];
                $productInfo = "School subscription fee deposit through online";
                // redirect url
                $success = base_url('subscription/payumoney_success');
                $fail = base_url('subscription/payumoney_success');
                $params['txn_id'] = $txnid;
                session()->set("params", $params);
                // optional udf values
                $udf1 = '';
                $udf2 = '';
                $udf3 = '';
                $udf4 = '';
                $udf5 = '';
                $hashstring = $key . '|' . $txnid . '|' . $amount . '|' . $productInfo . '|' . $payerName . '|' . $payerEmail . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $salt;
                $hash = strtolower(hash('sha512', $hashstring));
                $data = ['salt' => $salt, 'key' => $key, 'payu_base_url' => $apiLink, 'action' => $apiLink, 'surl' => $success, 'furl' => $fail, 'txnid' => $txnid, 'amount' => $amount, 'firstname' => $payerName, 'email' => $payerEmail, 'phone' => $payerPhone, 'productinfo' => $productInfo, 'hash' => $hash];
                echo view('layout/payumoney', $data);
            }
        }
    }

    /* payumoney successpayment redirect */
    public function payumoney_success()
    {
        if ($this->request->getServer('REQUEST_METHOD') == 'POST') {
            $params = session()->get('params');
            // null session data
            session()->set("params", "");
            if ($this->request->getPost('status') == "success") {
                $txnId = $params['txn_id'];
                $mihpayid = $this->request->getPost('mihpayid');
                $transactionid = $this->request->getPost('txnid');
                if ($txnId == $transactionid) {
                    // transition history save in database
                    $params['amount'] = $amount;
                    $params['payment_id'] = $mihpayid;
                    $params['payment_method'] = 8;
                    $this->subscriptionModel->savePaymentData($params);
                    set_alert('success', translate('payment_successfull'));
                    return redirect()->to(base_url('subscription/index'));
                }
                set_alert('error', translate('invalid_transaction'));
                return redirect()->to(base_url('subscription/index'));
            }
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('subscription/index'));
        }
        return null;
    }

    public function razorpay()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['razorpay_key_id'] == "" || $config['razorpay_key_secret'] == "") {
                set_alert('error', 'Razorpay config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $params['invoice_no'] = $params['package_id'];
                $params['fine'] = 0;
                $this->razorpay_payment->initialize($this->globalPaymentID);
                $response = $this->razorpay_payment->payment($params);
                $params['razorpay_order_id'] = $response;
                session()->set("params", $params);
                $arrayData = ['key' => $config['razorpay_key_id'], 'amount' => $params['amount'] * 100, 'name' => $params['name'], 'description' => "School subscription fee deposit through online", 'image' => base_url('uploads/app_image/logo-small.png'), 'currency' => 'INR', 'order_id' => $params['razorpay_order_id'], 'theme' => ["color" => "#F37254"]];
                $data['return_url'] = base_url('subscription/index');
                $data['pay_data'] = json_encode($arrayData);
                echo view('layout/razorpay', $data);
            }
        }
    }

    public function razorpay_verify()
    {
        $params = session()->get('params');
        if ($this->request->getPost('razorpay_payment_id')) {
            // null session data
            session()->set("params", "");
            $attributes = ['razorpay_order_id' => $params['razorpay_order_id'], 'razorpay_payment_id' => $this->request->getPost('razorpay_payment_id'), 'razorpay_signature' => $this->request->getPost('razorpay_signature')];
            $this->razorpay_payment->initialize($this->globalPaymentID);
            $response = $this->razorpay_payment->verify($attributes);
            if ($response == true) {
                $params['payment_id'] = $attributes['razorpay_payment_id'];
                $params['payment_method'] = 10;
                $this->subscriptionModel->savePaymentData($params);
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('subscription/index'));
            }
            set_alert('error', $response);
            return redirect()->to(base_url('subscription/index'));
        }
        return null;
    }

    public function sslcommerz()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['sslcz_store_id'] == "" || $config['sslcz_store_passwd'] == "") {
                set_alert('error', 'SSLcommerz config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $postData = [];
                $postData['total_amount'] = floatval($params['amount']);
                $postData['currency'] = "BDT";
                $postData['tran_id'] = $params['tran_id'];
                $postData['success_url'] = base_url('subscription/sslcommerz_success');
                $postData['fail_url'] = base_url('subscription/sslcommerz_success');
                $postData['cancel_url'] = base_url('subscription/sslcommerz_success');
                $postData['ipn_url'] = base_url() . "ipn";
                # CUSTOMER INFORMATION
                $postData['cus_name'] = $params['cus_name'];
                $postData['cus_email'] = $params['cus_email'];
                $postData['cus_add1'] = $params['cus_address'];
                $postData['cus_city'] = $params['cus_state'];
                $postData['cus_state'] = $params['cus_state'];
                $postData['cus_postcode'] = $params['cus_postcode'];
                $postData['cus_country'] = "Bangladesh";
                $postData['cus_phone'] = $params['cus_phone'];
                $postData['product_profile'] = "non-physical-goods";
                $postData['shipping_method'] = "No";
                $postData['num_of_item'] = "1";
                $postData['product_name'] = "School Fee";
                $postData['product_category'] = "SchoolFee";
                $this->sslcommerz->initialize($this->globalPaymentID);
                $this->sslcommerz->RequestToSSLC($postData);
            }
        }
    }

    /* sslcommerz successpayment redirect */
    public function sslcommerz_success()
    {
        $params = session()->get('params');
        if ($_POST['status'] == 'VALID' && $params['tran_id'] == $_POST['tran_id']) {
            $this->sslcommerz->initialize($this->globalPaymentID);
            if ($this->sslcommerz->ValidateResponse($_POST['currency_amount'], "BDT", $_POST)) {
                $tranId = $params['tran_id'];
                // transition history save in database
                $params['amount'] = $_POST['currency_amount'];
                $params['payment_id'] = $tranId;
                $params['payment_method'] = 11;
                $this->subscriptionModel->savePaymentData($params);
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('subscription/index'));
            }
        } else {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('subscription/index'));
        }
        return null;
    }

    public function jazzcash()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['jazzcash_merchant_id'] == "" || $config['jazzcash_passwd'] == "" || $config['jazzcash_integerity_salt'] == "") {
                set_alert('error', 'Jazzcash config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $integeritySalt = $config['jazzcash_integerity_salt'];
                $ppTxnRefNo = 'T' . date('YmdHis');
                $postData = ["pp_Version" => "2.0", "pp_TxnType" => "MPAY", "pp_Language" => "EN", "pp_IsRegisteredCustomer" => "Yes", "pp_TokenizedCardNumber" => "", "pp_CustomerEmail" => "", "pp_CustomerMobile" => "", "pp_CustomerID" => uniqid(), "pp_MerchantID" => $config['jazzcash_merchant_id'], "pp_Password" => $config['jazzcash_passwd'], "pp_TxnRefNo" => $ppTxnRefNo, "pp_Amount" => floatval($params['amount']) * 100, "pp_DiscountedAmount" => "", "pp_DiscountBank" => "", "pp_TxnCurrency" => "PKR", "pp_TxnDateTime" => date('YmdHis'), "pp_BillReference" => uniqid(), "pp_Description" => "School subscription fee deposit through online", "pp_TxnExpiryDateTime" => date('YmdHis', strtotime("+1 hours")), "pp_ReturnURL" => base_url('subscription/jazzcash_success'), "ppmpf_1" => "1", "ppmpf_2" => "2", "ppmpf_3" => "3", "ppmpf_4" => "4", "ppmpf_5" => "5"];
                $sortedString = $integeritySalt . '&';
                $sortedString .= $postData['pp_Amount'] . '&';
                $sortedString .= $postData['pp_BillReference'] . '&';
                $sortedString .= $postData['pp_CustomerID'] . '&';
                $sortedString .= $postData['pp_Description'] . '&';
                $sortedString .= $postData['pp_IsRegisteredCustomer'] . '&';
                $sortedString .= $postData['pp_Language'] . '&';
                $sortedString .= $postData['pp_MerchantID'] . '&';
                $sortedString .= $postData['pp_Password'] . '&';
                $sortedString .= $postData['pp_ReturnURL'] . '&';
                $sortedString .= $postData['pp_TxnCurrency'] . '&';
                $sortedString .= $postData['pp_TxnDateTime'] . '&';
                $sortedString .= $postData['pp_TxnExpiryDateTime'] . '&';
                $sortedString .= $postData['pp_TxnRefNo'] . '&';
                $sortedString .= $postData['pp_TxnType'] . '&';
                $sortedString .= $postData['pp_Version'] . '&';
                $sortedString .= $postData['ppmpf_1'] . '&';
                $sortedString .= $postData['ppmpf_2'] . '&';
                $sortedString .= $postData['ppmpf_3'] . '&';
                $sortedString .= $postData['ppmpf_4'] . '&';
                $sortedString .= $postData['ppmpf_5'];
                //sha256 hash encoding
                $ppSecureHash = hash_hmac('sha256', $sortedString, (string) $integeritySalt);
                $postData['pp_SecureHash'] = $ppSecureHash;
                if ($config['jazzcash_sandbox'] == 1) {
                    $data['api_url'] = "https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/";
                } else {
                    $data['api_url'] = "https://jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/";
                }

                $data['post_data'] = $postData;
                echo view('layout/jazzcash_pay', $data);
            }
        }
    }

    /* jazzcash successpayment redirect */
    public function jazzcash_success()
    {
        $params = session()->get('params');
        if ($_POST['pp_ResponseCode'] == '000') {
            $tranId = $_POST['pp_TxnRefNo'];
            // transition history save in database
            $params['amount'] = floatval($params['amount']);
            $params['payment_id'] = $tranId;
            $params['payment_method'] = 12;
            $this->subscriptionModel->savePaymentData($params);
            set_alert('success', translate('payment_successfull'));
            return redirect()->to(base_url('subscription/index'));
        }
        if ($_POST['pp_ResponseCode'] == '112') {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('subscription/index'));
        }
        else {
            set_alert('error', $_POST['pp_ResponseMessage']);
            return redirect()->to(base_url('subscription/index'));
        }
        return null;
    }

    public function midtrans()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['midtrans_client_key'] == "" && $config['midtrans_server_key'] == "") {
                set_alert('error', 'Midtrans config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $amount = number_format($params['amount'], 2, '.', '');
                $orderID = random_int(0, mt_getrandmax());
                $params['orderID'] = $orderID;
                session()->set("params", $params);
                $this->midtrans_payment->initialize($this->globalPaymentID);
                $response = $this->midtrans_payment->get_SnapToken(round($amount), $orderID);
                $data['snapToken'] = $response;
                $data['midtrans_client_key'] = $config['midtrans_client_key'];
                $data['midtrans_sandbox'] = $config['midtrans_sandbox'];
                echo view('layout/midtrans', $data);
            }
        }
    }

    public function midtrans_success()
    {
        $params = session()->get('params');
        $response = json_decode((string) $_POST['post_data']);
        if (!empty($params) && !empty($params['orderID']) && !empty($response)) {
            // null session data
            session()->set("params", "");
            if ($response->order_id == $params['orderID']) {
                $tranId = $response->transaction_id;
                // transition history save in database
                $params['amount'] = floatval($params['amount']);
                $params['payment_id'] = $tranId;
                $params['payment_method'] = 13;
                $this->subscriptionModel->savePaymentData($params);
                set_alert('success', translate('payment_successfull'));
            } else {
                set_alert('error', "Something went wrong!");
            }

            echo json_encode(['url' => base_url('subscription/index')]);
        }
    }

    public function flutterwave()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['flutterwave_public_key'] == "" && $config['flutterwave_secret_key'] == "") {
                set_alert('error', 'Flutter Wave config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $amount = floatval($params['amount']);
                $txref = "rsm" . app_generate_hash();
                $params['txref'] = $txref;
                session()->set("params", $params);
                $callbackUrl = base_url('subscription/verify_flutterwave_payment');
                $data = ['student_name' => $params['name'], 'amount' => $amount, 'customer_email' => $params['email'], 'currency' => $params['currency'], "txref" => $txref, "pubKey" => $config['flutterwave_public_key'], "redirect_url" => $callbackUrl];
                echo view('layout/flutterwave', $data);
            }
        }
    }

    public function verify_flutterwave_payment()
    {
        if (isset($_GET['cancelled']) && $_GET['cancelled'] == 'true') {
            set_alert('error', "Payment Cancelled");
            return redirect()->to(base_url('subscription/index'));
        }

        if (isset($_GET['tx_ref'])) {
            $config = $this->getPaymentConfig();
            $params = session()->get('params');
            session()->set("params", "");
            $postdata = ["SECKEY" => $config['flutterwave_secret_key'], "txref" => $params['txref']];
            $url = 'https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
            //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $headers = ['content-type: application/json', 'cache-control: no-cache'];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $request = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($request, true);
            if ($result['status'] == 'success' && isset($result['data']['chargecode']) && ($result['data']['chargecode'] == '00' || $result['data']['chargecode'] == '0')) {
                // transition history save in database
                $params['amount'] = floatval($params['amount']);
                $params['payment_id'] = $params['txref'];
                $params['payment_method'] = 14;
                $this->subscriptionModel->savePaymentData($params);
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('subscription/index'));
            }
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('subscription/index'));
        }
        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('subscription/index'));
    }

    // toyyibpay payment gateway script start
    public function toyyibpay()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['toyyibpay_secretkey'] == "" && $config['toyyibpay_categorycode'] == "") {
                set_alert('error', 'toyyibPay config not available');
                return redirect()->to(base_url('subscription/index'));
            }
            $paymentData = ['userSecretKey' => $config['toyyibpay_secretkey'], 'categoryCode' => $config['toyyibpay_categorycode'], 'billName' => 'School Subscription fees', 'billDescription' => 'School Subscription fees', 'billPriceSetting' => 1, 'billPayorInfo' => 1, 'billAmount' => floatval($params['amount']) * 100, 'billReturnUrl' => base_url('subscription/toyyibpay_success'), 'billCallbackUrl' => base_url('subscription/toyyibpay_callbackurl'), 'billExternalReferenceNo' => substr(hash('sha256', mt_rand() . microtime()), 0, 20), 'billTo' => $params['name'], 'billEmail' => $params['email'], 'billPhone' => $params['mobile_no'], 'billSplitPayment' => 0, 'billSplitPaymentArgs' => '', 'billPaymentChannel' => '0', 'billContentEmail' => 'Thank you for pay subscription fees', 'billChargeToCustomer' => 1];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/createBill');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paymentData);
            $result = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            $obj = json_decode($result);
            if (!empty($obj) && $obj->status != "error") {
                $url = "https://toyyibpay.com/" . $obj[0]->BillCode;
                header('Location: ' . $url);
            } else {
                set_alert('error', "Transaction Failed");
                return redirect()->to(base_url('subscription/index'));
            }
        }
        return null;
    }

    public function toyyibpay_success()
    {
        if ($_GET['status_id'] == 1 && !empty($_GET['billcode'])) {
            $params = session()->get('params');
            session()->set("params", "");
            $redirectUrl = base_url('subscription/index');
            $someData = ['billCode' => $_GET['billcode'], 'billpaymentStatus' => '1'];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/getBillTransactions');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $someData);
            $result = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            $result = json_decode($result);
            if (!empty($result[0]->billpaymentStatus) && $result[0]->billpaymentStatus == 1) {
                $refno = $_GET['transaction_id'];
                // transition history save in database
                $params['amount'] = floatval($params['amount']);
                $params['payment_id'] = $refno;
                $params['payment_method'] = 17;
                $this->subscriptionModel->savePaymentData($params);
                set_alert('success', translate('payment_successfull'));
                redirect($redirectUrl);
            } else {
                set_alert('error', "Transaction Failed");
                redirect($redirectUrl);
            }
        } else {
            set_alert('error', "Transaction Failed");
            redirect($redirectUrl);
        }
    }

    public function toyyibpay_callbackurl()
    {
        //some code here
    }

    //Paytm payment gateway script start
    public function paytm()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['paytm_merchantmid'] == "" && $config['paytm_merchantkey'] == "") {
                set_alert('error', 'Paytm config not available');
                return redirect()->to(base_url('subscription/index'));
            }
            $pAYTMMERCHANTMID = $config['paytm_merchantmid'];
            $pAYTMMERCHANTKEY = $config['paytm_merchantkey'];
            $pAYTMMERCHANTWEBSITE = $config['paytm_merchant_website'];
            $pAYTMINDUSTRYTYPE = $config['paytm_industry_type'];
            $transactionURL = 'https://securegw.paytm.in/theia/processTransaction';
            //For Production or LIVE Credentials
            // $transactionURL = 'https://securegw-stage.paytm.in/theia/processTransaction'; //TEST Credentials
            $orderID = time();
            $paytmParams = [];
            $paytmParams['ORDER_ID'] = $orderID;
            $paytmParams['TXN_AMOUNT'] = floatval($params['amount']);
            $paytmParams["CUST_ID"] = "1";
            $paytmParams["EMAIL"] = empty($params['email']) ? "" : $params['email'];
            $paytmParams["MID"] = $pAYTMMERCHANTMID;
            $paytmParams["CHANNEL_ID"] = "WEB";
            $paytmParams["WEBSITE"] = $pAYTMMERCHANTWEBSITE;
            $paytmParams["CALLBACK_URL"] = base_url('subscription/paytm_success');
            $paytmParams["INDUSTRY_TYPE_ID"] = $pAYTMINDUSTRYTYPE;
            $paytmChecksum = $this->paytm_kit_lib->generateSignature($paytmParams, $pAYTMMERCHANTMID);
            $paytmParams["CHECKSUMHASH"] = $paytmChecksum;
            $data = [];
            $data['paytmParams'] = $paytmParams;
            $data['transactionURL'] = $transactionURL;
            echo view('layout/paytm', $data);
        }
        return null;
    }

    public function paytm_success()
    {
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription/index');
        $config = $this->getPaymentConfig();
        $pAYTMMERCHANTKEY = $config['paytm_merchantkey'];
        $paytmChecksum = "";
        $paramList = [];
        $isValidChecksum = "FALSE";
        $paramList = $_POST;
        $paytmChecksum = $_POST["CHECKSUMHASH"] ?? "";
        $isValidChecksum = $this->paytm_kit_lib->verifySignature($paramList, $pAYTMMERCHANTKEY, $paytmChecksum);
        if ($isValidChecksum == "TRUE") {
            if ($_POST["STATUS"] == "TXN_SUCCESS") {
                $tranId = $_POST['TXNID'];
                // transition history save in database
                $params['amount'] = floatval($params['amount']);
                $params['payment_id'] = $tranId;
                $params['payment_method'] = 16;
                $this->subscriptionModel->savePaymentData($params);
                set_alert('success', translate('payment_successfull'));
                redirect($redirectUrl);
            } else {
                set_alert('error', "Something went wrong!");
                redirect($redirectUrl);
            }
        } else {
            set_alert('error', "Checksum mismatched.");
            redirect($redirectUrl);
        }
    }

    // payhere payment gateway script start
    public function payhere()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['payhere_merchant_id'] == "" && $config['payhere_merchant_secret'] == "") {
                set_alert('error', 'Payhere config not available.');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $merchantID = $config['payhere_merchant_id'];
                $orderID = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
                $currency = 'LKR';
                $merchantSecret = $config['payhere_merchant_secret'];
                $hash = strtoupper(md5($merchantID . $orderID . number_format($params['amount'], 2, '.', '') . $currency . strtoupper(md5((string) $merchantSecret))));
                $paytmParams = [];
                $paytmParams['merchant_id'] = $merchantID;
                $paytmParams['return_url'] = base_url('subscription/payhere_return');
                $paytmParams["cancel_url"] = base_url('subscription/payhere_cancel');
                $paytmParams["notify_url"] = base_url('subscription/payhere_notify');
                $paytmParams["order_id"] = $orderID;
                $paytmParams["items"] = "School subscription fees";
                $paytmParams["currency"] = "LKR";
                $paytmParams["amount"] = number_format($params['amount'], 2, '.', '');
                $paytmParams["first_name"] = $params['name'];
                $paytmParams["last_name"] = '';
                $paytmParams["email"] = $params['email'];
                $paytmParams["phone"] = $params['mobile_no'];
                $paytmParams["address"] = '';
                $paytmParams["city"] = '';
                $paytmParams["country"] = 'Sri Lanka';
                $paytmParams["hash"] = $hash;
                $data['paytmParams'] = $paytmParams;
                echo view('layout/payhere', $data);
            }
        }
    }

    public function payhere_notify()
    {
        if ($_POST !== []) {
            $config = $this->getPaymentConfig();
            $merchantId = $_POST['merchant_id'];
            $orderId = $_POST['order_id'];
            $payhereAmount = $_POST['payhere_amount'];
            $payhereCurrency = $_POST['payhere_currency'];
            $statusCode = $_POST['status_code'];
            $md5sig = $_POST['md5sig'];
            $merchantSecret = $config['payhere_merchant_secret'];
            $localMd5sig = strtoupper(md5($merchantId . $orderId . $payhereAmount . $payhereCurrency . $statusCode . strtoupper(md5((string) $merchantSecret))));
            if ($localMd5sig === $md5sig && $statusCode == 2) {
                $params = session()->get('params');
                session()->set("params", "");
                // transition history save in database
                $params['amount'] = floatval($params['amount']);
                $params['payment_id'] = $orderId;
                $params['payment_method'] = 18;
                $this->subscriptionModel->savePaymentData($params);
            }
        }
    }

    public function payhere_cancel()
    {
        set_alert('error', "Something went wrong!");
        return redirect()->to(base_url('subscription/index'));
    }

    public function payhere_return()
    {
        set_alert('success', translate('payment_successfull'));
        return redirect()->to(base_url('subscription/index'));
    }

    // Subscription.php
    public function tap()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if (empty($config['tap_secret_key']) || empty($config['tap_public_key']) || empty($config['tap_merchant_id'])) {
                set_alert('error', 'Tap config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $data = [
                    'amount' => $params['amount'],
                    'currency' => $this->subscriptionModel->getCurrency()->currency_symbol,
                    // Ensuring currency is always up-to-date from the database
                    'customer_initiated' => true,
                    'threeDSecure' => true,
                    'save_card' => false,
                    'description' => 'Subscription renewal fees',
                    'metadata' => ['udf1' => 'Metadata 1'],
                    'reference' => ['transaction' => 'txn_01', 'order' => 'ord_01'],
                    'receipt' => ['email' => true, 'sms' => false],
                    'customer' => ['first_name' => $params['name'], 'middle_name' => '', 'last_name' => $params['last_name'] ?? '', 'email' => $params['email'], 'phone' => ['country_code' => '965', 'number' => $jSONDecodeErrorwParams['mobile_no']]],
                    'merchant' => ['id' => $config['tap_merchant_id']],
                    'source' => ['id' => 'src_all'],
                    'post' => ['url' => base_url("subscription/tap_post")],
                    'redirect' => ['url' => base_url("subscription/tap_verify")],
                ];
                $url = "https://api.tap.company/v2/charges";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $headers = ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $request = curl_exec($ch);
                curl_close($ch);
                if ($request) {
                    $response = json_decode($request, true);
                    if (isset($response['transaction']['url'])) {
                        redirect($response['transaction']['url']);
                    } else {
                        set_alert('error', 'Failed to initialize Tap payment.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                } else {
                    set_alert('error', 'Error in Tap payment initialization.');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } else {
            set_alert('error', 'Payment parameters are missing.');
            redirect('subscription/index');
        }
    }

    public function tap_verify()
    {
        $tapId = $this->request->getGet('tap_id');
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription/index/' . ($params['reference_no'] ?? ''));
        if ($tapId && !empty($params)) {
            $config = $this->getPaymentConfig();
            $url = 'https://api.tap.company/v2/charges/' . $tapId;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $headers = ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json'];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $request = curl_exec($ch);
            curl_close($ch);
            if ($request) {
                $result = json_decode($request, true);
                if ($result && isset($result['status']) && $result['status'] == 'CAPTURED') {
                    // Preparing the necessary parameters
                    $params['amount'] = floatval($params['amount']);
                    // Confirm that $params['amount'] is already set properly
                    $params['payment_id'] = $tapId;
                    $params['payment_method'] = 20;
                    // Payment method identifier for Tap Payments
                    // Calling the model function to handle the data
                    $this->subscriptionModel->savePaymentData($params);
                    set_alert('success', 'Subscription renewal successful.');
                    redirect($redirectUrl);
                } else {
                    set_alert('error', "Transaction Failed");
                    redirect($redirectUrl);
                }
            } else {
                set_alert('error', "Transaction Failed");
                redirect($redirectUrl);
            }
        } else {
            set_alert('error', 'Payment verification failed or parameters are missing.');
            redirect($redirectUrl);
        }
    }

    public function getPaymentConfig()
    {
        $this->db->table('branch_id')->where();
        $builder->select('*')->from('payment_config');
        return $builder->get()->row_array();
    }
}
