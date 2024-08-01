<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmployeeModel;
use App\Models\StudentModel;
use App\Models\FeesModel;
use App\Models\ParentsModel;
use App\Models\EmailModel;
use App\Models\StudentFieldsModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Profile.php
 * @copyright : Reserved RamomCoder Team
 */
class Profile extends AdminController

{
    protected $db;


    /**
     * @var App\Models\EmployeeModel
     */
    public $employee;

    /**
     * @var App\Models\StudentModel
     */
    public $student;

    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    /**
     * @var App\Models\ParentsModel
     */
    public $parents;

    /**
     * @var App\Models\ProfileModel
     */
    public $profile;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\StudentFieldsModel
     */
    public $studentFields;

    public $validation;

    public $input;

    public $profileModel;

    public $student_fieldsModel;

    public $load;

    public $appLib;

    public $emailModel;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->employee = new \App\Models\EmployeeModel();
        $this->student = new \App\Models\StudentModel();
        $this->fees = new \App\Models\FeesModel();
        $this->parents = new \App\Models\ParentsModel();
        $this->profile = new \App\Models\ProfileModel();
        $this->email = new \App\Models\EmailModel();
        $this->studentFields = new \App\Models\StudentFieldsModel();
    }

    public function index()
    {
        $userID = get_loggedin_user_id();
        $loggedinRoleID = loggedin_role_id();
        $branchID = get_loggedin_branch_id();
        if ($loggedinRoleID == 6) {
            if ($_POST !== []) {
                $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $this->validation->setRules(['relation' => ["label" => translate('relation'), "rules" => 'trim|required']]);
                $this->validation->setRules(['occupation' => ["label" => translate('occupation'), "rules" => 'trim|required']]);
                $this->validation->setRules(['income' => ["label" => translate('income'), "rules" => 'trim|numeric']]);
                $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'trim|required']]);
                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|valid_email']]);
                $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required|callback_unique_username']]);
                $this->validation->setRules(['user_photo' => ["label" => 'profile_picture', "rules" => 'callback_photoHandleUpload[user_photo]']]);
                $this->validation->setRules(['facebook' => ["label" => 'Facebook', "rules" => 'valid_url']]);
                $this->validation->setRules(['twitter' => ["label" => 'Twitter', "rules" => 'valid_url']]);
                $this->validation->setRules(['linkedin' => ["label" => 'Linkedin', "rules" => 'valid_url']]);
                if ($this->validation->run() == true) {
                    $data = $this->request->getPost();
                    $this->profileModel->parentUpdate($data);
                    set_alert('success', translate('information_has_been_updated_successfully'));
                    return redirect()->to(base_url('profile'));
                }
            }

            $this->data['parent'] = $this->parentsModel->getSingleParent($userID);
            $this->data['sub_page'] = 'profile/parent';
        } elseif ($loggedinRoleID == 7) {
            if ($_POST !== []) {
                $this->validation->setRules(['student_id' => ["label" => translate('student'), "rules" => 'trim']]);
                // system fields validation rules
                $validArr = [];
                $validationArr = $this->student_fieldsModel->getStatusProfileArr($branchID);
                foreach ($validationArr as $value) {
                    if ($value->status && $value->required) {
                        $validArr[$value->prefix] = 1;
                    }
                }

                $this->validation->setRules(['user_photo' => ["label" => 'profile_picture', "rules" => 'callback_photoHandleUpload[user_photo]']]);
                if (isset($validArr['admission_date'])) {
                    $this->validation->setRules(['admission_date' => ["label" => translate('admission_date'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['student_photo']) && (isset($_FILES["user_photo"]) && empty($_FILES["user_photo"]['name']) && empty($_POST['old_user_photo']))) {
                    $this->validation->setRules(['user_photo' => ["label" => translate('profile_picture'), "rules" => 'required']]);
                }

                if (isset($validArr['first_name'])) {
                    $this->validation->setRules(['first_name' => ["label" => translate('first_name'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['last_name'])) {
                    $this->validation->setRules(['last_name' => ["label" => translate('last_name'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['gender'])) {
                    $this->validation->setRules(['gender' => ["label" => translate('gender'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['birthday'])) {
                    $this->validation->setRules(['birthday' => ["label" => translate('birthday'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['category'])) {
                    $this->validation->setRules(['category_id' => ["label" => translate('category'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['religion'])) {
                    $this->validation->setRules(['religion' => ["label" => translate('religion'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['caste'])) {
                    $this->validation->setRules(['caste' => ["label" => translate('caste'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['blood_group'])) {
                    $this->validation->setRules(['blood_group' => ["label" => translate('blood_group'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['mother_tongue'])) {
                    $this->validation->setRules(['mother_tongue' => ["label" => translate('mother_tongue'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['present_address'])) {
                    $this->validation->setRules(['current_address' => ["label" => translate('present_address'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['permanent_address'])) {
                    $this->validation->setRules(['permanent_address' => ["label" => translate('permanent_address'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['city'])) {
                    $this->validation->setRules(['city' => ["label" => translate('city'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['state'])) {
                    $this->validation->setRules(['state' => ["label" => translate('state'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['student_email'])) {
                    $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                }

                if (isset($validArr['student_mobile_no'])) {
                    $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
                }

                if (isset($validArr['previous_school_details'])) {
                    $this->validation->setRules(['school_name' => ["label" => translate('school_name'), "rules" => 'trim|required']]);
                    $this->validation->setRules(['qualification' => ["label" => translate('qualification'), "rules" => 'trim|required']]);
                }

                if ($this->validation->run() == true) {
                    $data = $this->request->getPost();
                    $this->profileModel->studentUpdate($data);
                    set_alert('success', translate('information_has_been_updated_successfully'));
                    $array = ['status' => 'success'];
                } else {
                    $error = $this->validation->error_array();
                    $array = ['status' => 'fail', 'error' => $error];
                }

                echo json_encode($array);
                exit;
            }

            $this->data['student'] = $this->studentModel->getSingleStudent(session()->get('enrollID'), true);
            $this->data['sub_page'] = 'profile/student';
        } else {
            if ($_POST !== []) {
                $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $this->validation->setRules(['mobile_no' => ["label" => translate('mobile_no'), "rules" => 'trim|required']]);
                $this->validation->setRules(['present_address' => ["label" => translate('present_address'), "rules" => 'trim|required']]);
                if (is_admin_loggedin()) {
                    $this->validation->setRules(['designation_id' => ["label" => translate('designation'), "rules" => 'trim|required']]);
                    $this->validation->setRules(['department_id' => ["label" => translate('department'), "rules" => 'trim|required']]);
                    $this->validation->setRules(['joining_date' => ["label" => translate('joining_date'), "rules" => 'trim|required']]);
                    $this->validation->setRules(['qualification' => ["label" => translate('qualification'), "rules" => 'trim|required']]);
                }

                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['facebook' => ["label" => 'Facebook', "rules" => 'trim|valid_url']]);
                $this->validation->setRules(['twitter' => ["label" => 'Twitter', "rules" => 'trim|valid_url']]);
                $this->validation->setRules(['linkedin' => ["label" => 'Linkedin', "rules" => 'trim|valid_url']]);
                $this->validation->setRules(['user_photo' => ["label" => 'profile_picture', "rules" => 'callback_photoHandleUpload[user_photo]']]);
                if ($this->validation->run() == true) {
                    $data = $this->request->getPost();
                    $this->profileModel->staffUpdate($data);
                    set_alert('success', translate('information_has_been_updated_successfully'));
                    return redirect()->to(base_url('profile'));
                }
            }

            $this->data['staff'] = $this->employeeModel->getSingleStaff($userID);
            $this->data['sub_page'] = 'profile/employee';
        }

        $this->data['title'] = translate('profile') . " " . translate('edit');
        $this->data['main_menu'] = 'profile';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if (empty($username)) {
            return true;
        }

        $this->db->where_not_in('id', get_loggedin_id());
        $this->db->table('username')->where();
        $query = $builder->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_username", translate('username_has_already_been_used'));
            return false;
        }

        return true;
    }

    // when user change his password
    public function password()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['current_password' => ["label" => 'Current Password', "rules" => 'trim|required|min_length[4]|callback_check_validate_password']]);
            $this->validation->setRules(['new_password' => ["label" => 'New Password', "rules" => 'trim|required|min_length[4]']]);
            $this->validation->setRules(['confirm_password' => ["label" => 'Confirm Password', "rules" => 'trim|required|min_length[4]|matches[new_password]']]);
            if ($this->validation->run() == true) {
                $newPassword = $this->request->getPost('new_password');
                $this->db->table('id')->where();
                $this->db->table('login_credential')->update();
                // password change email alert
                $emailData = ['branch_id' => get_loggedin_branch_id(), 'password' => $newPassword];
                $this->emailModel->changePassword($emailData);
                set_alert('success', translate('password_has_been_changed'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['sub_page'] = 'profile/password_change';
        $this->data['main_menu'] = 'profile';
        $this->data['title'] = translate('profile');
        echo view('layout/index', $this->data);
    }

    // when user change his username
    public function username_change()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required|callback_unique_username']]);
            if ($this->validation->run() == true) {
                $username = $this->request->getPost('username');
                // update login credential information in the database
                $this->db->table('user_id')->where();
                $this->db->table('role')->where();
                $this->db->table('login_credential')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    // current password verification is done here
    public function check_validate_password($password)
    {
        if ($password) {
            $getPassword = $db->table('login_credential')->get('login_credential')->row()->password;
            $getVerify = $this->appLib->verify_password($password, $getPassword);
            if ($getVerify) {
                return true;
            }

            $this->validation->setRule("check_validate_password", translate('current_password_is_invalid'));
            return false;
        }

        return null;
    }
}
