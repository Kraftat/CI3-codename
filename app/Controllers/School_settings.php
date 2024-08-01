<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SchoolModel;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : School_settings.php
 * @copyright : Reserved RamomCoder Team
 */
class School_settings extends AdminController

{
    /**
     * @var mixed
     */
    public $Mailer;

    public $twilio;

    public $textlocal;

    public $msg91;

    public $customSms;

    public $clickatell;

    public $bulksmsbd;

    public $bulk;

    protected $db;


    /**
     * @var App\Models\SchoolModel
     */
    public $school;

    public $schoolModel;

    public $appLib;

    public $validation;

    public $input;

    public $load;

    public $applicationModel;

    public $session;

    public $mailer;

    public function __construct()
    {


        parent::__construct();









        $this->twilio = service('twilio');$this->textlocal = service('textlocal');$this->msg91 = service('msg91');$this->mailer = service('mailer');$this->customSms = service('customSms');$this->clickatell = service('clickatell');$this->bulksmsbd = service('bulksmsbd');$this->bulk = service('bulk');$this->appLib = service('appLib'); 
$this->school = new \App\Models\SchoolModel();
    }

    public function index()
    {
        if (!get_permission('school_settings', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('school_settings', 'is_edit')) {
                ajax_access_denied();
            }

            if ($this->appLib->licenceVerify() == false) {
                set_alert('error', translate('invalid_license'));
                $array = ['status' => 'access_denied'];
                echo json_encode($array);
                exit;
            }

            $this->validation->setRules(['branch_name' => ["label" => translate('branch_name'), "rules" => 'trim|required|callback_unique_branchname']]);
            $this->validation->setRules(['school_name' => ["label" => translate('school_name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
            $this->validation->setRules(['currency' => ["label" => translate('currency'), "rules" => 'trim|required']]);
            $this->validation->setRules(['currency_symbol' => ["label" => translate('currency_symbol'), "rules" => 'trim|required']]);
            $this->validation->setRules(['due_days' => ["label" => translate('due_days'), "rules" => 'trim|required|numeric']]);
            if (isset($_POST['generate_student'])) {
                $this->validation->setRules(['stu_username_prefix' => ["label" => translate('username_prefix'), "rules" => 'trim|required']]);
                $this->validation->setRules(['stu_default_password' => ["label" => translate('default_password'), "rules" => 'trim|required']]);
            }

            if (isset($_POST['generate_guardian'])) {
                $this->validation->setRules(['grd_username_prefix' => ["label" => translate('username_prefix'), "rules" => 'trim|required']]);
                $this->validation->setRules(['grd_default_password' => ["label" => translate('default_password'), "rules" => 'trim|required']]);
            }

            if (isset($_POST['reg_prefix_enable'])) {
                $this->validation->setRules(['reg_start_from' => ["label" => translate('register_no') . " " . translate('start_from'), "rules" => 'trim|required|numeric']]);
                $this->validation->setRules(['institution_code' => ["label" => translate('institution_code'), "rules" => 'trim|required']]);
                $this->validation->setRules(['reg_prefix_digit' => ["label" => translate('register_no') . " " . translate('digit'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['weekends[]' => ["label" => translate('weekends'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $post['brance_id'] = $branchID;
                $this->schoolModel->branchUpdate($post);
                $id = $branchID;
                if (isset($_FILES["logo_file"]) && !empty($_FILES['logo_file']['name'])) {
                    $fileInfo = pathinfo((string) $_FILES["logo_file"]["name"]);
                    $imgName = $id . '.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["logo_file"]["tmp_name"], "uploads/app_image/logo-" . $imgName);
                }

                if (isset($_FILES["text_logo"]) && !empty($_FILES['text_logo']['name'])) {
                    $fileInfo = pathinfo((string) $_FILES["text_logo"]["name"]);
                    $imgName = $id . '.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["text_logo"]["tmp_name"], "uploads/app_image/logo-small-" . $imgName);
                }

                if (isset($_FILES["print_file"]) && !empty($_FILES['print_file']['name'])) {
                    $fileInfo = pathinfo((string) $_FILES["print_file"]["name"]);
                    $imgName = $id . '.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["print_file"]["tmp_name"], "uploads/app_image/printing-logo-" . $imgName);
                }

                if (isset($_FILES["report_card"]) && !empty($_FILES['report_card']['name'])) {
                    $fileInfo = pathinfo((string) $_FILES["report_card"]["name"]);
                    $imgName = $id . '.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["report_card"]["tmp_name"], "uploads/app_image/report-card-logo-" . $imgName);
                }

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

        $this->data['branchID'] = $branchID;
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['school'] = $this->schoolModel->get('branch', ['id' => $branchID], true);
        $this->data['title'] = translate('school_settings');
        $this->data['sub_page'] = 'school_settings/school';
        $this->data['main_menu'] = 'school_m';
        echo view('layout/index', $this->data);
    }

    public function unique_branchname($name)
    {
        $branchID = $this->schoolModel->getBranchID();
        $this->db->where_not_in('id', $branchID);
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

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['config'] = $this->schoolModel->get('payment_config', ['branch_id' => $branchID], true);
        $this->data['sub_page'] = 'school_settings/payment_gateway';
        $this->data['main_menu'] = 'school_m';
        $this->data['title'] = translate('payment_control');
        echo view('layout/index', $this->data);
    }

    public function smsconfig()
    {
        if (!get_permission('sms_settings', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['api'] = $this->schoolModel->getSmsConfig($branchID);
        $this->data['title'] = translate('sms_settings');
        $this->data['sub_page'] = 'school_settings/smsconfig';
        $this->data['main_menu'] = 'school_m';
        echo view('layout/index', $this->data);
    }

    public function sms_active()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $providerID = $this->request->getPost('sms_service_provider');
        $this->db->table('branch_id')->update('sms_credential', ['is_active' => 0])->where();
        $this->db->table(['sms_api_id' => $providerID, 'branch_id' => $branchID])->update('sms_credential', ['is_active' => 1])->where();
        if ($db->affectedRows() > 0) {
            $message = translate('information_has_been_saved_successfully');
        } else {
            $message = translate("SMS configuration not found");
        }

        $array = ['status' => 'success', 'message' => $message];
        echo json_encode($array);
    }

    public function twilio()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['twilio_sid' => ["label" => translate('account_sid'), "rules" => 'trim|required']]);
        $this->validation->setRules(['twilio_auth_token' => ["label" => translate('authentication_token'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sender_number' => ["label" => translate('sender_number'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayTwilio = ['field_one' => $this->request->getPost('twilio_sid'), 'field_two' => $this->request->getPost('twilio_auth_token'), 'field_three' => $this->request->getPost('sender_number')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arrayTwilio['sms_api_id'] = 1;
                $arrayTwilio['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function clickatell()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['clickatell_user' => ["label" => translate('username'), "rules" => 'trim|required']]);
        $this->validation->setRules(['clickatell_password' => ["label" => translate('password'), "rules" => 'trim|required']]);
        $this->validation->setRules(['clickatell_api' => ["label" => translate('api_key'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sender_number' => ["label" => translate('sender_number'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayTwilio = ['field_one' => $this->request->getPost('clickatell_user'), 'field_two' => $this->request->getPost('clickatell_password'), 'field_three' => $this->request->getPost('clickatell_api'), 'field_four' => $this->request->getPost('sender_number')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arrayTwilio['sms_api_id'] = 2;
                $arrayTwilio['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function msg91()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['msg91_auth_key' => ["label" => translate('authkey'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sender_id' => ["label" => translate('sender_id'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayTwilio = ['field_one' => $this->request->getPost('msg91_auth_key'), 'field_two' => $this->request->getPost('sender_id')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arrayTwilio['sms_api_id'] = 3;
                $arrayTwilio['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function bulksms()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['bulk_sms_username' => ["label" => translate('username'), "rules" => 'trim|required']]);
        $this->validation->setRules(['bulk_sms_password' => ["label" => translate('password'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayTwilio = ['field_one' => $this->request->getPost('bulk_sms_username'), 'field_two' => $this->request->getPost('bulk_sms_password')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arrayTwilio['sms_api_id'] = 4;
                $arrayTwilio['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function textlocal()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['textlocal_sender_id' => ["label" => translate('sender_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['api_key' => ["label" => translate('api_key'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayTwilio = ['field_one' => $this->request->getPost('textlocal_sender_id'), 'field_two' => $this->request->getPost('api_key')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arrayTwilio['sms_api_id'] = 5;
                $arrayTwilio['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function sms_country()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required']]);
        $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sender_id' => ["label" => translate('sender_id'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arraySMScountry = ['field_one' => $this->request->getPost('username'), 'field_two' => $this->request->getPost('password'), 'field_three' => $this->request->getPost('sender_id')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arraySMScountry['sms_api_id'] = 6;
                $arraySMScountry['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function bulksmsbd()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['sender_id' => ["label" => translate('sender_id'), "rules" => 'trim|required']]);
        $this->validation->setRules(['api_key' => ["label" => translate('api_key'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arraySMScountry = ['field_one' => $this->request->getPost('sender_id'), 'field_two' => $this->request->getPost('api_key')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arraySMScountry['sms_api_id'] = 7;
                $arraySMScountry['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function customSms()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['api_url' => ["label" => translate('api_url'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arraycustomSms = ['field_one' => $this->request->getPost('api_url')];
            $this->db->table('sms_api_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_credential');
            if ($q->num_rows() == 0) {
                $arraycustomSms['sms_api_id'] = 8;
                $arraycustomSms['branch_id'] = $branchID;
                $this->db->table('sms_credential')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_credential')->update();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function smstemplate()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['templatelist'] = $this->appLib->get_table('sms_template');
        $this->data['title'] = translate('sms_settings');
        $this->data['sub_page'] = 'school_settings/smstemplate';
        $this->data['main_menu'] = 'school_m';
        echo view('layout/index', $this->data);
    }

    public function smsTemplateeSave()
    {
        if (!get_permission('sms_settings', 'is_add')) {
            access_denied();
        }

        $this->validation->setRules(['template_body' => ["label" => translate('body'), "rules" => 'required']]);
        if ($this->validation->run() !== false) {
            $branchID = $this->schoolModel->getBranchID();
            $templateID = $this->request->getPost('template_id');
            $dltTemplateID = $this->request->getPost('dlt_template_id');
            $notifyStudent = isset($_POST['notify_student']) ? 1 : 0;
            $notifyParent = isset($_POST['notify_parent']) ? 1 : 0;
            $arrayTemplate = ['notify_student' => $notifyStudent, 'notify_parent' => $notifyParent, 'dlt_template_id' => $dltTemplateID, 'template_body' => $this->request->getPost('template_body'), 'template_id' => $templateID, 'branch_id' => $branchID];
            $this->db->table('template_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('sms_template_details');
            if ($q->num_rows() == 0) {
                $this->db->table('sms_template_details')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('sms_template_details')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function emailconfig()
    {
        if (!get_permission('email_settings', 'is_view')) {
            access_denied();
        }

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

        $branchID = $this->schoolModel->getBranchID();
        $this->data['config'] = $this->schoolModel->get('email_config', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('email_settings');
        $this->data['sub_page'] = 'school_settings/emailconfig';
        $this->data['main_menu'] = 'school_m';
        echo view('layout/index', $this->data);
        return null;
    }

    public function saveEmailConfig()
    {
        if (!get_permission('email_settings', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $protocol = $this->request->getPost('protocol');
        $this->validation->setRules(['email' => ["label" => 'System Email', "rules" => 'trim|required']]);
        $this->validation->setRules(['protocol' => ["label" => 'Email Protocol', "rules" => 'trim|required']]);
        if ($protocol == 'smtp') {
            $this->validation->setRules(['smtp_host' => ["label" => 'SMTP Host', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_user' => ["label" => 'SMTP Username', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_pass' => ["label" => 'SMTP Password', "rules" => 'trim|required']]);
            $this->validation->setRules(['smtp_port' => ["label" => 'SMTP Port', "rules" => 'trim|required']]);
        }

        if ($this->validation->run() !== false) {
            $arrayConfig = ['email' => $this->request->getPost('email'), 'protocol' => $protocol, 'branch_id' => $branchID];
            if ($protocol == 'smtp') {
                $arrayConfig['smtp_host'] = $this->request->getPost("smtp_host");
                $arrayConfig['smtp_user'] = $this->request->getPost("smtp_user");
                $arrayConfig['smtp_pass'] = $this->request->getPost("smtp_pass");
                $arrayConfig['smtp_port'] = $this->request->getPost("smtp_port");
                $arrayConfig['smtp_encryption'] = $this->request->getPost("smtp_encryption");
                $arrayConfig['smtp_auth'] = $this->request->getPost("smtp_auth");
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

    public function emailtemplate()
    {
        if (!get_permission('email_settings', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['templatelist'] = $this->appLib->get_table('email_templates');
        $this->data['title'] = translate('email_settings');
        $this->data['sub_page'] = 'school_settings/emailtemplate';
        $this->data['main_menu'] = 'school_m';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        echo view('layout/index', $this->data);
    }

    public function emailTemplateSave()
    {
        if (!get_permission('email_settings', 'is_add')) {
            access_denied();
        }

        $this->validation->setRules(['subject' => ["label" => translate('subject'), "rules" => 'required']]);
        $this->validation->setRules(['template_body' => ["label" => translate('body'), "rules" => 'required']]);
        if ($this->validation->run() !== false) {
            $branchID = $this->applicationModel->get_branch_id();
            $notified = isset($_POST['notify_enable']) ? 1 : 0;
            $templateID = $this->request->getPost('template_id');
            $arrayTemplate = ['template_id' => $templateID, 'subject' => $this->request->getPost('subject'), 'template_body' => $this->request->getPost('template_body'), 'notified' => $notified, 'branch_id' => $branchID];
            $this->db->table('template_id')->where();
            $this->db->table('branch_id')->where();
            $q = $builder->get('email_templates_details');
            if ($q->num_rows() == 0) {
                $this->db->table('email_templates_details')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('email_templates_details')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    // transactions links enable / disabled
    public function accounting_links()
    {
        // check access permission
        if (!get_permission('accounting_links', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['transactions'] = $this->schoolModel->get('transactions_links', ['branch_id' => $branchID], true);
        $this->data['sub_page'] = 'school_settings/accounting_links';
        $this->data['main_menu'] = 'school_m';
        $this->data['title'] = translate('accounting_links');
        echo view('layout/index', $this->data);
    }

    public function accountingLinksSave()
    {
        // check access permission
        if (!get_permission('accounting_links', 'is_edit')) {
            ajax_access_denied();
        }

        if (isset($_POST['status'])) {
            $this->validation->setRules(['deposit' => ["label" => translate('deposit'), "rules" => 'trim|required']]);
            $this->validation->setRules(['expense' => ["label" => translate('expense'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['status' => ["label" => translate('status'), "rules" => 'trim']]);
        if ($this->validation->run() !== false) {
            $branchID = $this->schoolModel->getBranchID();
            $array = [];
            if (isset($_POST['status'])) {
                $array['status'] = 1;
                $array['deposit'] = $this->request->getPost('deposit');
                $array['expense'] = $this->request->getPost('expense');
            } else {
                $array['status'] = 0;
            }

            $array['branch_id'] = $branchID;
            $this->db->table('branch_id')->where();
            $query = $builder->get('transactions_links');
            if ($query->num_rows() > 0) {
                $this->db->table('id')->where();
                $this->db->table('transactions_links')->update();
            } else {
                $this->db->table('transactions_links')->insert();
            }

            $array = ['status' => 'success', 'message' => translate('information_has_been_saved_successfully')];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function live_class_config()
    {
        // check access permission
        if (!get_permission('live_class_config', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['config'] = $this->schoolModel->get('live_class_config', ['branch_id' => $branchID], true);
        $this->data['sub_page'] = 'school_settings/live_class_config';
        $this->data['main_menu'] = 'school_m';
        $this->data['title'] = translate('live_class') . " " . translate('settings');
        echo view('layout/index', $this->data);
    }

    public function liveClassSave()
    {
        // check access permission
        if (!get_permission('live_class_config', 'is_edit')) {
            ajax_access_denied();
        }

        $method = $this->request->getPost('method');
        if ($method == 'zoom') {
            $this->validation->setRules(['zoom_api_key' => ["label" => "Zoom API Key", "rules" => 'trim|required']]);
            $this->validation->setRules(['zoom_api_secret' => ["label" => "Zoom API Secret", "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $branchID = $this->schoolModel->getBranchID();
                $array = ['zoom_api_key' => $this->request->getPost('zoom_api_key'), 'zoom_api_secret' => $this->request->getPost('zoom_api_secret'), 'staff_api_credential' => empty($this->request->getPost('staff_api_credential')) ? 0 : 1, 'student_api_credential' => empty($this->request->getPost('student_api_credential')) ? 0 : 1, 'branch_id' => $branchID];
                $this->db->table('branch_id')->where();
                $query = $builder->get('live_class_config');
                if ($query->num_rows() > 0) {
                    $this->db->table('id')->where();
                    $this->db->table('live_class_config')->update();
                } else {
                    $this->db->table('live_class_config')->insert();
                }

                $array = ['status' => 'success', 'message' => translate('information_has_been_saved_successfully')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
        } elseif ($method == 'bbb') {
            $this->validation->setRules(['bbb_salt_key' => ["label" => "Salt Key", "rules" => 'trim|required']]);
            $this->validation->setRules(['bbb_server_base_url' => ["label" => "Server Base URL", "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $branchID = $this->schoolModel->getBranchID();
                $serverBaseUrl = $this->request->getPost('bbb_server_base_url');
                if (substr((string) $serverBaseUrl, strlen((string) $serverBaseUrl) - 1, 1) !== '/') {
                    $serverBaseUrl .= '/';
                }

                $array = ['bbb_salt_key' => $this->request->getPost('bbb_salt_key'), 'bbb_server_base_url' => $serverBaseUrl, 'branch_id' => $branchID];
                $this->db->table('branch_id')->where();
                $query = $builder->get('live_class_config');
                if ($query->num_rows() > 0) {
                    $this->db->table('id')->where();
                    $this->db->table('live_class_config')->update();
                } else {
                    $this->db->table('live_class_config')->insert();
                }

                $array = ['status' => 'success', 'message' => translate('information_has_been_saved_successfully')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
        }

        echo json_encode($array);
    }

    public function whatsapp_setting()
    {
        // check access permission
        if (!get_permission('whatsapp_config', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css', 'vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js', 'vendor/dropify/js/dropify.min.js']];
        $this->data['whatsapp'] = $this->schoolModel->get('whatsapp_chat', ['branch_id' => $branchID], true);
        $this->data['sub_page'] = 'school_settings/whatsapp_settings';
        $this->data['main_menu'] = 'school_m';
        $this->data['title'] = translate('whatsapp_settings');
        echo view('layout/index', $this->data);
    }

    public function saveWhatsappConfig()
    {
        if (!get_permission('whatsapp_config', 'is_add')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['header_title' => ["label" => translate('header_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['subtitle' => ["label" => translate('subtitle'), "rules" => 'trim|required']]);
        $this->validation->setRules(['footer_text' => ["label" => translate('footer_text'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $arrayConfig = ['header_title' => $this->request->getPost('header_title'), 'subtitle' => $this->request->getPost('subtitle'), 'footer_text' => $this->request->getPost('footer_text'), 'frontend_enable_chat' => isset($_POST['frontend_enable_chat']) ? 1 : 0, 'backend_enable_chat' => isset($_POST['backend_enable_chat']) ? 1 : 0, 'branch_id' => $branchID];
            $this->db->table('branch_id')->where();
            $q = $builder->get('whatsapp_chat');
            if ($q->num_rows() == 0) {
                $this->db->table('whatsapp_chat')->insert();
            } else {
                $this->db->table('id')->where();
                $this->db->table('whatsapp_chat')->update();
            }

            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function saveWhatsappAgent()
    {
        if (!get_permission('whatsapp_config', 'is_add')) {
            ajax_access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['designation' => ["label" => translate('designation'), "rules" => 'trim|required']]);
        $this->validation->setRules(['whataspp_number' => ["label" => translate('whataspp_number'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['start_time' => ["label" => translate('start_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['end_time' => ["label" => translate('end_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['user_photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[user_photo]']]);
        if ($this->validation->run() !== false) {
            $arrayConfig = ['agent_name' => $this->request->getPost('name'), 'agent_designation' => $this->request->getPost('designation'), 'whataspp_number' => $this->request->getPost('whataspp_number'), 'start_time' => date("H:i", strtotime((string) $this->request->getPost('start_time'))), 'end_time' => date("H:i", strtotime((string) $this->request->getPost('end_time'))), 'weekend' => $this->request->getPost('weekend'), 'agent_image' => $this->schoolModel->uploadImage('whatsapp_agent'), 'enable' => isset($_POST['agent_active']) ? 1 : 0, 'branch_id' => $branchID];
            $agentID = $this->request->getPost('agent_id');
            if (empty($agentID)) {
                $this->db->table('whatsapp_agent')->insert();
            } else {
                unset($arrayConfig['branch_id']);
                $this->db->table('id')->where();
                $this->db->table('whatsapp_agent')->update();
            }

            set_alert('success', translate('the_configuration_has_been_updated'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function whatsappAgent_delete($id)
    {
        if (get_permission('whatsapp_config', 'is_delete')) {
            $agentImage = $db->table('whatsapp_agent')->get('whatsapp_agent')->row()->agent_image;
            $fileName = FCPATH . 'uploads/images/whatsapp_agent/' . $agentImage;
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('whatsapp_agent')->delete();
        }
    }

    public function getWhatsappDetails($id)
    {
        if (get_permission('whatsapp_config', 'is_edit') && !empty($id)) {
            $this->data['whatsapp'] = $this->appLib->getTable('whatsapp_agent', ['t.id' => $id], true);
            echo view('school_settings/whatsapp_editModal', $this->data);
        }
    }

    public function send_test_email()
    {
        if ($_POST !== []) {
            if (!get_permission('email_settings', 'is_add')) {
                ajax_access_denied();
            }

            $this->validation->setRules(['test_email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
            if ($this->validation->run() == true) {
                $branchID = $this->schoolModel->getBranchID();
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

    public function attendance_type()
    {
        // check access permission
        if (!moduleIsEnabled('attendance')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        if ($_POST !== []) {
            $arrayBranch = ['attendance_type' => $this->request->getPost('attendance_type')];
            $this->db->table('id')->where();
            $this->db->table('branch')->update();
            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['school'] = $this->schoolModel->get('branch', ['id' => $branchID], true);
        $this->data['sub_page'] = 'school_settings/attendance_type';
        $this->data['main_menu'] = 'school_m';
        $this->data['title'] = translate('attendance_type');
        echo view('layout/index', $this->data);
    }

    public function student_parent_panel()
    {
        // check access permission
        if (!get_permission('school_settings', 'is_view')) {
            access_denied();
        }

        $branchID = $this->schoolModel->getBranchID();
        if ($_POST !== []) {
            $mobileVisible = isset($_POST['teacher_mobile_visible']) ? 1 : 0;
            $emailVisible = isset($_POST['teacher_email_visible']) ? 1 : 0;
            $arrayBranch = ['teacher_mobile_visible' => $mobileVisible, 'teacher_email_visible' => $emailVisible, 'student_login' => $this->request->getPost('student_login'), 'parent_login' => $this->request->getPost('parent_login')];
            $this->db->table('id')->where();
            $this->db->table('branch')->update();
            $message = translate('the_configuration_has_been_updated');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['school'] = $this->schoolModel->get('branch', ['id' => $branchID], true);
        $this->data['sub_page'] = 'school_settings/student_parent_panel';
        $this->data['main_menu'] = 'school_m';
        $this->data['title'] = translate('student_parent_panel');
        echo view('layout/index', $this->data);
    }
}
