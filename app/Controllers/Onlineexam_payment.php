<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\UserroleModel;
use App\Models\FeesModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Onlineexam_payment.php
 * @copyright : Reserved RamomCoder Team
 */
class Onlineexam_payment extends AdminController
{
    /**
     * @var mixed
     */
    public $Sslcommerz;

    /**
     * @var mixed
     */
    public $Tap_payments;

    public $tapPayments;

    public $stripePayment;

    public $razorpayPayment;

    public $paytmKitLib;

    public $paypalPayment;

    public $midtransPayment;

    public $appLib;

    /**
     * @var App\Models\UserroleModel
     */
    public $userrole;

    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    public $load;

    public $input;

    public $validation;

    public $userroleModel;

    public $session;

    public $paypal_payment;

    public $applicationModel;

    public $stripe_payment;

    public $razorpay_payment;

    public $sslcommerz;

    public $midtrans_payment;

    public $paytm_kit_lib;

    public $paymentModel;

    public $db;

    public function __construct()
    {
        parent::__construct();








        $this->tapPayments = service('tapPayments');$this->stripePayment = service('stripePayment');$this->sslcommerz = service('sslcommerz');$this->razorpayPayment = service('razorpayPayment');$this->paytmKitLib = service('paytmKitLib');$this->paypalPayment = service('paypalPayment');$this->midtransPayment = service('midtransPayment');$this->appLib = service('appLib'); 
$this->userrole = new \App\Models\UserroleModel();
        $this->fees = new \App\Models\FeesModel();
        $this->paypal_payment = service('paypalPayment');
        $this->stripe_payment = service('stripePayment');
        $this->razorpay_payment = service('razorpayPayment');
        $this->Sslcommerz = service('sslcommerz');
        $this->midtrans_payment = service('midtransPayment');
        $this->paytm_kit_lib = service('paytmKitLib');
        $this->Tap_payments = service('tapPayments');
    }

    public function checkout()
    {
        if (!is_student_loggedin()) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $examID = $this->request->getPost('exam_id');
            $payVia = $this->request->getPost('pay_via');
            $this->validation->setRules(['exam_id' => ["label" => translate('exam_id'), "rules" => 'trim|required']]);
            if ($payVia == 'payumoney') {
                $this->validation->setRules(['payer_name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['phone' => ["label" => translate('phone'), "rules" => 'trim|required']]);
            }

            if ($payVia == 'toyyibpay' || $payVia == 'payhere') {
                $this->validation->setRules(['toyyibpay_email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['toyyibpay_phone' => ["label" => translate('phone'), "rules" => 'trim|required']]);
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
                $stu = $this->userroleModel->getStudentDetails();
                $onlineExam = $this->userroleModel->getSingle('online_exam', $examID, true);
                $params = ['student_id' => $stu['student_id'], 'student_name' => $stu['fullname'], 'student_email' => $stu['student_email'], 'register_no' => $stu['register_no'], 'exam_id' => $onlineExam->id, 'amount' => $onlineExam->fee, 'currency' => $this->data['global_config']['currency']];
                if ($payVia == 'paypal') {
                    $url = base_url("onlineexam_payment/paypal");
                    session()->set("params", $params);
                }

                if ($payVia == 'stripe') {
                    $url = base_url("onlineexam_payment/stripe");
                    session()->set("params", $params);
                }

                if ($payVia == 'payumoney') {
                    $payerData = ['name' => $this->request->getPost('payer_name'), 'email' => $this->request->getPost('email'), 'phone' => $this->request->getPost('phone')];
                    $params['payer_data'] = $payerData;
                    $url = base_url("onlineexam_payment/payumoney");
                    session()->set("params", $params);
                }

                if ($payVia == 'paystack') {
                    $url = base_url("onlineexam_payment/paystack");
                    session()->set("params", $params);
                }

                if ($payVia == 'razorpay') {
                    $url = base_url("onlineexam_payment/razorpay");
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
                    $url = base_url("onlineexam_payment/sslcommerz");
                    session()->set("params", $params);
                }

                if ($payVia == 'jazzcash') {
                    $url = base_url("onlineexam_payment/jazzcash");
                    session()->set("params", $params);
                }

                if ($payVia == 'midtrans') {
                    $url = base_url("onlineexam_payment/midtrans");
                    session()->set("params", $params);
                }

                if ($payVia == 'flutterwave') {
                    $url = base_url("onlineexam_payment/flutterwave");
                    session()->set("params", $params);
                }

                if ($payVia == 'paytm') {
                    $url = base_url("onlineexam_payment/paytm");
                    session()->set("params", $params);
                }

                if ($payVia == 'toyyibpay') {
                    $url = base_url("onlineexam_payment/toyyibpay");
                    $params['payer_email'] = $this->request->getPost('toyyibpay_email');
                    $params['payer_phone'] = $this->request->getPost('toyyibpay_phone');
                    session()->set("params", $params);
                }

                if ($payVia == 'payhere') {
                    $url = base_url("onlineexam_payment/payhere");
                    $params['payer_email'] = $this->request->getPost('toyyibpay_email');
                    $params['payer_phone'] = $this->request->getPost('toyyibpay_phone');
                    session()->set("params", $params);
                }

                if ($payVia == 'nepalste') {
                    $url = base_url("onlineexam_payment/nepalste");
                    session()->set("params", $params);
                }

                if ($payVia == 'tap') {
                    $url = base_url("onlineexam_payment/tap");
                    $params['student_name'] = $this->request->getPost('tap_name');
                    $params['email'] = $this->request->getPost('tap_email');
                    $params['mobile_no'] = $this->request->getPost('tap_phone');
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
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['paypal_username'] == "" || $config['paypal_password'] == "" || $config['paypal_signature'] == "") {
                set_alert('error', 'Paypal config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $data = ['cancelUrl' => base_url('onlineexam_payment/getsuccesspayment'), 'returnUrl' => base_url('onlineexam_payment/getsuccesspayment'), 'name' => $params['student_name'], 'description' => "Online Exam fees deposit. Student Register No - " . $params['register_no'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
                $response = $this->paypal_payment->payment($data);
                if ($response->isSuccessful()) {
                } elseif ($response->isRedirect()) {
                    $response->redirect();
                } else {
                    echo $response->getMessage();
                }
            }
        }
    }

    /* paypal successpayment redirect */
    public function getsuccesspayment()
    {
        $params = session()->get('params');
        if (!empty($params)) {
            // null session data
            session()->set("params", "");
            $data = ['name' => $params['student_name'], 'description' => "Online Exam fees deposit. Student Register No - " . $params['register_no'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
            $response = $this->paypal_payment->success($data);
            $paypalResponse = $response->getData();
            if ($response->isSuccessful()) {
                $purchaseId = $_GET['PayerID'];
                if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success' && $purchaseId) {
                    $refId = $paypalResponse['PAYMENTINFO_0_TRANSACTIONID'];
                    // payment info update in invoice
                    $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($paypalResponse['PAYMENTINFO_0_AMT']), 'transaction_id' => "Fees deposits online via Paypal Ref ID: " . $refId, 'created_at' => date('Y-m-d H:i:s')];
                    $this->savePaymentData();
                    set_alert('success', translate('payment_successfull'));
                    return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
                }
            } elseif ($response->isRedirect()) {
                $response->redirect();
            } else {
                set_alert('error', translate('payment_cancelled'));
                return redirect()->to(base_url('userrole/online_exam'));
            }
        }

        return null;
    }

    public function stripe()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['stripe_secret'] == "") {
                set_alert('error', 'Stripe config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $data = ['imagesURL' => $this->applicationModel->getBranchImage(get_loggedin_branch_id(), 'logo-small'), 'success_url' => base_url("onlineexam_payment/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'cancel_url' => base_url("onlineexam_payment/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'name' => $params['student_name'], 'description' => "Online Exam fees deposit. Student Register No - " . $params['register_no'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
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
                $response = $this->stripe_payment->verify($sessionId);
                if (isset($response->payment_status) && $response->payment_status == 'paid') {
                    $amount = floatval($response->amount_total) / 100;
                    $refId = $response->payment_intent;
                    // payment info update in invoice
                    $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => $amount, 'transaction_id' => "Fees deposits online via Stripe Ref ID: " . $refId, 'created_at' => date('Y-m-d H:i:s')];
                    $this->savePaymentData();
                    set_alert('success', translate('payment_successfull'));
                    return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
                }

                // payment failed: display message to customer
                set_alert('error', "Something went wrong!");
                return redirect()->to(base_url('userrole/online_exam'));
            } catch (\Exception $ex) {
                set_alert('error', $ex->getMessage());
                redirect(site_url('userrole/online_exam'));
            }
        }

        return null;
    }

    public function paystack()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['paystack_secret_key'] == "") {
                set_alert('error', 'Paystack config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $result = [];
                $amount = $params['amount'] * 100;
                $ref = app_generate_hash();
                $callbackUrl = base_url() . 'onlineexam_payment/verify_paystack_payment/' . $ref;
                $postdata = ['email' => $params['student_email'], 'amount' => $amount, "reference" => $ref, "callback_url" => $callbackUrl];
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
        $config = $this->get_payment_config();
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
                        // payment info update in invoice
                        $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => $params['amount'], 'transaction_id' => "Fees deposits online via Paystack Ref ID: " . $ref, 'created_at' => date('Y-m-d H:i:s')];
                        $this->savePaymentData();
                        set_alert('success', translate('payment_successfull'));
                        return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
                    }

                    // the transaction was not successful, do not deliver value'
                    // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                    set_alert('error', "Transaction Failed");
                    return redirect()->to(base_url('userrole/online_exam'));
                }

                //echo $result['message'];
                set_alert('error', "Transaction Failed");
                return redirect()->to(base_url('userrole/online_exam'));
            }

            //print_r($result);
            //die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        //var_dump($request);
        //die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('userrole/online_exam'));
    }

    /* PayUmoney Payment */
    public function payumoney()
    {
        $config = $this->get_payment_config();
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
                $amount = floatval($params['amount']);
                $payerName = $params['payer_data']['name'];
                $payerEmail = $params['payer_data']['email'];
                $payerPhone = $params['payer_data']['phone'];
                $productInfo = "Online Exam fees deposit. Student Register No - " . $params['register_no'];
                // redirect url
                $success = base_url('onlineexam_payment/payumoney_success');
                $fail = base_url('onlineexam_payment/payumoney_success');
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
                    $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => $this->request->getPost('amount'), 'transaction_id' => "Fees deposits online via PayU TXN ID: " . $txnId . " / PayU Ref ID: " . $mihpayid, 'created_at' => date('Y-m-d H:i:s')];
                    $this->savePaymentData();
                    set_alert('success', translate('payment_successfull'));
                    return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
                }

                set_alert('error', translate('invalid_transaction'));
                return redirect()->to(base_url('userrole/online_exam'));
            }

            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        return null;
    }

    public function razorpay()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['razorpay_key_id'] == "" || $config['razorpay_key_secret'] == "") {
                set_alert('error', 'Razorpay config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $params['invoice_no'] = $params['register_no'];
                $params['fine'] = 0;
                $response = $this->razorpay_payment->payment($params);
                $params['razorpay_order_id'] = $response;
                session()->set("params", $params);
                $arrayData = ['key' => $config['razorpay_key_id'], 'amount' => $params['amount'] * 100, 'name' => $params['student_name'], 'description' => "Submitting student fees online. Student ID - " . $params['student_id'], 'image' => base_url('uploads/app_image/logo-small.png'), 'currency' => 'INR', 'order_id' => $params['razorpay_order_id'], 'theme' => ["color" => "#F37254"]];
                $data['return_url'] = base_url('userrole/online_exam');
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
            $response = $this->razorpay_payment->verify($attributes);
            if ($response == true) {
                // payment info update in invoice
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => $params['amount'], 'transaction_id' => "Fees deposits online via Razorpay TxnID: " . $attributes['razorpay_payment_id'], 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
            }

            set_alert('error', $response);
            return redirect()->to(base_url('userrole/online_exam'));
        }

        return null;
    }

    public function sslcommerz()
    {
        $config = $this->get_payment_config();
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
                $postData['success_url'] = base_url('onlineexam_payment/sslcommerz_success');
                $postData['fail_url'] = base_url('onlineexam_payment/sslcommerz_success');
                $postData['cancel_url'] = base_url('onlineexam_payment/sslcommerz_success');
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
                $this->sslcommerz->RequestToSSLC($postData);
            }
        }
    }

    /* sslcommerz successpayment redirect */
    public function sslcommerz_success()
    {
        $params = session()->get('params');
        if ($_POST['status'] == 'VALID' && $params['tran_id'] == $_POST['tran_id']) {
            if ($this->sslcommerz->ValidateResponse($_POST['currency_amount'], "BDT", $_POST)) {
                $tranId = $params['tran_id'];
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($_POST['currency_amount']), 'transaction_id' => "Fees deposits online via SSLcommerz TXN ID: " . $tranId, 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
            }
        } else {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        return null;
    }

    public function jazzcash()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['jazzcash_merchant_id'] == "" || $config['jazzcash_passwd'] == "" || $config['jazzcash_integerity_salt'] == "") {
                set_alert('error', 'Jazzcash config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $integeritySalt = $config['jazzcash_integerity_salt'];
                $ppTxnRefNo = 'T' . date('YmdHis');
                $postData = ["pp_Version" => "2.0", "pp_TxnType" => "MPAY", "pp_Language" => "EN", "pp_IsRegisteredCustomer" => "Yes", "pp_TokenizedCardNumber" => "", "pp_CustomerEmail" => "", "pp_CustomerMobile" => "", "pp_CustomerID" => uniqid(), "pp_MerchantID" => $config['jazzcash_merchant_id'], "pp_Password" => $config['jazzcash_passwd'], "pp_TxnRefNo" => $ppTxnRefNo, "pp_Amount" => floatval($params['amount']) * 100, "pp_DiscountedAmount" => "", "pp_DiscountBank" => "", "pp_TxnCurrency" => "PKR", "pp_TxnDateTime" => date('YmdHis'), "pp_BillReference" => uniqid(), "pp_Description" => "Submitting student fees online. Student ID - " . $params['student_id'], "pp_TxnExpiryDateTime" => date('YmdHis', strtotime("+1 hours")), "pp_ReturnURL" => base_url('onlineexam_payment/jazzcash_success'), "ppmpf_1" => "1", "ppmpf_2" => "2", "ppmpf_3" => "3", "ppmpf_4" => "4", "ppmpf_5" => "5"];
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
            $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($params['amount']), 'transaction_id' => "Fees deposits online via JazzCash TXN ID: " . $tranId, 'created_at' => date('Y-m-d H:i:s')];
            $this->savePaymentData();
            set_alert('success', translate('payment_successfull'));
            return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
        }

        if ($_POST['pp_ResponseCode'] == '112') {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('userrole/online_exam'));
        }
        set_alert('error', $_POST['pp_ResponseMessage']);
        return redirect()->to(base_url('userrole/online_exam'));

        return null;
    }

    public function midtrans()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['midtrans_client_key'] == "" && $config['midtrans_server_key'] == "") {
                set_alert('error', 'Stripe config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $amount = number_format($params['amount'], 2, '.', '');
                $orderID = random_int(0, mt_getrandmax());
                $params['orderID'] = $orderID;
                session()->set("params", $params);
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
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($params['amount']), 'transaction_id' => "Fees deposits online via Midtrans TXN ID: " . $tranId, 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
                set_alert('success', translate('payment_successfull'));
            } else {
                set_alert('error', "Something went wrong!");
            }

            echo json_encode(['url' => base_url('userrole/onlineexam_take/' . $params['exam_id'])]);
        }
    }

    public function flutterwave()
    {
        $config = $this->get_payment_config();
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
                $callbackUrl = base_url('onlineexam_payment/verify_flutterwave_payment');
                $data = ['student_name' => $params['student_name'], 'amount' => $amount, 'customer_email' => $params['student_email'], 'currency' => $params['currency'], "txref" => $txref, "pubKey" => $config['flutterwave_public_key'], "redirect_url" => $callbackUrl];
                echo view('layout/flutterwave', $data);
            }
        }
    }

    public function verify_flutterwave_payment()
    {
        if (isset($_GET['cancelled']) && $_GET['cancelled'] == 'true') {
            set_alert('error', "Payment Cancelled");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        if (isset($_GET['tx_ref'])) {
            $config = $this->get_payment_config();
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
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($params['amount']), 'transaction_id' => "Fees deposits online via FlutterWave TXREF: " . $params['txref'], 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
            }

            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('userrole/online_exam'));
    }

    //Paytm payment gateway script start
    public function paytm()
    {
        $config = $this->get_payment_config();
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
                $paytmParams["CUST_ID"] = get_loggedin_user_id();
                $paytmParams["EMAIL"] = empty($params['student_email']) ? "" : $params['student_email'];
                $paytmParams["MID"] = $pAYTMMERCHANTMID;
                $paytmParams["CHANNEL_ID"] = "WEB";
                $paytmParams["WEBSITE"] = $pAYTMMERCHANTWEBSITE;
                $paytmParams["CALLBACK_URL"] = base_url('onlineexam_payment/paytm_success');
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
        $config = $this->get_payment_config();
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
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($params['amount']), 'transaction_id' => "Fees deposits online via Paytm TXREF: " . $tranId, 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
                set_alert('success', translate('payment_successfull'));
                return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
            }

            set_alert('error', "Something went wrong!");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        set_alert('error', "Checksum mismatched.");
        return redirect()->to(base_url('userrole/online_exam'));
    }

    // toyyibpay payment gateway script start
    public function toyyibpay()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['toyyibpay_secretkey'] == "" && $config['toyyibpay_categorycode'] == "") {
                set_alert('error', 'toyyibPay config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $paymentData = ['userSecretKey' => $config['toyyibpay_secretkey'], 'categoryCode' => $config['toyyibpay_categorycode'], 'billName' => 'School Fee', 'billDescription' => 'Student Fee', 'billPriceSetting' => 1, 'billPayorInfo' => 1, 'billAmount' => floatval($params['amount']) * 100, 'billReturnUrl' => base_url('onlineexam_payment/toyyibpay_success'), 'billCallbackUrl' => base_url('onlineexam_payment/toyyibpay_callbackurl'), 'billExternalReferenceNo' => substr(hash('sha256', mt_rand() . microtime()), 0, 20), 'billTo' => $params['student_name'], 'billEmail' => $params['payer_email'], 'billPhone' => $params['payer_phone'], 'billSplitPayment' => 0, 'billSplitPaymentArgs' => '', 'billPaymentChannel' => '0', 'billContentEmail' => 'Thank you for pay online exam fees', 'billChargeToCustomer' => 1];
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
                if (!empty($obj)) {
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
        $params = session()->get('params');
        if ($_GET['status_id'] == 1) {
            session()->set("params", "");
            set_alert('success', translate('payment_successfull'));
            return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
        }

        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('userrole/online_exam'));
    }

    public function toyyibpay_callbackurl()
    {
        if (!empty($_POST['status']) && $_POST['status'] == 1) {
            $refno = $_POST['refno'];
            $params = session()->get('params');
            $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($params['amount']), 'transaction_id' => "Fees deposits online via toyyibPay TXREF: " . $refno, 'created_at' => date('Y-m-d H:i:s')];
            $this->savePaymentData();
        }
    }

    // payhere payment gateway script start
    public function payhere()
    {
        $config = $this->get_payment_config();
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
                $paytmParams['return_url'] = base_url('onlineexam_payment/payhere_return');
                $paytmParams["cancel_url"] = base_url('onlineexam_payment/payhere_cancel');
                $paytmParams["notify_url"] = base_url('onlineexam_payment/payhere_notify');
                $paytmParams["order_id"] = $orderID;
                $paytmParams["items"] = "School online exam fees";
                $paytmParams["currency"] = "LKR";
                $paytmParams["amount"] = floatval($params['amount']);
                $paytmParams["first_name"] = $params['student_name'];
                $paytmParams["last_name"] = '';
                $paytmParams["email"] = $params['payer_email'];
                $paytmParams["phone"] = $params['payer_phone'];
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
            $config = $this->get_payment_config();
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
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => floatval($params['amount']), 'transaction_id' => "Fees deposits online via Payhere TXREF: " . $orderId, 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
            }
        }
    }

    public function payhere_cancel()
    {
        session()->get('params');
        session()->set("params", "");
        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('userrole/online_exam'));
    }

    public function payhere_return()
    {
        set_alert('success', translate('payment_successfull'));
        return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
    }

    public function nepalste()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['nepalste_public_key'] == "" && $config['nepalste_secret_key'] == "") {
                set_alert('error', 'Nepalste config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $orderID = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
                $params['myIdentifier'] = $orderID;
                session()->set("params", $params);
                $parameters = ['identifier' => $orderID, 'currency' => 'NPR', 'amount' => number_format($params['amount'], 2, '.', ''), 'details' => "Admission Fees deposits online via nepalste Student ID:" . $params['student_id'], 'ipn_url' => base_url('onlineexam_payment/nepalste_notify'), 'cancel_url' => base_url('onlineexam_payment/payhere_cancel'), 'success_url' => base_url('onlineexam_payment/payhere_return'), 'public_key' => $config['nepalste_public_key'], 'site_logo' => $this->applicationModel->getBranchImage(get_loggedin_branch_id(), 'logo-small'), 'checkout_theme' => 'dark', 'customer_name' => $params['student_name'], 'customer_email' => empty($params['student_email']) ? 'john@mail.com' : $params['student_email']];
                //live end point
                $url = "https://nepalste.com.np/payment/initiate";
                /*test end point
                  $url = "https://nepalste.com.np/sandbox/payment/initiate";*/
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                $obj = json_decode($result);
                if (!empty($obj)) {
                    $url = $obj->url;
                    header('Location: ' . $url);
                } else {
                    set_alert('error', "Transaction Failed");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }

    public function nepalste_notify()
    {
        if ($_POST !== []) {
            $params = session()->get('params');
            session()->set("params", "");
            $config = $this->get_payment_config();
            //Receive the response parameter
            $status = $_POST['status'];
            $signature = $_POST['signature'];
            $identifier = $_POST['identifier'];
            $data = $_POST['data'];
            // Generate your signature
            $customKey = $data['amount'] . $identifier;
            $secret = $config['nepalste_secret_key'];
            $mySignature = strtoupper(hash_hmac('sha256', $customKey, (string) $secret));
            $myIdentifier = $params['myIdentifier'];
            if ($status == "success" && $signature == $mySignature && $identifier == $myIdentifier) {
                // payment info update in invoice
                $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => "", 'amount' => $params['amount'], 'transaction_id' => "Fees deposits online via Paystack Order ID: " . $identifier, 'created_at' => date('Y-m-d H:i:s')];
                $this->savePaymentData();
            }
        }
    }

    //Fahad - Tap payments:
    // Fahad updated - July
    public function tap()
    {
        $config = $this->get_payment_config();
        $params = session()->get('params');
        if (empty($params)) {
            set_alert('error', 'Payment parameters are missing.');
            redirect('onlineexam_payment/index');
        }

        if (empty($config['tap_secret_key']) || empty($config['tap_merchant_id'])) {
            set_alert('error', 'Tap Payment configuration not available');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $data = ['amount' => $params['amount'], 'currency' => $params['currency'], 'threeDSecure' => true, 'description' => 'Online exam fees', 'customer' => ['first_name' => $params['name'], 'email' => $params['email'], 'phone' => ['country_code' => '965', 'number' => $params['mobile_no']]], 'redirect' => ['url' => base_url("onlineexam_payment/tap_verify")]];
            $url = "https://api.tap.company/v2/charges";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json']);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err !== '' && $err !== '0') {
                log_message('error', 'cURL Error #:' . $err);
                set_alert('error', 'cURL Error #:' . $err);
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $result = json_decode($response, true);
                if (isset($result['transaction']['url'])) {
                    redirect($result['transaction']['url']);
                } else {
                    log_message('error', 'Tap payment error: ' . $response);
                    set_alert('error', 'Failed to initialize Tap payment.');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }

    // Fahad updated - July
    public function tap_verify()
    {
        $tapId = $this->request->getGet('tap_id');
        if (!$tapId) {
            set_alert('error', 'Invalid Tap Payment attempt');
            return redirect()->to(base_url('onlineexam_payment/index'));
        }

        $config = $this->get_payment_config();
        $url = 'https://api.tap.company/v2/charges/' . $tapId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json']);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err !== '' && $err !== '0') {
            log_message('error', 'cURL Error #:' . $err);
            set_alert('error', 'cURL Error #:' . $err);
            return redirect()->to(base_url('onlineexam_payment/index'));
        }

        $result = json_decode($response, true);
        if ($result && $result['status'] == 'CAPTURED') {
            $params = session()->get('params');
            session()->set("params", "");
            // Clear session parameters
            $arrayFees = ['student_id' => $params['student_id'], 'exam_id' => $params['exam_id'], 'payment_method' => 'tap', 'amount' => floatval($params['amount']), 'transaction_id' => $tapId, 'created_at' => date('Y-m-d H:i:s')];
            $this->paymentModel->savePaymentData($arrayFees);
            // Save or update payment data
            set_alert('success', 'Payment successful');
            return redirect()->to(base_url('userrole/onlineexam_take/' . $params['exam_id']));
        }

        set_alert('error', 'Payment verification failed');
        return redirect()->to(base_url('onlineexam_payment/index'));
    }

    private function savePaymentData()
    {
        // insert in DB
        $this->db->table('online_exam_payment')->insert();
    }
}
