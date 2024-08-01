<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\OfflinePaymentsModel;
use App\Models\FeesModel;
/**
 * @package : Ramom school management system (Saas)
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Offline_payments.php
 * @copyright : Reserved RamomCoder Team
 */
class Offline_payments extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\OfflinePaymentsModel
     */
    public $offlinePayments;

    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    public $validation;

    public $input;

    public $offline_paymentsModel;

    public $load;

    public $applicationModel;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->offlinePayments = new \App\Models\OfflinePaymentsModel();
        $this->fees = new \App\Models\FeesModel();
    }

    /* offline payments type form validation rules */
    protected function type_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['type_name' => ["label" => translate('name'), "rules" => 'trim|required|callback_unique_type']]);
        $this->validation->setRules(['note' => ["label" => translate('note'), "rules" => 'trim']]);
    }

    /* offline payments type control */
    public function type()
    {
        if (!get_permission('offline_payments_type', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('offline_payments_type', 'is_add')) {
                ajax_access_denied();
            }

            $this->type_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->offline_paymentsModel->typeSave($post);
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
        $this->data['categorylist'] = $this->appLib->getTable('offline_payment_types');
        $this->data['title'] = translate('offline_payments') . " " . translate('type');
        $this->data['sub_page'] = 'offline_payments/type';
        $this->data['main_menu'] = 'offline_payments';
        echo view('layout/index', $this->data);
    }

    public function type_edit($id = '')
    {
        if (!get_permission('offline_payments_type', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->type_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->offline_paymentsModel->typeSave($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('offline_payments/type');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['category'] = $this->appLib->getTable('offline_payment_types', ['t.id' => $id], true);
        $this->data['title'] = translate('offline_payments') . " " . translate('type');
        $this->data['sub_page'] = 'offline_payments/type_edit';
        $this->data['main_menu'] = 'offline_payments';
        echo view('layout/index', $this->data);
    }

    public function type_delete($id = '')
    {
        if (get_permission('offline_payments_type', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('offline_payment_types')->delete();
        }
    }

    public function unique_type($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $typeID = $this->request->getPost('type_id');
        if (!empty($typeID)) {
            $this->db->where_not_in('id', $typeID);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('offline_payment_types')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_type", translate('already_taken'));
        return false;
    }

    /* offline fees payments  history */
    public function payments()
    {
        if (!get_permission('offline_payments', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $filter = [];
        if ($this->request->getPost('search')) {
            $filter['enroll.branch_id'] = $branchID;
            $filter['op.status'] = $this->request->getPost('payments_status');
        }

        $this->data['paymentslist'] = $this->offline_paymentsModel->getOfflinePaymentsList($filter);
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('offline_payments');
        $this->data['sub_page'] = 'offline_payments/history';
        $this->data['main_menu'] = 'offline_payments';
        echo view('layout/index', $this->data);
    }

    // get payments details modal
    public function getApprovelDetails()
    {
        if (get_permission('offline_payments', 'is_view')) {
            $this->data['payments_id'] = $this->request->getPost('id');
            echo view('offline_payments/approvel_modalView', $this->data);
        }
    }

    public function download($id = '', $file = '')
    {
        if (!empty($id) && !empty($file)) {
            $builder->select('orig_file_name,enc_file_name');
            $this->db->table('id')->where();
            $payments = $builder->get('offline_fees_payments')->row();
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
            if (!get_permission('offline_payments', 'is_view')) {
                access_denied();
            }

            $status = $this->request->getPost('status');
            if ($status != 1) {
                $arrayLeave = ['approved_by' => get_loggedin_user_id(), 'status' => $status, 'comments' => $this->request->getPost('comments'), 'approve_date' => date('Y-m-d H:i:s')];
                $id = $this->request->getPost('id');
                $this->db->table('id')->where();
                $this->db->table('offline_fees_payments')->update();
                if ($status == 2) {
                    $this->offline_paymentsModel->update($id);
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
            }

            return redirect()->to(base_url('offline_payments/payments'));
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
