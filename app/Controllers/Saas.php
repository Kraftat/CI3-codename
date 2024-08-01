<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SaasEmailModel;
/**
 * @package : Ramom school management system (Saas)
 * @version : 3.1
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Saas.php
 * @copyright : Reserved RamomCoder Team
 */
class Saas extends MyController

{
    /**
     * @var mixed
     */
    public $Mailer;

    protected $db;


    /**
     * @var App\Models\SaasModel
     */
    public $saas;

    /**
     * @var App\Models\SaasEmailModel
     */
    public $saasEmail;

    public $validation;

    public $input;

    public $load;

    public $saasModel;

    public $saas_emailModel;

    public $session;

    public $mailer;

    public function __construct()
    {


        $this->saas = new \App\Models\SaasModel();
        $this->saasEmail = new \App\Models\SaasEmailModel();
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    public function index()
    {
        return redirect()->to(base_url('saas/package'));
    }

    /* package form validation rules */
    protected function package_validation()
    {
        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        if ($this->request->getPost('free_trial') != 1) {
            $this->validation->setRules(['price' => ["label" => translate('price'), "rules" => 'trim|required|numeric']]);
            $this->validation->setRules(['discount' => ["label" => translate('discount'), "rules" => 'trim|numeric|less_than[' . $this->request->getPost('price') . ']']]);
        }

        $this->validation->setRules(['student_limit' => ["label" => translate('student') . " " . translate('limit'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['parents_limit' => ["label" => translate('parents') . " " . translate('limit'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['staff_limit' => ["label" => translate('staff') . " " . translate('limit'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['teacher_limit' => ["label" => translate('teacher') . " " . translate('limit'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['period_type' => ["label" => "Subscription Period", "rules" => 'trim|required|numeric']]);

        $periodType = $this->request->getPost('period_type');
        if ($periodType != '' && $periodType != 1) {
            $this->validation->setRules(['period_value' => ["label" => translate('period'), "rules" => 'trim|required|numeric|greater_than[0]']]);
        }
    }

    public function package()
    {
        $this->data['arrayPeriod'] = $this->saasModel->getPeriodType();
        $this->data['packageList'] = $this->saasModel->getPackageList();
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('subscription');
        $this->data['sub_page'] = 'saas/package';
        $this->data['main_menu'] = 'saas';
        echo view('layout/index', $this->data);
    }

    public function package_edit($id = '')
    {
        if ($_POST !== []) {
            $this->package_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->saasModel->packageSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => base_url('saas/package')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['row'] = $this->appLib->get_table('saas_package', $id, true);
        $this->data['arrayPeriod'] = $this->saasModel->getPeriodType();
        $this->data['title'] = translate('subscription');
        $this->data['sub_page'] = 'saas/package_edit';
        $this->data['main_menu'] = 'saas';
        echo view('layout/index', $this->data);
    }

    public function package_delete($id)
    {
        $this->db->table('id')->where();
        $this->db->table('saas_package')->delete();
    }

    public function package_save()
    {
        if ($_POST !== []) {
            $this->package_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->saasModel->packageSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    /* school form validation rules */
    protected function school_validation()
    {
        $this->validation->setRules(['branch_name' => ["label" => translate('branch_name'), "rules" => 'required|callback_unique_name']]);
        $this->validation->setRules(['school_name' => ["label" => translate('school_name'), "rules" => 'required']]);
        $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'required|valid_email']]);
        $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'required']]);
        $this->validation->setRules(['currency' => ["label" => translate('currency'), "rules" => 'required']]);
        $this->validation->setRules(['currency_symbol' => ["label" => translate('currency_symbol'), "rules" => 'required']]);
        $this->validation->setRules(['country' => ["label" => translate('country'), "rules" => 'required']]);
        $this->validation->setRules(['saas_package_id' => ["label" => translate('package'), "rules" => 'required']]);
        $this->validation->setRules(['state_id' => ["label" => translate('state'), "rules" => 'required']]);
    }

    /* school all data are prepared and stored in the database here */
    public function school()
    {
        if ($this->request->getPost('submit') == 'save') {
            $this->school_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $schooolID = $this->saasModel->schoolSave($post);
                //Saas data are prepared and stored in the database
                $this->saasModel->saveSchoolSaasData($post['saas_package_id'], $schooolID);
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('saas/school'));
            }

            $this->data['validation_error'] = true;
        }

        $type = $this->request->getGet('type');
        $type = empty($type) ? '' : urldecode((string) $type);
        $this->data['type'] = $type;
        $this->data['subscriptionList'] = $this->saasModel->getSubscriptionList($type);
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'saas/school';
        $this->data['main_menu'] = 'saas';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    public function enabled_school($schoolId = '')
    {
        $school = $this->saasModel->getSingle('branch', $schoolId, true);
        $isEnabled = $this->saasModel->getSchool($schoolId);
        if (!empty($isEnabled)) {
            set_alert('error', "This is not acceptable.");
            return redirect()->to(base_url('branch'));
        }

        if (empty($school)) {
            return redirect()->to(base_url('branch'));
        }

        if ($this->request->getPost('submit') == 'save') {
            $this->validation->setRules(['saas_package_id' => ["label" => translate('package'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $schooolID = $post['branch_id'];
                //Saas data are prepared and stored in the database
                $this->saasModel->saveSchoolSaasData($post['saas_package_id'], $schooolID);
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('saas/school'));
            }

            $this->data['validation_error'] = true;
        }

        $this->data['school'] = $this->saasModel->getSingle('branch', $schoolId, true);
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'saas/enabled_subscription';
        $this->data['main_menu'] = 'branch';
        echo view('layout/index', $this->data);
        return null;
    }

    public function pending_request()
    {
        if (isset($_POST['search'])) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['getPendingRequest'] = $this->saasModel->getPendingRequest($start, $end);
        } else {
            $this->data['getPendingRequest'] = $this->saasModel->getPendingRequest();
        }

        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'saas/pending_request';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        $this->data['main_menu'] = 'saas';
        echo view('layout/index', $this->data);
    }

    public function school_edit($id = '')
    {
        $getSchool = $this->saasModel->getSchool($id);
        if (empty($getSchool)) {
            return redirect()->to(base_url('dashboard'));
        }

        $currentPackageID = $getSchool->package_id;
        if ($this->request->getPost('submit') == 'save') {
            $this->school_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $schooolID = $this->saasModel->schoolSave($post);
                $dateAdd = $this->request->getPost('expire_date');
                $getSubscriptions = $db->table('saas_subscriptions')->get('saas_subscriptions')->row();
                //add subscriptions data stored in the database
                $arraySubscriptions = ['package_id' => $post['saas_package_id'], 'school_id' => $schooolID, 'expire_date' => $dateAdd];
                $this->db->table('id')->where();
                $this->db->table('saas_subscriptions')->update();
                if ($currentPackageID != $post['saas_package_id']) {
                    $subscriptionsID = $getSubscriptions->id;
                    $saasPackage = $db->table('saas_package')->get('saas_package')->row();
                    //manage modules permission
                    $permission = json_decode($saasPackage->permission, true);
                    $modulesManageInsert = [];
                    $modulesManageUpdate = [];
                    $getPermissions = $db->table('permission_modules')->get('permission_modules')->getResult();
                    foreach ($getPermissions as $value) {
                        $getExistPermissions = $db->table('modules_manage')->get('modules_manage');
                        if (in_array($value->id, $permission, true)) {
                            if ($getExistPermissions->num_rows() > 0) {
                                $modulesManageUpdate[] = ['id' => $getExistPermissions->row()->id, 'modules_id' => $value->id, 'isEnabled' => 1, 'branch_id' => $schooolID];
                            } else {
                                $modulesManageInsert[] = ['modules_id' => $value->id, 'isEnabled' => 1, 'branch_id' => $schooolID];
                            }
                        } elseif ($getExistPermissions->num_rows() > 0) {
                            $modulesManageUpdate[] = ['id' => $getExistPermissions->row()->id, 'modules_id' => $value->id, 'isEnabled' => 0, 'branch_id' => $schooolID];
                        } else {
                            $modulesManageInsert[] = ['modules_id' => $value->id, 'isEnabled' => 0, 'branch_id' => $schooolID];
                        }
                    }

                    if ($modulesManageUpdate !== []) {
                        $this->db->update_batch('modules_manage', $modulesManageUpdate, 'id');
                    }

                    if ($modulesManageInsert !== []) {
                        $this->db->insert_batch('modules_manage', $modulesManageInsert);
                    }
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                return redirect()->to(base_url('saas/school'));
            }
        }

        $this->data['data'] = $getSchool;
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'saas/school_edit';
        $this->data['main_menu'] = 'saas';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    /* unique valid branch name verification is done here */
    public function unique_name($name)
    {
        $branchId = $this->request->getPost('branch_id');
        if (!empty($branchId)) {
            $this->db->where_not_in('id', $branchId);
        }

        $this->db->table('name')->where();
        $name = $builder->get('branch')->num_rows();
        if ($name == 0) {
            return true;
        }

        $this->validation->setRule("unique_name", translate('already_taken'));
        return false;
    }

    /* delete information */
    public function school_delete($id = '')
    {
        $this->db->table('id')->delete('branch')->where();
        $this->db->table('branch_id')->delete('modules_manage')->where();
        //delete branch all staff
        $result = $db->table('staff')->get('staff')->getResult();
        foreach ($result as $value) {
            $this->db->table('user_id')->where();
            $this->db->table('login_credential')->delete();
            $this->db->table('id')->where();
            $this->db->table('staff')->delete();
        }

        //delete branch all student
        $result = $db->table('enroll')->get('enroll')->getResult();
        foreach ($result as $value) {
            $this->db->table('id')->where();
            $this->db->table('student')->delete();
            $this->db->table('id')->where();
            $this->db->table('enroll')->delete();
        }

        //delete branch all parent
        $this->db->table('branch_id')->where();
        $this->db->table('parent')->delete();
        $getSubscriptions = $db->table('saas_subscriptions')->get('saas_subscriptions')->row();
        if (!empty($getSubscriptions)) {
            $this->db->table('school_id')->where();
            $this->db->table('saas_subscriptions')->delete();
            $this->db->table('subscriptions_id')->where();
            $this->db->table('saas_subscriptions_transactions')->delete();
        }

        $unlinkPath = 'uploads/app_image/';
        if (file_exists($unlinkPath . sprintf('logo-%s.png', $id))) {
            @unlink($unlinkPath . sprintf('logo-%s.png', $id));
        }

        if (file_exists($unlinkPath . sprintf('logo-small-%s.png', $id))) {
            @unlink($unlinkPath . sprintf('logo-small-%s.png', $id));
        }

        if (file_exists($unlinkPath . sprintf('printing-logo-%s.png', $id))) {
            @unlink($unlinkPath . sprintf('printing-logo-%s.png', $id));
        }

        if (file_exists($unlinkPath . sprintf('report-card-logo-%s.png', $id))) {
            @unlink($unlinkPath . sprintf('report-card-logo-%s.png', $id));
        }
    }

    public function ajaxGetExpireDate()
    {
        if ($_POST !== []) {
            $packageID = $this->request->getPost('id');
            if (empty($packageID)) {
                echo "";
            } else {
                $saasPackage = $db->table('saas_package')->get('saas_package')->row();
                $periodValue = $saasPackage->period_value;
                $dateAdd = '';
                if ($saasPackage->period_type == 2) {
                    $dateAdd = sprintf('+%s days', $periodValue);
                }

                if ($saasPackage->period_type == 3) {
                    $dateAdd = sprintf('+%s month', $periodValue);
                }

                if ($saasPackage->period_type == 4) {
                    $dateAdd = sprintf('+%s year', $periodValue);
                }

                if ($dateAdd !== '' && $dateAdd !== '0') {
                    $dateAdd = date('Y-m-d', strtotime($dateAdd));
                }

                echo $dateAdd;
            }
        }
    }

    public function school_details($id = '')
    {
        $school = $this->saasModel->getSchool($id);
        $this->data['school'] = $school;
        $this->data['schoolID'] = $id;
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'saas/school_details';
        $this->data['main_menu'] = 'saas';
        echo view('layout/index', $this->data);
    }

    public function settings_general()
    {
        if ($_POST !== []) {
            $expiredAlert = $this->request->getPost('expired_alert');
            $captchaStatus = $this->request->getPost('captcha_status');
            if ($expiredAlert == 1) {
                $this->validation->setRules(['expired_alert_days' => ["label" => translate('expired_alert_days'), "rules" => 'trim|required|numeric']]);
                $this->validation->setRules(['expired_reminder_message' => ["label" => translate('expired_reminder_message'), "rules" => 'trim|required']]);
                $this->validation->setRules(['expired_message' => ["label" => translate('expired_message'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['expired_alert' => ["label" => translate('expired_alert'), "rules" => 'trim']]);
            $this->validation->setRules(['seo_title' => ["label" => translate('site') . " " . translate('title'), "rules" => 'trim|required']]);
            if ($captchaStatus == 1) {
                $this->validation->setRules(['recaptcha_site_key' => ["label" => translate('recaptcha_site_key'), "rules" => 'trim|required']]);
                $this->validation->setRules(['recaptcha_secret_key' => ["label" => translate('recaptcha_secret_key'), "rules" => 'trim|required']]);
            }

            if ($this->validation->run() == true) {
                if ($expiredAlert == 1) {
                    $arraySetting = ['expired_alert' => 1, 'expired_alert_days' => $this->request->getPost('expired_alert_days'), 'expired_alert_message' => $this->request->getPost('expired_reminder_message'), 'expired_message' => $this->request->getPost('expired_message')];
                } else {
                    $arraySetting = ['expired_alert' => 0];
                }

                $arraySetting['seo_title'] = $this->request->getPost('seo_title');
                $arraySetting['seo_keyword'] = $this->request->getPost('seo_keyword');
                $arraySetting['seo_description'] = $this->request->getPost('seo_description');
                $arraySetting['google_analytics'] = $this->request->getPost('google_analytics', false);
                $arraySetting['automatic_approval'] = $this->request->getPost('automatic_approval');
                $arraySetting['offline_payments'] = $this->request->getPost('offline_payments');
                $arraySetting['captcha_status'] = $captchaStatus;
                $arraySetting['recaptcha_site_key'] = $this->request->getPost('recaptcha_site_key');
                $arraySetting['recaptcha_secret_key'] = $this->request->getPost('recaptcha_secret_key');
                $this->db->table('id')->where();
                $this->db->table('saas_settings')->update();
                $message = translate('the_configuration_has_been_updated');
                set_alert('success', $message);
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['config'] = $this->db->table('saas_settings')->where('id', 1)->get()->getRowArray();
        $this->data['title'] = translate('school_settings');
        $this->data['sub_page'] = 'saas/general_settings';
        $this->data['main_menu'] = 'saas_setting';
        echo view('layout/index', $this->data);
    }

    public function settings_payment()
    {
        $this->data['config'] = $this->saasModel->get('payment_config', ['branch_id' => 9999], true);
        $this->data['sub_page'] = 'saas/payment_gateway';
        $this->data['main_menu'] = 'saas_setting';
        $this->data['title'] = translate('payment_control');
        echo view('layout/index', $this->data);
    }

    public function savePaymentConfig()
    {
        if ($_POST !== []) {
            $post = $this->request->getPost();
            $postData = [];
            foreach ($post as $key => $value) {
                $name = ucwords(str_replace('_', ' ', $key));
                $this->validation->setRules([$key => ["label" => $name, "rules" => 'trim|required']]);
                if ($key == 'stripe_publishiable_key') {
                    $key = 'stripe_publishiable';
                }

                // Handle checkbox for sandbox
                if ($key == 'paypal_sandbox' || $key == 'stripe_demo' || $key == 'payumoney_demo') {
                    $postData[$key] = $this->request->getPost($key) ? 1 : 0;
                    // Ensuring the sandbox value is set correctly
                } else {
                    $postData[$key] = $value;
                }
            }

            if ($this->validation->run() !== false) {
                $builder->select("id");
                $this->db->table('branch_id')->where();
                $q = $builder->get('payment_config');
                if ($q->num_rows() == 0) {
                    $postData['branch_id'] = 9999;
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
    }

    public function payment_active()
    {
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
        $tapStatus = isset($_POST['tap_status']) ? 1 : 0;
        $arrayData = ['paypal_status' => $paypalStatus, 'stripe_status' => $stripeStatus, 'payumoney_status' => $payumoneyStatus, 'paystack_status' => $paystackStatus, 'razorpay_status' => $razorpayStatus, 'midtrans_status' => $midtransStatus, 'sslcommerz_status' => $sslcommerzStatus, 'jazzcash_status' => $jazzcashStatus, 'flutterwave_status' => $flutterwaveStatus, 'paytm_status' => $paytmStatus, 'toyyibpay_status' => $toyyibpayStatus, 'payhere_status' => $payhereStatus, 'tap_status' => $tapStatus];
        $builder->select('id');
        $this->db->table('branch_id')->where();
        $q = $builder->get('payment_config');
        if ($q->num_rows() == 0) {
            $arrayData['branch_id'] = 9999;
            $this->db->table('payment_config')->insert();
        } else {
            $this->db->table('id')->where();
            $this->db->table('payment_config')->update();
        }

        $message = translate('the_configuration_has_been_updated');
        $array = ['status' => 'success', 'message' => $message];
        echo json_encode($array);
    }

    public function website_settings()
    {
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/jquery-asColorPicker-master/css/asColorPicker.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asColor.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asGradient.js', 'vendor/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js']];
        $this->data['config'] = $this->saasModel->get('saas_settings', ['id' => 1], true);
        $this->data['sub_page'] = 'saas/website_settings';
        $this->data['main_menu'] = 'saas_setting';
        $this->data['title'] = translate('website_settings');
        echo view('layout/index', $this->data);
    }

    /* saas website settings stored in the database here */
    public function websiteSettingsSave()
    {
        if ($_POST !== []) {
            $ignoreArray = ['facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'youtube_url', 'google_plus', 'old_payment_logo', 'old_slider_image', 'old_overly_image', 'terms_and_conditions', 'agree_checkbox_text', 'overly_image_status', 'old_slider_bg_image', 'button_text_1', 'button_url_1', 'button_text_2', 'button_url_2'];
            foreach ($this->request->getPost() as $input => $value) {
                if (in_array($input, $ignoreArray, true)) {
                    continue;
                }

                $this->validation->setRule($input, ucwords(str_replace('_', ' ', $input)), 'trim|required');
            }

            $this->validation->setRules(['payment_logo' => ["label" => "Payment Logo", "rules" => 'callback_photoHandleUpload[payment_logo]']]);
            $this->validation->setRules(['slider_image' => ["label" => "Photo", "rules" => 'callback_photoHandleUpload[slider_image]']]);
            $this->validation->setRules(['slider_bg_image' => ["label" => "Slider Background Image", "rules" => 'callback_photoHandleUpload[slider_bg_image]']]);
            $this->validation->setRules(['overly_image' => ["label" => "Overly Image", "rules" => 'callback_photoHandleUpload[overly_image]']]);
            if ($this->request->getPost('terms_status') == 1) {
                $this->validation->setRules(['agree_checkbox_text' => ["label" => "Agree Checkbox Text", "rules" => 'trim|required']]);
                $this->validation->setRules(['terms_and_conditions' => ["label" => "Terms And Conditions Text", "rules" => 'trim|required']]);
            }

            if ($this->validation->run() == true) {
                $inputData = [];
                $ignoreArray[] = 'footer_text';
                foreach ($this->request->getPost() as $input => $value) {
                    if (in_array($input, $ignoreArray, true)) {
                        continue;
                    }

                    $inputData[$input] = $value;
                }

                //upload slider background images
                $sliderBgImage = $this->request->getPost('old_slider_bg_image');
                if (isset($_FILES["slider_bg_image"]) && $_FILES['slider_bg_image']['name'] != '' && !empty($_FILES['slider_bg_image']['name'])) {
                    $sliderBgImage = $this->saasModel->fileupload("slider_bg_image", "./assets/frontend/images/saas/", $sliderBgImage);
                }

                $inputData['slider_bg_image'] = $sliderBgImage;
                //upload slider images
                $sliderImagFile = $this->request->getPost('old_slider_image');
                if (isset($_FILES["slider_image"]) && $_FILES['slider_image']['name'] != '' && !empty($_FILES['slider_image']['name'])) {
                    $sliderImagFile = $this->saasModel->fileupload("slider_image", "./assets/frontend/images/saas/", $sliderImagFile);
                }

                $inputData['slider_image'] = $sliderImagFile;
                //upload footer payment logo images
                $paymentLogoFile = $this->request->getPost('old_payment_logo');
                if (isset($_FILES["payment_logo"]) && $_FILES['payment_logo']['name'] != '' && !empty($_FILES['payment_logo']['name'])) {
                    $paymentLogoFile = $this->saasModel->fileupload("payment_logo", "./assets/frontend/images/saas/", $paymentLogoFile);
                }

                $inputData['payment_logo'] = $paymentLogoFile;
                //upload overly images
                $overlyImageFile = $this->request->getPost('old_overly_image');
                if (isset($_FILES["overly_image"]) && $_FILES['overly_image']['name'] != '' && !empty($_FILES['overly_image']['name'])) {
                    $overlyImageFile = $this->saasModel->fileupload("overly_image", "./assets/frontend/images/saas/", $overlyImageFile);
                }

                $inputData['overly_image'] = $overlyImageFile;
                $inputData['overly_image_status'] = isset($_POST['overly_image_status']) ? 1 : 0;
                $inputData['agree_checkbox_text'] = $this->request->getPost('agree_checkbox_text', false);
                $inputData['terms_and_conditions'] = $this->request->getPost('terms_and_conditions', false);
                //slider button data
                $inputData['button_text_1'] = $this->request->getPost('button_text_1');
                $inputData['button_url_1'] = $this->request->getPost('button_url_1');
                $inputData['button_text_2'] = $this->request->getPost('button_text_2');
                $inputData['button_url_2'] = $this->request->getPost('button_url_2');
                $this->db->table('id')->where();
                $this->db->table('saas_settings')->update();
                $updateGlobalConfig = [];
                $updateGlobalConfig['facebook_url'] = $this->request->getPost('facebook_url');
                $updateGlobalConfig['twitter_url'] = $this->request->getPost('twitter_url');
                $updateGlobalConfig['linkedin_url'] = $this->request->getPost('linkedin_url');
                $updateGlobalConfig['instagram_url'] = $this->request->getPost('instagram_url');
                $updateGlobalConfig['youtube_url'] = $this->request->getPost('youtube_url');
                $updateGlobalConfig['google_plus_url'] = $this->request->getPost('google_plus');
                $updateGlobalConfig['footer_text'] = $this->request->getPost('footer_text');
                $this->db->table('id')->where();
                $this->db->table('global_settings')->update();
                set_alert('success', translate('the_configuration_has_been_updated'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function emailconfig()
    {
        if ($this->request->getPost('submit') == 'update') {
            $data = [];
            foreach ($this->request->getPost() as $key => $value) {
                if ($key == 'submit') {
                    continue;
                }

                $data[$key] = $value;
            }

            $this->db->table('id')->where();
            $this->db->table('email_config')->update();
            set_alert('success', translate('the_configuration_has_been_updated'));
            return redirect()->to(base_url('mailconfig/email'));
        }

        $this->data['config'] = $this->saasModel->get('email_config', ['branch_id' => 9999], true);
        $this->data['title'] = translate('email_settings');
        $this->data['sub_page'] = 'saas/emailconfig';
        $this->data['main_menu'] = 'saas_setting';
        echo view('layout/index', $this->data);
        return null;
    }

    public function emailtemplate()
    {
        $this->data['branch_id'] = 9999;
        $this->data['templatelist'] = $this->appLib->get_table('saas_email_templates');
        $this->data['title'] = translate('email_settings');
        $this->data['sub_page'] = 'saas/emailtemplate';
        $this->data['main_menu'] = 'saas_setting';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        echo view('layout/index', $this->data);
    }

    public function saveEmailConfig()
    {
        $branchID = 9999;
        $protocol = $this->request->getPost('protocol');
        $this->validation->setRules(['email' => ["label" => 'System Email', "rules" => 'trim|required']]);
        $this->validation->setRules(['protocol' => ["label" => 'Email Protocol', "rules" => 'trim|required']]);
        if ($protocol == 'smtp') {
            $this->validation->setRules(['smtp_host' => ["label" => 'SMTP Host', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_user' => ["label" => 'SMTP Username', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_pass' => ["label" => 'SMTP Password', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_port' => ["label" => 'SMTP Port', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_encryption' => ["label" => 'Email Encryption', "rules" => 'trim|required']]);
        }

        if ($this->validation->run() !== false) {
            $arrayConfig = ['email' => $this->request->getPost('email'), 'protocol' => $protocol, 'branch_id' => $branchID];
            if ($protocol == 'smtp') {
                $arrayConfig['smtp_host'] = $this->request->getPost("smtp_host");
                $arrayConfig['smtp_user'] = $this->request->getPost("smtp_user");
                $arrayConfig['smtp_pass'] = $this->request->getPost("smtp_pass");
                $arrayConfig['smtp_port'] = $this->request->getPost("smtp_port");
                $arrayConfig['smtp_auth'] = $this->request->getPost("smtp_auth");
                $arrayConfig['smtp_encryption'] = $this->request->getPost("smtp_encryption");
            }

            $this->db->table('branch_id')->where();
            $q = $builder->get('email_config');
            if ($q->num_rows() == 0) {
                $this->db->table('email_config')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('email_config')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function emailTemplateSave()
    {
        $this->validation->setRules(['subject' => ["label" => translate('subject'), "rules" => 'required']]);
        $this->validation->setRules(['template_body' => ["label" => translate('body'), "rules" => 'required']]);
        if ($this->validation->run() !== false) {
            $notified = isset($_POST['notify_enable']) ? 1 : 0;
            $templateID = $this->request->getPost('template_id');
            $arrayTemplate = ['subject' => $this->request->getPost('subject'), 'template_body' => $this->request->getPost('template_body'), 'notified' => $notified];
            $this->db->table('id')->where();
            $q = $builder->get('saas_email_templates');
            if ($q->num_rows() == 0) {
                $this->db->table('saas_email_templates')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('saas_email_templates')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function school_approved($id = '')
    {
        $getSchool = $this->saasModel->getPendingSchool($id);
        if (empty($getSchool)) {
            return redirect()->to(base_url('dashboard'));
        }

        $this->data['data'] = $getSchool;
        $this->data['title'] = translate('school') . " " . translate('subscription');
        $this->data['sub_page'] = 'saas/school_approved';
        $this->data['main_menu'] = 'saas';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    public function schoolApprovedSave()
    {
        if ($_POST !== []) {
            $saasRegisterId = $this->request->getPost('saas_register_id');
            $getSchool = $this->saasModel->getPendingSchool($saasRegisterId);
            if (empty($getSchool)) {
                ajax_access_denied();
            }

            $currentPackageID = $getSchool->package_id;
            $this->validation->setRules(['school_name' => ["label" => translate('school_name'), "rules" => 'required']]);
            $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'required|valid_email']]);
            $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'required']]);
            $this->validation->setRules(['currency' => ["label" => translate('currency'), "rules" => 'required']]);
            $this->validation->setRules(['currency_symbol' => ["label" => translate('currency_symbol'), "rules" => 'required']]);
            $this->validation->setRules(['country' => ["label" => translate('country'), "rules" => 'required']]);
            $this->validation->setRules(['city' => ["label" => translate('city'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                //update status
                $this->db->table('id')->update('saas_school_register', ['status' => 1, 'date_of_approval' => date('Y-m-d H:i:s')])->where();
                //stored in branch table
                $arrayBranch = ['name' => $this->request->getPost('school_name'), 'school_name' => $this->request->getPost('school_name'), 'email' => $this->request->getPost('email'), 'mobileno' => $this->request->getPost('mobileno'), 'currency' => $this->request->getPost('currency'), 'symbol' => $this->request->getPost('currency_symbol'), 'country' => $this->request->getPost('country'), 'city' => $this->request->getPost('city'), 'state' => $this->request->getPost('state'), 'address' => $this->request->getPost('address'), 'status' => 1];
                $this->db->table('branch')->insert();
                $schooolID = $this->db->insert_id();
                $inserData1 = ['branch_id' => $schooolID, 'name' => $getSchool->admin_name, 'sex' => $getSchool->gender = 1 !== 0 ? 'male' : 'female', 'mobileno' => $getSchool->contact_number, 'joining_date' => date("Y-m-d"), 'email' => $getSchool->email];
                $inserData2 = ['username' => $getSchool->username, 'role' => 2];
                //random staff id generate
                $inserData1['staff_id'] = substr((string) app_generate_hash(), 3, 7);
                //save employee information in the database
                $this->db->table('staff')->insert();
                $staffID = $this->db->insert_id();
                //save employee login credential information in the database
                $inserData2['active'] = 1;
                $inserData2['user_id'] = $staffID;
                $inserData2['password'] = $this->appLib->pass_hashed($getSchool->password);
                $this->db->table('login_credential')->insert();
                //school logo uploaded
                if (isset($_FILES["text_logo"]) && !empty($_FILES['text_logo']['name'])) {
                    $fileInfo = pathinfo((string) $_FILES["text_logo"]["name"]);
                    $imgName = $schooolID . '.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["text_logo"]["tmp_name"], "uploads/app_image/logo-small-" . $imgName);
                    $fileUpload = true;
                } elseif (!empty($getSchool->logo)) {
                    copy('./uploads/saas_school_logo/' . $getSchool->logo, sprintf('./uploads/app_image/logo-small-%s.png', $schooolID));
                }

                $paymentData = [];
                if ($getSchool->payment_status == 1 && !empty($getSchool->payment_data)) {
                    $paymentData = json_decode($getSchool->payment_data, TRUE);
                }

                //saas data are prepared and stored in the database
                $this->saasModel->saveSchoolSaasData($currentPackageID, $schooolID, $paymentData);
                // send email subscription approval confirmation
                $arrayData['email'] = $getSchool->email;
                $arrayData['package_id'] = $getSchool->package_id;
                $arrayData['admin_name'] = $getSchool->admin_name;
                $arrayData['reference_no'] = $getSchool->reference_no;
                $arrayData['school_name'] = $getSchool->school_name;
                $arrayData['login_username'] = $getSchool->username;
                $arrayData['password'] = $getSchool->password;
                $arrayData['subscription_start_date'] = _d(date("Y-m-d"));
                $arrayData['invoice_url'] = base_url('subscription_review/' . $arrayData['reference_no']);
                $this->saas_emailModel->sentSubscriptionApprovalConfirmation($arrayData);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success', 'url' => base_url('saas/pending_request')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function getRejectsDetails()
    {
        if ($_POST !== []) {
            $this->data['school_id'] = $this->request->getPost('id');
            echo view('saas/getRejectsDetails_modal', $this->data);
        }
    }

    public function reject()
    {
        if ($_POST !== []) {
            $schoolID = $this->request->getPost('school_id');
            $comments = $this->request->getPost('comments');
            //update status
            $this->db->table('id')->update('saas_school_register', ['status' => 2, 'comments' => $comments, 'date_of_approval' => date('Y-m-d H:i:s')])->where();
            // send email subscription reject
            $getSchool = $this->saasModel->getPendingSchool($schoolID);
            $arrayData['email'] = $getSchool->email;
            $arrayData['admin_name'] = $getSchool->admin_name;
            $arrayData['reference_no'] = $getSchool->reference_no;
            $arrayData['school_name'] = $getSchool->school_name;
            $arrayData['reject_reason'] = $comments;
            $this->saas_emailModel->sentSchoolSubscriptionReject($arrayData);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
            echo json_encode($array);
        }
    }

    public function delete($id)
    {
        if (!empty($id)) {
            $logo = $db->table('saas_school_register')->get('saas_school_register')->row()->logo;
            $this->db->table('id')->where();
            $this->db->table('saas_school_register')->delete();
            if ($db->affectedRows() > 0 && !empty($logo)) {
                $existFilePath = FCPATH . 'uploads/saas_school_logo/' . $logo;
                if (file_exists($existFilePath)) {
                    unlink($existFilePath);
                }
            }
        }
    }

    /* website FAQ manage script start */
    private function faq_validation()
    {
        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => translate('description'), "rules" => 'trim|required']]);
    }

    public function faqs()
    {
        if ($_POST !== []) {
            $this->faq_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->saasModel->save_faq($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['faqlist'] = $builder->get('saas_cms_faq_list')->result_array();
        $this->data['title'] = translate('subscription');
        $this->data['sub_page'] = 'saas/faq';
        $this->data['main_menu'] = 'saas_setting';
        echo view('layout/index', $this->data);
    }

    public function faq_edit($id = '')
    {
        if ($_POST !== []) {
            $this->faq_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->saasModel->save_faq($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('saas/faqs');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['faq'] = $this->db->table('saas_cms_faq_list')->where('id', $id)->get()->getRowArray();
        $this->data['title'] = translate('subscription');
        $this->data['sub_page'] = 'saas/faq_edit';
        $this->data['main_menu'] = 'saas_setting';
        echo view('layout/index', $this->data);
    }

    public function faq_delete($id = '')
    {
        $this->db->table(['id' => $id])->delete("saas_cms_faq_list")->where();
    }

    /* website features manage script start */
    private function features_validation()
    {
        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => translate('description'), "rules" => 'trim|required']]);
        $this->validation->setRules(['icon' => ["label" => translate('icon'), "rules" => 'trim|required']]);
    }

    public function features()
    {
        if ($_POST !== []) {
            $this->features_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->saasModel->save_features($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['faqlist'] = $builder->get('saas_cms_features')->result_array();
        $this->data['title'] = translate('subscription');
        $this->data['sub_page'] = 'saas/features';
        $this->data['main_menu'] = 'saas_setting';
        echo view('layout/index', $this->data);
    }

    public function features_edit($id = '')
    {
        if ($_POST !== []) {
            $this->features_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->saasModel->save_features($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('saas/features');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['faq'] = $this->db->table('saas_cms_features')->where('id', $id)->get()->getRowArray();
        $this->data['title'] = translate('subscription');
        $this->data['sub_page'] = 'saas/features_edit';
        $this->data['main_menu'] = 'saas_setting';
        echo view('layout/index', $this->data);
    }

    public function features_delete($id = '')
    {
        $this->db->table(['id' => $id])->delete("saas_cms_features")->where();
    }

    public function transactions()
    {
        if (isset($_POST['search'])) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['getTransactions'] = $this->saasModel->getTransactions($start, $end);
        }

        $this->data['title'] = translate('subscription') . " " . translate('transactions');
        $this->data['sub_page'] = 'saas/transactions';
        $this->data['main_menu'] = 'saas';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function send_test_email()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['test_email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
            if ($this->validation->run() == true) {
                $branchID = 9999;
                $getConfig = $builder->select('id')->get_where('email_config', ['branch_id' => $branchID])->row();
                if (empty($getConfig)) {
                    session()->set_flashdata('test-email-error', 'Email Configuration not found.');
                    $array = ['status' => 'success'];
                    echo json_encode($array);
                    exit;
                }

                $recipient = $this->request->getPost('test_email');
                $this->Mailer = service('mailer');
                $data = [];
                $data['branch_id'] = $branchID;
                $data['recipient'] = $recipient;
                $data['subject'] = 'Cleve School SMTP Config Testing';
                $data['message'] = 'This is test SMTP config email. <br />If you received this message that means that your SMTP settings is set correctly.';
                $r = $this->mailer->send($data, true);
                if ($r == "true") {
                    session()->set_flashdata('test-email-success', 1);
                } else {
                    session()->set_flashdata('test-email-error', 'Mailer Error: ' . $r);
                }

                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    //fahad test - Registeration forms
    public function registration_forms()
    {
        if ($this->request->getPost('search')) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $startDate = date("Y-m-d", strtotime($daterange[0]));
            $endDate = date("Y-m-d", strtotime($daterange[1]));
            $status = $this->request->getPost('status_filter');
            $this->data['requests'] = $this->saasModel->getFilteredRegistrationRequests($startDate, $endDate, $status);
        } else {
            $this->data['requests'] = $this->saasModel->getRegistrationRequests();
        }

        $this->data['saas_packages'] = $this->saasModel->getSaasPackage();
        $this->data['title'] = translate('Registration Forms');
        $this->data['sub_page'] = 'saas/registration_forms';
        $this->data['main_menu'] = 'saas';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    // Function to update the registration status
    public function updateRequestStatus()
    {
        $this->validation = service('validation');
        $this->saas = new \App\Models\SaasModel();
        // Load saas_model
        $this->validation->setRules(['request_id' => ["label" => 'Request ID', "rules" => 'required']]);
        $this->validation->setRules(['name' => ["label" => 'Name', "rules" => 'required']]);
        $this->validation->setRules(['organization_type' => ["label" => 'Organization Type', "rules" => 'required']]);
        $this->validation->setRules(['school_name' => ["label" => 'School Name', "rules" => 'required']]);
        $this->validation->setRules(['number_of_branches' => ["label" => 'Number of Branches', "rules" => 'numeric']]);
        $this->validation->setRules(['number_of_students' => ["label" => 'Number of Students', "rules" => 'required|numeric']]);
        $this->validation->setRules(['email' => ["label" => 'Email', "rules" => 'required|valid_email']]);
        $this->validation->setRules(['phone_number' => ["label" => 'Phone Number', "rules" => 'required']]);
        $this->validation->setRules(['status' => ["label" => 'Status', "rules" => 'required']]);
        $this->validation->setRules(['comments' => ["label" => 'Comments', "rules" => 'max_length[255]']]);
        $this->validation->setRules(['package_id' => ["label" => 'Package ID', "rules" => 'numeric']]);
        if ($this->validation->run() == FALSE) {
            // Handle validation errors
            $errors = validation_errors();
            session()->set_flashdata('alert-message-error', $errors);
        } else {
            // Fetch Saas packages
            $saasPackages = $this->saasModel->getSaasPackage();
            $data = ['name' => $this->request->getPost('name'), 'organization_type' => $this->request->getPost('organization_type'), 'school_name' => $this->request->getPost('school_name'), 'number_of_branches' => $this->request->getPost('number_of_branches') ?: null, 'number_of_students' => $this->request->getPost('number_of_students'), 'email' => $this->request->getPost('email'), 'phone_number' => $this->request->getPost('phone_number'), 'status' => $this->request->getPost('status'), 'comments' => $this->request->getPost('comments'), 'package_id' => $this->request->getPost('package_id') ?: null, 'updated_at' => date('Y-m-d H:i:s')];
            $this->db->table('id')->where();
            $this->db->table('saas_registration_form')->update();
            session()->set_flashdata('alert-message-success', 'Request updated successfully.');
        }

        return redirect()->to(base_url('saas/registration_forms'));
    }

    // Function to delete a registration
    public function deleteRegistration()
    {
        $id = $this->request->getPost('id');
        if ($this->saasModel->deleteRegistration($id)) {
            echo json_encode(['status' => 'success', 'message' => translate('delete_successful')]);
        } else {
            echo json_encode(['status' => 'error', 'message' => translate('delete_failed')]);
        }
    }
}
