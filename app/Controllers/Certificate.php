<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmployeeModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Certificate.php
 * @copyright : Reserved RamomCoder Team
 */
class Certificate extends AdminController
{
    /**
     * @var mixed
     */
    public $Ciqrcode;

    public $ciqrcode;

    public $appLib;

    /**
     * @var App\Models\CertificateModel
     */
    public $certificate;

    public $load;

    /**
     * @var App\Models\EmployeeModel
     */
    public $employee;

    public $validation;

    public $input;

    public $certificateModel;

    public $db;

    public $applicationModel;

    public function __construct()
    {
        parent::__construct();


        $this->ciqrcode = service('ciqrcode');$this->appLib = service('appLib'); 
$this->certificate = new \App\Models\CertificateModel();
        $this->Ciqrcode = service('ciqrcode', ['cacheable' => false]);
        $this->employee = new \App\Models\EmployeeModel();
        if (!moduleIsEnabled('certificate')) {
            access_denied();
        }
    }

    /* live class form validation rules */
    protected function certificate_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['certificate_name' => ["label" => translate('certificate_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['user_type' => ["label" => translate('applicable_user'), "rules" => 'trim|required']]);
        $this->validation->setRules(['page_layout' => ["label" => translate('page_layout'), "rules" => 'trim|required']]);
        $this->validation->setRules(['top_space' => ["label" => "Top Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['bottom_space' => ["label" => "Bottom Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['right_space' => ["label" => "Right Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['left_space' => ["label" => "Left Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['photo_size' => ["label" => "Photo Size", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['content' => ["label" => translate('certificate') . " " . translate('content'), "rules" => 'trim|required']]);
    }

    public function index()
    {
        if (!get_permission('certificate_templete', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('certificate_templete', 'is_add')) {
            $roleID = $this->request->getPost('role_id');
            $this->certificate_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->certificateModel->save($this->request->getPost());
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
        $this->data['certificatelist'] = $this->certificateModel->getList();
        $this->data['title'] = translate('certificate') . " " . translate('templete');
        $this->data['sub_page'] = 'certificate/index';
        $this->data['main_menu'] = 'certificate';
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('certificate_templete', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->certificate_validation();
            if ($this->validation->run() !== false) {
                // save all information in the database file
                $this->certificateModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('certificate');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['certificate'] = $this->appLib->getTable('certificates_templete', ['t.id' => $id], true);
        $this->data['title'] = translate('certificate') . " " . translate('templete');
        $this->data['headerelements'] = ['css' => ['css/certificate.css', 'vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['js/certificate.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['sub_page'] = 'certificate/edit';
        $this->data['main_menu'] = 'certificate';
        echo view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('certificate_templete', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $getRow = $builder->get('certificates_templete')->row_array();
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
                $this->db->table('certificates_templete')->delete();
            }
        }
    }

    public function getCertificate()
    {
        if (get_permission('certificate_templete', 'is_view')) {
            $templateID = $this->request->getPost('id');
            $this->data['template'] = $this->certificateModel->get('certificates_templete', ['id' => $templateID], true);
            echo view('certificate/viewTemplete', $this->data);
        }
    }

    public function generate_student()
    {
        if (!get_permission('generate_student_certificate', 'is_view')) {
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
        $this->data['title'] = translate('student') . " " . translate('certificate') . " " . translate('generate');
        $this->data['sub_page'] = 'certificate/generate_student';
        $this->data['main_menu'] = 'certificate';
        echo view('layout/index', $this->data);
    }

    public function generate_employee()
    {
        if (!get_permission('generate_employee_certificate', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $staffRole = $this->request->getPost('staff_role');
            $this->data['stafflist'] = $this->employeeModel->getStaffList($branchID, $staffRole);
        }

        $this->data['headerelements'] = ['js' => ['js/certificate.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('employee') . " " . translate('certificate') . " " . translate('generate');
        $this->data['sub_page'] = 'certificate/generate_employee';
        $this->data['main_menu'] = 'certificate';
        echo view('layout/index', $this->data);
    }

    public function printFn($opt = '')
    {
        if ($_POST !== []) {
            if ($opt == 1) {
                if (!get_permission('generate_student_certificate', 'is_view')) {
                    ajax_access_denied();
                }
            } elseif ($opt == 2) {
                if (!get_permission('generate_employee_certificate', 'is_view')) {
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
            $this->data['template'] = $this->certificateModel->get('certificates_templete', ['id' => $templateID], true);
            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            echo view('certificate/printFn', $this->data, true);
        }
    }

    // get templete list based on the branch
    public function getTempleteByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        $userType = $this->request->getPost('user_type');
        if ($userType == 'student') {
            $userType = 1;
        }

        if ($userType == 'staff') {
            $userType = 2;
        }

        if (!empty($branchID)) {
            $builder->select('id,name');
            $this->db->table(['branch_id' => $branchID, 'user_type' => $userType])->where();
            $result = $builder->get('certificates_templete')->result_array();
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
}
