<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\OnlineAdmissionModel;
use App\Models\StudentFieldsModel;
use App\Models\EmailModel;
use App\Models\SmsModel;
/**
 * @package : Ramom school management system
 * @version : 5.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Online_admission.php
 * @copyright : Reserved RamomCoder Team
 */
class Online_admission extends AdminController

{
    protected $db;




    public $load;

    /**
     * @var App\Models\OnlineAdmissionModel
     */
    public $onlineAdmission;

    /**
     * @var App\Models\StudentFieldsModel
     */
    public $studentFields;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    public $applicationModel;

    public $input;

    public $appLib;

    public $online_admissionModel;

    public $validation;

    public $student_fieldsModel;

    public $emailModel;

    public $smsModel;

    public $uri;

    public function __construct()
    {




        parent::__construct();

        $this->appLib = service('appLib'); 
$this->load->helpers('custom_fields');
        $this->onlineAdmission = new \App\Models\OnlineAdmissionModel();
        $this->studentFields = new \App\Models\StudentFieldsModel();
        $this->email = new \App\Models\EmailModel();
        $this->sms = new \App\Models\SmsModel();
    }

    public function index()
    {
        // check access permission
        if (!get_permission('online_admission', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['students'] = $this->online_admissionModel->getOnlineAdmission($classID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_list');
        $this->data['main_menu'] = 'admission';
        $this->data['sub_page'] = 'online_admission/index';
        $this->data['headerelements'] = ['js' => ['js/student.js']];
        echo view('layout/index', $this->data);
    }

    // delete student from database
    public function delete($id)
    {
        if (get_permission('online_admission', 'is_delete')) {
            $branchId = $db->table('online_admission')->get('online_admission')->row()->branch_id;
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('online_admission')->delete();
            if ($db->affectedRows() > 0) {
                $result = $db->table('custom_field')->get('custom_field')->result_array();
                foreach ($result as $value) {
                    $this->db->table('relid')->where();
                    $this->db->table('field_id')->where();
                    $this->db->table('custom_fields_values')->delete();
                }
            }
        }
    }

    public function decline($id)
    {
        if (get_permission('online_admission', 'is_add')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('online_admission')->update();
        }
    }

    public function approved($studentId = '')
    {
        // check access permission
        if (!get_permission('online_admission', 'is_add')) {
            access_denied();
        }

        // check saas student add limit
        if ($this->appLib->isExistingAddon('saas') && !checkSaasLimit('student')) {
            set_alert('error', translate('update_your_package'));
            redirect(site_url('dashboard'));
        }

        $stuDetails = $this->online_admissionModel->get('online_admission', ['id' => $studentId, 'status !=' => 2], true, true);
        if (empty($stuDetails['id'])) {
            access_denied();
        }

        $branchID = $stuDetails['branch_id'];
        $getBranch = $this->db->table('branch')->where('id', $branchID)->get()->getRowArray();
        $guardian = false;
        if ($_POST !== []) {
            $newStudentPhoto = 0;
            $newGuardianPhoto = 0;
            $existStudentPhoto = $this->request->getPost('exist_student_photo');
            $existGuardianPhoto = $this->request->getPost('exist_guardian_photo');
            if (isset($_FILES["student_photo"]) && empty($_FILES["student_photo"]['name'])) {
                $newStudentPhoto = 1;
            }

            if (isset($_FILES["guardian_photo"]) && empty($_FILES["guardian_photo"]['name'])) {
                $newGuardianPhoto = 1;
            }

            $this->validation->setRules(['first_name' => ["label" => translate('first_name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['year_id' => ["label" => translate('academic_year'), "rules" => 'trim|required']]);
            $this->validation->setRules(['register_no' => ["label" => translate('register_no'), "rules" => 'trim|required']]);
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'trim|required']]);
            // checking profile photo format
            $this->validation->setRules(['student_photo' => ["label" => translate('profile_picture'), "rules" => 'callback_photoHandleUpload[student_photo]']]);
            $this->validation->setRules(['guardian_photo' => ["label" => translate('profile_picture'), "rules" => 'callback_photoHandleUpload[guardian_photo]']]);
            // custom fields validation rules
            $customFields = getOnlineCustomFields('student', $branchID);
            foreach ($customFields as $fieldsValue) {
                if ($fieldsValue['required']) {
                    $fieldsID = $fieldsValue['id'];
                    $fieldLabel = $fieldsValue['field_label'];
                    $this->validation->setRules(["custom_fields[student][" . $fieldsID . "]" => ["label" => $fieldLabel, "rules" => 'trim|required']]);
                }
            }

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

            if (isset($validArr['student_photo']) && ($newStudentPhoto == 1 && empty($existStudentPhoto))) {
                $this->validation->setRules(['student_photo' => ["label" => translate('profile_picture'), "rules" => 'required']]);
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

            if (isset($validArr['guardian_name'])) {
                $this->validation->setRules(['grd_name' => ["label" => translate('name'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_relation'])) {
                $this->validation->setRules(['grd_relation' => ["label" => translate('relation'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['father_name'])) {
                $this->validation->setRules(['father_name' => ["label" => translate('father_name'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['mother_name'])) {
                $this->validation->setRules(['mother_name' => ["label" => translate('mother_name'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_occupation'])) {
                $this->validation->setRules(['grd_occupation' => ["label" => translate('occupation'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_income'])) {
                $this->validation->setRules(['grd_income' => ["label" => translate('occupation'), "rules" => 'trim|required|numeric']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_education'])) {
                $this->validation->setRules(['grd_education' => ["label" => translate('education'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_email'])) {
                $this->validation->setRules(['grd_email' => ["label" => translate('email'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_mobile_no'])) {
                $this->validation->setRules(['grd_mobileno' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_address'])) {
                $this->validation->setRules(['grd_address' => ["label" => translate('address'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_photo']) && ($newGuardianPhoto == 1 && empty($existGuardianPhoto))) {
                $this->validation->setRules(['guardian_photo' => ["label" => translate('guardian_picture'), "rules" => 'required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_city'])) {
                $this->validation->setRules(['grd_city' => ["label" => translate('city'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if (isset($validArr['guardian_state'])) {
                $this->validation->setRules(['grd_state' => ["label" => translate('state'), "rules" => 'trim|required']]);
                $guardian = true;
            }

            if ($getBranch['stu_generate'] == 0 || isset($_POST['student_id'])) {
                $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required|callback_unique_username']]);
                if (!isset($_POST['student_id'])) {
                    $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
                    $this->validation->setRules(['retype_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[password]']]);
                }
            }

            if ($getBranch['grd_generate'] == 0 && $guardian == true) {
                $this->validation->setRules(['grd_username' => ["label" => translate('username'), "rules" => 'trim|required|callback_get_valid_guardian_username']]);
                $this->validation->setRules(['grd_password' => ["label" => translate('password'), "rules" => 'trim|required']]);
                $this->validation->setRules(['grd_retype_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[grd_password]']]);
            }

            // custom fields validation rules
            $classSlug = "student";
            $customFields = getCustomFields($classSlug, $branchID);
            foreach ($customFields as $fieldsValue) {
                if ($fieldsValue['required']) {
                    $fieldsID = $fieldsValue['id'];
                    $fieldLabel = $fieldsValue['field_label'];
                    $this->validation->setRules(["custom_fields[student][" . $fieldsID . "]" => ["label" => $fieldLabel, "rules" => 'trim|required']]);
                }
            }

            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                //save all student information in the database file
                $studentData = $this->online_admissionModel->save($post, $getBranch);
                $studentID = $studentData['student_id'];
                //save student enroll information in the database file
                $arrayEnroll = ['student_id' => $studentID, 'class_id' => $post['class_id'], 'section_id' => $post['section_id'] ?? 0, 'roll' => $post['roll'] ?? 0, 'session_id' => $post['year_id'], 'branch_id' => $branchID];
                $this->db->table('enroll')->insert();
                $this->db->table('id')->where();
                $this->db->table('online_admission')->update();
                // handle custom fields data
                $classSlug = "student";
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $studentID);
                }

                // send student admission email
                $this->emailModel->studentAdmission($studentData);
                //send account activate sms
                $this->smsModel->send_sms($arrayEnroll, 1);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('online_admission');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['stuDetails'] = $stuDetails;
        $this->data['getBranch'] = $getBranch;
        $this->data['sub_page'] = 'online_admission/approved';
        $this->data['main_menu'] = 'admission';
        $this->data['register_id'] = $this->online_admissionModel->regSerNumber($branchID);
        $this->data['title'] = translate('online_admission');
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['js/student.js', 'vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
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
        $schoolSettings = $this->online_admissionModel->get('branch', ['id' => $branchID], true, false, 'unique_roll');
        $uniqueRoll = $schoolSettings['unique_roll'];
        if (empty($uniqueRoll) && $uniqueRoll == 0) {
            return true;
        }

        $classID = $this->request->getPost('class_id');
        $this->request->getPost('section_id');
        if ($this->uri->segment(3)) {
            $this->db->where_not_in('student_id', $this->uri->segment(3));
        }

        if ($uniqueRoll == 2) {
            $this->db->table('section_id')->where();
        }

        $this->db->table(['roll' => $roll, 'class_id' => $classID, 'branch_id' => $branchID])->where();
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
            $this->db->where_not_in('id', $this->uri->segment(3));
        }

        $this->db->table('register_no')->where();
        $query = $builder->get('student')->num_rows();
        if ($query == 0) {
            return true;
        }

        $this->validation->setRule("unique_registerid", translate('already_taken'));
        return false;
    }

    public function download($id)
    {
        helper('download');
        $filepath = "./uploads/online_ad_documents/" . $id;
        $data = file_get_contents($filepath);
        return $this->response->download($id, $data);
    }
}
