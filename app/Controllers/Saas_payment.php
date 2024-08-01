<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SaasModel;
use App\Models\SaasEmailModel;
/**
 * @package : Ramom school management system (Saas)
 * @version : 3.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Saas_payment.php
 * @copyright : Reserved RamomCoder Team
 */
class Saas_payment extends MyController

{
    /**
     * @var mixed
     */
    public $Sslcommerz;

    protected $db;


    /**
     * @var App\Models\SaasModel
     */
    public $saas;

    public $load;

    /**
     * @var App\Models\SaasEmailModel
     */
    public $saasEmail;

    public $validation;

    public $input;

    public $saasModel;

    public $upload;

    public $session;

    public $paypal_payment;

    public $stripe_payment;

    public $razorpay_payment;

    public $sslcommerz;

    public $midtrans_payment;

    public $paytm_kit_lib;

    public $saas_emailModel;

    private $globalPaymentID = 9999;

    public function __construct()
    {


        $this->saas = new \App\Models\SaasModel();
        $this->paypal_payment = service('paypalPayment');
        $this->stripe_payment = service('stripePayment');
        $this->razorpay_payment = service('razorpayPayment');
        $this->Sslcommerz = service('sslcommerz');
        $this->midtrans_payment = service('midtransPayment');
        $this->saasEmail = new \App\Models\SaasEmailModel();
        $this->paytm_kit_lib = service('paytmKitLib');
        // $this->Tap_payments = service('tapPayments');
    }

    public function index($referenceNo = '')
    {
        $this->data['getSettings'] = $this->saasModel->getSettings('offline_payments');
        $this->data['payment_config'] = $this->getPaymentConfig();
        $this->data['get_school'] = $this->saasModel->getSchoolRegDetails($referenceNo);
        if (empty($this->data['get_school'])) {
            set_alert('error', "Invalid Reference.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->data['get_school']['free_trial'] == 1) {
            set_alert('error', "No payment required.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->data['get_school']['payment_status'] == 1) {
            set_alert('error', "The payment has already been paid.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->data['get_school']['payment_data'] == 'olp') {
            set_alert('error', "Your offline payment is being reviewed.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        echo view('saas_website/payment', $this->data);
    }

    public function checkout()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['payment_method' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
            $payVia = $this->request->getPost('payment_method');
            $referenceNo = $this->request->getPost('reference_no');
            $getSchoolRegDetails = $this->saasModel->getSchoolRegDetails($referenceNo);
            if ($payVia == 'olp') {
                $this->validation->setRules(['payment_type' => ["label" => translate('payment_type'), "rules" => 'trim|required']]);
                $this->validation->setRules(['date_of_payment' => ["label" => translate('date_of_payment'), "rules" => 'trim|required']]);
                $this->validation->setRules(['reference' => ["label" => translate('reference'), "rules" => 'trim|required']]);
                $this->validation->setRules(['note' => ["label" => translate('note'), "rules" => 'trim|required']]);
                $this->validation->setRules(['proof_of_payment' => ["label" => translate('proof_of_payment'), "rules" => 'callback_fileHandleUpload[proof_of_payment]']]);
            } else {
                $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['mobile_no' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
                $this->validation->setRules(['post_code' => ["label" => translate('post_code'), "rules" => 'trim|required']]);
                $this->validation->setRules(['state' => ["label" => translate('state'), "rules" => 'trim|required']]);
                $this->validation->setRules(['address' => ["label" => translate('address'), "rules" => 'trim|required']]);
            }

            if ($this->validation->run() !== false) {
                if ($payVia == 'olp') {
                    $amount = $getSchoolRegDetails['price'] - $getSchoolRegDetails['discount'];
                    $encName = null;
                    $origName = null;
                    $config = [];
                    $config['upload_path'] = 'uploads/attachments/offline_payments/';
                    $config['encrypt_name'] = true;
                    $config['allowed_types'] = '*';
                    $file = $this->request->getFile('attachment_file'); $file->initialize($config);
                    if ($this->upload->do_upload("proof_of_payment")) {
                        $origName = $this->request->getFile('attachment_file');
                        $file = $origName;
                        $file->data('orig_name');
                        $encName = $this->request->getFile('attachment_file');
                        $file = $encName;
                        $file->data('file_name');
                    }

                    $dateOfPayment = $this->request->getPost('date_of_payment');
                    $arrayFees = ['school_register_id' => $getSchoolRegDetails['id'], 'amount' => $amount, 'payment_type' => $this->request->getPost('payment_type'), 'reference' => $this->request->getPost('reference'), 'note' => $this->request->getPost('note'), 'payment_date' => date('Y-m-d', strtotime((string) $dateOfPayment)), 'submit_date' => date('Y-m-d H:i:s'), 'enc_file_name' => $encName, 'orig_file_name' => $origName, 'status' => 1];
                    $this->db->table('saas_offline_payments')->insert();
                    $arrayData = ['payment_amount' => $amount, 'payment_data' => "olp"];
                    $this->db->table('reference_no')->where();
                    $this->db->table('saas_school_register')->update();
                    $url = base_url('subscription_review/' . $referenceNo);
                } else {
                    $params = ['register_id' => $getSchoolRegDetails['id'], 'reference_no' => $referenceNo, 'amount' => $getSchoolRegDetails['price'] - $getSchoolRegDetails['discount'], 'currency' => $this->data['global_config']['currency'], 'name' => $this->request->getPost('name'), 'email' => $this->request->getPost('email'), 'mobile_no' => $this->request->getPost('mobile_no'), 'post_code' => $this->request->getPost('post_code'), 'state' => $this->request->getPost('state'), 'address' => $this->request->getPost('address'), 'payment_method' => $payVia];
                    if ($payVia == 'paypal') {
                        $params['payment_method'] = 6;
                        $url = base_url("saas_payment/paypal");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'stripe') {
                        $params['payment_method'] = 7;
                        $url = base_url("saas_payment/stripe");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'payumoney') {
                        $params['payment_method'] = 8;
                        $payerData = ['name' => $this->request->getPost('name'), 'email' => $this->request->getPost('email'), 'phone' => $this->request->getPost('mobile_no')];
                        $params['payer_data'] = $payerData;
                        $url = base_url("saas_payment/payumoney");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'paystack') {
                        $params['payment_method'] = 9;
                        $url = base_url("saas_payment/paystack");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'razorpay') {
                        $params['payment_method'] = 10;
                        $url = base_url("saas_payment/razorpay");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'sslcommerz') {
                        $params['payment_method'] = 11;
                        $params['tran_id'] = "SSLC" . uniqid();
                        $url = base_url("saas_payment/sslcommerz");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'jazzcash') {
                        $params['payment_method'] = 12;
                        $url = base_url("saas_payment/jazzcash");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'midtrans') {
                        $params['payment_method'] = 13;
                        $url = base_url("saas_payment/midtrans");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'flutterwave') {
                        $params['payment_method'] = 14;
                        $url = base_url("saas_payment/flutterwave");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'paytm') {
                        $params['payment_method'] = 16;
                        $url = base_url("saas_payment/paytm");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'toyyibpay') {
                        $params['payment_method'] = 17;
                        $url = base_url("saas_payment/toyyibpay");
                        $params['name'] = $this->request->getPost('name');
                        $params['email'] = $this->request->getPost('email');
                        $params['phone'] = $this->request->getPost('mobile_no');
                        session()->set("params", $params);
                    }

                    if ($payVia == 'payhere') {
                        $params['payment_method'] = 18;
                        $url = base_url("saas_payment/payhere");
                        session()->set("params", $params);
                    }

                    if ($payVia == 'tap') {
                        $params['payment_method'] = 20;
                        $url = base_url("saas_payment/tap");
                        session()->set("params", $params);
                    }
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
        if (!empty($params)) {
            if ($config['paypal_client_id'] == "" || $config['paypal_client_secret'] == "") {
                set_alert('error', 'PayPal configuration not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $sandbox = $config['paypal_sandbox'] == 1;
                $data = ['cancelUrl' => base_url('saas_payment/cancel_payment'), 'returnUrl' => base_url('saas_payment/success_payment'), 'reference_no' => $params['reference_no'], 'name' => $params['name'], 'description' => "School Subscription fees deposit via PayPal, Reference No - " . $params['reference_no'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
                // Initialize PayPal with client ID, secret, and sandbox mode
                $this->paypal_payment->initialize($config['paypal_client_id'], $config['paypal_client_secret'], $sandbox);
                $response = $this->paypal_payment->payment($data);
                if ($response && isset($response['status']) && $response['status'] == 'redirect') {
                    redirect($response['url']);
                } else {
                    log_message('error', 'PayPal payment error: ' . json_encode($response));
                    set_alert('error', 'Payment error: ' . json_encode($response));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } else {
            set_alert('error', 'Payment parameters are missing.');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function success_payment()
    {
        $paymentId = $this->request->getGet('paymentId');
        $payerId = $this->request->getGet('PayerID');
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
        if ($paymentId && $payerId && !empty($params)) {
            $config = $this->getPaymentConfig();
            $this->paypal_payment->initialize($config['paypal_client_id'], $config['paypal_client_secret'], $config['paypal_sandbox']);
            $response = $this->paypal_payment->success($paymentId, $payerId);
            // Log the response for debugging
            log_message('info', 'PayPal verification response: ' . json_encode($response));
            if (isset($response['state']) && $response['state'] == 'approved') {
                $refId = $response['transactions'][0]['related_resources'][0]['sale']['id'];
                $amount = floatval($response['transactions'][0]['amount']['total']);
                $currency = $response['transactions'][0]['amount']['currency'];
                $arrayFees = ['data' => $params, 'amount' => $amount, 'txn_id' => $refId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                redirect($redirectUrl);
            } else {
                set_alert('error', "Transaction Failed");
                redirect($redirectUrl);
            }
        } else {
            set_alert('error', 'Payment verification failed or parameters are missing.');
            redirect($redirectUrl);
        }
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
                $data = ['imagesURL' => base_url('uploads/app_image/logo.png'), 'success_url' => base_url("saas_payment/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'cancel_url' => base_url("saas_payment/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'amount' => $params['amount'], 'description' => "School Subscription fees deposit via Stripe, Reference No - " . $params['reference_no']];
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
            // null session data
            session()->set("params", "");
            try {
                $this->stripe_payment->initialize($this->globalPaymentID);
                $response = $this->stripe_payment->verify($sessionId);
                if (isset($response->payment_status) && $response->payment_status == 'paid') {
                    $amount = floatval($response->amount_total) / 100;
                    $refId = $response->payment_intent;
                    // payment info update in invoice
                    $arrayFees = ['data' => $params, 'amount' => $amount, 'txn_id' => $refId, 'date' => date("Y-m-d H:i:s")];
                    $this->savePaymentData($arrayFees);
                    $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                    session()->set_flashdata('success', $success);
                    return redirect()->to(base_url('subscription_review/' . $params['reference_no']));
                }

                // payment failed: display message to customer
                set_alert('error', "Something went wrong!");
                return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
            } catch (\Exception $ex) {
                set_alert('error', $ex->getMessage());
                return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
            }
        }

        return null;
    }

    public function paystack()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['paystack_secret_key'] == "") {
                set_alert('error', 'Paystack config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $result = [];
                $amount = $params['amount'] * 100;
                $ref = app_generate_hash();
                $callbackUrl = base_url() . 'saas_payment/verify_paystack_payment/' . $ref;
                $postdata = ['email' => $params['email'], 'amount' => $amount, "reference" => $ref, "callback_url" => $callbackUrl];
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
                        $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $ref, 'date' => date("Y-m-d H:i:s")];
                        $this->savePaymentData($arrayFees);
                        $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                        session()->set_flashdata('success', $success);
                        return redirect()->to(base_url('subscription_review/' . $params['reference_no']));
                    }

                    // the transaction was not successful, do not deliver value'
                    // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                    set_alert('error', "Transaction Failed");
                    return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
                }

                //echo $result['message'];
                set_alert('error', "Transaction Failed");
                return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
            }

            //print_r($result);
            //die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
        }

        //var_dump($request);
        //die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
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
                // payumoney details
                $studentID = $params['reference_no'];
                $amount = floatval($params['amount']);
                $payerName = $params['name'];
                $payerEmail = $params['email'];
                $payerPhone = $params['mobile_no'];
                $productInfo = "Online Admission fees deposit. Student Id - " . $studentID;
                // redirect url
                $success = base_url('saas_payment/payumoney_success');
                $fail = base_url('saas_payment/payumoney_success');
                // generate transaction id
                $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
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
                    // payment info update in invoice
                    $arrayFees = ['data' => $params, 'amount' => floatval($this->request->getPost('amount')), 'txn_id' => $mihpayid, 'date' => date("Y-m-d H:i:s")];
                    $this->savePaymentData($arrayFees);
                    $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                    session()->set_flashdata('success', $success);
                    return redirect()->to(base_url('subscription_review/' . $params['reference_no']));
                }

                set_alert('error', translate('invalid_transaction'));
                return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
            }

            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
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
                $params['invoice_no'] = $params['reference_no'];
                $params['fine'] = 0;
                $this->razorpay_payment->initialize($this->globalPaymentID);
                $response = $this->razorpay_payment->payment($params);
                $params['razorpay_order_id'] = $response;
                session()->set("params", $params);
                $arrayData = ['key' => $config['razorpay_key_id'], 'amount' => $params['amount'] * 100, 'name' => $params['name'], 'description' => "School Subscription fees deposit Reference No - " . $params['reference_no'], 'image' => base_url('uploads/app_image/logo-small.png'), 'currency' => 'INR', 'order_id' => $params['razorpay_order_id'], 'theme' => ["color" => "#F37254"]];
                $data['return_url'] = base_url('userrole/invoice');
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
                // payment info update in invoice
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $attributes['razorpay_payment_id'], 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                return redirect()->to(base_url('subscription_review/' . $params['reference_no']));
            }

            set_alert('error', $response);
            return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
        }

        set_alert('error', "Payment Cancelled");
        return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
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
                $postData['success_url'] = base_url('saas_payment/sslcommerz_success');
                $postData['fail_url'] = base_url('saas_payment/sslcommerz_success');
                $postData['cancel_url'] = base_url('saas_payment/sslcommerz_success');
                $postData['ipn_url'] = base_url() . "ipn";
                # CUSTOMER INFORMATION
                $postData['cus_name'] = $params['name'];
                $postData['cus_email'] = $params['email'];
                $postData['cus_add1'] = $params['address'];
                $postData['cus_city'] = $params['state'];
                $postData['cus_state'] = $params['state'];
                $postData['cus_postcode'] = $params['post_code'];
                $postData['cus_country'] = "Bangladesh";
                $postData['cus_phone'] = $params['mobile_no'];
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
        if ($_POST['status'] == 'VALID' && $params['tran_id'] == $_POST['tran_id'] && $params['amount'] == $_POST['currency_amount']) {
            $this->sslcommerz->initialize($this->globalPaymentID);
            if ($this->sslcommerz->ValidateResponse($_POST['currency_amount'], "BDT", $_POST)) {
                $tranId = $params['tran_id'];
                $arrayFees = ['data' => $params, 'amount' => floatval($_POST['currency_amount']), 'txn_id' => $tranId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                return redirect()->to(base_url('subscription_review/' . $params['reference_no']));
            }
        } else {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
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
                $postData = ["pp_Version" => "2.0", "pp_TxnType" => "MPAY", "pp_Language" => "EN", "pp_IsRegisteredCustomer" => "Yes", "pp_TokenizedCardNumber" => "", "pp_CustomerEmail" => "", "pp_CustomerMobile" => "", "pp_CustomerID" => uniqid(), "pp_MerchantID" => $config['jazzcash_merchant_id'], "pp_Password" => $config['jazzcash_passwd'], "pp_TxnRefNo" => $ppTxnRefNo, "pp_Amount" => floatval($params['amount']) * 100, "pp_DiscountedAmount" => "", "pp_DiscountBank" => "", "pp_TxnCurrency" => "PKR", "pp_TxnDateTime" => date('YmdHis'), "pp_BillReference" => uniqid(), "pp_Description" => "School Subscription fees deposit Reference No - " . $params['invoice_no'], "pp_TxnExpiryDateTime" => date('YmdHis', strtotime("+1 hours")), "pp_ReturnURL" => base_url('saas_payment/jazzcash_success'), "ppmpf_1" => "1", "ppmpf_2" => "2", "ppmpf_3" => "3", "ppmpf_4" => "4", "ppmpf_5" => "5"];
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
            $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $tranId, 'date' => date("Y-m-d H:i:s")];
            $this->savePaymentData($arrayFees);
            $success = "Thank you for submitting the online registration form. Please you can print this copy.";
            session()->set_flashdata('success', $success);
            return redirect()->to(base_url('subscription_review/' . $params['reference_no']));
        }

        if ($_POST['pp_ResponseCode'] == '112') {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));
        }
        set_alert('error', $_POST['pp_ResponseMessage']);
        return redirect()->to(base_url('saas_payment/index/' . $params['reference_no']));

        return null;
    }

    public function midtrans()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['midtrans_client_key'] == "" || $config['midtrans_server_key'] == "") {
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $tranId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                $url = base_url('subscription_review/' . $params['reference_no']);
            } else {
                $url = base_url('saas_payment/index/' . $params['reference_no']);
                set_alert('error', "Something went wrong!");
            }

            echo json_encode(['url' => $url]);
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
                $callbackUrl = base_url('saas_payment/verify_flutterwave_payment');
                $data = ['name' => $params['name'], 'amount' => $amount, 'customer_email' => $params['email'], 'currency' => $params['currency'], "txref" => $txref, "pubKey" => $config['flutterwave_public_key'], "redirect_url" => $callbackUrl];
                echo view('layout/flutterwave', $data);
            }
        }
    }

    public function verify_flutterwave_payment()
    {
        $params = session()->get('params');
        $config = $this->getPaymentConfig();
        session()->set("params", "");
        if (empty($params)) {
            redirect(base_url());
        }

        $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
        if (isset($_GET['cancelled']) && $_GET['cancelled'] == 'true') {
            set_alert('error', "Payment Cancelled");
            redirect($redirectUrl);
        }

        if (isset($_GET['tx_ref'])) {
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $params['txref'], 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
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

    // toyyibpay payment gateway script start
    public function toyyibpay()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['toyyibpay_secretkey'] == "" && $config['toyyibpay_categorycode'] == "") {
                set_alert('error', 'toyyibPay config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $paymentData = ['userSecretKey' => $config['toyyibpay_secretkey'], 'categoryCode' => $config['toyyibpay_categorycode'], 'billName' => 'School Subscription fees', 'billDescription' => 'School Subscription fees', 'billPriceSetting' => 1, 'billPayorInfo' => 1, 'billAmount' => floatval($params['amount']) * 100, 'billReturnUrl' => base_url('saas_payment/toyyibpay_success'), 'billCallbackUrl' => base_url('saas_payment/toyyibpay_callbackurl'), 'billExternalReferenceNo' => substr(hash('sha256', mt_rand() . microtime()), 0, 20), 'billTo' => $params['name'], 'billEmail' => $params['email'], 'billPhone' => $params['mobile_no'], 'billSplitPayment' => 0, 'billSplitPaymentArgs' => '', 'billPaymentChannel' => '0', 'billContentEmail' => 'Thank you for pay subscription fees', 'billChargeToCustomer' => 1];
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
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }

    public function toyyibpay_success()
    {
        if ($_GET['status_id'] == 1 && !empty($_GET['billcode'])) {
            $params = session()->get('params');
            session()->set("params", "");
            $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $refno, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
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
                redirect($_SERVER['HTTP_REFERER']);
            } else {
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
                $paytmParams["CALLBACK_URL"] = base_url('saas_payment/paytm_success');
                $paytmParams["INDUSTRY_TYPE_ID"] = $pAYTMINDUSTRYTYPE;
                $paytmChecksum = $this->paytm_kit_lib->generateSignature($paytmParams, $pAYTMMERCHANTMID);
                $paytmParams["CHECKSUMHASH"] = $paytmChecksum;
                $data = [];
                $data['paytmParams'] = $paytmParams;
                $data['transactionURL'] = $transactionURL;
                echo view('layout/paytm', $data);
            }
        }
    }

    public function paytm_success()
    {
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $tranId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
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
                $paytmParams['return_url'] = base_url('saas_payment/payhere_return');
                $paytmParams["cancel_url"] = base_url('saas_payment/payhere_cancel');
                $paytmParams["notify_url"] = base_url('saas_payment/payhere_notify');
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
                $arrayFees = ['data' => $params, 'amount' => floatval($payhereAmount), 'txn_id' => $orderId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
            }
        }
    }

    public function payhere_cancel()
    {
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
        set_alert('error', "Something went wrong!");
        redirect($redirectUrl);
    }

    public function payhere_return()
    {
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
        set_alert('success', translate('payment_successfull'));
        redirect($redirectUrl);
    }

    //fahad new - working
    // Add this block right after the PayHere functions
    public function tap()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if (empty($config['tap_secret_key']) || empty($config['tap_public_key']) || empty($config['tap_merchant_id'])) {
                set_alert('error', 'Tap config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $data = ['amount' => $params['amount'], 'currency' => $params['currency'], 'customer_initiated' => true, 'threeDSecure' => true, 'save_card' => false, 'description' => 'School subscription fees', 'metadata' => ['udf1' => 'Metadata 1'], 'reference' => ['transaction' => 'txn_01', 'order' => 'ord_01'], 'receipt' => ['email' => true, 'sms' => false], 'customer' => ['first_name' => $params['name'], 'middle_name' => '', 'last_name' => $params['last_name'] ?? '', 'email' => $params['email'], 'phone' => ['country_code' => '965', 'number' => $params['mobile_no']]], 'merchant' => ['id' => $config['tap_merchant_id']], 'source' => ['id' => 'src_all'], 'post' => ['url' => base_url("saas_payment/tap_post")], 'redirect' => ['url' => base_url("saas_payment/tap_verify")]];
                $url = "https://api.tap.company/v2/charges";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                // Post Fields
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $headers = ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $request = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                // Get HTTP status code
                curl_close($ch);
                // Log the response for debugging
                log_message('info', 'Tap payment response: ' . $request);
                log_message('info', 'HTTP Code: ' . $httpCode);
                // Log the HTTP status code
                if ($httpCode == 200 && $request) {
                    $response = json_decode($request, true);
                    if (isset($response['transaction']['url'])) {
                        $redir = $response['transaction']['url'];
                        header("Location: " . $redir);
                    } else {
                        // Log the response in case of error
                        log_message('error', 'Tap payment error response: ' . json_encode($response));
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
            redirect('saas_payment/index');
        }
    }

    public function tap_verify()
    {
        $tapId = $this->request->getGet('tap_id');
        $params = session()->get('params');
        session()->set("params", "");
        $redirectUrl = base_url('subscription_review/' . $params['reference_no']);
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
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // Get HTTP status code
            curl_close($ch);
            // Log the response for debugging
            log_message('info', 'Tap verification response: ' . $request);
            log_message('info', 'HTTP Code: ' . $httpCode);
            // Log the HTTP status code
            if ($httpCode == 200 && $request) {
                $result = json_decode($request, true);
                if ($result && isset($result['status']) && $result['status'] == 'CAPTURED') {
                    $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'txn_id' => $tapId, 'date' => date("Y-m-d H:i:s")];
                    $this->savePaymentData($arrayFees);
                    $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                    session()->set_flashdata('success', $success);
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

    private function savePaymentData($data)
    {
        if (!empty($data)) {
            // payer details json encode
            $referenceNo = $data['data']['reference_no'];
            $paymentDetails = ['name' => $data['data']['name'], 'email' => $data['data']['email'], 'post_code' => $data['data']['post_code'], 'state' => $data['data']['state'], 'address' => $data['data']['address'], 'payment_method' => $data['data']['payment_method'], 'txn_id' => $data['txn_id'], 'date' => $data['date']];
            // insert in DB
            $arrayData = ['payment_status' => 1, 'payment_amount' => $data['amount'], 'payment_data' => json_encode($paymentDetails)];
            $this->db->table('reference_no')->where();
            $this->db->table('saas_school_register')->update();
            //automatic subscription approval
            $getSettings = $this->saasModel->getSettings();
            if ($getSettings->automatic_approval == 1) {
                $this->saasModel->automaticSubscriptionApproval($data['data']['register_id'], $this->data['global_config']['currency'], $this->data['global_config']['currency_symbol']);
            }

            // send email school subscription payment confirmation
            $schoolRegDetails = $this->saasModel->getSchoolRegDetails($referenceNo);
            $schoolRegDetails['date'] = _d($paymentDetails['date']);
            $schoolRegDetails['paid_amount'] = number_format($data['amount'], 2, '.', '');
            $schoolRegDetails['invoice_url'] = base_url('subscription_review/' . $referenceNo);
            $this->saas_emailModel->sentSchoolSubscriptionPaymentConfirmation($schoolRegDetails);
        }
    }

    public function getPaymentConfig()
    {
        $this->db->table('branch_id')->where();
        $builder->select('*')->from('payment_config');
        return $builder->get()->row_array();
    }

    public function getTypeInstruction()
    {
        if ($_POST !== []) {
            $typeID = $this->request->getPost('typeID');
            if (empty($typeID)) {
                echo null;
                exit;
            }

            $r = $db->table('saas_offline_payment_types')->get('saas_offline_payment_types')->row();
            if (!empty($r->note)) {
                echo $r->note;
            } else {
                echo "";
            }
        }
    }
}
