<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EmailModel;
use App\Models;
/**
 * @package : Ramom school management system
 * @version : 5.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Admissionpayment.php
 * @copyright : Reserved RamomCoder Team
 */
class Admissionpayment extends FrontendController
{
    /**
     * @var mixed
     */
    public $tapPayments;

    /**
     * @var mixed
     */
    public $stripePayment;

    /**
     * @var mixed
     */
    public $razorpayPayment;

    /**
     * @var mixed
     */
    public $paytmKitLib;

    /**
     * @var mixed
     */
    public $paypalPayment;

    /**
     * @var mixed
     */
    public $midtransPayment;

    /**
     * @var mixed
     */
    public $appLib;

    /**
     * @var mixed
     */
    public $Sslcommerz;

    /**
     * @var App\Models\AdmissionpaymentModel
     */
    public $admissionpayment;

    public $load;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $validation;

    public $input;

    public $admissionpaymentModel;

    public $session;

    public $paypal_payment;

    public $applicationModel;

    public $stripe_payment;

    public $razorpay_payment;

    public $sslcommerz;

    public $midtrans_payment;

    public $paytm_kit_lib;

    public $db;

    public $emailModel;

    public function __construct()
    {
        parent::__construct();








        $this->tapPayments = service('tapPayments');$this->stripePayment = service('stripePayment');$this->sslcommerz = service('sslcommerz');$this->razorpayPayment = service('razorpayPayment');$this->paytmKitLib = service('paytmKitLib');$this->paypalPayment = service('paypalPayment');$this->midtransPayment = service('midtransPayment');$this->appLib = service('appLib'); 
        $this->admissionpayment = new \App\Models\AdmissionpaymentModel();
        $this->paypal_payment = service('paypalPayment');
        $this->stripe_payment = service('stripePayment');
        $this->razorpay_payment = service('razorpayPayment');
        $this->Sslcommerz = service('sslcommerz');
        $this->midtrans_payment = service('midtransPayment');
        $this->paytm_kit_lib = service('paytmKitLib');
        $this->email = new \App\Models\EmailModel();
        // $this->Tap_payments = service('tapPayments');
    }

    public function index($id = '')
    {
        $this->data['get_student'] = $this->admissionpaymentModel->getStudentDetails($id);
        if ($this->data['get_student']['fee_elements']['status'] == 0) {
            set_alert('error', "Admission fee is not required.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->data['get_student']['payment_status'] == 1) {
            set_alert('error', "This student admission fee has already been paid.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        echo view('home/payment', $this->data);
    }

    public function checkout()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
            $this->validation->setRules(['mobile_no' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
            $this->validation->setRules(['post_code' => ["label" => translate('post_code'), "rules" => 'trim|required']]);
            $this->validation->setRules(['state' => ["label" => translate('state'), "rules" => 'trim|required']]);
            $this->validation->setRules(['address' => ["label" => translate('address'), "rules" => 'trim|required']]);
            $this->validation->setRules(['payment_method' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $payVia = $this->request->getPost('payment_method');
                $studentID = $this->request->getPost('student_id');
                $getStudent = $this->admissionpaymentModel->getStudentDetails($studentID);
                $params = ['student_id' => $studentID, 'branch_id' => $getStudent['branch_id'], 'student_mobile' => $getStudent['mobile_no'], 'student_email' => $getStudent['email'], 'class_name' => $getStudent['class_name'], 'section_name' => $getStudent['section_name'], 'student_name' => $getStudent['first_name'] . " " . $getStudent['last_name'], 'amount' => $getStudent['fee_elements']['amount'], 'currency' => $getStudent['currency'], 'name' => $this->request->getPost('name'), 'email' => $this->request->getPost('email'), 'mobile_no' => $this->request->getPost('mobile_no'), 'post_code' => $this->request->getPost('post_code'), 'state' => $this->request->getPost('state'), 'address' => $this->request->getPost('address'), 'payment_method' => $payVia];
                if ($payVia == 'paypal') {
                    $url = base_url("admissionpayment/paypal");
                    session()->set("params", $params);
                }

                if ($payVia == 'stripe') {
                    $url = base_url("admissionpayment/stripe");
                    session()->set("params", $params);
                }

                if ($payVia == 'payumoney') {
                    $payerData = ['name' => $this->request->getPost('payer_name'), 'email' => $this->request->getPost('email'), 'phone' => $this->request->getPost('phone')];
                    $params['payer_data'] = $payerData;
                    $url = base_url("admissionpayment/payumoney");
                    session()->set("params", $params);
                }

                if ($payVia == 'paystack') {
                    $url = base_url("admissionpayment/paystack");
                    session()->set("params", $params);
                }

                if ($payVia == 'razorpay') {
                    $url = base_url("admissionpayment/razorpay");
                    session()->set("params", $params);
                }

                if ($payVia == 'sslcommerz') {
                    $params['tran_id'] = "SSLC" . uniqid();
                    $url = base_url("admissionpayment/sslcommerz");
                    session()->set("params", $params);
                }

                if ($payVia == 'jazzcash') {
                    $url = base_url("admissionpayment/jazzcash");
                    session()->set("params", $params);
                }

                if ($payVia == 'midtrans') {
                    $url = base_url("admissionpayment/midtrans");
                    session()->set("params", $params);
                }

                if ($payVia == 'flutterwave') {
                    $url = base_url("admissionpayment/flutterwave");
                    session()->set("params", $params);
                }

                if ($payVia == 'paytm') {
                    $url = base_url("admissionpayment/paytm");
                    session()->set("params", $params);
                }

                if ($payVia == 'toyyibpay') {
                    $url = base_url("admissionpayment/toyyibpay");
                    session()->set("params", $params);
                }

                if ($payVia == 'payhere') {
                    $url = base_url("admissionpayment/payhere");
                    session()->set("params", $params);
                }

                if ($payVia == 'nepalste') {
                    $url = base_url("admissionpayment/nepalste");
                    session()->set("params", $params);
                }

                if ($payVia == 'tap') {
                    $url = base_url("admissionpayment/tap");
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
        if (!empty($params)) {
            // Check for required PayPal REST API credentials
            if ($config['paypal_client_id'] == "" || $config['paypal_client_secret'] == "") {
                set_alert('error', 'PayPal configuration not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $data = ['cancelUrl' => base_url('admissionpayment/getsuccesspayment'), 'returnUrl' => base_url('admissionpayment/getsuccesspayment'), 'student_id' => $params['student_id'], 'name' => $params['student_name'], 'description' => "Online Student fees deposit. Student Id - " . $params['student_id'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
                $this->paypal_payment->initialize($params['branch_id']);
                $response = $this->paypal_payment->payment($data);
                if ($response->isSuccessful()) {
                    // Handle success scenario
                    // redirect to success page or perform other success actions
                } elseif ($response->isRedirect()) {
                    // If the transaction requires redirection to the payment portal
                    $response->redirect();
                } else {
                    // Output failure message
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
            $data = ['student_id' => $params['student_id'], 'name' => $params['student_name'], 'description' => "Online Student fees deposit. Student Id - " . $params['student_id'], 'amount' => floatval($params['amount']), 'currency' => $params['currency']];
            $this->paypal_payment->initialize($params['branch_id']);
            $response = $this->paypal_payment->success($data);
            $paypalResponse = $response->getData();
            if ($response->isSuccessful()) {
                $purchaseId = $_GET['PayerID'];
                if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success' && $purchaseId) {
                    $refId = $paypalResponse['PAYMENTINFO_0_TRANSACTIONID'];
                    // payment info update in invoice
                    $arrayFees = ['data' => $params, 'amount' => floatval($paypalResponse['PAYMENTINFO_0_AMT']), 'remarks' => "Admission Fees deposits online via Paypal Ref ID: " . $refId, 'date' => date("Y-m-d H:i:s")];
                    $this->savePaymentData($arrayFees);
                    $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                    session()->set_flashdata('success', $success);
                    return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
                }
            } elseif ($response->isRedirect()) {
                $response->redirect();
            } else {
                set_alert('error', translate('payment_cancelled'));
                return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
            }
        }

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
                $data = ['imagesURL' => $this->applicationModel->getBranchImage($params['branch_id'], 'logo-small'), 'success_url' => base_url("admissionpayment/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'cancel_url' => base_url("admissionpayment/stripe_success?session_id={CHECKOUT_SESSION_ID}"), 'amount' => $params['amount'], 'description' => "Online Student fees deposit. Student Id - " . $params['student_id']];
                $this->stripe_payment->initialize($params['branch_id']);
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
                $this->stripe_payment->initialize($params['branch_id']);
                $response = $this->stripe_payment->verify($sessionId);
                if (isset($response->payment_status) && $response->payment_status == 'paid') {
                    $amount = floatval($response->amount_total) / 100;
                    $refId = $response->payment_intent;
                    // payment info update in invoice
                    $arrayFees = ['data' => $params, 'amount' => $amount, 'remarks' => "Admission Fees deposits online via Stripe Ref ID: " . $refId, 'date' => date("Y-m-d H:i:s")];
                    $this->savePaymentData($arrayFees);
                    $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                    session()->set_flashdata('success', $success);
                    return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
                }

                // payment failed: display message to customer
                set_alert('error', "Something went wrong!");
                return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
            } catch (\Exception $ex) {
                set_alert('error', $ex->getMessage());
                return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
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
                $callbackUrl = base_url() . 'admissionpayment/verify_paystack_payment/' . $ref;
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
                        $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Paystack Ref ID: " . $ref, 'date' => date("Y-m-d H:i:s")];
                        $this->savePaymentData($arrayFees);
                        $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                        session()->set_flashdata('success', $success);
                        return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
                    }

                    // the transaction was not successful, do not deliver value'
                    // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                    set_alert('error', "Transaction Failed");
                    return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
                }

                //echo $result['message'];
                set_alert('error', "Transaction Failed");
                return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
            }

            //print_r($result);
            //die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
        }

        //var_dump($request);
        //die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        set_alert('error', "Transaction Failed");
        return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
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
                $studentID = $params['student_id'];
                $amount = floatval($params['amount']);
                $payerName = $params['name'];
                $payerEmail = $params['email'];
                $payerPhone = $params['mobile_no'];
                $productInfo = "Online Admission fees deposit. Student Id - " . $studentID;
                // redirect url
                $success = base_url('admissionpayment/payumoney_success');
                $fail = base_url('admissionpayment/payumoney_success');
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
                    $arrayFees = ['data' => $params, 'amount' => floatval($this->request->getPost('amount')), 'remarks' => "Admission Fees deposits online via PayU TXN ID: " . $txnId . " / PayU Ref ID: " . $mihpayid, 'date' => date("Y-m-d H:i:s")];
                    $this->savePaymentData($arrayFees);
                    $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                    session()->set_flashdata('success', $success);
                    return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
                }

                set_alert('error', translate('invalid_transaction'));
                return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
            }

            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
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
                $params['invoice_no'] = $params['student_id'];
                $params['fine'] = 0;
                $this->razorpay_payment->initialize($params['branch_id']);
                $response = $this->razorpay_payment->payment($params);
                $params['razorpay_order_id'] = $response;
                session()->set("params", $params);
                $arrayData = ['key' => $config['razorpay_key_id'], 'amount' => $params['amount'] * 100, 'name' => $params['student_name'], 'description' => "Admission Fees deposits online via Razorpay. Student ID - " . $params['student_id'], 'image' => base_url('uploads/app_image/logo-small.png'), 'currency' => 'INR', 'order_id' => $params['razorpay_order_id'], 'theme' => ["color" => "#F37254"]];
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
            $this->razorpay_payment->initialize($params['branch_id']);
            $response = $this->razorpay_payment->verify($attributes);
            if ($response == true) {
                // payment info update in invoice
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Razorpay TxnID: " . $attributes['razorpay_payment_id'], 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
            }

            set_alert('error', $response);
            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
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
                $postData['success_url'] = base_url('admissionpayment/sslcommerz_success');
                $postData['fail_url'] = base_url('admissionpayment/sslcommerz_success');
                $postData['cancel_url'] = base_url('admissionpayment/sslcommerz_success');
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
                $this->sslcommerz->initialize($params['branch_id']);
                $this->sslcommerz->RequestToSSLC($postData);
            }
        }
    }

    /* sslcommerz successpayment redirect */
    public function sslcommerz_success()
    {
        $params = session()->get('params');
        if ($_POST['status'] == 'VALID' && $params['tran_id'] == $_POST['tran_id'] && $params['amount'] == $_POST['currency_amount']) {
            $this->sslcommerz->initialize($params['branch_id']);
            if ($this->sslcommerz->ValidateResponse($_POST['currency_amount'], "BDT", $_POST)) {
                $tranId = $params['tran_id'];
                $arrayFees = ['data' => $params, 'amount' => floatval($_POST['currency_amount']), 'remarks' => "Admission Fees deposits online via SSLcommerz TXN ID: " . $tranId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
            }
        } else {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
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
                $postData = ["pp_Version" => "2.0", "pp_TxnType" => "MPAY", "pp_Language" => "EN", "pp_IsRegisteredCustomer" => "Yes", "pp_TokenizedCardNumber" => "", "pp_CustomerEmail" => "", "pp_CustomerMobile" => "", "pp_CustomerID" => uniqid(), "pp_MerchantID" => $config['jazzcash_merchant_id'], "pp_Password" => $config['jazzcash_passwd'], "pp_TxnRefNo" => $ppTxnRefNo, "pp_Amount" => floatval($params['amount']) * 100, "pp_DiscountedAmount" => "", "pp_DiscountBank" => "", "pp_TxnCurrency" => "PKR", "pp_TxnDateTime" => date('YmdHis'), "pp_BillReference" => uniqid(), "pp_Description" => "Submitting student fees online. Student ID - " . $params['student_id'], "pp_TxnExpiryDateTime" => date('YmdHis', strtotime("+1 hours")), "pp_ReturnURL" => base_url('admissionpayment/jazzcash_success'), "ppmpf_1" => "1", "ppmpf_2" => "2", "ppmpf_3" => "3", "ppmpf_4" => "4", "ppmpf_5" => "5"];
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
            $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via JazzCash TXN ID: " . $tranId, 'date' => date("Y-m-d H:i:s")];
            $this->savePaymentData($arrayFees);
            $success = "Thank you for submitting the online registration form. Please you can print this copy.";
            session()->set_flashdata('success', $success);
            return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
        }

        if ($_POST['pp_ResponseCode'] == '112') {
            set_alert('error', "Transaction Failed");
            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
        }

        set_alert('error', $_POST['pp_ResponseMessage']);
        return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
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
                $this->midtrans_payment->initialize($params['branch_id']);
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Midtrans TXN ID: " . $tranId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                $url = base_url('home/admission_confirmation/' . $params['student_id']);
            } else {
                $url = base_url('admissionpayment/index/' . $params['student_id']);
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
                $callbackUrl = base_url('admissionpayment/verify_flutterwave_payment');
                $data = ['student_name' => $params['student_name'], 'amount' => $amount, 'customer_email' => $params['email'], 'currency' => $params['currency'], "txref" => $txref, "pubKey" => $config['flutterwave_public_key'], "redirect_url" => $callbackUrl];
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

        $redirectUrl = base_url('home/admission_confirmation/' . $params['student_id']);
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Flutterwave TXN ID: " . $params['txref'], 'date' => date("Y-m-d H:i:s")];
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
                $paytmParams["CUST_ID"] = $params['student_id'];
                $paytmParams["EMAIL"] = empty($params['email']) ? "" : $params['email'];
                $paytmParams["MID"] = $pAYTMMERCHANTMID;
                $paytmParams["CHANNEL_ID"] = "WEB";
                $paytmParams["WEBSITE"] = $pAYTMMERCHANTWEBSITE;
                $paytmParams["CALLBACK_URL"] = base_url('admissionpayment/paytm_success');
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Paytm TXN ID: " . $tranId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
                $success = "Thank you for submitting the online registration form. Please you can print this copy.";
                session()->set_flashdata('success', $success);
                return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
            }

            set_alert('error', "Something went wrong!");
            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
        }

        set_alert('error', "Checksum mismatched.");
        return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
    }

    public function toyyibpay()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['toyyibpay_secretkey'] == "" && $config['toyyibpay_categorycode'] == "") {
                set_alert('error', 'toyyibPay config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $paymentData = ['userSecretKey' => $config['toyyibpay_secretkey'], 'categoryCode' => $config['toyyibpay_categorycode'], 'billName' => 'School Fee', 'billDescription' => 'Student Admission Fee', 'billPriceSetting' => 1, 'billPayorInfo' => 1, 'billAmount' => floatval($params['amount']) * 100, 'billReturnUrl' => base_url('admissionpayment/toyyibpay_success'), 'billCallbackUrl' => base_url('admissionpayment/toyyibpay_callbackurl'), 'billExternalReferenceNo' => substr(hash('sha256', mt_rand() . microtime()), 0, 20), 'billTo' => $params['name'], 'billEmail' => $params['email'], 'billPhone' => $params['mobile_no'], 'billSplitPayment' => 0, 'billSplitPaymentArgs' => '', 'billPaymentChannel' => '0', 'billContentEmail' => 'Thank you for pay admission Fee', 'billChargeToCustomer' => 1];
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
        session()->set("params", "");
        if (!empty($_GET['status_id']) && $_GET['status_id'] == 1) {
            $success = "Thank you for submitting the online registration form. Please you can print this copy.";
            session()->set_flashdata('success', $success);
            return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
        }

        set_alert('error', "Something went wrong!");
        return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
    }

    public function toyyibpay_callbackurl()
    {
        if (!empty($_POST['status']) && $_POST['status'] == 1) {
            $refno = $_POST['refno'];
            $params = session()->get('params');
            $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via toyyibPay TXN ID: " . $refno, 'date' => date("Y-m-d H:i:s")];
            $this->savePaymentData($arrayFees);
        }
    }

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
                $paytmParams['return_url'] = base_url('admissionpayment/payhere_return');
                $paytmParams["cancel_url"] = base_url('admissionpayment/payhere_cancel');
                $paytmParams["notify_url"] = base_url('admissionpayment/payhere_notify');
                $paytmParams["order_id"] = $orderID;
                $paytmParams["items"] = "School Fees";
                $paytmParams["currency"] = "LKR";
                $paytmParams["amount"] = floatval($params['amount']);
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Payhere Order ID: " . $orderId, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
            }
        }
    }

    public function payhere_cancel()
    {
        $params = session()->get('params');
        session()->set("params", "");
        set_alert('error', "Something went wrong!");
        return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
    }

    public function payhere_return()
    {
        $success = "Thank you for submitting the online registration form. Please you can print this copy.";
        session()->set_flashdata('success', $success);
        return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
    }

    public function nepalste()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (!empty($params)) {
            if ($config['nepalste_public_key'] == "" && $config['nepalste_secret_key'] == "") {
                set_alert('error', 'Nepalste config not available');
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $orderID = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
                $params['myIdentifier'] = $orderID;
                session()->set("params", $params);
                $parameters = ['identifier' => $orderID, 'currency' => 'NPR', 'amount' => number_format($params['amount'], 2, '.', ''), 'details' => "Admission Fees deposits online via nepalste Student ID:" . $params['student_id'], 'ipn_url' => base_url('admissionpayment/nepalste_notify'), 'cancel_url' => base_url('admissionpayment/payhere_cancel'), 'success_url' => base_url('admissionpayment/payhere_return'), 'public_key' => $config['nepalste_public_key'], 'site_logo' => $this->applicationModel->getBranchImage($params['branch_id'], 'logo-small'), 'checkout_theme' => 'dark', 'customer_name' => $params['name'], 'customer_email' => empty($params['email']) ? 'john@mail.com' : $params['email']];
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
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposits online via Nepalste Order ID: " . $identifier, 'date' => date("Y-m-d H:i:s")];
                $this->savePaymentData($arrayFees);
            }
        }
    }

    //fahad - Tap payments 
    //fahad - Tap payments - July
    public function tap()
    {
        $config = $this->getPaymentConfig();
        $params = session()->get('params');
        if (empty($params) || empty($config['tap_secret_key']) || empty($config['tap_merchant_id'])) {
            set_alert('error', 'Tap Payment configuration not available');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $data = ['amount' => $params['amount'], 'currency' => $params['currency'], 'customer_initiated' => true, 'threeDSecure' => true, 'save_card' => false, 'description' => 'Admission fee payment', 'metadata' => ['udf1' => 'Metadata 1'], 'reference' => ['transaction' => 'txn_01', 'order' => 'ord_01'], 'receipt' => ['email' => true, 'sms' => false], 'customer' => ['first_name' => $params['student_name'], 'email' => $params['email'], 'phone' => ['country_code' => '965', 'number' => $params['mobile_no']]], 'merchant' => ['id' => $config['tap_merchant_id']], 'redirect' => ['url' => base_url("admissionpayment/tap_verify")]];
        $url = "https://api.tap.company/v2/charges";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $headers = ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        log_message('info', 'Tap payment response: ' . $request);
        log_message('info', 'HTTP Code: ' . $httpCode);
        if ($httpCode == 200 && $request) {
            $response = json_decode($request, true);
            if (isset($response['transaction']['url'])) {
                redirect($response['transaction']['url']);
            } else {
                log_message('error', 'Tap payment error response: ' . json_encode($response));
                set_alert('error', 'Failed to initialize Tap payment.');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            set_alert('error', 'Error in Tap payment initialization.');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function verify_tap()
    {
        $tapID = $this->request->getGet('tap_id');
        $params = session()->get('params');
        if (!$tapID || empty($params)) {
            set_alert('error', 'Invalid request or parameters missing.');
            return redirect()->to(base_url('admissionpayment/index'));
        }

        $config = $this->getPaymentConfig();
        $url = 'https://api.tap.company/v2/charges/' . $tapID;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['tap_secret_key'], 'Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) {
            $result = json_decode($response, true);
            if ($result && isset($result['status']) && $result['status'] == 'CAPTURED') {
                $arrayFees = ['data' => $params, 'amount' => floatval($params['amount']), 'remarks' => "Admission Fees deposited online via Tap Payment Charge ID: " . $tapID, 'date' => date("Y-m-d")];
                $this->savePaymentData($arrayFees);
                session()->set("params", "");
                // Clear session parameters
                set_alert('success', 'Payment successful');
                return redirect()->to(base_url('home/admission_confirmation/' . $params['student_id']));
            }

            // Check for errors in the response or handle unsuccessful payment
            if (isset($result['error'])) {
                log_message('error', 'Tap payment error: ' . json_encode($result['error']));
                set_alert('error', "Transaction Failed: " . $result['error']['description']);
            } else {
                set_alert('error', "Transaction Failed");
            }

            return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
        }

        log_message('error', 'cURL Error during Tap payment verification.');
        set_alert('error', 'cURL Error during payment verification.');
        return redirect()->to(base_url('admissionpayment/index/' . $params['student_id']));
    }

    private function savePaymentData($data)
    {
        if (!empty($data)) {
            // payer details json encode
            $studentID = $data['data']['student_id'];
            $paymentDetails = ['name' => $data['data']['name'], 'email' => $data['data']['email'], 'post_code' => $data['data']['post_code'], 'state' => $data['data']['state'], 'address' => $data['data']['address'], 'payment_method' => $data['data']['payment_method'], 'remarks' => $data['remarks'], 'date' => $data['date']];
            // insert in DB
            $arrayData = ['payment_status' => 1, 'payment_amount' => $data['amount'], 'payment_details' => json_encode($paymentDetails)];
            $this->db->table('id')->where();
            $this->db->table('online_admission')->update();
            // transaction voucher save function
            $getSeeting = $this->admissionpaymentModel->get('transactions_links', ['branch_id' => $data['data']['branch_id']], true);
            if ($getSeeting['status']) {
                $arrayTransaction = ['account_id' => $getSeeting['deposit'], 'branch_id' => $getSeeting['branch_id'], 'amount' => $data['amount'], 'date' => $data['date']];
                $this->admissionpaymentModel->saveTransaction($arrayTransaction);
            }

            // applicant email send
            $emailData['institute_name'] = get_type_name_by_id('branch', $data['data']['branch_id']);
            $emailData['admission_id'] = $studentID;
            $emailData['apply_date'] = $data['date'];
            $emailData['branch_id'] = $data['data']['branch_id'];
            $emailData['mobile_no'] = $data['data']['student_mobile'];
            $emailData['student_name'] = $data['data']['student_name'];
            $emailData['class_name'] = $data['data']['class_name'];
            $emailData['section_name'] = $data['data']['section_name'];
            $emailData['payment_url'] = base_url("admissionpayment/index/" . $studentID);
            $emailData['admission_copy_url'] = base_url("home/admission_confirmation/" . $studentID);
            $emailData['paid_amount'] = $data['amount'];
            $emailData['email'] = $data['data']['student_email'];
            $this->emailModel->onlineadmission($emailData);
        }
    }

    public function getPaymentConfig()
    {
        session()->get('params');
        $this->db->table('branch_id')->where();
        $builder->select('*')->from('payment_config');
        return $builder->get()->row_array();
    }
}
