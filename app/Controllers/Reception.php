<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Reception.php
 * @copyright : Reserved RamomCoder Team
 */
class Reception extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\ReceptionModel
     */
    public $reception;

    public $validation;

    public $receptionModel;

    public $input;

    public $load;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->reception = new \App\Models\ReceptionModel();
        if (!moduleIsEnabled('reception')) {
            access_denied();
        }
    }

    public function index()
    {
        return redirect()->to(base_url('reception/postal'));
    }

    /* postal form validation rules */
    protected function postal_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['type' => ["label" => translate('type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['reference_no' => ["label" => translate('reference_no'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sender_title' => ["label" => translate('sender') . " " . translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['receiver_title' => ["label" => translate('receiver') . " " . translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['address' => ["label" => translate('address'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['document_file' => ["label" => translate('document') . " " . translate('file'), "rules" => 'callback_photoHandleUpload[document_file]']]);
    }

    public function postal()
    {
        if ($_POST !== [] && get_permission('postal_record', 'is_add')) {
            $this->postal_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->receptionModel->postalSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('postal_record', 'is_view')) {
            access_denied();
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['result'] = $this->appLib->getTable('postal_record');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('postal_record');
        $this->data['sub_page'] = 'reception/postal';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function postal_edit($id = '')
    {
        if (!get_permission('postal_record', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->postal_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->receptionModel->postalSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => base_url('reception/postal')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['row'] = $this->appLib->getTable('postal_record', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('postal_record');
        $this->data['sub_page'] = 'reception/postal_edit';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function postal_delete($id)
    {
        if (get_permission('postal_record', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('postal_record')->delete();
        }
    }

    public function getPostalRecord()
    {
        if (get_permission('postal_record', 'is_view')) {
            $templateID = $this->request->getPost('id');
            $this->data['postal'] = $this->receptionModel->get('postal_record', ['id' => $templateID], true);
            echo view('reception/viewPostalRecord', $this->data);
        }
    }

    // file downloader
    public function download($type = '')
    {
        $encryptName = urldecode((string) $this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            helper('download');
            return $this->response->download($encryptName, file_get_contents(sprintf('uploads/reception/%s/', $type) . $encryptName));
        }

        return null;
    }

    /* call log form validation rules */
    protected function callLog_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['call_type' => ["label" => translate('call_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['purpose_id' => ["label" => translate('calling_purpose'), "rules" => 'trim|required']]);
        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['phone_number' => ["label" => translate('phone'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['start_time' => ["label" => translate('start_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['end_time' => ["label" => translate('end_time'), "rules" => 'trim|required']]);
    }

    public function call_log()
    {
        if ($_POST !== [] && get_permission('call_log', 'is_add')) {
            $this->callLog_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->receptionModel->call_logSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('call_log', 'is_view')) {
            access_denied();
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['result'] = $this->appLib->getTable('call_log');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('call_log');
        $this->data['sub_page'] = 'reception/call_log';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function call_log_edit($id = '')
    {
        if (!get_permission('call_log', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->callLog_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->receptionModel->call_logSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => base_url('reception/call_log')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['row'] = $this->appLib->getTable('call_log', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('call_log');
        $this->data['sub_page'] = 'reception/call_log_edit';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function call_log_delete($id)
    {
        if (get_permission('call_log', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('call_log')->delete();
        }
    }

    /* visitor form validation rules */
    protected function visitor_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['purpose_id' => ["label" => translate('visiting_purpose'), "rules" => 'trim|required']]);
        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['phone_number' => ["label" => translate('phone'), "rules" => 'trim|numeric']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['entry_time' => ["label" => translate('entry_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['exit_time' => ["label" => translate('exit_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['number_of_visitor' => ["label" => translate('number_of_visitor'), "rules" => 'trim|required|numeric']]);
    }

    public function visitor_log()
    {
        if ($_POST !== [] && get_permission('visitor_log', 'is_add')) {
            $this->visitor_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->receptionModel->visitor_logSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('visitor_log', 'is_view')) {
            access_denied();
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['result'] = $this->appLib->getTable('visitor_log');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('visitor_log');
        $this->data['sub_page'] = 'reception/visitor';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function visitor_edit($id = '')
    {
        if (!get_permission('visitor_log', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->visitor_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->receptionModel->visitor_logSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => base_url('reception/visitor_log')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['row'] = $this->appLib->getTable('visitor_log', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('visitor_log');
        $this->data['sub_page'] = 'reception/visitor_edit';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function visitor_delete($id)
    {
        if (get_permission('visitor_log', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('visitor_log')->delete();
        }
    }

    /* complaint form validation rules */
    protected function complaint_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['type_id' => ["label" => translate('type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['staff_id' => ["label" => translate('assign_to'), "rules" => 'trim|required']]);
        $this->validation->setRules(['complainant_name' => ["label" => translate('complainant') . " " . translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['phone_number' => ["label" => translate('complainant') . " " . translate('mobile_no'), "rules" => 'trim|numeric']]);
        $this->validation->setRules(['document_file' => ["label" => translate('document') . " " . translate('file'), "rules" => 'callback_photoHandleUpload[document_file]']]);
    }

    public function complaint()
    {
        if ($_POST !== [] && get_permission('complaint', 'is_add')) {
            $this->complaint_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->receptionModel->complaintSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('complaint', 'is_view')) {
            access_denied();
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['result'] = $this->appLib->getTable('complaint');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('complaint');
        $this->data['sub_page'] = 'reception/complaint';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function complaint_edit($id = '')
    {
        if (!get_permission('complaint', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->complaint_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->receptionModel->complaintSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => base_url('reception/complaint')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['row'] = $this->appLib->getTable('complaint', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('complaint');
        $this->data['sub_page'] = 'reception/complaint_edit';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function getComplaintDetails()
    {
        if (get_permission('complaint', 'is_view')) {
            $templateID = $this->request->getPost('id');
            $this->data['complaint'] = $this->receptionModel->get('complaint', ['id' => $templateID], true, true);
            echo view('reception/viewComplaintDetails', $this->data);
        }
    }

    public function complaint_delete($id)
    {
        if (get_permission('complaint', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('complaint')->delete();
        }
    }

    public function getComplaintAction()
    {
        if (get_permission('complaint', 'is_view')) {
            $templateID = $this->request->getPost('id');
            $complaint = $this->receptionModel->get('complaint', ['id' => $templateID], true, true, 'id,date_of_solution,action');
            if ($complaint['date_of_solution'] == "0000-00-00" || empty($complaint['date_of_solution'])) {
                $complaint['date_of_solution'] = "";
            }

            echo json_encode($complaint);
        }
    }

    public function complaint_action_taken()
    {
        if (get_permission('complaint', 'is_edit') && $_POST) {
            $this->validation->setRules(['date_of_solution' => ["label" => translate('date_of_solution'), "rules" => 'trim|required']]);
            $this->validation->setRules(['action' => ["label" => translate('action_taken'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $complaintId = $this->request->getPost('complaint_id');
                $dateOfSolution = $this->request->getPost('date_of_solution');
                $action = $this->request->getPost('action');
                $arrayComplaint = ['date_of_solution' => date("Y-m-d", strtotime((string) $dateOfSolution)), 'action' => $action];
                if (!is_superadmin_loggedin()) {
                    $this->db->table('branch_id')->where();
                }

                $this->db->table('id')->where();
                $this->db->table('complaint')->update();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    /* enquiry form validation rules */
    protected function enquiry_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['gender' => ["label" => translate('gender'), "rules" => 'trim|required']]);
        $this->validation->setRules(['father_name' => ["label" => translate('father_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['mother_name' => ["label" => translate('mother_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['mobile_no' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['no_of_child' => ["label" => translate('no_of_child'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['staff_id' => ["label" => translate('assigned'), "rules" => 'trim|required']]);
        $this->validation->setRules(['reference' => ["label" => translate('reference'), "rules" => 'trim|required']]);
        $this->validation->setRules(['response_id' => ["label" => translate('reference'), "rules" => 'trim|required']]);
        $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|valid_email']]);
        $this->validation->setRules(['address' => ["label" => translate('address'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['class_id' => ["label" => translate('class_applying_for'), "rules" => 'trim|required']]);
    }

    public function enquiry()
    {
        if ($_POST !== [] && get_permission('enquiry', 'is_add')) {
            $this->enquiry_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->receptionModel->enquirySave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('enquiry', 'is_view')) {
            access_denied();
        }

        $this->data['result'] = $this->appLib->getTable('enquiry');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('admission') . " " . translate('enquiry');
        $this->data['sub_page'] = 'reception/enquiry';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function enquiry_edit($id = '')
    {
        if (!get_permission('enquiry', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->enquiry_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->receptionModel->enquirySave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => base_url('reception/enquiry')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['row'] = $this->appLib->getTable('enquiry', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('admission') . " " . translate('enquiry');
        $this->data['sub_page'] = 'reception/enquiry_edit';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function enquiry_delete($id)
    {
        if (get_permission('enquiry', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('enquiry')->delete();
        }
    }

    protected function follow_up_validation()
    {
        $this->validation->setRules(['date' => ["label" => translate('follow_up') . " " . translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['follow_up_date' => ["label" => translate('next') . " " . translate('follow_up') . " " . translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['status' => ["label" => translate('status'), "rules" => 'trim|required']]);
    }

    public function enquiry_details($id)
    {
        if ($_POST !== [] && get_permission('follow_up', 'is_add')) {
            $this->follow_up_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $arrayInsert = ['enquiry_id' => $this->request->getPost('enquiry_id'), 'date' => $this->request->getPost('date'), 'next_date' => $this->request->getPost('follow_up_date'), 'response' => $this->request->getPost('response'), 'note' => $this->request->getPost('note'), 'status' => $this->request->getPost('status'), 'follow_up_by' => get_loggedin_user_id(), 'created_at' => date('Y-m-d')];
                $this->db->table('enquiry_follow_up')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('follow_up', 'is_view')) {
            access_denied();
        }

        $this->data['row'] = $this->appLib->getTable('enquiry', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('admission') . " " . translate('enquiry');
        $this->data['sub_page'] = 'reception/enquiry_details';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function follow_up_delete($id)
    {
        if (get_permission('follow_up', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('enquiry_follow_up')->delete();
        }
    }
}
