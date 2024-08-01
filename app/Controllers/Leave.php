<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Leave.php
 * @copyright : Reserved RamomCoder Team
 */
class Leave extends AdminController

{
    /**
     * @var \App\Models\LeaveModel
     */
    public $leaveModel;

    public $appLib;

    protected $db;

    /**
     * @var App\Models\LeaveModel
     */
    public $leave;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $input;

    public $emailModel;

    public $applicationModel;

    public $load;

    public $validation;

    public $upload;

    public function __construct()
    {

        parent::__construct();

        $this->appLib = service('appLib'); 
$this->leave = new \App\Models\LeaveModel();
        $this->email = new \App\Models\EmailModel();
        $this->leaveModel = new \App\Models\LeaveModel();
        $this->emailModel = new \App\Models\EmailModel();
    }

    public function index()
    {
        if (!get_permission('leave_manage', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['update'])) {
            if (!get_permission('leave_manage', 'is_add')) {
                access_denied();
            }

            $arrayLeave = ['approved_by' => get_loggedin_user_id(), 'status' => $this->request->getPost('status'), 'comments' => $this->request->getPost('comments')];
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $this->db->table('leave_application')->update();
            // getting information for send email alert
            $getApplication = $this->db->table('leave_application')->where('id', $id)->get()->getRow();
            if ($getApplication->role_id == 7) {
                $getApplicant = $db->table('student')->get('student')->row();
            } else {
                $getApplicant = $db->table('staff')->get('staff')->row();
            }

            $arrayLeave['applicant'] = $getApplicant->name;
            $arrayLeave['email'] = $getApplicant->email;
            $arrayLeave['start_date'] = $getApplication->start_date;
            $arrayLeave['end_date'] = $getApplication->end_date;
            $arrayLeave['comments'] = $getApplication->comments;
            $this->emailModel->sentLeaveRequest($arrayLeave);
            set_alert('success', translate('information_has_been_updated_successfully'));
            return redirect()->to(base_url('leave'));
        }

        $where = [];
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $where['la.branch_id'] = $branchId;
        }

        if (isset($_POST['search'])) {
            $userRole = $this->request->getPost('role_id');
            $where['la.role_id'] = $userRole;
        }

        $this->data['title'] = translate('leave');
        $this->data['sub_page'] = 'leave/index';
        $this->data['leavelist'] = $this->leaveModel->getLeaveList($where);
        $this->data['main_menu'] = 'leave';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    // get add leave modal
    public function getApprovelLeaveDetails()
    {
        if (get_permission('leave_manage', 'is_add')) {
            $this->data['leave_id'] = $this->request->getPost('id');
            echo view('leave/approvel_modalView', $this->data);
        }
    }

    public function save()
    {
        if ($_POST !== []) {
            if (!get_permission('leave_manage', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['user_role' => ["label" => translate('role'), "rules" => 'trim|required']]);
            $this->validation->setRules(['applicant_id' => ["label" => translate('applicant'), "rules" => 'trim|required']]);
            $this->validation->setRules(['leave_category' => ["label" => translate('leave_category'), "rules" => 'required|callback_leave_check']]);
            $this->validation->setRules(['daterange' => ["label" => translate('leave_date'), "rules" => 'trim|required|callback_date_check']]);
            $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_handle_upload']]);
            if ($this->validation->run() !== false) {
                $applicantId = $this->request->getPost('applicant_id');
                $roleId = $this->request->getPost('user_role');
                $leaveTypeId = $this->request->getPost('leave_category');
                $branchId = $this->applicationModel->get_branch_id();
                $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
                $startDate = date("Y-m-d", strtotime($daterange[0]));
                $endDate = date("Y-m-d", strtotime($daterange[1]));
                $reason = $this->request->getPost('reason');
                $comments = $this->request->getPost('comments');
                $applyDate = date("Y-m-d H:i:s");
                $datetime1 = new DateTime($startDate);
                $datetime2 = new DateTime($endDate);
                $leaveDays = $datetime2->diff($datetime1)->format("%a") + 1;
                $origFileName = '';
                $encFileName = '';
                // upload attachment file
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config['upload_path'] = './uploads/attachments/leave/';
                    $config['allowed_types'] = "*";
                    $config['max_size'] = '2024';
                    $config['encrypt_name'] = true;
                    $file = $this->request->getFile('attachment_file'); $file->initialize($config);
                    $file = $this->request->getFile('attachment_file'); $file->do_upload("attachment_file");
                    $origFileName = $this->request->getFile('attachment_file');
                    $file = $origFileName; $file->data('orig_name');
                    $encFileName = $this->request->getFile('attachment_file');
                    $file = $encFileName; $file->data('file_name');
                }

                $arrayData = ['user_id' => $applicantId, 'role_id' => $roleId, 'branch_id' => $branchId, 'session_id' => get_session_id(), 'category_id' => $leaveTypeId, 'reason' => $reason, 'start_date' => date("Y-m-d", strtotime($startDate)), 'end_date' => date("Y-m-d", strtotime($endDate)), 'leave_days' => $leaveDays, 'status' => 2, 'orig_file_name' => $origFileName, 'enc_file_name' => $encFileName, 'apply_date' => $applyDate, 'approved_by' => get_loggedin_user_id(), 'comments' => $comments];
                $this->db->table('leave_application')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('leave');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function delete($id = '')
    {
        if (get_permission('leave_manage', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('leave_application')->delete();
        }
    }

    public function date_check($daterange)
    {
        $daterange = explode(' - ', (string) $daterange);
        $startDate = date("Y-m-d", strtotime($daterange[0]));
        $endDate = date("Y-m-d", strtotime($daterange[1]));
        $today = date('Y-m-d');
        if ($today === $startDate) {
            $this->validation->setRule('date_check', "You can not leave the current day.");
            return false;
        }

        if ($this->request->getPost('applicant_id')) {
            $applicantId = $this->request->getPost('applicant_id');
            $roleId = $this->request->getPost('user_role');
        } else {
            $applicantId = get_loggedin_user_id();
            $roleId = loggedin_role_id();
        }

        $getUserLeaves = $builder->getWhere('leave_application', ['user_id' => $applicantId, 'role_id' => $roleId])->result();
        if (!empty($getUserLeaves)) {
            foreach ($getUserLeaves as $userLeave) {
                $getDates = $this->user_leave_days($userLeave->start_date, $userLeave->end_date);
                $resultStart = in_array($startDate, $getDates, true);
                $resultEnd = in_array($endDate, $getDates, true);
                if ($resultStart || $resultEnd) {
                    $this->validation->setRule('date_check', 'Already have leave in the selected time.');
                    return false;
                }
            }
        }

        return true;
    }

    public function leave_check($typeId, string $fields, array $data)
    {
        if (!empty($typeId)) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $startDate = new DateTime(date("Y-m-d", strtotime($daterange[0])));
            $endDate = new DateTime(date("Y-m-d", strtotime($daterange[1])));

            if ($this->request->getPost('applicant_id')) {
                $applicantId = $this->request->getPost('applicant_id');
                $roleId = $this->request->getPost('user_role');
            } else {
                $applicantId = get_loggedin_user_id(); // Ensure this function is defined or adapt as necessary
                $roleId = loggedin_role_id(); // Ensure this function is defined or adapt as necessary
            }

            if ($endDate > $startDate) {
                $db = db_connect(); // Get database connection
                $leaveTotal = get_type_name_by_id('leave_category', $typeId, 'days'); // Ensure this function is defined or adapt as necessary
                $totalSpent = $db->table('leave_application')
                                  ->where('user_id', $applicantId)
                                  ->where('role_id', $roleId)
                                  ->selectSum('total_days')
                                  ->get()
                                  ->getRow()
                                  ->total_days;

                $leaveDays = $endDate->diff($startDate)->format("%a") + 1;
                $leftLeave = $leaveTotal - $totalSpent;

                if ($leftLeave < $leaveDays) {
                    $this->validator->setError('daterange', sprintf('Applied for %s days, maximum allowed is %s days.', $leaveDays, $leftLeave));
                    return false;
                }

                return true;
            }

            $this->validator->setError('daterange', "Select a valid date range.");
            return false;
        }

        return true;
    }

    public function getRequestDetails()
    {
        $this->data['leave_id'] = $this->request->getPost('id');
        echo view('leave/modal_request_details', $this->data);
    }

    public function request()
    {
        // check access permission
        if (!get_permission('leave_request', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['save'])) {
            if (!get_permission('leave_request', 'is_add')) {
                access_denied();
            }

            $this->validation->setRules(['leave_category' => ["label" => translate('leave_category'), "rules" => 'required|callback_leave_check']]);
            $this->validation->setRules(['daterange' => ["label" => translate('leave_date'), "rules" => 'trim|required|callback_date_check']]);
            $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_handle_upload']]);
            if ($this->validation->run() !== false) {
                $leaveTypeId = $this->request->getPost('leave_category');
                $branchId = $this->applicationModel->get_branch_id();
                $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
                $startDate = date("Y-m-d", strtotime($daterange[0]));
                $endDate = date("Y-m-d", strtotime($daterange[1]));
                $reason = $this->request->getPost('reason');
                $applyDate = date("Y-m-d H:i:s");
                $datetime1 = new DateTime($startDate);
                $datetime2 = new DateTime($endDate);
                $leaveDays = $datetime2->diff($datetime1)->format("%a") + 1;
                $origFileName = '';
                $encFileName = '';
                // upload attachment file
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config['upload_path'] = './uploads/attachments/leave/';
                    $config['allowed_types'] = "*";
                    $config['max_size'] = '2024';
                    $config['encrypt_name'] = true;
                    $file = $this->request->getFile('attachment_file'); $file->initialize($config);
                    $file = $this->request->getFile('attachment_file'); $file->do_upload("attachment_file");
                    $origFileName = $this->request->getFile('attachment_file');
                    $file = $origFileName; $file->data('orig_name');
                    $encFileName = $this->request->getFile('attachment_file');
                    $file = $encFileName; $file->data('file_name');
                }

                $arrayData = ['user_id' => get_loggedin_user_id(), 'role_id' => loggedin_role_id(), 'session_id' => get_session_id(), 'category_id' => $leaveTypeId, 'reason' => $reason, 'branch_id' => $branchId, 'start_date' => date("Y-m-d", strtotime($startDate)), 'end_date' => date("Y-m-d", strtotime($endDate)), 'leave_days' => $leaveDays, 'status' => 1, 'orig_file_name' => $origFileName, 'enc_file_name' => $encFileName, 'apply_date' => $applyDate];
                $this->db->table('leave_application')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('leave/request'));
            }
        }

        $where = ['la.user_id' => get_loggedin_user_id(), 'la.role_id' => loggedin_role_id()];
        $this->data['leavelist'] = $this->leaveModel->getLeaveList($where);
        $this->data['title'] = translate('leaves');
        $this->data['sub_page'] = 'leave/request';
        $this->data['main_menu'] = 'leave';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    public function request_delete($id = '')
    {
        $where = ['status' => 1, 'user_id' => get_loggedin_user_id(), 'role_id' => loggedin_role_id(), 'id' => $id];
        $app = $this->db->table('leave_application')->where($where)->get()->getRowArray();
        $fileName = FCPATH . 'uploads/attachments/leave/' . $app['enc_file_name'];
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $this->db->table($where)->delete('leave_application')->where();
    }

    /* category form validation rules */
    protected function category_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['leave_category' => ["label" => translate('leave_category'), "rules" => 'trim|required|callback_unique_category']]);
        $this->validation->setRules(['leave_days' => ["label" => translate('leave_days'), "rules" => 'trim|required']]);
        $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'trim|required']]);
    }

    // leave category information are prepared and stored in the database here
    public function category()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('leave_category', 'is_add')) {
                access_denied();
            }

            $this->category_validation();
            if ($this->validation->run() !== false) {
                $arrayData = ['branch_id' => $this->applicationModel->get_branch_id(), 'name' => $this->request->getPost('leave_category'), 'role_id' => $this->request->getPost('role_id'), 'days' => $this->request->getPost('leave_days')];
                $this->db->table('leave_category')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('leave/category'));
            }
        }

        $this->data['title'] = translate('leave');
        $this->data['category'] = $this->appLib->getTable('leave_category');
        $this->data['sub_page'] = 'leave/category';
        $this->data['main_menu'] = 'leave';
        echo view('layout/index', $this->data);
        return null;
    }

    public function category_edit()
    {
        if (!get_permission('leave_category', 'is_edit')) {
            ajax_access_denied();
        }

        $this->category_validation();
        if ($this->validation->run() !== false) {
            $categoryId = $this->request->getPost('category_id');
            $arrayData = ['branch_id' => $this->applicationModel->get_branch_id(), 'name' => $this->request->getPost('leave_category'), 'role_id' => $this->request->getPost('role_id'), 'days' => $this->request->getPost('leave_days')];
            $this->db->table('id')->where();
            $this->db->table('leave_category')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function category_delete($id = '')
    {
        if (!get_permission('leave_category', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('leave_category')->delete();
    }

    public function getCategory()
    {
        $html = "";
        $roleID = $this->request->getPost("role_id");
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($roleID) && !empty($branchID)) {
            $query = $db->table('leave_category')->get('leave_category');
            if ($query->num_rows() != 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                $sections = $query->getResultArray();
                foreach ($sections as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . $row['days'] . ')' . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }

        echo $html;
    }

    // unique valid name verification is done here
    public function unique_category($name)
    {
        $categoryId = $this->request->getPost('category_id');
        $this->request->getPost('role_id');
        $this->applicationModel->get_branch_id();
        if (!empty($categoryId)) {
            $this->db->where_not_in('id', $categoryId);
        }

        $this->db->table('name')->where();
        $this->db->table('role_id')->where();
        $this->db->table('branch_id')->where();
        $query = $builder->get('leave_category');
        if ($query->num_rows() > 0) {
            if (!empty($categoryId)) {
                set_alert('error', "The Category name are already used");
            } else {
                $this->validation->setRule("unique_category", translate('already_taken'));
            }

            return false;
        }

        return true;
    }

    public function handle_upload()
    {
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            $fileType = $_FILES["attachment_file"]['type'];
            $fileSize = $_FILES["attachment_file"]["size"];
            $fileName = $_FILES["attachment_file"]["name"];
            $allowedExts = ['pdf', 'doc', 'xls', 'docx', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'bmp'];
            $uploadSize = 2097152;
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES['attachment_file']['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setRule('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > $uploadSize) {
                    $this->validation->setRule('handle_upload', translate('file_size_shoud_be_less_than') . " " . $uploadSize / 1024 . " KB");
                    return false;
                }
            } else {
                $this->validation->setRule('handle_upload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return true;
    }

    public function download($id = '', $file = '')
    {
        if (!empty($id) && !empty($file)) {
            $builder->select('orig_file_name,enc_file_name');
            $this->db->table('id')->where();
            $leave = $builder->get('leave_application')->row();
            if ($file != $leave->enc_file_name) {
                access_denied();
            }

            helper('download');
            $fileData = file_get_contents('./uploads/attachments/leave/' . $leave->enc_file_name);
            return $this->response->download($leave->orig_file_name, $fileData);
        }

        return null;
    }

    public function user_leave_days($startDate, $endDate)
    {
        $dates = [];
        $current = strtotime((string) $startDate);
        $endDate = strtotime((string) $endDate);
        while ($current <= $endDate) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }

    public function reports()
    {
        if (!get_permission('leave_reports', 'is_view')) {
            access_denied();
        }

        $where = [];
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $where['la.branch_id'] = $branchId;
        }

        if (isset($_POST['search'])) {
            $userRole = $this->request->getPost('role_id');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $where['la.start_date >='] = $start;
            $where['la.start_date <='] = $end;
            $where['la.role_id'] = $userRole;
            $this->data['leavelist'] = $this->leaveModel->getLeaveList($where);
        }

        $this->data['title'] = translate('leave');
        $this->data['sub_page'] = 'leave/reports';
        $this->data['main_menu'] = 'leave_reports';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }
}
