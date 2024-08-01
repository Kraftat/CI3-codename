<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
use App\Models\SmsModel;
use App\Models\StudentFieldsModel;
use App\Models\FeesModel;
use App\Models\ExamModel;
/**
 * @package : Ramom school management system
 * @version : 6.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Student.php
 * @copyright : Reserved RamomCoder Team
 */
class Student extends AdminController

{
    /**
     * @var mixed
     */
    public $Csvimport;
    public $bulk;

    protected $db;


    public $load;

    /**
     * @var App\Models\StudentModel
     */
    public $student;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    /**
     * @var App\Models\StudentFieldsModel
     */
    public $studentFields;

    public $applicationModel;

    public $validation;

    public $student_fieldsModel;

    public $router;

    public $appLib;

    public $input;

    public $studentModel;

    public $emailModel;

    public $smsModel;

    public $csvimport;

    public $session;

    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    /**
     * @var App\Models\ExamModel
     */
    public $exam;

    public $upload;

    public $uri;

    public function __construct()
    {


        parent::__construct();



        $this->csvimport = service('csvimport');$this->bulk = service('bulk');$this->appLib = service('appLib'); 
$this->load->helpers('download');
        $this->load->helpers('custom_fields');

        $this->student = new \App\Models\StudentModel();
        $this->email = new \App\Models\EmailModel();
        $this->sms = new \App\Models\SmsModel();
        $this->studentFields = new \App\Models\StudentFieldsModel();
    }

    public function index()
    {
        return redirect()->to(base_url('student/view'));
    }

    /* student form validation rules */
    protected function student_validation()
    {
        $branchID = $this->applicationModel->get_branch_id();
        $getBranch = $this->getBranchDetails();
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['year_id' => ["label" => translate('academic_year'), "rules" => 'trim|required']]);
        $this->validation->setRules(['first_name' => ["label" => translate('first_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
        $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'trim|required']]);
        $this->validation->setRules(['register_no' => ["label" => translate('register_no'), "rules" => 'trim|required|callback_unique_registerid']]);
        // checking profile photo format
        $this->validation->setRules(['user_photo' => ["label" => translate('profile_picture'), "rules" => 'callback_photoHandleUpload[user_photo]']]);
        // system fields validation rules
        $validArr = [];
        $validationArr = $this->student_fieldsModel->getStatusArr($branchID);
        foreach ($validationArr as $value) {
            if ($value->status && $value->required) {
                $validArr[$value->prefix] = 1;
            }
        }

        if (isset($validArr['admission_date'])) {
            $this->validation->setRules(['admission_date' => ["label" => translate('admission_date'), "rules" => 'trim|required']]);
        }

        if (isset($validArr['student_photo']) && (isset($_FILES["user_photo"]) && empty($_FILES["user_photo"]['name']) && empty($_POST['old_user_photo']))) {
            $this->validation->setRules(['user_photo' => ["label" => translate('profile_picture'), "rules" => 'required']]);
        }

        if (isset($validArr['roll'])) {
            $this->validation->setRules(['roll' => ["label" => translate('roll'), "rules" => 'trim|numeric|required|callback_unique_roll']]);
        } else {
            $this->validation->setRules(['roll' => ["label" => translate('roll'), "rules" => 'trim|numeric|callback_unique_roll']]);
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

        if ($getBranch['stu_generate'] == 0 || isset($_POST['student_id'])) {
            $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required|callback_unique_username']]);
            if (!isset($_POST['student_id'])) {
                $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
                $this->validation->setRules(['retype_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[password]']]);
            }
        }

        // custom fields validation rules
        $classSlug = $this->router->fetch_class();
        $customFields = getCustomFields($classSlug);
        foreach ($customFields as $fieldsValue) {
            if ($fieldsValue['required']) {
                $fieldsID = $fieldsValue['id'];
                $fieldLabel = $fieldsValue['field_label'];
                $this->validation->setRules(["custom_fields[student][" . $fieldsID . "]" => ["label" => $fieldLabel, "rules" => 'trim|required']]);
            }
        }
    }

    /* student admission information are prepared and stored in the database here */
    public function add()
    {
        // check access permission
        if (!get_permission('student', 'is_add')) {
            access_denied();
        }

        // check saas student add limit
        if ($this->appLib->isExistingAddon('saas') && !checkSaasLimit('student')) {
            set_alert('error', translate('update_your_package'));
            redirect(site_url('dashboard'));
        }

        $getBranch = $this->getBranchDetails();
        $branchID = $this->applicationModel->get_branch_id();
        $this->data['getBranch'] = $getBranch;
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'student/add';
        $this->data['main_menu'] = 'admission';
        $this->data['register_id'] = $this->studentModel->regSerNumber($branchID);
        $this->data['title'] = translate('create_admission');
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['js/student.js', 'vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    public function save()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('student', 'is_add')) {
                ajax_access_denied();
            }

            // check saas student add limit
            if ($this->appLib->isExistingAddon('saas') && !checkSaasLimit('student')) {
                ajax_access_denied();
            }

            $getBranch = $this->getBranchDetails();
            $branchID = $this->applicationModel->get_branch_id();
            $this->student_validation();
            if (!isset($_POST['guardian_chk'])) {
                // system fields validation rules
                $validArr = [];
                $validationArr = $this->student_fieldsModel->getStatusArr($branchID);
                foreach ($validationArr as $value) {
                    if ($value->status && $value->required) {
                        $validArr[$value->prefix] = 1;
                    }
                }

                if (isset($validArr['guardian_name'])) {
                    $this->validation->setRules(['grd_name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_relation'])) {
                    $this->validation->setRules(['grd_relation' => ["label" => translate('relation'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['father_name'])) {
                    $this->validation->setRules(['father_name' => ["label" => translate('father_name'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['mother_name'])) {
                    $this->validation->setRules(['mother_name' => ["label" => translate('mother_name'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_occupation'])) {
                    $this->validation->setRules(['grd_occupation' => ["label" => translate('occupation'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_income'])) {
                    $this->validation->setRules(['grd_income' => ["label" => translate('occupation'), "rules" => 'trim|required|numeric']]);
                }

                if (isset($validArr['guardian_education'])) {
                    $this->validation->setRules(['grd_education' => ["label" => translate('education'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_email'])) {
                    $this->validation->setRules(['grd_email' => ["label" => translate('email'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_mobile_no'])) {
                    $this->validation->setRules(['grd_mobileno' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
                }

                if (isset($validArr['guardian_address'])) {
                    $this->validation->setRules(['grd_address' => ["label" => translate('address'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_photo']) && (isset($_FILES["guardian_photo"]) && empty($_FILES["guardian_photo"]['name']))) {
                    $this->validation->setRules(['guardian_photo' => ["label" => translate('guardian_picture'), "rules" => 'required']]);
                }

                if (isset($validArr['guardian_city'])) {
                    $this->validation->setRules(['grd_city' => ["label" => translate('city'), "rules" => 'trim|required']]);
                }

                if (isset($validArr['guardian_state'])) {
                    $this->validation->setRules(['grd_state' => ["label" => translate('state'), "rules" => 'trim|required']]);
                }

                if ($getBranch['grd_generate'] == 0) {
                    if (isset($validArr['grd_username'])) {
                        $this->validation->setRules(['grd_username' => ["label" => translate('username'), "rules" => 'trim|required|callback_get_valid_guardian_username']]);
                    }

                    if (isset($validArr['grd_password'])) {
                        $this->validation->setRules(['grd_password' => ["label" => translate('password'), "rules" => 'trim|required']]);
                        $this->validation->setRules(['grd_retype_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[grd_password]']]);
                    }
                }
            } else {
                $this->validation->setRules(['parent_id' => ["label" => translate('guardian'), "rules" => 'required']]);
            }

            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                //save all student information in the database file
                $studentData = $this->studentModel->save($post, $getBranch);
                $studentID = $studentData['student_id'];
                //save student enroll information in the database file
                $arrayEnroll = ['student_id' => $studentID, 'class_id' => $post['class_id'], 'section_id' => $post['section_id'], 'roll' => $post['roll'] ?? 0, 'session_id' => $post['year_id'], 'branch_id' => $branchID];
                $this->db->table('enroll')->insert();
                // handle custom fields data
                $classSlug = $this->router->fetch_class();
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $studentID);
                }

                // send student admission email
                $this->emailModel->studentAdmission($studentData);
                // send account activate sms
                $this->smsModel->send_sms($arrayEnroll, 1);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('student/add');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    /* csv file to import student information and stored in the database here */
    public function csv_import()
    {
        // check access permission
        if (!get_permission('multiple_import', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['save'])) {
            $errMsg = "";
            $i = 0;
            $this->Csvimport = service('csvimport');
            // form validation rules
            if (is_superadmin_loggedin() == true) {
                $this->validation->setRules(['branch_id' => ["label" => 'Branch', "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => 'Class', "rules" => 'trim|required']]);
            $this->validation->setRules(['section_id' => ["label" => 'Section', "rules" => 'trim|required']]);
            if (isset($_FILES["userfile"]) && empty($_FILES['userfile']['name'])) {
                $this->validation->setRules(['userfile' => ["label" => 'CSV File', "rules" => 'required']]);
            }

            if ($this->validation->run() == true) {
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $csvArray = $this->csvimport->get_array($_FILES["userfile"]["tmp_name"]);
                if ($csvArray) {
                    $columnHeaders = ['FirstName', 'LastName', 'BloodGroup', 'Gender', 'Birthday', 'MotherTongue', 'Religion', 'Caste', 'Phone', 'City', 'State', 'PresentAddress', 'PermanentAddress', 'CategoryID', 'Roll', 'RegisterNo', 'AdmissionDate', 'StudentEmail', 'StudentUsername', 'StudentPassword', 'GuardianName', 'GuardianRelation', 'FatherName', 'MotherName', 'GuardianOccupation', 'GuardianMobileNo', 'GuardianAddress', 'GuardianEmail', 'GuardianUsername', 'GuardianPassword'];
                    $csvData = [];
                    foreach ($csvArray as $row) {
                        if ($i == 0) {
                            $csvData = array_keys($row);
                        }

                        $csvChk = array_diff($columnHeaders, $csvData);
                        if (count($csvChk) <= 0) {
                            $schoolSettings = $this->studentModel->get('branch', ['id' => $branchID], true, false, 'unique_roll');
                            $uniqueRoll = $schoolSettings['unique_roll'];
                            $r = $this->csvCheckExistsData($uniqueRoll, $row['StudentUsername'], $row['Roll'], $row['RegisterNo'], $classID, $sectionID, $branchID);
                            if ($r['status'] == false) {
                                $errMsg .= $row['FirstName'] . ' ' . $row['LastName'] . " - Imported Failed : " . $r['message'] . "<br>";
                            } else {
                                $this->studentModel->csvImport($row, $classID, $sectionID, $branchID);
                                $i++;
                            }
                        } else {
                            set_alert('error', translate('invalid_csv_file'));
                            return redirect()->to(base_url("student/csv_import"));
                        }
                    }

                    if ($errMsg != null) {
                        session()->set_flashdata('csvimport', $errMsg);
                    }

                    if ($i > 0) {
                        set_alert('success', $i . ' Students Have Been Successfully Added');
                    }

                    return redirect()->to(base_url("student/csv_import"));
                }
                set_alert('error', translate('invalid_csv_file'));
                return redirect()->to(base_url("student/csv_import"));
            }
        }

        $this->data['title'] = translate('multiple_import');
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'student/multi_add';
        $this->data['main_menu'] = 'admission';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    /* showing disable authentication student list */
    public function disable_authentication()
    {
        // check access permission
        if (!get_permission('student_disable_authentication', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['students'] = $this->applicationModel->getStudentListByClassSection($classID, $sectionID, $branchID, true);
        }

        if (isset($_POST['auth'])) {
            if (!get_permission('student_disable_authentication', 'is_add')) {
                access_denied();
            }

            $stafflist = $this->request->getPost('views_bulk_operations');
            if (isset($stafflist)) {
                foreach ($stafflist as $id) {
                    $this->db->table(['role' => 7, 'user_id' => $id])->where();
                    $this->db->table('login_credential')->update();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                set_alert('error', 'Please select at least one item');
            }

            return redirect()->to(base_url('student/disable_authentication'));
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('deactivate_account');
        $this->data['sub_page'] = 'student/disable_authentication';
        $this->data['main_menu'] = 'student';
        echo view('layout/index', $this->data);
        return null;
    }

    // add new student category
    public function category()
    {
        if (isset($_POST['category'])) {
            if (!get_permission('student_category', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['category_name' => ["label" => translate('category_name'), "rules" => 'trim|required|callback_unique_category']]);
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('category_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('student_category')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('student/category'));
            }
        }

        $this->data['title'] = translate('student') . " " . translate('details');
        $this->data['sub_page'] = 'student/category';
        $this->data['main_menu'] = 'admission';
        echo view('layout/index', $this->data);
        return null;
    }

    // update existing student category
    public function category_edit()
    {
        if (!get_permission('student_category', 'is_edit')) {
            ajax_access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['category_name' => ["label" => translate('category_name'), "rules" => 'trim|required|callback_unique_category']]);
        if ($this->validation->run() !== false) {
            $categoryId = $this->request->getPost('category_id');
            $arrayData = ['name' => $this->request->getPost('category_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id')->where();
            $this->db->table('student_category')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    // delete student category from database
    public function category_delete($id)
    {
        if (get_permission('student_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('student_category')->delete();
        }
    }

    // student category details send by ajax
    public function categoryDetails()
    {
        if (get_permission('student_category', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $query = $builder->get('student_category');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    /* validate here, if the check student category name */
    public function unique_category($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $categoryId = $this->request->getPost('category_id');
        if (!empty($categoryId)) {
            $this->db->where_not_in('id', $categoryId);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('student_category')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }
        $this->validation->setRule("unique_category", translate('already_taken'));
        return false;
    }

    /* showing student list by class and section */
    public function view()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['students'] = $this->applicationModel->getStudentListByClassSection($classID, $sectionID, $branchID, false, true);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_list');
        $this->data['main_menu'] = 'student';
        $this->data['sub_page'] = 'student/view';
        $this->data['headerelements'] = ['js' => ['js/student.js']];
        echo view('layout/index', $this->data);
    }

    /* profile preview and information are updating here */
    public function profile($id = '')
    {
        // check access permission
        if (!get_permission('student', 'is_edit')) {
            access_denied();
        }

        $this->fees = new \App\Models\FeesModel();
        $this->exam = new \App\Models\ExamModel();
        $getStudent = $this->studentModel->getSingleStudent($id, true);
        if (isset($_POST['update'])) {
            session()->set_flashdata('profile_tab', 1);
            $this->data['branch_id'] = $this->applicationModel->get_branch_id();
            $this->student_validation();
            $this->validation->setRules(['parent_id' => ["label" => translate('guardian'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                //save all student information in the database file
                $studentID = $this->studentModel->save($post);
                //save student enroll information in the database file
                $arrayEnroll = ['class_id' => $this->request->getPost('class_id'), 'section_id' => $this->request->getPost('section_id'), 'roll' => $this->request->getPost('roll'), 'session_id' => $this->request->getPost('year_id'), 'branch_id' => $this->data['branch_id']];
                $this->db->table('id')->where();
                $this->db->table('enroll')->update();
                // handle custom fields data
                $classSlug = $this->router->fetch_class();
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $id);
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                return redirect()->to(base_url('student/profile/' . $id));
            }
        }

        $this->data['student'] = $getStudent;
        $this->data['title'] = translate('student_profile');
        $this->data['sub_page'] = 'student/profile';
        $this->data['main_menu'] = 'student';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['js/student.js', 'vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    /* student information delete here */
    public function delete_data($eid = '', $sid = '')
    {
        if (get_permission('student', 'is_delete')) {
            $branchID = get_type_name_by_id('enroll', $eid, 'branch_id');
            // Check student restrictions
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('student_id')->delete('enroll')->where();
            if ($db->affectedRows() > 0) {
                $this->db->table('id')->delete('student')->where();
                $this->db->table(['user_id' => $sid, 'role' => 7])->delete('login_credential')->where();
                $r = $db->table('fee_allocation')->get('fee_allocation')->result_array();
                $this->db->where_in('student_id', $sid)->delete('fee_allocation');
                $r = array_column($r, 'id');
                if ($r !== []) {
                    $this->db->where_in('allocation_id', $r)->delete('fee_payment_history');
                }

                $getField = $db->table('custom_field')->get('custom_field')->result_array();
                $fieldId = array_column($getField, 'id');
                $this->db->table('relid')->where();
                $this->db->where_in('field_id', $fieldId);
                $this->db->table('custom_fields_values')->delete();
            }
        }
    }

    // student document details are create here / ajax
    public function document_create()
    {
        if (!get_permission('student', 'is_edit')) {
            ajax_access_denied();
        }

        $this->validation->setRules(['document_title' => ["label" => translate('document_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['document_category' => ["label" => translate('document_category'), "rules" => 'trim|required']]);
        if (isset($_FILES['document_file']['name']) && empty($_FILES['document_file']['name'])) {
            $this->validation->setRules(['document_file' => ["label" => translate('document_file'), "rules" => 'required']]);
        }

        if ($this->validation->run() !== false) {
            $insertDoc = [
                'student_id' => $this->request->getPost('patient_id'), 
                'title' => $this->request->getPost('document_title'), 
                'type' => $this->request->getPost('document_category'), 
                'remarks' => $this->request->getPost('remarks')
            ];
            // uploading file using codeigniter upload library
            $config['upload_path'] = './uploads/attachments/documents/';
            $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt';
            $config['max_size'] = '2048';
            $config['encrypt_name'] = true;
            $file = $this->request->getFile('attachment_file');
            $file->initialize($config);

            if ($file->isValid() && !$file->hasMoved()) {
                $file->move($config['upload_path']);
                $insertDoc['file_name'] = $file->getClientName();
                $insertDoc['enc_name'] = $file->getName();
                $this->db->table('student_documents')->insert($insertDoc);
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                set_alert('error', strip_tags((string) $file->getErrorString()));
            }

            session()->setFlashdata('documents_details', 1);
            echo json_encode(['status' => 'success']);
        } else {
            $error = $this->validation->getErrors();
            echo json_encode(['status' => 'fail', 'error' => $error]);
        }
    }

    // student document details are update here / ajax
    public function document_update()
    {
        if (!get_permission('student', 'is_edit')) {
            ajax_access_denied();
        }

        // validate inputs
        $this->validation->setRules(['document_title' => ["label" => translate('document_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['document_category' => ["label" => translate('document_category'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $documentId = $this->request->getPost('document_id');
            $insertDoc = [
                'title' => $this->request->getPost('document_title'),
                'type' => $this->request->getPost('document_category'),
                'remarks' => $this->request->getPost('remarks')
            ];
            if (isset($_FILES["document_file"]) && !empty($_FILES['document_file']['name'])) {
                $config['upload_path'] = './uploads/attachments/documents/';
                $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt';
                $config['max_size'] = '2048';
                $config['encrypt_name'] = true;
                $file = $this->request->getFile('document_file');
                $file->move($config['upload_path'], $file->getRandomName());
                if ($file->isValid() && !$file->hasMoved()) {
                    $existFileName = $this->request->getPost('exist_file_name');
                    $existFilePath = FCPATH . 'uploads/attachments/documents/' . $existFileName;
                    if (file_exists($existFilePath)) {
                        unlink($existFilePath);
                    }

                    $insertDoc['file_name'] = $file->getClientName();
                    $insertDoc['enc_name'] = $file->getName();
                    set_alert('success', translate('information_has_been_updated_successfully'));
                } else {
                    set_alert('error', strip_tags($file->getErrorString()));
                }
            }

            $this->db->table('student_documents')->where('id', $documentId)->update($insertDoc);
            echo json_encode(['status' => 'success']);
            session()->setFlashdata('documents_details', 1);
        } else {
            $error = $this->validation->getErrors();
            echo json_encode(['status' => 'fail', 'error' => $error]);
        }
    }

    // student document details are delete here
    public function document_delete($id)
    {
        if (get_permission('student', 'is_edit')) {
            $encName = $db->table('student_documents')->get('student_documents')->row()->enc_name;
            $fileName = FCPATH . 'uploads/attachments/documents/' . $encName;
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $this->db->table('id')->where();
            $this->db->table('student_documents')->delete();
            session()->set_flashdata('documents_details', 1);
        }
    }

    public function document_details()
    {
        $this->request->getPost('id');
        $this->db->table('id')->where();
        $query = $builder->get('student_documents');
        $result = $query->row_array();
        echo json_encode($result);
    }

    // file downloader
    public function documents_download()
    {
        $encryptName = urldecode((string) $this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            $fileName = $db->table('student_documents')->get('student_documents')->row()->file_name;
            if (!empty($fileName)) {
                helper('download');
                return $this->response->download($fileName, file_get_contents('./uploads/attachments/documents/' . $encryptName));
            }
        }
        return null;
    }

    /* sample csv downloader */
    public function csv_Sampledownloader()
    {
        helper('download');
        $data = file_get_contents('uploads/multi_student_sample.csv');
        return $this->response->download("multi_student_sample.csv", $data);
    }

    /* validate here, if the check multi admission  email and roll */
    public function csvCheckExistsData($uniqueRoll, $studentUsername = '', $roll = '', $registerno = '', $classId = '', $sectionId = '', $branchID = '')
    {
        $array = [];
        if (!empty($roll) && $uniqueRoll != 0) {
            if ($uniqueRoll == 2) {
                $this->db->table('section_id')->where();
            }

            $this->db->table(['roll' => $roll, 'class_id' => $classId, 'branch_id' => $branchID])->where();
            $rollQuery = $builder->get('enroll');
            if ($rollQuery->num_rows() > 0) {
                $array['status'] = false;
                $array['message'] = "Roll Already Exists.";
                return $array;
            }
        }

        if ($studentUsername !== '') {
            $this->db->table('username')->where();
            $query = $builder->getWhere('login_credential');
            if ($query->num_rows() > 0) {
                $array['status'] = false;
                $array['message'] = "Student Username Already Exists.";
                return $array;
            }
        }

        if ($registerno !== '') {
            $this->db->table('register_no')->where();
            $query = $builder->getWhere('student');
            if ($query->num_rows() > 0) {
                $array['status'] = false;
                $array['message'] = "Student Register No Already Exists.";
                return $array;
            }
        } else {
            $array['status'] = false;
            $array['message'] = "Register No Is Required.";
            return $array;
        }

        $array['status'] = true;
        return $array;
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if ($this->request->getPost('student_id')) {
            $studentId = $this->request->getPost('student_id');
            $loginId = $this->appLib->getCredentialId($studentId, 'student');
            $this->db->where_not_in('id', $loginId);
        }

        $this->db->table('username')->where();
        $query = $builder->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_username", translate('already_taken'));
            return false;
        }
        return true;
    }

    /* unique valid guardian email address verification is done here */
    public function get_valid_guardian_username($username)
    {
        $this->db->table('username')->where();
        $query = $builder->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("get_valid_guardian_username", translate('username_has_already_been_used'));
            return false;
        }
        return true;
    }

    /* unique valid student roll verification is done here */
    public function unique_roll($roll)
    {
        if (empty($roll)) {
            return true;
        }

        $branchID = $this->applicationModel->get_branch_id();
        $schoolSettings = $this->studentModel->get('branch', ['id' => $branchID], true, false, 'unique_roll');
        $uniqueRoll = $schoolSettings['unique_roll'];
        if (empty($uniqueRoll) && $uniqueRoll == 0) {
            return true;
        }

        $classID = $this->request->getPost('class_id');
        $this->request->getPost('section_id');
        if ($this->uri->segment(3)) {
            $studentID = $db->table('enroll')->get('enroll')->row()->student_id;
            $this->db->where_not_in('student_id', $studentID);
        }

        if ($uniqueRoll == 2) {
            $this->db->table('section_id')->where();
        }

        $this->db->table(['roll' => $roll, 'class_id' => $classID, 'branch_id' => $branchID, 'session_id' => get_session_id()])->where();
        $q = $builder->get('enroll')->num_rows();
        if ($q == 0) {
            return true;
        }
        $this->validation->setRule("unique_roll", translate('already_taken'));
        return false;
    }

    /* unique valid register ID verification is done here */
    public function unique_registerid($register)
    {
        $this->applicationModel->get_branch_id();
        if ($this->uri->segment(3)) {
            $studentID = $db->table('enroll')->get('enroll')->row()->student_id;
            $this->db->where_not_in('id', $studentID);
        }

        $this->db->table('register_no')->where();
        $query = $builder->get('student')->num_rows();
        if ($query == 0) {
            return true;
        }
        $this->validation->setRule("unique_registerid", translate('already_taken'));
        return false;
    }

    public function search()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $searchText = $this->request->getPost('search_text');
        $this->data['query'] = $this->studentModel->getSearchStudentList(trim((string) $searchText));
        $this->data['title'] = translate('searching_results');
        $this->data['sub_page'] = 'student/search';
        $this->data['main_menu'] = '';
        echo view('layout/index', $this->data);
    }

    /* student password change here */
    public function change_password()
    {
        if (get_permission('student', 'is_edit')) {
            if (!isset($_POST['authentication'])) {
                $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
            } else {
                $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
                $this->validation->setRules(['reason_id' => ["label" => translate('disable_reason'), "rules" => 'trim|required']]);
            }

            if ($this->validation->run() !== false) {
                $studentID = $this->request->getPost('student_id');
                $password = $this->request->getPost('password');
                if (!isset($_POST['authentication'])) {
                    $this->db->table('role')->where();
                    $this->db->table('user_id')->where();
                    $this->db->table('login_credential')->update();
                } else {
                    $this->db->table('role')->where();
                    $this->db->table('user_id')->where();
                    $this->db->table('login_credential')->update();
                    // insert disable reason history in DB
                    $insertData = ['student_id' => $studentID, 'reason_id' => $this->request->getPost('reason_id'), 'note' => $this->request->getPost('note'), 'date' => date("Y-m-d", strtotime((string) $this->request->getPost('date')))];
                    $this->db->table('disable_reason_details')->insert();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // student quick details
    public function quickDetails()
    {
        $this->request->getPost('student_id');
        $builder->select('student.*,enroll.student_id,enroll.roll,student_category.name as cname');
        $this->db->from('enroll');
        $builder->join('student', 'student.id = enroll.student_id', 'inner');
        $builder->join('student_category', 'student_category.id = student.category_id', 'left');
        $this->db->table('enroll.id')->where();
        $row = $builder->get()->row();
        $data['photo'] = get_image_url('student', $row->photo);
        $data['full_name'] = $row->first_name . " " . $row->last_name;
        $data['student_category'] = $row->cname;
        $data['register_no'] = $row->register_no;
        $data['roll'] = $row->roll;
        $data['admission_date'] = empty($row->admission_date) ? "N/A" : _d($row->admission_date);
        $data['birthday'] = empty($row->birthday) ? "N/A" : _d($row->birthday);
        $data['blood_group'] = empty($row->blood_group) ? "N/A" : $row->blood_group;
        $data['religion'] = empty($row->religion) ? "N/A" : $row->religion;
        $data['email'] = $row->email;
        $data['mobileno'] = empty($row->mobileno) ? "N/A" : $row->mobileno;
        $data['state'] = empty($row->state) ? "N/A" : $row->state;
        $data['address'] = empty($row->current_address) ? "N/A" : $row->current_address;
        echo json_encode($data);
    }

    public function bulk_delete()
    {
        $status = 'success';
        $message = translate('information_deleted');
        if (get_permission('student', 'is_delete')) {
            $arrayID = $this->request->getPost('array_id');
            foreach ($arrayID as $row) {
                $branchID = get_type_name_by_id('enroll', $row, 'branch_id');
                $getField = $db->table('custom_field')->get('custom_field')->result_array();
                $fieldId = array_column($getField, 'id');
                $this->db->table('relid')->where();
                $this->db->where_in('field_id', $fieldId);
                $this->db->table('custom_fields_values')->delete();
            }

            $this->db->where_in('student_id', $arrayID)->delete('enroll');
            $this->db->where_in('id', $arrayID)->delete('student');
            $this->db->where_in('user_id', $arrayID)->where('role', 7)->delete('login_credential');
            $r = $builder->select('id')->where_in('student_id', $arrayID)->get('fee_allocation')->result_array();
            $this->db->where_in('student_id', $arrayID)->delete('fee_allocation');
            $r = array_column($r, 'id');
            if ($r !== []) {
                $this->db->where_in('allocation_id', $r)->delete('fee_payment_history');
            }
        } else {
            $message = translate('access_denied');
            $status = 'error';
        }

        echo json_encode(['status' => $status, 'message' => $message]);
    }

    /* student login credential list by class and section */
    public function login_credential_reports()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['students'] = $this->applicationModel->getStudentListByClassSection($classID, $sectionID, $branchID, false, true);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('login_credential');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/login_credential_reports';
        echo view('layout/index', $this->data);
    }

    public function password_reset($type)
    {
        if ($_POST !== []) {
            $this->validation->setRules(['new_password' => ["label" => 'New Password', "rules" => 'trim|required|min_length[4]']]);
            $this->validation->setRules(['confirm_password' => ["label" => 'Confirm Password', "rules" => 'trim|required|min_length[4]|matches[new_password]']]);
            if ($this->validation->run() == true) {
                $newPassword = $this->request->getPost('new_password');
                if (!empty($type)) {
                    if ($type == 'student') {
                        $studentId = $this->request->getPost('student_id');
                        if (!is_superadmin_loggedin()) {
                            $chkID = $db->table('enroll')->get('enroll')->row();
                            if (empty($chkID)) {
                                exit;
                            }
                        }

                        $this->db->table('user_id')->where();
                        $this->db->table('role')->where();
                    }

                    if ($type == 'parent') {
                        $parentId = $this->request->getPost('parent_id');
                        if (!is_superadmin_loggedin()) {
                            $chkID = $db->table('parent')->get('parent')->row();
                            if (empty($chkID)) {
                                exit;
                            }
                        }

                        $this->db->table('user_id')->where();
                        $this->db->table('role')->where();
                    }

                    $this->db->table('login_credential')->update();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    /* student admission list by date */
    public function admission_reports()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['start'] = $start;
            $this->data['end'] = $end;
            $this->data['students'] = $this->studentModel->getStudentList($classID, $sectionID, $branchID, false, $start, $end)->result_array();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('admission_reports');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/admission_reports';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function classsection_reports()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('class_&_section');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/classsection_reports';
        echo view('layout/index', $this->data);
    }

    // add new student deactivate reason
    public function disable_reason()
    {
        if (isset($_POST['disable_reason'])) {
            if (!get_permission('disable_reason', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['name' => ["label" => translate('reason'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('disable_reason')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('student/disable_reason'));
            }
        }

        $this->data['title'] = translate('deactivate_reason');
        $this->data['categorylist'] = $this->appLib->getTable('disable_reason');
        $this->data['sub_page'] = 'student/disable_reason';
        $this->data['main_menu'] = 'student';
        echo view('layout/index', $this->data);
        return null;
    }

    // update existing student deactivate reason
    public function disable_reason_edit()
    {
        if (!get_permission('disable_reason', 'is_edit')) {
            ajax_access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('reason'), "rules" => 'trim|required']]);
        if ($this->validation->run() !== false) {
            $categoryId = $this->request->getPost('reason_id');
            $arrayData = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id')->where();
            $this->db->table('disable_reason')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    // delete student deactivate reason from database
    public function disable_reason_delete($id)
    {
        if (get_permission('disable_reason', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('disable_reason')->delete();
        }
    }

    // student disable reason details send by ajax
    public function disableReasonDetails()
    {
        if (get_permission('disable_reason', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $query = $builder->get('disable_reason');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function sibling_report()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $getParentsList = $this->studentModel->getParentList($classID, $sectionID, $branchID);
            $list = [];
            foreach ($getParentsList as $key => $parent) {
                if (intval($parent['child']) > 1) {
                    $getParentsList[$key]['student'] = $this->studentModel->getSiblingListByClass($parent['parent_id'], $classID, $sectionID);
                    $list[] = $getParentsList[$key];
                }
            }

            $this->data['students'] = $list;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('sibling_report');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/sibling_report';
        echo view('layout/index', $this->data);
    }
}
