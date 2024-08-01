<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SaasModel;
use App\Models\SaasEmailModel;
use App\Models\SaasOfflinePaymentsModel;
/**
 * @package : Ramom school management system (Saas)
 * @version : 3.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Saas_offline_payments.php
 * @copyright : Reserved RamomCoder Team
 */
class Saas_offline_payments extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\SaasModel
     */
    public $saas;

    /**
     * @var App\Models\SaasEmailModel
     */
    public $saasEmail;

    /**
     * @var App\Models\SaasOfflinePaymentsModel
     */
    public $saasOfflinePayments;

    public $validation;

    public $input;

    public $saas_offline_paymentsModel;

    public $load;

    public $saasModel;

    public $saas_emailModel;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->saas = new \App\Models\SaasModel();
        $this->saasEmail = new \App\Models\SaasEmailModel();
        $this->saasOfflinePayments = new \App\Models\SaasOfflinePaymentsModel();
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    /* offline payments type form validation rules */
    protected function type_validation()
    {
        $this->validation->setRules(['type_name' => ["label" => translate('name'), "rules" => 'trim|required|callback_unique_type']]);
        $this->validation->setRules(['note' => ["label" => translate('note'), "rules" => 'trim']]);
    }

    /* offline payments type control */
    public function type()
    {
        if ($_POST !== []) {
            $this->type_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->saas_offline_paymentsModel->typeSave($post);
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
        $this->data['categorylist'] = $builder->get('saas_offline_payment_types')->result_array();
        $this->data['title'] = translate('offline_payments') . " " . translate('type');
        $this->data['sub_page'] = 'saas_offline_payments/type';
        $this->data['main_menu'] = 'saas_offline_payments';
        echo view('layout/index', $this->data);
    }

    public function type_edit($id = '')
    {
        if ($_POST !== []) {
            $this->type_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->saas_offline_paymentsModel->typeSave($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('saas_offline_payments/type');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['category'] = $this->db->table('saas_offline_payment_types')->where('id', $id)->get()->getRowArray();
        $this->data['title'] = translate('offline_payments') . " " . translate('type');
        $this->data['sub_page'] = 'saas_offline_payments/type_edit';
        $this->data['main_menu'] = 'saas_offline_payments';
        echo view('layout/index', $this->data);
    }

    public function type_delete($id = '')
    {
        $this->db->table('id')->where();
        $this->db->table('saas_offline_payment_types')->delete();
    }

    public function unique_type($name)
    {
        $typeID = $this->request->getPost('type_id');
        if (!empty($typeID)) {
            $this->db->where_not_in('id', $typeID);
        }

        $this->db->table(['name' => $name])->where();
        $uniformRow = $builder->get('saas_offline_payment_types')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_type", translate('already_taken'));
        return false;
    }

    // get payments details modal
    public function getApprovelDetails()
    {
        $this->data['payments_id'] = $this->request->getPost('id');
        echo view('saas_offline_payments/approvel_modalView', $this->data);
    }

    public function download($id = '', $file = '')
    {
        if (!empty($id) && !empty($file)) {
            $builder->select('orig_file_name,enc_file_name');
            $this->db->table('id')->where();
            $payments = $builder->get('saas_offline_payments')->row();
            if ($file != $payments->enc_file_name) {
                access_denied();
            }

            helper('download');
            $fileData = file_get_contents('./uploads/attachments/offline_payments/' . $payments->enc_file_name);
            return $this->response->download($payments->orig_file_name, $fileData);
        }

        return null;
    }

    public function approved($id = '', $file = '')
    {
        if ($_POST !== []) {
            $status = $this->request->getPost('status');
            if ($status != 1) {
                $arrayLeave = ['approved_by' => get_loggedin_user_id(), 'status' => $status, 'comments' => $this->request->getPost('comments'), 'approve_date' => date('Y-m-d H:i:s')];
                $id = $this->request->getPost('id');
                $schoolRegisterID = $this->request->getPost('school_register_id');
                $this->db->table('id')->where();
                $this->db->table('saas_offline_payments')->update();
                if ($status != 1) {
                    $getSettings = $this->saasModel->getSettings('automatic_approval');
                    //automatic approval
                    if ($getSettings->automatic_approval == 1) {
                        // send email subscription approval confirmation
                        if ($status == 2) {
                            $this->saasModel->automaticSubscriptionApproval($schoolRegisterID, $this->data['global_config']['currency'], $this->data['global_config']['currency_symbol']);
                        }

                        // send email subscription reject
                        if ($status == 3) {
                            $getSchool = $this->saasModel->getPendingSchool($schoolRegisterID);
                            $this->saas_offline_paymentsModel->update($schoolRegisterID, $status);
                            $arrayData['email'] = $getSchool->email;
                            $arrayData['admin_name'] = $getSchool->admin_name;
                            $arrayData['reference_no'] = $getSchool->reference_no;
                            $arrayData['school_name'] = $getSchool->school_name;
                            $arrayData['reject_reason'] = $comments;
                            $this->saas_emailModel->sentSchoolSubscriptionReject($arrayData);
                        }
                    }
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
            }

            return redirect()->to(base_url('saas/pending_request'));
        }

        return null;
    }

    public function getTypeInstruction()
    {
        if ($_POST !== []) {
            $typeID = $this->request->getPost('typeID');
            if (empty($typeID)) {
                echo null;
                exit;
            }

            $r = $db->table('offline_payment_types')->get('offline_payment_types')->row();
            if (!empty($r->note)) {
                echo $r->note;
            } else {
                echo "";
            }
        }
    }
}
