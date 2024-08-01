<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 6.6
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Fees.php
 * @copyright : Reserved RamomCoder Team
 */
class Fees extends AdminController

{
    /**
     * @var mixed
     */
    public $Html2pdf;

    public $fees_model;

    public $appLib;

    protected $db;

    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $validation;

    public $input;

    public $feesModel;

    public $load;

    public $applicationModel;

    public $html2pdf;

    public $emailModel;

    public $smsModel;

    public function __construct()
    {

        parent::__construct();


        $this->html2pdf = service('html2pdf');$this->appLib = service('appLib'); 
$this->fees = new \App\Models\FeesModel();
        $this->email = new \App\Models\EmailModel();
        if (!moduleIsEnabled('student_accounting')) {
            access_denied();
        }
    }

    public function index()
    {
        return redirect()->to(base_url('fees/type'));
    }

    /* fees type form validation rules */
    protected function type_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['type_name' => ["label" => translate('name'), "rules" => 'trim|required|callback_unique_type']]);
    }

    /* fees type control */
    public function type()
    {
        if (!get_permission('fees_type', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('fees_type', 'is_add')) {
                ajax_access_denied();
            }

            $this->type_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->feesModel->typeSave($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['categorylist'] = $this->appLib->getTable('fees_type', ['system' => 0]);
        $this->data['title'] = translate('fees_type');
        $this->data['sub_page'] = 'fees/type';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function type_edit($id = '')
    {
        if (!get_permission('fees_type', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->type_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->feesModel->typeSave($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('fees/type');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['category'] = $this->appLib->getTable('fees_type', ['t.id' => $id], true);
        $this->data['title'] = translate('fees_type');
        $this->data['sub_page'] = 'fees/type_edit';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function type_delete($id = '')
    {
        if (get_permission('fees_type', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('fees_type')->delete();
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
        $uniformRow = $builder->get('fees_type')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_type", translate('already_taken'));
        return false;
    }

    public function group($branchId = '')
    {
        if (!get_permission('fees_group', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('fees_group', 'is_add')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['name' => ["label" => translate('group_name'), "rules" => 'trim|required']]);
            $elems = $this->request->getPost('elem');
            $sel = 0;
            if (count($elems) > 0) {
                foreach ($elems as $key => $value) {
                    if (isset($value['fees_type_id'])) {
                        $sel++;
                        $this->validation->setRules(['elem[' . $key . '][due_date]' => ["label" => translate('due_date'), "rules" => 'trim|required']]);
                        $this->validation->setRules(['elem[' . $key . '][amount]' => ["label" => translate('amount'), "rules" => 'trim|required|greater_than[0]']]);
                    }
                }
            }

            if ($this->validation->run() !== false) {
                if ($sel != 0) {
                    $arrayGroup = ['name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description'), 'session_id' => get_session_id(), 'branch_id' => $this->applicationModel->get_branch_id()];
                    $this->db->table('fee_groups')->insert();
                    $groupID = $this->db->insert_id();
                    foreach ($elems as $row) {
                        if (isset($row['fees_type_id'])) {
                            $arrayData = ['fee_groups_id' => $groupID, 'fee_type_id' => $row['fees_type_id'], 'due_date' => date("Y-m-d", strtotime((string) $row['due_date'])), 'amount' => $row['amount']];
                            $this->db->table(['fee_groups_id' => $groupID, 'fee_type_id' => $row['fees_type_id']])->where();
                            $query = $builder->get("fee_groups_details");
                            if ($query->num_rows() == 0) {
                                $this->db->table('fee_groups_details')->insert();
                            }
                        }
                    }

                    set_alert('success', translate('information_has_been_saved_successfully'));
                } else {
                    set_alert('error', 'At least one type has to be selected.');
                }

                $url = base_url('fees/group');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchId;
        $this->data['categorylist'] = $this->appLib->getTable('fee_groups', ['t.session_id' => get_session_id(), 't.system' => 0]);
        $this->data['title'] = translate('fees_group');
        $this->data['sub_page'] = 'fees/group';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function group_edit($id = '')
    {
        if (!get_permission('fees_group', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->validation->setRules(['name' => ["label" => translate('group_name'), "rules" => 'trim|required']]);
            $elems = $this->request->getPost('elem');
            $sel = [];
            if (count($elems) > 0) {
                foreach ($elems as $key => $value) {
                    if (isset($value['fees_type_id'])) {
                        $sel[] = $value['fees_type_id'];
                        $this->validation->setRules(['elem[' . $key . '][due_date]' => ["label" => translate('due_date'), "rules" => 'trim|required']]);
                        $this->validation->setRules(['elem[' . $key . '][amount]' => ["label" => translate('amount'), "rules" => 'trim|required|greater_than[0]']]);
                    }
                }
            }

            if ($this->validation->run() !== false) {
                if ($sel !== []) {
                    $groupID = $this->request->getPost('group_id');
                    $arrayGroup = ['name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description')];
                    $this->db->table('id')->where();
                    $this->db->table('fee_groups')->update();
                    foreach ($elems as $row) {
                        if (isset($row['fees_type_id'])) {
                            $arrayData = ['fee_groups_id' => $groupID, 'fee_type_id' => $row['fees_type_id'], 'due_date' => date("Y-m-d", strtotime((string) $row['due_date'])), 'amount' => $row['amount']];
                            $this->db->table(['fee_groups_id' => $groupID, 'fee_type_id' => $row['fees_type_id']])->where();
                            $query = $builder->get("fee_groups_details");
                            if ($query->num_rows() == 0) {
                                $this->db->table('fee_groups_details')->insert();
                            } else {
                                $this->db->table('id')->where();
                                $this->db->table('fee_groups_details')->update();
                            }
                        }
                    }

                    $this->db->where_not_in('fee_type_id', $sel);
                    $this->db->table('fee_groups_id')->where();
                    $this->db->table('fee_groups_details')->delete();
                    set_alert('success', translate('information_has_been_updated_successfully'));
                } else {
                    set_alert('error', 'At least one type has to be selected.');
                }

                $url = base_url('fees/group');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['group'] = $this->appLib->getTable('fee_groups', ['t.id' => $id], true);
        $this->data['title'] = translate('fees_group');
        $this->data['sub_page'] = 'fees/group_edit';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function group_delete($id)
    {
        if (get_permission('fees_group', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('fee_groups')->delete();
            if ($db->affectedRows() > 0) {
                $this->db->table('fee_groups_id')->where();
                $this->db->table('fee_groups_details')->delete();
            }
        }
    }

    /* fees type form validation rules */
    protected function fine_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['group_id' => ["label" => translate('group_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['fine_type_id' => ["label" => translate('fees_type'), "rules" => 'trim|required|callback_check_feetype']]);
        $this->validation->setRules(['fine_type' => ["label" => translate('fine_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['fine_value' => ["label" => translate('fine') . " " . translate('value'), "rules" => 'trim|required|numeric|greater_than[0]']]);
        $this->validation->setRules(['fee_frequency' => ["label" => translate('late_fee_frequency'), "rules" => 'trim|required']]);
    }

    public function fine_setup()
    {
        if (!get_permission('fees_fine_setup', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (!get_permission('fees_fine_setup', 'is_add')) {
                ajax_access_denied();
            }

            $this->fine_validation();
            if ($this->validation->run() !== false) {
                $insertData = ['group_id' => $this->request->getPost('group_id'), 'type_id' => $this->request->getPost('fine_type_id'), 'fine_value' => $this->request->getPost('fine_value'), 'fine_type' => $this->request->getPost('fine_type'), 'fee_frequency' => $this->request->getPost('fee_frequency'), 'branch_id' => $branchID, 'session_id' => get_session_id()];
                $this->db->table('fee_fine')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['finelist'] = $this->appLib->getTable('fee_fine');
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fine_setup');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/fine_setup';
        echo view('layout/index', $this->data);
    }

    public function fine_setup_edit($id = '')
    {
        if (!get_permission('fees_fine_setup', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $this->fine_validation();
            if ($this->validation->run() !== false) {
                $insertData = ['group_id' => $this->request->getPost('group_id'), 'type_id' => $this->request->getPost('fine_type_id'), 'fine_value' => $this->request->getPost('fine_value'), 'fine_type' => $this->request->getPost('fine_type'), 'fee_frequency' => $this->request->getPost('fee_frequency'), 'branch_id' => $branchID, 'session_id' => get_session_id()];
                $this->db->table('id')->where();
                $this->db->table('fee_fine')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('fees/fine_setup');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['fine'] = $this->appLib->getTable('fee_fine', ['t.id' => $id], true);
        $this->data['title'] = translate('fine_setup');
        $this->data['sub_page'] = 'fees/fine_setup_edit';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function check_feetype($id)
    {
        $this->request->getPost('group_id');
        $fineID = $this->request->getPost('fine_id');
        if (!empty($fineID)) {
            $this->db->where_not_in('id', $fineID);
        }

        $this->db->table('group_id')->where();
        $this->db->table('type_id')->where();
        $query = $builder->get('fee_fine');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("check_feetype", translate('already_taken'));
            return false;
        }

        return true;
    }

    public function fine_delete($id)
    {
        if (get_permission('fees_fine_setup', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('fee_fine')->delete();
        }
    }

    public function allocation()
    {
        if (!get_permission('fees_allocation', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['fee_group_id'] = $this->request->getPost('fee_group_id');
            $this->data['branch_id'] = $branchID;
            $this->data['studentlist'] = $this->feesModel->getStudentAllocationList($this->data['class_id'], $this->data['section_id'], $this->data['fee_group_id'], $branchID);
        }

        if (isset($_POST['save'])) {
            $studentArray = $this->request->getPost('stu_operations');
            $studentIds = $this->request->getPost('student_ids');
            $studentSelArray = $studentArray ?? [];
            $delStudent = array_diff($studentIds, $studentSelArray);
            $feeGroupID = $this->request->getPost('fee_group_id');
            foreach ($studentArray as $value) {
                $arrayData = ['student_id' => $value, 'group_id' => $feeGroupID, 'session_id' => get_session_id(), 'branch_id' => $branchID];
                $this->db->table($arrayData)->where();
                $q = $builder->get('fee_allocation');
                if ($q->num_rows() == 0) {
                    $this->db->table('fee_allocation')->insert();
                }
            }

            if ($delStudent !== []) {
                $this->db->where_in('student_id', $delStudent);
                $this->db->table('group_id')->where();
                $this->db->table('session_id')->where();
                $this->db->table('fee_allocation')->delete();
            }

            set_alert('success', translate('information_has_been_saved_successfully'));
            return redirect()->to(base_url('fees/allocation'));
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_allocation');
        $this->data['sub_page'] = 'fees/allocation';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
        return null;
    }

    public function allocation_save()
    {
        if (!get_permission('fees_allocation', 'is_add')) {
            access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $studentArray = $this->request->getPost('stu_operations');
            $studentIds = $this->request->getPost('student_ids');
            $studentSelArray = $studentArray ?? [];
            $delStudent = array_diff($studentIds, $studentSelArray);
            $feeGroupID = $this->request->getPost('fee_group_id');
            if (!empty($studentSelArray)) {
                foreach ($studentArray as $value) {
                    $arrayData = ['student_id' => $value, 'group_id' => $feeGroupID, 'session_id' => get_session_id(), 'branch_id' => $branchID];
                    $this->db->table($arrayData)->where();
                    $q = $builder->get('fee_allocation');
                    if ($q->num_rows() == 0) {
                        $this->db->table('fee_allocation')->insert();
                    }
                }
            }

            if ($delStudent !== []) {
                $this->db->where_in('student_id', $delStudent);
                $this->db->table('group_id')->where();
                $this->db->table('session_id')->where();
                $this->db->table('fee_allocation')->delete();
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
        }
    }

    /* student fees invoice search user interface */
    public function invoice_list()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost('search')) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['invoicelist'] = $this->feesModel->getInvoiceList($this->data['class_id'], $this->data['section_id'], $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('payments_history');
        $this->data['sub_page'] = 'fees/invoice_list';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function invoice_delete($enrollID = '')
    {
        if (!get_permission('invoice', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('student_id')->where();
        $result = $builder->get('fee_allocation')->result_array();
        foreach ($result as $value) {
            $this->db->table('allocation_id')->where();
            $this->db->table('fee_payment_history')->delete();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('student_id')->where();
        $this->db->table('fee_allocation')->delete();
    }

    /* invoice user interface with information are controlled here */
    public function invoice($enrollID = '')
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $basic = $this->feesModel->getInvoiceBasic($enrollID);
        if (empty($basic)) {
            return redirect()->to(base_url('dashboard'));
        }

        $this->data['invoice'] = $this->feesModel->getInvoiceStatus($enrollID);
        $this->data['basic'] = $basic;
        $this->data['title'] = translate('invoice_history');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/collect';
        echo view('layout/index', $this->data);
        return null;
    }

    public function invoicePrint()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->data['student_array'] = $this->request->getPost('student_id');
            echo view('fees/invoicePrint', $this->data, true);
        }
    }

    public function invoicePDFdownload()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->data['student_array'] = $this->request->getPost('student_id');
            $html = view('fees/invoicePDFdownload', $this->data, true);
            $this->Html2pdf = service('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/ramom.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang = true;
            $this->html2pdf->mpdf->baseScript = 1;
            $this->html2pdf->mpdf->autoLangToFont = true;
            return $this->html2pdf->mpdf->Output(time() . '.pdf', "I");
        }

        return null;
    }

    public function pdf_sendByemail()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->data['student_array'] = [$this->request->getPost('enrollID')];
            $html = view('fees/invoicePDFdownload', $this->data, true);
            $this->Html2pdf = service('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/ramom.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang = true;
            $this->html2pdf->mpdf->baseScript = 1;
            $this->html2pdf->mpdf->autoLangToFont = true;
            $file = $this->html2pdf->mpdf->Output(time() . '.pdf', "S");
            $data['file'] = $file;
            $data['enroll_id'] = $this->request->getPost('enrollID');
            $response = $this->emailModel->emailPDF_Fee_invoice($data);
            if ($response == true) {
                $array = ['status' => 'success', 'message' => translate('mail_sent_successfully')];
            } else {
                $array = ['status' => 'error', 'message' => translate('something_went_wrong')];
            }

            echo json_encode($array);
        }
    }

    public function due_invoice()
    {
        if (!get_permission('due_invoice', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost('search')) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $feegroup = explode("|", (string) $this->request->getPost('fees_type'));
            $feegroupId = $feegroup[0];
            $feeFeetypeId = $feegroup[1];
            $this->data['invoicelist'] = $this->feesModel->getDueInvoiceList($this->data['class_id'], $this->data['section_id'], $feegroupId, $feeFeetypeId);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('payments_history');
        $this->data['sub_page'] = 'fees/due_invoice';
        $this->data['main_menu'] = 'fees';
        echo view('layout/index', $this->data);
    }

    public function fee_add()
    {
        if (!get_permission('collect_fees', 'is_add')) {
            ajax_access_denied();
        }

        $this->validation->setRules(['fees_type' => ["label" => translate('fees_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRule('amount', translate('amount'), ['trim', 'required', 'numeric', 'greater_than[0]', ['deposit_verify', [$this->fees_model, 'depositAmountVerify']]]);
        $this->validation->setRule('discount_amount', translate('discount'), ['trim', 'numeric', ['deposit_verify', [$this->fees_model, 'depositAmountVerify']]]);
        $this->validation->setRules(['pay_via' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $feesType = explode("|", (string) $this->request->getPost('fees_type'));
            $amount = $this->request->getPost('amount');
            $fineAmount = $this->request->getPost('fine_amount');
            $discountAmount = $this->request->getPost('discount_amount');
            $date = $this->request->getPost('date');
            $payVia = $this->request->getPost('pay_via');
            $arrayFees = ['allocation_id' => $feesType[0], 'type_id' => $feesType[1], 'collect_by' => get_loggedin_user_id(), 'amount' => $amount - $discountAmount, 'discount' => $discountAmount, 'fine' => $fineAmount, 'pay_via' => $payVia, 'remarks' => $this->request->getPost('remarks'), 'date' => $date];
            $this->db->table('fee_payment_history')->insert();
            $paymentHistoryID = $this->db->insert_id();
            // transaction voucher save function
            if (isset($_POST['account_id'])) {
                $arrayTransaction = ['account_id' => $this->request->getPost('account_id'), 'amount' => $amount + $fineAmount - $discountAmount, 'date' => $date];
                $this->feesModel->saveTransaction($arrayTransaction, $paymentHistoryID);
            }

            // send payment confirmation sms
            if (isset($_POST['guardian_sms'])) {
                $arrayData = ['student_id' => $this->request->getPost('student_id'), 'amount' => $amount + $fineAmount - $discountAmount, 'paid_date' => _d($date)];
                $this->smsModel->send_sms($arrayData, 2);
            }

            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'url' => '', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function getBalanceByType()
    {
        $input = $this->request->getPost('typeID');
        if (empty($input)) {
            $balance = 0;
            $fine = 0;
        } else {
            $feesType = explode("|", (string) $input);
            $fine = $this->feesModel->feeFineCalculation($feesType[0], $feesType[1]);
            $b = $this->feesModel->getBalance($feesType[0], $feesType[1]);
            $balance = $b['balance'];
            $fine = abs($fine - $b['fine']);
        }

        echo json_encode(['balance' => $balance, 'fine' => $fine]);
    }

    public function getTypeByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        $typeID = $_POST['type_id'] ?? 0;
        if (!empty($branchID)) {
            $this->db->table('session_id')->where();
            $this->db->table('branch_id')->where();
            $result = $builder->get('fee_groups')->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<optgroup label="' . $row['name'] . '">';
                    $this->db->table('fee_groups_id')->where();
                    $resultdetails = $builder->get('fee_groups_details')->result_array();
                    foreach ($resultdetails as $t) {
                        $sel = $t['fee_groups_id'] . "|" . $t['fee_type_id'] == $typeID ? 'selected' : '';
                        $html .= '<option value="' . $t['fee_groups_id'] . "|" . $t['fee_type_id'] . '"' . $sel . '>' . get_type_name_by_id('fees_type', $t['fee_type_id']) . '</option>';
                    }

                    $html .= '</optgroup>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getGroupByBranch()
    {
        $html = "";
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $result = $db->table('fee_groups')->get('fee_groups')->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getTypeByGroup()
    {
        $html = "";
        $groupID = $this->request->getPost('group_id');
        $typeID = $_POST['type_id'] ?? 0;
        if (!empty($groupID)) {
            $builder->select('t.id,t.name');
            $this->db->from('fee_groups_details as gd');
            $builder->join('fees_type as t', 't.id = gd.fee_type_id', 'left');
            $this->db->table('gd.fee_groups_id')->where();
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $sel = $row['id'] == $typeID ? 'selected' : '';
                    $html .= '<option value="' . $row['id'] . '" ' . $sel . '>' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('first_select_the_group') . '</option>';
        }

        echo $html;
    }

    protected function reminder_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['frequency' => ["label" => translate('frequency'), "rules" => 'trim|required']]);
        $this->validation->setRules(['days' => ["label" => translate('days'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['message' => ["label" => translate('message'), "rules" => 'trim|required']]);
    }

    public function reminder()
    {
        if (!get_permission('fees_reminder', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (!get_permission('fees_reminder', 'is_add')) {
                ajax_access_denied();
            }

            $this->reminder_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $post['branch_id'] = $branchID;
                $this->feesModel->reminderSave($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['reminderlist'] = $this->appLib->getTable('fees_reminder');
        $this->data['title'] = translate('fees_reminder');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/reminder';
        echo view('layout/index', $this->data);
    }

    public function edit_reminder($id = '')
    {
        if (!get_permission('fees_reminder', 'is_edit')) {
            ajax_access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->reminder_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $post['branch_id'] = $branchID;
                $this->feesModel->reminderSave($post);
                $url = base_url('fees/reminder');
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['reminder'] = $this->appLib->getTable('fees_reminder', ['t.id' => $id], true);
        $this->data['title'] = translate('fees_reminder');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/edit_reminder';
        echo view('layout/index', $this->data);
    }

    public function reminder_delete($id = '')
    {
        if (get_permission('fees_reminder', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('fees_reminder')->delete();
        }
    }

    public function due_report()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost('search')) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['invoicelist'] = $this->feesModel->getDueReport($this->data['class_id'], $this->data['section_id']);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('due_fees_report');
        $this->data['sub_page'] = 'fees/due_report';
        $this->data['main_menu'] = 'fees_repots';
        echo view('layout/index', $this->data);
    }

    public function payment_history()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost('search')) {
            $classID = $this->request->getPost('class_id');
            $paymentVia = $this->request->getPost('payment_via');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['invoicelist'] = $this->feesModel->getStuPaymentHistory($classID, "", $paymentVia, $start, $end, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_payment_history');
        $this->data['sub_page'] = 'fees/payment_history';
        $this->data['main_menu'] = 'fees_repots';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function student_fees_report()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost('search')) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $enrollId = $this->request->getPost('enroll_id');
            $typeID = $this->request->getPost('fees_type');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['invoicelist'] = $this->feesModel->getStuPaymentReport($classID, $sectionID, $enrollId, $typeID, $start, $end, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_fees_report');
        $this->data['sub_page'] = 'fees/student_fees_report';
        $this->data['main_menu'] = 'fees_repots';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function fine_report()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost('search')) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $paymentVia = $this->request->getPost('payment_via');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['invoicelist'] = $this->feesModel->getStuPaymentHistory($classID, $sectionID, $paymentVia, $start, $end, $branchID, true);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_fine_reports');
        $this->data['sub_page'] = 'fees/fine_report';
        $this->data['main_menu'] = 'fees_repots';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function paymentRevert()
    {
        if (!get_permission('fees_revert', 'is_delete')) {
            $array = ['status' => 'error', 'message' => translate('access_denied')];
            echo json_encode($array);
            exit;
        }

        $array = ['status' => 'success', 'message' => translate('information_deleted')];
        $ids = $this->request->getPost('id');
        foreach ($ids as $value) {
            $feeDetails = $db->table('fee_payment_history')->get('fee_payment_history')->row();
            if (!empty($feeDetails)) {
                $amount = $feeDetails->amount + $feeDetails->fine;
                $sql = "SELECT `transactions`.`account_id`, `transactions_links_details`.`transactions_id` FROM `transactions_links_details` INNER JOIN `transactions` ON `transactions`.`id` = `transactions_links_details`.`transactions_id` WHERE `transactions_links_details`.`payment_id` = " . $db->escape($value);
                $transactionsDetails = $db->query($sql)->row();
                if (!empty($transactionsDetails)) {
                    $sql = sprintf('UPDATE `transactions` SET `amount` = `amount` + %s, `cr` = `cr` - %s, `bal` = `bal` - %s WHERE `id` = ', $amount, $amount, $amount) . $db->escape($transactionsDetails->transactions_id);
                    $db->query($sql);
                    $sql = sprintf('UPDATE `accounts` SET `balance` = `balance` - %s WHERE `id` = ', $amount) . $db->escape($transactionsDetails->account_id);
                    $db->query($sql);
                    /*$this->db->set('amount', 'amount+' . $amount, false);
                                          $this->db->set('cr', 'cr-' . $amount, false);
                                          $this->db->set('bal', 'bal-' . $amount, false);
                                          $this->db->table('id', $transactionsDetails->transactions_id)->where();
                                          $this->db->table('transactions')->update();

                                          $this->db->set('balance', 'balance-' . $amount, false);
                                          $this->db->table('id', $transactionsDetails->account_id)->where();
                                          $this->db->table('accounts')->update();*/
                }

                $this->db->table('id')->where();
                $this->db->table('fee_payment_history')->delete();
            }
        }

        echo json_encode($array);
    }

    public function fee_fully_paid()
    {
        if (!get_permission('collect_fees', 'is_add')) {
            ajax_access_denied();
        }

        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['pay_via' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $date = $this->request->getPost('date');
            $payVia = $this->request->getPost('pay_via');
            $invoiceID = $this->request->getPost('invoice_id');
            $allocations = $this->feesModel->getInvoiceDetails($invoiceID);
            $totalBalance = 0;
            $totalFine = 0;
            foreach ($allocations as $row) {
                $fine = $this->feesModel->feeFineCalculation($row['allocation_id'], $row['fee_type_id']);
                $b = $this->feesModel->getBalance($row['allocation_id'], $row['fee_type_id']);
                $fine = abs($fine - $b['fine']);
                if ($b['balance'] != 0) {
                    $totalBalance += $b['balance'];
                    $totalFine += $fine;
                    $arrayFees = ['allocation_id' => $row['allocation_id'], 'type_id' => $row['fee_type_id'], 'collect_by' => get_loggedin_user_id(), 'amount' => $b['balance'], 'discount' => 0, 'fine' => $fine, 'pay_via' => $payVia, 'remarks' => $this->request->getPost('remarks'), 'date' => $date];
                    $this->db->table('fee_payment_history')->insert();
                }
            }

            // transaction voucher save function
            if (isset($_POST['account_id'])) {
                $arrayTransaction = ['account_id' => $this->request->getPost('account_id'), 'amount' => $totalBalance + $totalFine, 'date' => $date];
                $this->feesModel->saveTransaction($arrayTransaction);
            }

            // send payment confirmation sms
            if (isset($_POST['guardian_sms'])) {
                $arrayData = ['student_id' => $this->request->getPost('student_id'), 'amount' => $totalBalance + $totalFine, 'paid_date' => $date];
                $this->smsModel->send_sms($arrayData, 2);
            }

            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'url' => '', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function printFeesPaymentHistory()
    {
        if ($_POST !== []) {
            $record = $this->request->getPost('data');
            $recordArray = json_decode((string) $record, true);
            $this->db->where_in('id', array_column($recordArray, 'payment_id'));
            $paymentHistory = $builder->select("sum(amount) as total_amount,sum(discount) as total_discount,sum(fine) as total_fine")->get('fee_payment_history')->row_array();
            $this->data['total_paid'] = $paymentHistory['total_amount'];
            $this->data['total_discount'] = $paymentHistory['total_discount'];
            $this->data['total_fine'] = $paymentHistory['total_fine'];
            echo view('fees/printFeesPaymentHistory', $this->data);
        }
    }

    public function printFeesInvoice()
    {
        if ($_POST !== []) {
            $record = $this->request->getPost('data');
            $recordArray = json_decode((string) $record);
            $totalFine = 0;
            $totalDiscount = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $totalAmount = 0;
            foreach ($recordArray as $value) {
                $deposit = $this->feesModel->getStudentFeeDeposit($value->allocationID, $value->feeTypeID);
                $fullAmount = $value->feeAmount;
                $typeDiscount = $deposit['total_discount'];
                $typeFine = $deposit['total_fine'];
                $typeAmount = $deposit['total_amount'];
                $balance = $fullAmount - ($typeAmount + $typeDiscount);
                $totalDiscount += $typeDiscount;
                $totalFine += $typeFine;
                $totalPaid += $typeAmount;
                $totalBalance += $balance;
                $totalAmount += $fullAmount;
            }

            $this->data['total_amount'] = $totalAmount;
            $this->data['total_paid'] = $totalPaid;
            $this->data['total_discount'] = $totalDiscount;
            $this->data['total_fine'] = $totalFine;
            $this->data['total_balance'] = $totalBalance;
            echo view('fees/printFeesInvoice', $this->data);
        }
    }

    public function payReceiptPrint()
    {
        if ($_POST !== []) {
            if (!get_permission('collect_fees', 'is_add')) {
                ajax_access_denied();
            }

            $studentID = $this->request->getPost('student_id');
            $record = $this->request->getPost('data');
            $this->data['studentID'] = $studentID;
            $this->data['record'] = $record;
            echo view('fees/paySlipPrint', $this->data);
        }
    }

    public function selectedFeesPay()
    {
        if (!get_permission('collect_fees', 'is_add')) {
            ajax_access_denied();
        }

        $items = $this->request->getPost('collect_fees');
        foreach ($items as $key => $value) {
            $this->validation->setRules(['collect_fees[' . $key . '][date]' => ["label" => translate('date'), "rules" => 'trim|required']]);
            $this->validation->setRules(['collect_fees[' . $key . '][pay_via]' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
            $this->validation->setRules(['collect_fees[' . $key . '][amount]' => ["label" => translate('amount'), "rules" => 'trim|required|numeric|greater_than[0]']]);
            $this->validation->setRules(['collect_fees[' . $key . '][discount_amount]' => ["label" => translate('discount'), "rules" => 'trim|numeric']]);
            $this->validation->setRules(['collect_fees[' . $key . '][fine_amount]' => ["label" => translate('fine'), "rules" => 'trim|numeric']]);
            if (isset($value['account_id'])) {
                $this->validation->setRules(['collect_fees[' . $key . '][account_id]' => ["label" => translate('account'), "rules" => 'trim|required']]);
            }

            $remainAmount = $this->feesModel->getBalance($value['allocation_id'], $value['type_id']);
            if ($remainAmount['balance'] < $value['amount']) {
                $error = ['collect_fees[' . $key . '][amount]' => 'Amount cannot be greater than the remaining.'];
                $array = ['status' => 'fail', 'error' => $error];
                echo json_encode($array);
                exit;
            }

            $remainAmount = $this->feesModel->getBalance($value['allocation_id'], $value['type_id']);
            if ($remainAmount['balance'] < $value['discount_amount']) {
                $error = ['collect_fees[' . $key . '][discount_amount]' => 'Amount cannot be greater than the remaining.'];
                $array = ['status' => 'fail', 'error' => $error];
                echo json_encode($array);
                exit;
            }
        }

        if ($this->validation->run() !== false) {
            $studentID = $this->request->getPost('student_id');
            foreach ($items as $value) {
                $amount = $value['amount'];
                $fineAmount = $value['fine_amount'];
                $discountAmount = $value['discount_amount'];
                $date = $value['date'];
                $payVia = $value['pay_via'];
                $arrayFees = ['allocation_id' => $value['allocation_id'], 'type_id' => $value['type_id'], 'collect_by' => get_loggedin_user_id(), 'amount' => $amount - $discountAmount, 'discount' => $discountAmount, 'fine' => $fineAmount, 'pay_via' => $payVia, 'remarks' => $value['remarks'], 'date' => $date];
                $this->db->table('fee_payment_history')->insert();
                // transaction voucher save function
                if (isset($value['account_id'])) {
                    $arrayTransaction = ['account_id' => $value['account_id'], 'amount' => $amount + $fineAmount - $discountAmount, 'date' => $date];
                    $this->feesModel->saveTransaction($arrayTransaction);
                }

                // send payment confirmation sms
                $arrayData = ['student_id' => $studentID, 'amount' => $amount + $fineAmount - $discountAmount, 'paid_date' => _d($date)];
                $this->smsModel->send_sms($arrayData, 2);
            }

            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function selectedFeesCollect()
    {
        if ($_POST !== []) {
            $record = $this->request->getPost('data');
            $recordArray = json_decode((string) $record);
            $this->data['student_id'] = $this->request->getPost('student_id');
            $this->data['branch_id'] = $this->applicationModel->get_branch_id();
            $this->data['record_array'] = $recordArray;
            echo view('fees/selectedFeesCollect', $this->data);
        }
    }
}
