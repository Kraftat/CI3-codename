<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\CardManageModel;
use App\Models\EmployeeModel;
use App\Models\TimetableModel;
/**
 * @package : Ramom school management system
 * @version : 5.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Card_manage.php
 * @copyright : Reserved RamomCoder Team
 */
class Card_manage extends AdminController

{
    /**
     * @var mixed
     */
    public $Ciqrcode;

    public $ciqrcode;

    public $appLib;

    protected $db;


    /**
     * @var App\Models\CardManageModel
     */
    public $cardManage;

    public $load;

    /**
     * @var App\Models\EmployeeModel
     */
    public $employee;

    /**
     * @var App\Models\TimetableModel
     */
    public $timetable;

    public $validation;

    public $input;

    public $card_manageModel;

    public $applicationModel;

    public function __construct()
    {

        parent::__construct();


        $this->ciqrcode = service('ciqrcode');$this->appLib = service('appLib'); 
$this->cardManage = new \App\Models\CardManageModel();
        $this->Ciqrcode = service('ciqrcode', ['cacheable' => false]);
        $this->employee = new \App\Models\EmployeeModel();
        $this->timetable = new \App\Models\TimetableModel();
        if (!moduleIsEnabled('card_management')) {
            access_denied();
        }
    }

    /* id card templete form validation rules */
    protected function idard_templete_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['card_name' => ["label" => translate('id_card') . " " . translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['user_type' => ["label" => translate('applicable_user'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['layout_width' => ["label" => translate('layout_width'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['layout_height' => ["label" => translate('layout_height'), "rules" => 'trim|required']]);
        $this->validation->setRules(['top_space' => ["label" => "Top Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['bottom_space' => ["label" => "Bottom Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['right_space' => ["label" => "Right Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['left_space' => ["label" => "Left Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['content' => ["label" => translate('certificate') . " " . translate('content'), "rules" => 'trim|required']]);
    }

    public function id_card_templete()
    {
        if (!get_permission('id_card_templete', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('id_card_templete', 'is_add')) {
            $roleID = $this->request->getPost('role_id');
            $this->idard_templete_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $post = $this->request->getPost();
                $post['card_type'] = 1;
                $this->card_manageModel->save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['css/certificate.css', 'vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['js/certificate.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['certificatelist'] = $this->card_manageModel->getList();
        $this->data['title'] = translate('id_card') . " " . translate('templete');
        $this->data['sub_page'] = 'card_manage/id_card_templete';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function id_card_templete_edit($id = '')
    {
        if (!get_permission('id_card_templete', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->idard_templete_validation();
            if ($this->validation->run() !== false) {
                // save all information in the database file
                $post = $this->request->getPost();
                $post['card_type'] = 1;
                $this->card_manageModel->save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('card_manage/id_card_templete');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['certificate'] = $this->appLib->getTable('card_templete', ['t.id' => $id], true);
        $this->data['title'] = translate('id_card') . " " . translate('templete');
        $this->data['headerelements'] = ['css' => ['css/certificate.css', 'vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['js/certificate.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['sub_page'] = 'card_manage/id_card_templete_edit';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function id_card_delete($id = '')
    {
        if (get_permission('id_card_templete', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $getRow = $builder->get('card_templete')->row_array();
            if (!empty($getRow)) {
                $path = 'uploads/certificate/';
                if (file_exists($path . $getRow['background'])) {
                    unlink($path . $getRow['background']);
                }

                if (file_exists($path . $getRow['logo'])) {
                    unlink($path . $getRow['logo']);
                }

                if (file_exists($path . $getRow['signature'])) {
                    unlink($path . $getRow['signature']);
                }

                $this->db->table('id')->where();
                $this->db->table('card_type')->where();
                $this->db->table('card_templete')->delete();
            }
        }
    }

    public function getIDCard()
    {
        if (get_permission('id_card_templete', 'is_view')) {
            $templateID = $this->request->getPost('id');
            $this->data['template'] = $this->card_manageModel->get('card_templete', ['id' => $templateID], true);
            echo view('card_manage/viewIDCard', $this->data);
        }
    }

    public function generate_student_idcard()
    {
        if (!get_permission('generate_student_idcard', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['stuList'] = $this->applicationModel->getStudentListByClassSection($classID, $sectionID, $branchID);
        }

        $this->data['headerelements'] = ['js' => ['js/certificate.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('student') . " " . translate('id_card') . " " . translate('generate');
        $this->data['sub_page'] = 'card_manage/generate_student_idcard';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function generate_employee_idcard()
    {
        if (!get_permission('generate_employee_idcard', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $staffRole = $this->request->getPost('staff_role');
            $this->data['stafflist'] = $this->employeeModel->getStaffList($branchID, $staffRole);
        }

        $this->data['headerelements'] = ['js' => ['js/certificate.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('employee') . " " . translate('id_card') . " " . translate('generate');
        $this->data['sub_page'] = 'card_manage/generate_employee_idcard';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function idCardprintFn($opt = '')
    {
        if ($_POST !== []) {
            if ($opt == 1) {
                if (!get_permission('generate_student_idcard', 'is_view')) {
                    ajax_access_denied();
                }
            } elseif ($opt == 2) {
                if (!get_permission('generate_employee_idcard', 'is_view')) {
                    ajax_access_denied();
                }
            } else {
                ajax_access_denied();
            }

            //get all QR Code file
            $files = glob('uploads/qr_code/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    //delete file
                }
            }

            $this->data['user_type'] = $opt;
            $this->data['user_array'] = $this->request->getPost('user_id');
            $templateID = $this->request->getPost('templete_id');
            $this->data['template'] = $this->card_manageModel->get('card_templete', ['id' => $templateID], true);
            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['expiry_date'] = $this->request->getPost('expiry_date');
            echo view('card_manage/idCardprintFn', $this->data, true);
        }
    }

    public function getIDCardTempleteByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        $userType = $this->request->getPost('user_type');
        $cardType = $this->request->getPost('card_type');
        $cardType = $cardType == 'idcard' ? 1 : 2;
        if ($userType == 'student') {
            $userType = 1;
        }

        if ($userType == 'staff') {
            $userType = 2;
        }

        if (!empty($branchID)) {
            $builder->select('id,name');
            $this->db->table(['branch_id' => $branchID, 'user_type' => $userType, 'card_type' => $cardType])->where();
            $result = $builder->get('card_templete')->result_array();
            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
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

    /* admit card templete form validation rules */
    protected function admitcard_templete_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['card_name' => ["label" => translate('admit_card') . " " . translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['stu_qr_code' => ["label" => "QR Code Text", "rules" => 'trim|required']]);
        $this->validation->setRules(['layout_width' => ["label" => translate('layout_width'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['layout_height' => ["label" => translate('layout_height'), "rules" => 'trim|required']]);
        $this->validation->setRules(['top_space' => ["label" => "Top Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['bottom_space' => ["label" => "Bottom Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['right_space' => ["label" => "Right Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['left_space' => ["label" => "Left Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['content' => ["label" => translate('admit_card') . " " . translate('content'), "rules" => 'trim|required']]);
    }

    public function admit_card_templete()
    {
        if (!get_permission('admit_card_templete', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('admit_card_templete', 'is_add')) {
            $roleID = $this->request->getPost('role_id');
            $this->admitcard_templete_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $post = $this->request->getPost();
                $post['card_type'] = 2;
                $this->card_manageModel->save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['css/certificate.css', 'vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['js/certificate.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['certificatelist'] = $this->card_manageModel->getList(2);
        $this->data['title'] = translate('admit_card') . " " . translate('templete');
        $this->data['sub_page'] = 'card_manage/admit_card_templete';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function admit_card_templete_edit($id = '')
    {
        if (!get_permission('admit_card_templete', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->admitcard_templete_validation();
            if ($this->validation->run() !== false) {
                // save all information in the database file
                $post = $this->request->getPost();
                $post['card_type'] = 2;
                $this->card_manageModel->save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('card_manage/admit_card_templete');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['templete'] = $this->appLib->getTable('card_templete', ['t.id' => $id], true);
        $this->data['title'] = translate('admit_card') . " " . translate('templete');
        $this->data['headerelements'] = ['css' => ['css/certificate.css', 'vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['js/certificate.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['sub_page'] = 'card_manage/admit_card_templete_edit';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function admit_card_templete_delete($id = '')
    {
        if (get_permission('admit_card_templete', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $getRow = $builder->get('card_templete')->row_array();
            if (!empty($getRow)) {
                $path = 'uploads/certificate/';
                if (file_exists($path . $getRow['background'])) {
                    unlink($path . $getRow['background']);
                }

                if (file_exists($path . $getRow['logo'])) {
                    unlink($path . $getRow['logo']);
                }

                if (file_exists($path . $getRow['signature'])) {
                    unlink($path . $getRow['signature']);
                }

                $this->db->table('id')->where();
                $this->db->table('card_type')->where();
                $this->db->table('card_templete')->delete();
            }
        }
    }

    public function generate_student_admitcard()
    {
        if (!get_permission('generate_admit_card', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->data['exam_id'] = $this->request->getPost('exam_id');
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['stuList'] = $this->applicationModel->getStudentListByClassSection($classID, $sectionID, $branchID);
        }

        $this->data['headerelements'] = ['js' => ['js/certificate.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('admit_card') . " " . translate('generate');
        $this->data['sub_page'] = 'card_manage/generate_student_admitcard';
        $this->data['main_menu'] = 'card_manage';
        echo view('layout/index', $this->data);
    }

    public function admitCardprintFn()
    {
        if (!get_permission('generate_admit_card', 'is_view')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            //get all QR Code file
            $files = glob('uploads/qr_code/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    //delete file
                }
            }

            $this->data['exam_id'] = $this->request->getPost('exam_id');
            $this->data['user_array'] = $this->request->getPost('user_id');
            $templateID = $this->request->getPost('templete_id');
            $this->data['template'] = $this->card_manageModel->get('card_templete', ['id' => $templateID], true);
            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            echo view('card_manage/admitCardprintFn', $this->data, true);
        }
    }

    public function getExamByBranch()
    {
        $html = "";
        $this->request->getPost('class_id');
        $this->request->getPost('section_id');

        $selectedId = $_POST['selected'] ?? 0;
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $builder->select('exam.id,exam.name,exam.term_id');
            $this->db->from('timetable_exam');
            $builder->join('exam', 'exam.id = timetable_exam.exam_id', 'left');
            $this->db->table('timetable_exam.branch_id')->where();
            $this->db->table('timetable_exam.session_id')->where();
            $this->db->table('timetable_exam.class_id')->where();
            $this->db->table('timetable_exam.section_id')->where();
            $this->db->group_by('timetable_exam.exam_id');
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    if ($row['term_id'] != 0) {
                        $term = $db->table('exam_term')->get('exam_term')->row()->name;
                        $name = $row['name'] . ' (' . $term . ')';
                    } else {
                        $name = $row['name'];
                    }

                    $selected = $row['id'] == $selectedId ? 'selected' : '';
                    $html .= '<option value="' . $row['id'] . '"' . $selected . '>' . $name . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }
}
