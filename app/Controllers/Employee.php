<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
use App\Models\CrudModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Employee.php
 * @copyright : Reserved RamomCoder Team
 */
class Employee extends AdminController

{
    /**
     * @var mixed
     */
    public $Csvimport;

    public $bulk;

    protected $db;




    public $load;

    /**
     * @var App\Models\EmployeeModel
     */
    public $employee;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\CrudModel
     */
    public $crud;

    public $validation;

    public $input;

    public $router;

    public $applicationModel;

    public $appLib;

    public $employeeModel;

    public $emailModel;

    public $session;

    public $uri;

    public $upload;

    public $csvimport;

    public function __construct()
    {




        parent::__construct();



        $this->csvimport = service('csvimport');$this->bulk = service('bulk');$this->appLib = service('appLib'); 
$this->load->helpers('custom_fields');
        $this->employee = new \App\Models\EmployeeModel();
        $this->email = new \App\Models\EmailModel();
        $this->crud = new \App\Models\CrudModel();
    }

    public function index()
    {
        return redirect()->to(base_url('dashboard'));
    }

    /* staff form validation rules */
    protected function employee_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['mobile_no' => ["label" => translate('mobile_no'), "rules" => 'trim|required']]);
        $this->validation->setRules(['present_address' => ["label" => translate('present_address'), "rules" => 'trim|required']]);
        $this->validation->setRules(['designation_id' => ["label" => translate('designation'), "rules" => 'trim|required']]);
        $this->validation->setRules(['department_id' => ["label" => translate('department'), "rules" => 'trim|required']]);
        $this->validation->setRules(['joining_date' => ["label" => translate('joining_date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['qualification' => ["label" => translate('qualification'), "rules" => 'trim|required']]);
        $this->validation->setRules(['user_role' => ["label" => translate('role'), "rules" => 'trim|required|callback_valid_role']]);
        $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required|callback_unique_username']]);
        if ($this->request->getPost('staff_id')) {
            $this->validation->setRules(['staff_id_no' => ["label" => translate('staff_id'), "rules" => 'trim|required|callback_unique_staffID']]);
        }

        $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email']]);
        if (!isset($_POST['staff_id'])) {
            $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
            $this->validation->setRules(['retype_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[password]']]);
        }

        $this->validation->setRules(['facebook' => ["label" => 'Facebook', "rules" => 'valid_url']]);
        $this->validation->setRules(['twitter' => ["label" => 'Twitter', "rules" => 'valid_url']]);
        $this->validation->setRules(['linkedin' => ["label" => 'Linkedin', "rules" => 'valid_url']]);
        $this->validation->setRules(['user_photo' => ["label" => 'profile_picture', "rules" => 'callback_photoHandleUpload[user_photo]']]);
        // custom fields validation rules
        $classSlug = $this->router->fetch_class();
        $customFields = getCustomFields($classSlug);
        foreach ($customFields as $fieldsValue) {
            if ($fieldsValue['required']) {
                $fieldsID = $fieldsValue['id'];
                $fieldLabel = $fieldsValue['field_label'];
                $this->validation->setRules(["custom_fields[employee][" . $fieldsID . "]" => ["label" => $fieldLabel, "rules" => 'trim|required']]);
            }
        }
    }

    /* getting all employee list */
    public function view($role = 2)
    {
        if (!get_permission('employee', 'is_view') || ($role == 1 || $role == 6 || $role == 7)) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['act_role'] = $role;
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'employee/view';
        $this->data['main_menu'] = 'employee';
        $this->data['stafflist'] = $this->employeeModel->getStaffList($branchID, $role);
        echo view('layout/index', $this->data);
    }

    /* bank form validation rules */
    protected function bank_validation()
    {
        $this->validation->setRules(['bank_name' => ["label" => translate('bank_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['holder_name' => ["label" => translate('holder_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['bank_branch' => ["label" => translate('bank_branch'), "rules" => 'trim|required']]);
        $this->validation->setRules(['account_no' => ["label" => translate('account_no'), "rules" => 'trim|required']]);
    }

    /* employees all information are prepared and stored in the database here */
    public function add()
    {
        if (!get_permission('employee', 'is_add')) {
            access_denied();
        }

        if ($_POST !== []) {
            $userRole = $this->request->getPost('user_role');
            //Saas addon script
            if ($this->appLib->isExistingAddon('saas')) {
                if ($userRole == 3) {
                    // check saas teacher add limit
                    if (!checkSaasLimit('teacher')) {
                        set_alert('error', translate('update_your_package'));
                        redirect(site_url('dashboard'));
                    }
                } elseif (!checkSaasLimit('staff')) {
                    // check saas staff add limit
                    set_alert('error', translate('update_your_package'));
                    redirect(site_url('dashboard'));
                }
            }

            $this->employee_validation();
            if (!isset($_POST['chkskipped'])) {
                $this->bank_validation();
            }

            if ($this->validation->run() !== false) {
                //save all employee information in the database
                $post = $this->request->getPost();
                $empID = $this->employeeModel->save($post);
                // handle custom fields data
                $classSlug = $this->router->fetch_class();
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $empID);
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                //send account activate email
                $this->emailModel->sentStaffRegisteredAccount($post);
                return redirect()->to(base_url('employee/view/' . $post['user_role']));
            }
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('add_employee');
        $this->data['sub_page'] = 'employee/add';
        $this->data['main_menu'] = 'employee';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['js/employee.js', 'vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    /* profile preview and information are controlled here */
    public function profile($id = '')
    {
        if (!get_permission('employee', 'is_edit')) {
            access_denied();
        }

        if ($this->request->getPost('submit') == 'update') {
            $this->employee_validation();
            if ($this->validation->run() == true) {
                //save all employee information in the database
                $this->employeeModel->save($this->request->getPost());
                // handle custom fields data
                $classSlug = $this->router->fetch_class();
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $id);
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                session()->set_flashdata('profile_tab', 1);
                return redirect()->to(base_url('employee/profile/' . $id));
            }

            session()->set_flashdata('profile_tab', 1);
        }

        $this->data['categorylist'] = $this->appLib->get_document_category();
        $this->data['staff'] = $this->employeeModel->getSingleStaff($id);
        $this->data['title'] = translate('employee_profile');
        $this->data['sub_page'] = 'employee/profile';
        $this->data['main_menu'] = 'employee';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['js/employee.js', 'vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    // user interface and employees all information are prepared and stored in the database here
    public function delete($id = '')
    {
        if (!get_permission('employee', 'is_delete')) {
            access_denied();
        }

        // check student restrictions
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('staff')->delete();
        if ($db->affectedRows() > 0) {
            $this->db->table('user_id')->where();
            $this->db->where_not_in('role', [1, 6, 7]);
            $this->db->table('login_credential')->delete();
        }
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if ($this->request->getPost('staff_id')) {
            $staffId = $this->request->getPost('staff_id');
            $loginId = $this->appLib->getCredentialId($staffId);
            $this->db->where_not_in('id', $loginId);
        }

        $this->db->table('username')->where();
        $query = $builder->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_username", translate('username_has_already_been_used'));
            return false;
        }

        return true;
    }

    // unique valid staff id verification is done here
    public function unique_staffID($id)
    {
        $this->applicationModel->get_branch_id();
        if ($this->request->getPost('staff_id')) {
            $staffId = $this->request->getPost('staff_id');
            $this->db->where_not_in('id', $staffId);
        }

        $this->db->table('branch_id')->where();
        $this->db->table('staff_id')->where();
        $query = $builder->get('staff');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_staffID", translate('already_taken'));
            return false;
        }

        return true;
    }

    public function valid_role($id)
    {
        $restrictions = [1, 6, 7];
        if (in_array($id, $restrictions, true)) {
            $this->validation->setRule("valid_role", translate('selected_role_restrictions'));
            return false;
        }

        return true;
    }

    // employee login password change here by admin
    public function change_password()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }

        if (!isset($_POST['authentication'])) {
            $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
        } else {
            $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim']]);
        }

        if ($this->validation->run() !== false) {
            $studentID = $this->request->getPost('staff_id');
            $password = $this->request->getPost('password');
            $this->db->where_not_in('role', [1, 6, 7]);
            $this->db->table('user_id')->where();
            $this->db->table('login_credential')->update();

            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    // employee bank details are create here / ajax
    public function bank_account_create()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }

        $this->bank_validation();
        if ($this->validation->run() !== false) {
            $post = $this->request->getPost();
            $this->employeeModel->bankSave($post);
            set_alert('success', translate('information_has_been_saved_successfully'));
            session()->set_flashdata('bank_tab', 1);
            echo json_encode(['status' => 'success']);
        } else {
            $error = $this->validation->error_array();
            echo json_encode(['status' => 'fail', 'error' => $error]);
        }
    }

    // employee bank details are update here / ajax
    public function bank_account_update()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }

        $this->bank_validation();
        if ($this->validation->run() !== false) {
            $post = $this->request->getPost();
            $this->employeeModel->bankSave($post);
            session()->set_flashdata('bank_tab', 1);
            set_alert('success', translate('information_has_been_updated_successfully'));
            echo json_encode(['status' => 'success']);
        } else {
            $error = $this->validation->error_array();
            echo json_encode(['status' => 'fail', 'error' => $error]);
        }
    }

    // employee bank details are delete here
    public function bankaccount_delete($id)
    {
        if (get_permission('employee', 'is_edit')) {
            $this->db->table('id')->where();
            $this->db->table('staff_bank_account')->delete();
            session()->set_flashdata('bank_tab', 1);
        }
    }

    public function bank_details()
    {
        $this->request->getPost('id');
        $this->db->table('id')->where();
        $query = $builder->get('staff_bank_account');
        $result = $query->row_array();
        echo json_encode($result);
    }

    protected function document_validation()
    {
        $this->validation->setRules(['document_title' => ["label" => translate('document_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['document_category' => ["label" => translate('document_category'), "rules" => 'trim|required']]);
        if ($this->uri->segment(2) != 'document_update' && (isset($_FILES['document_file']['name']) && empty($_FILES['document_file']['name']))) {
            $this->validation->setRules(['document_file' => ["label" => translate('document_file'), "rules" => 'required']]);
        }
    }

    // employee document details are create here / ajax
    public function document_create()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }

        $this->document_validation();
        if ($this->validation->run() !== false) {
            $insertDoc = [
                'staff_id' => $this->request->getPost('staff_id'),
                'title' => $this->request->getPost('document_title'),
                'category_id' => $this->request->getPost('document_category'),
                'remarks' => $this->request->getPost('remarks')
            ];

            $file = $this->request->getFile('attachment_file');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $config = [
                    'upload_path'   => './uploads/attachments/documents/',
                    'allowed_types' => 'gif|jpg|png|pdf|docx|csv|txt',
                    'max_size'      => 2048,
                    'encrypt_name'  => true,
                ];

                $file->move($config['upload_path'], $file->getRandomName());

                $insertDoc['file_name'] = $file->getClientName();
                $insertDoc['enc_name'] = $file->getName();

                $this->db->table('staff_documents')->insert($insertDoc);
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                set_alert('error', $file->getErrorString());
            }

            session()->setFlashdata('documents_details', 1);
            echo json_encode(['status' => 'success']);
        } else {
            $error = $this->validation->getErrors();
            echo json_encode(['status' => 'fail', 'error' => $error]);
        }
    }

    // employee document details are update here / ajax
    public function document_update()
{
    if (!get_permission('employee', 'is_edit')) {
        ajax_access_denied();
    }

    // validate inputs
    $this->document_validation();
    if ($this->validation->run() !== false) {
        $documentId = $this->request->getPost('document_id');
        $insertDoc = [
            'title' => $this->request->getPost('document_title'),
            'category_id' => $this->request->getPost('document_category'),
            'remarks' => $this->request->getPost('remarks')
        ];

        $file = $this->request->getFile('document_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $config = [
                'upload_path' => './uploads/attachments/documents/',
                'allowed_types' => 'gif|jpg|png|pdf|docx|csv|txt',
                'max_size' => 2048,
                'encrypt_name' => true,
            ];
            $file->move($config['upload_path'], $file->getRandomName());
            // Remove the existing file
            $existFileName = $this->request->getPost('exist_file_name');
            $existFilePath = FCPATH . 'uploads/attachments/documents/' . $existFileName;
            if (file_exists($existFilePath)) {
                unlink($existFilePath);
            }

            // Set new file data
            $insertDoc['file_name'] = $file->getClientName();
            $insertDoc['enc_name'] = $file->getName();
        } elseif ($file) {
            set_alert('error', $file->getErrorString());
            echo json_encode(['status' => 'fail', 'error' => $file->getErrorString()]);
            return;
        }

        $this->db->table('staff_documents')->where('id', $documentId)->update($insertDoc);
        set_alert('success', translate('information_has_been_updated_successfully'));
        echo json_encode(['status' => 'success']);
        session()->setFlashdata('documents_details', 1);
    } else {
        $error = $this->validation->getErrors();
        echo json_encode(['status' => 'fail', 'error' => $error]);
    }
}

    // employee document details are delete here
    public function document_delete($id)
    {
        if (get_permission('employee', 'is_edit')) {
            $encName = $db->table('staff_documents')->get('staff_documents')->row()->enc_name;
            $fileName = FCPATH . 'uploads/attachments/documents/' . $encName;
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $this->db->table('id')->where();
            $this->db->table('staff_documents')->delete();
            session()->set_flashdata('documents_details', 1);
        }
    }

    public function document_details()
    {
        $this->request->getPost('id');
        $this->db->table('id')->where();
        $query = $builder->get('staff_documents');
        $result = $query->row_array();
        echo json_encode($result);
    }

    /* file downloader */
    public function documents_download()
    {
        $encryptName = urldecode((string) $this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            $fileName = $db->table('staff_documents')->get('staff_documents')->row()->file_name;
            if (!empty($fileName)) {
                helper('download');
                return $this->response->download($fileName, file_get_contents('uploads/attachments/documents/' . $encryptName));
            }
        }

        return null;
    }

    /* department form validation rules */
    protected function department_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['department_name' => ["label" => translate('department_name'), "rules" => 'trim|required|callback_unique_department']]);
    }

    // employee department user interface and information are controlled here
    public function department()
    {
        if ($_POST !== []) {
            if (!get_permission('department', 'is_add')) {
                access_denied();
            }

            $this->department_validation();
            if ($this->validation->run() !== false) {
                $arrayDepartment = ['name' => $this->request->getPost('department_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('staff_department')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('employee/department'));
            }
        }

        $this->data['department'] = $this->appLib->getTable('staff_department');
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'employee/department';
        $this->data['main_menu'] = 'employee';
        echo view('layout/index', $this->data);
        return null;
    }

    public function department_edit()
    {
        if (!get_permission('department', 'is_edit')) {
            ajax_access_denied();
        }

        $this->department_validation();
        if ($this->validation->run() !== false) {
            $arrayDepartment = ['name' => $this->request->getPost('department_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $departmentId = $this->request->getPost('department_id');
            $this->db->table('id')->where();
            $this->db->table('staff_department')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function department_delete($id)
    {
        if (!get_permission('department', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('staff_department')->delete();
    }

    // unique valid department name verification is done here
    public function unique_department($name)
    {
        $departmentId = $this->request->getPost('department_id');
        $this->applicationModel->get_branch_id();
        if (!empty($departmentId)) {
            $this->db->where_not_in('id', $departmentId);
        }

        $this->db->table('branch_id')->where();
        $this->db->table('name')->where();
        $q = $builder->get('staff_department');
        if ($q->num_rows() > 0) {
            $this->validation->setRule("unique_department", translate('already_taken'));
            return false;
        }

        return true;
    }

    /* designation form validation rules */
    protected function designation_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['designation_name' => ["label" => translate('designation_name'), "rules" => 'trim|required|callback_unique_designation']]);
    }

    // employee designation user interface and information are controlled here
    public function designation()
    {
        if ($_POST !== []) {
            if (!get_permission('designation', 'is_add')) {
                access_denied();
            }

            $this->designation_validation();
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('designation_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('staff_designation')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('employee/designation'));
            }
        }

        $this->data['designation'] = $this->appLib->getTable('staff_designation');
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'employee/designation';
        $this->data['main_menu'] = 'employee';
        echo view('layout/index', $this->data);
        return null;
    }

    public function designation_edit()
    {
        if (!get_permission('designation', 'is_edit')) {
            ajax_access_denied();
        }

        $this->designation_validation();
        if ($this->validation->run() !== false) {
            $designationId = $this->request->getPost('designation_id');
            $arrayData = ['name' => $this->request->getPost('designation_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id')->where();
            $this->db->table('staff_designation')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function designation_delete($id)
    {
        if (!get_permission('designation', 'is_delete')) {
            access_denied();
        }

        $this->db->table('id')->where();
        $this->db->table('staff_designation')->delete();
    }

    // unique valid designation name verification is done here
    public function unique_designation($name)
    {
        $designationId = $this->request->getPost('designation_id');
        $this->applicationModel->get_branch_id();
        if (!empty($designationId)) {
            $this->db->where_not_in('id', $designationId);
        }

        $this->db->table('name')->where();
        $this->db->table('branch_id')->where();
        $q = $builder->get('staff_designation');
        if ($q->num_rows() > 0) {
            $this->validation->setRule("unique_designation", translate('already_taken'));
            return false;
        }

        return true;
    }

    // showing disable authentication student list
    public function disable_authentication()
    {
        // check access permission
        if (!get_permission('employee_disable_authentication', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            $branchID = $this->applicationModel->get_branch_id();
            $role = $this->request->getPost('staff_role');
            $this->data['stafflist'] = $this->employeeModel->getStaffList($branchID, $role, 0);
        }

        if (isset($_POST['auth'])) {
            if (!get_permission('employee_disable_authentication', 'is_add')) {
                access_denied();
            }

            $stafflist = $this->request->getPost('views_bulk_operations');
            if (isset($stafflist)) {
                foreach ($stafflist as $id) {
                    $this->db->table('user_id')->where();
                    $this->db->where_not_in('role', [1, 6, 7]);
                    $this->db->table('login_credential')->update();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                set_alert('error', 'Please select at least one item');
            }

            return redirect()->to(base_url('employee/disable_authentication'));
        }

        $this->data['title'] = translate('deactivate_account');
        $this->data['sub_page'] = 'employee/disable_authentication';
        $this->data['main_menu'] = 'employee';
        echo view('layout/index', $this->data);
        return null;
    }

    /* employee csv importer */
    public function csv_import()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['user_role' => ["label" => translate('role'), "rules" => 'trim|required']]);
        $this->validation->setRules(['designation_id' => ["label" => translate('designation'), "rules" => 'trim|required']]);
        $this->validation->setRules(['department_id' => ["label" => translate('department'), "rules" => 'trim|required']]);
        if (isset($_FILES['userfile']['name']) && empty($_FILES['userfile']['name'])) {
            $this->validation->setRules(['userfile' => ["label" => "Select CSV File", "rules" => 'required']]);
        }

        if ($this->validation->run() !== false) {
            $branchID = $this->applicationModel->get_branch_id();
            $userRole = $this->request->getPost('user_role');
            $designationID = $this->request->getPost('designation_id');
            $departmentID = $this->request->getPost('department_id');
            $errMsg = "";
            $i = 0;
            $this->Csvimport = service('csvimport');
            $csvArray = $this->csvimport->get_array($_FILES["userfile"]["tmp_name"]);
            if ($csvArray) {
                $columnHeaders = ['Name', 'Gender', 'Religion', 'BloodGroup', 'DateOfBirth', 'JoiningDate', 'Qualification', 'MobileNo', 'PresentAddress', 'PermanentAddress', 'Email', 'Password'];
                $csvData = [];
                foreach ($csvArray as $row) {
                    if ($i == 0) {
                        $csvData = array_keys($row);
                    }

                    $checkCSV = array_diff($columnHeaders, $csvData);
                    if (count($checkCSV) <= 0) {
                        if (filter_var($row['Email'], FILTER_VALIDATE_EMAIL)) {
                            // verify existing username
                            $this->db->table('username')->where();
                            $query = $builder->getWhere('login_credential');
                            if ($query->num_rows() > 0) {
                                $errMsg .= $row['Name'] . " - Imported Failed : Email Already Exists.<br>";
                            } else {
                                // save all employee information in the database
                                $this->employeeModel->csvImport($row, $branchID, $userRole, $designationID, $departmentID);
                                $i++;
                            }
                        } else {
                            $errMsg .= $row['Name'] . " - Imported Failed : Invalid Email.<br>";
                        }
                    } else {
                        set_alert('error', translate('invalid_csv_file'));
                    }
                }

                if ($errMsg != null) {
                    $msgRes = $i . ' Students Have Been Successfully Added. <br>';
                    $msgRes .= $errMsg;
                    echo json_encode(['status' => 'errlist', 'errMsg' => $msgRes]);
                    exit;
                }

                if ($i > 0) {
                    set_alert('success', $i . ' Students Have Been Successfully Added');
                }
            } else {
                set_alert('error', translate('invalid_csv_file'));
            }

            echo json_encode(['status' => 'success']);
        } else {
            $error = $this->validation->error_array();
            echo json_encode(['status' => 'fail', 'error' => $error]);
        }
    }

    /* sample csv downloader */
    public function csv_Sampledownloader()
    {
        helper('download');
        $data = file_get_contents('uploads/multi_employee_sample.csv');
        return $this->response->download("multi_employee_sample.csv", $data);
    }
}
