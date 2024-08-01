<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Settings.php
 * @copyright : Reserved RamomCoder Team
 */
class Settings extends AdminController
{
    public $sslcommerz;

    public $appLib;

    public $input;

    public $db;

    public $session;

    public $load;

    public $validation;

    public $applicationModel;

    public function __construct()
    {
        parent::__construct();


        $this->sslcommerz = service('sslcommerz');$this->appLib = service('appLib');}

    public function index()
    {
        redirect(base_url(), 'refresh');
    }

    /* global settings controller */
    public function universal()
    {
        if (!get_permission('global_settings', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('global_settings', 'is_edit')) {
            }

            if ($this->appLib->licenceVerify() == false) {
                set_alert('error', translate('invalid_license'));
                redirect(site_url('dashboard'));
            }
        }

        $config = [];
        if ($this->request->getPost('submit') == 'setting') {
            foreach ($this->request->getPost() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }

                $config[$input] = $value;
            }

            if (empty($config['reg_prefix'])) {
                $config['reg_prefix'] = false;
            }

            $this->db->table('id')->where();
            $this->db->table('global_settings')->update();
            $isRTL = $this->appLib->getRTLStatus($config['translation']);
            session()->set(['set_lang' => $config['translation'], 'is_rtl' => $isRTL]);
            set_alert('success', translate('the_configuration_has_been_updated'));
            redirect(current_url());
        }

        if ($this->request->getPost('submit') == 'theme') {
            foreach ($this->request->getPost() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }

                $config[$input] = $value;
            }

            $this->db->table('id')->where();
            $this->db->table('theme_settings')->update();
            set_alert('success', translate('the_configuration_has_been_updated'));
            session()->set_flashdata('active', 2);
            redirect(current_url());
        }

        if ($this->request->getPost('submit') == 'logo') {
            move_uploaded_file($_FILES['logo_file']['tmp_name'], 'uploads/app_image/logo.png');
            move_uploaded_file($_FILES['text_logo']['tmp_name'], 'uploads/app_image/logo-small.png');
            move_uploaded_file($_FILES['print_file']['tmp_name'], 'uploads/app_image/printing-logo.png');
            move_uploaded_file($_FILES['report_card']['tmp_name'], 'uploads/app_image/report-card-logo.png');
            move_uploaded_file($_FILES['slider_1']['tmp_name'], 'uploads/login_image/slider_1.jpg');
            move_uploaded_file($_FILES['slider_2']['tmp_name'], 'uploads/login_image/slider_2.jpg');
            move_uploaded_file($_FILES['slider_3']['tmp_name'], 'uploads/login_image/slider_3.jpg');
            move_uploaded_file($_FILES['sidebox_1']['tmp_name'], 'assets/login_page/image/sidebox.jpg');
            move_uploaded_file($_FILES['profile_bg']['tmp_name'], 'assets/images/profile_bg.jpg');
            set_alert('success', translate('the_configuration_has_been_updated'));
            session()->set_flashdata('active', 3);
            redirect(current_url());
        }

        $this->data['title'] = translate('global_settings');
        $this->data['sub_page'] = 'settings/universal';
        $this->data['main_menu'] = 'settings';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    public function file_types_save()
    {
        if ($_POST !== []) {
            if (!get_permission('global_settings', 'is_view')) {
                ajax_access_denied();
            }

            $this->validation->setRules(['image_extension' => ["label" => translate('image_extension'), "rules" => 'trim|required']]);
            $this->validation->setRules(['image_size' => ["label" => translate('image_size'), "rules" => 'trim|required|numeric']]);
            $this->validation->setRules(['file_extension' => ["label" => translate('file_extension'), "rules" => 'trim|required']]);
            $this->validation->setRules(['file_size' => ["label" => translate('file_size'), "rules" => 'trim|required|numeric']]);
            if ($this->validation->run() == true) {
                $arrayType = ['image_extension' => $this->request->getPost('image_extension'), 'image_size' => $this->request->getPost('image_size'), 'file_extension' => $this->request->getPost('file_extension'), 'file_size' => $this->request->getPost('file_size')];
                $this->db->table('id')->where();
                $this->db->table('global_settings')->update();
                $array = ['status' => 'success', 'message' => translate('the_configuration_has_been_updated')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function unique_branchname($name)
    {
        $this->db->where_not_in('id', get_loggedin_branch_id());
        $this->db->table('name')->where();
        $name = $builder->get('branch')->num_rows();
        if ($name == 0) {
            return true;
        }
        $this->validation->setRule("unique_branchname", translate('already_taken'));
        return false;
    }

    public function payment()
    {
        if (!get_permission('payment_settings', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['config'] = $this->get_payment_config();
        $this->data['sub_page'] = 'settings/payment_gateway';
        $this->data['main_menu'] = 'settings';
        $this->data['title'] = translate('payment_control');
        echo view('layout/index', $this->data);
    }

    public function paypal_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['paypal_client_id' => ["label" => 'Client ID', "rules" => 'trim|required']]);
        $this->validation->setRules(['paypal_client_secret' => ["label" => 'Client Secret', "rules" => 'trim|required']]);
        $this->validation->setRules(['paypal_email' => ["label" => 'Paypal Email', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            // Correct handling of the checkbox when unchecked
            $paypalSandbox = $this->request->getPost('paypal_sandbox') ? 1 : 0;
            // If checked, 1, otherwise, 0
            $arrayPaypal = ['paypal_client_id' => $this->request->getPost('paypal_client_id'), 'paypal_client_secret' => $this->request->getPost('paypal_client_secret'), 'paypal_email' => $this->request->getPost('paypal_email'), 'paypal_sandbox' => $paypalSandbox];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPaypal['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function stripe_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['stripe_publishiable_key' => ["label" => 'Stripe Publishiable Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['stripe_secret' => ["label" => 'Stripe Secret Key', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $stripeDemo = isset($_POST['stripe_demo']) ? 1 : 2;
            $arrayPaypal = ['stripe_publishiable' => $this->request->getPost('stripe_publishiable_key'), 'stripe_secret' => $this->request->getPost('stripe_secret'), 'stripe_demo' => $stripeDemo];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPaypal['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function payumoney_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['payumoney_key' => ["label" => 'Payumoney Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['payumoney_salt' => ["label" => 'Payumoney Salt', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $payumoneyDemo = isset($_POST['payumoney_demo']) ? 1 : 2;
            $arrayPayumoney = ['payumoney_key' => $this->request->getPost('payumoney_key'), 'payumoney_salt' => $this->request->getPost('payumoney_salt'), 'payumoney_demo' => $payumoneyDemo];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPayumoney['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function paystack_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['paystack_secret_key' => ["label" => 'Paystack API Key', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayPaystack = ['paystack_secret_key' => $this->request->getPost('paystack_secret_key')];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayMollie['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function razorpay_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['razorpay_key_id' => ["label" => 'Key Id', "rules" => 'trim|required']]);
        $this->validation->setRules(['razorpay_key_secret' => ["label" => 'Key Secret', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $razorpayDemo = isset($_POST['razorpay_demo']) ? 1 : 2;
            $arrayRazorpay = ['razorpay_key_id' => $this->request->getPost('razorpay_key_id'), 'razorpay_key_secret' => $this->request->getPost('razorpay_key_secret')];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayRazorpay['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function payment_active()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $paypalStatus = isset($_POST['paypal_status']) ? 1 : 0;
        $stripeStatus = isset($_POST['stripe_status']) ? 1 : 0;
        $payumoneyStatus = isset($_POST['payumoney_status']) ? 1 : 0;
        $paystackStatus = isset($_POST['paystack_status']) ? 1 : 0;
        $razorpayStatus = isset($_POST['razorpay_status']) ? 1 : 0;
        $midtransStatus = isset($_POST['midtrans_status']) ? 1 : 0;
        $sslcommerzStatus = isset($_POST['sslcommerz_status']) ? 1 : 0;
        $jazzcashStatus = isset($_POST['jazzcash_status']) ? 1 : 0;
        $flutterwaveStatus = isset($_POST['flutterwave']) ? 1 : 0;
        $paytmStatus = isset($_POST['paytm_status']) ? 1 : 0;
        $toyyibpayStatus = isset($_POST['toyyibpay_status']) ? 1 : 0;
        $payhereStatus = isset($_POST['payhere_status']) ? 1 : 0;
        $nepalsteStatus = isset($_POST['nepalste_status']) ? 1 : 0;
        $tapStatus = isset($_POST['tap_status']) ? 1 : 0;
        $arrayData = ['paypal_status' => $paypalStatus, 'stripe_status' => $stripeStatus, 'payumoney_status' => $payumoneyStatus, 'paystack_status' => $paystackStatus, 'razorpay_status' => $razorpayStatus, 'midtrans_status' => $midtransStatus, 'sslcommerz_status' => $sslcommerzStatus, 'jazzcash_status' => $jazzcashStatus, 'flutterwave_status' => $flutterwaveStatus, 'paytm_status' => $paytmStatus, 'toyyibpay_status' => $toyyibpayStatus, 'payhere_status' => $payhereStatus, 'nepalste_status' => $nepalsteStatus, 'tap_status' => $tapStatus];
        $this->db->table('branch_id')->where();
        $q = $builder->get('payment_config');
        if ($q->num_rows() == 0) {
            $arrayData['branch_id'] = $branchID;
            $this->db->table('payment_config')->insert();
        } else {
            $this->db->table('id')->where();
            $this->db->table('payment_config')->update();
        }

        $message = translate('the_configuration_has_been_updated');
        $array = ['status' => 'success', 'message' => $message];
        echo json_encode($array);
    }

    public function midtrans_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['midtrans_client_key' => ["label" => 'Client Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['midtrans_server_key' => ["label" => 'Server Key', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $sandbox = isset($_POST['midtrans_sandbox']) ? 1 : 2;
            $arrayMidtrans = ['midtrans_client_key' => $this->request->getPost('midtrans_client_key'), 'midtrans_server_key' => $this->request->getPost('midtrans_server_key'), 'midtrans_sandbox' => $sandbox];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayMidtrans['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function sslcommerz_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['sslcz_store_id' => ["label" => 'Store ID', "rules" => 'trim|required']]);
        $this->validation->setRules(['sslcz_store_passwd' => ["label" => 'Store Password', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $sandbox = isset($_POST['sslcommerz_sandbox']) ? 1 : 2;
            $arraySSLcommerz = ['sslcz_store_id' => $this->request->getPost('sslcz_store_id'), 'sslcz_store_passwd' => $this->request->getPost('sslcz_store_passwd'), 'sslcommerz_sandbox' => $sandbox];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arraySSLcommerz['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function jazzcash_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['jazzcash_merchant_id' => ["label" => 'Jazzcash Merchant ID', "rules" => 'trim|required']]);
        $this->validation->setRules(['jazzcash_passwd' => ["label" => 'Jazzcash Password', "rules" => 'trim|required']]);
        $this->validation->setRules(['jazzcash_integerity_salt' => ["label" => 'Jazzcash Integerity Salt', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $sandbox = isset($_POST['jazzcash_sandbox']) ? 1 : 2;
            $arraySSLcommerz = ['jazzcash_merchant_id' => $this->request->getPost('jazzcash_merchant_id'), 'jazzcash_passwd' => $this->request->getPost('jazzcash_passwd'), 'jazzcash_integerity_salt' => $this->request->getPost('jazzcash_integerity_salt'), 'jazzcash_sandbox' => $sandbox];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arraySSLcommerz['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function flutterwave_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['flutterwave_public_key' => ["label" => 'Public Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['flutterwave_secret_key' => ["label" => 'Secret Key', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $sandbox = isset($_POST['flutterwave_sandbox']) ? 1 : 2;
            $arrayFlutterwave = ['flutterwave_public_key' => $this->request->getPost('flutterwave_public_key'), 'flutterwave_secret_key' => $this->request->getPost('flutterwave_secret_key'), 'flutterwave_sandbox' => $sandbox];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayFlutterwave['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function paytm_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['paytm_merchantmid' => ["label" => 'Merchant MID', "rules" => 'trim|required']]);
        $this->validation->setRules(['paytm_merchantkey' => ["label" => 'Merchant Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['paytm_merchant_website' => ["label" => 'Website', "rules" => 'trim|required']]);
        $this->validation->setRules(['paytm_industry_type' => ["label" => 'Industry Type', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayPaytm = ['paytm_merchantmid' => $this->request->getPost('paytm_merchantmid'), 'paytm_merchantkey' => $this->request->getPost('paytm_merchantkey'), 'paytm_merchant_website' => $this->request->getPost('paytm_merchant_website'), 'paytm_industry_type' => $this->request->getPost('paytm_industry_type')];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPaytm['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function toyyibPay_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['toyyibpay_secretkey' => ["label" => 'Secret key', "rules" => 'trim|required']]);
        $this->validation->setRules(['toyyibpay_categorycode' => ["label" => 'Category Code', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayPaytm = ['toyyibpay_secretkey' => $this->request->getPost('toyyibpay_secretkey'), 'toyyibpay_categorycode' => $this->request->getPost('toyyibpay_categorycode')];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPaytm['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function payhere_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['payhere_merchant_id' => ["label" => 'Merchant ID', "rules" => 'trim|required']]);
        $this->validation->setRules(['payhere_merchant_secret' => ["label" => 'Merchant Secret', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayPaytm = ['payhere_merchant_id' => $this->request->getPost('payhere_merchant_id'), 'payhere_merchant_secret' => $this->request->getPost('payhere_merchant_secret')];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPaytm['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function nepalste_save()
    {
        if (!get_permission('payment_settings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['nepalste_public_key' => ["label" => 'Public Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['nepalste_secret_key' => ["label" => 'Secret Key', "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayPaytm = ['nepalste_public_key' => $this->request->getPost('nepalste_public_key'), 'nepalste_secret_key' => $this->request->getPost('nepalste_secret_key')];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayPaytm['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    //fahad - tap payment
    public function tap_save()
    {
        if (!get_permission('payment_graphsettings', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->validation->setRules(['tap_secret_key' => ["label" => 'Tap Secret Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['tap_public_key' => ["label" => 'Tap Public Key', "rules" => 'trim|required']]);
        $this->validation->setRules(['tap_merchant_id' => ["label" => 'Tap Merchant ID', "rules" => 'trim|required']]);
        // Add validation rule for merchant ID
        if ($this->validation->run() !== false) {
            $tapDemo = $this->request->getPost('tap_demo') == 1 ? 1 : 0;
            $arrayTap = [
                'tap_secret_key' => $this->request->getPost('tap_secret_key'),
                'tap_public_key' => $this->request->getPost('tap_public_key'),
                'tap_merchant_id' => $this->request->getPost('tap_merchant_id'),
                // Add merchant ID to the data array
                'tap_demo' => $tapDemo,
            ];
            $this->db->table('branch_id')->where();
            $q = $builder->get('payment_config');
            if ($q->num_rows() == 0) {
                $arrayTap['branch_id'] = $branchID;
                $this->db->table('payment_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('payment_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function branchUpdate($data)
    {
        $this->db->table('id')->where();
        $this->db->table('branch')->update();
    }
}
