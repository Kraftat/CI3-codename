<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\StudentFieldsModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : system_student_field.php
 * @copyright : Reserved RamomCoder Team
 */
class System_student_field extends AdminController

{
    public $appLib;

    protected $db;



    /**
     * @var App\Models\StudentFieldsModel
     */
    public $studentFields;

    public $load;

    public $applicationModel;

    public $input;

    public function __construct()
    {



        parent::__construct();

        $this->appLib = service('appLib'); 
$this->studentFields = new \App\Models\StudentFieldsModel();
    }

    public function index()
    {
        // check access permission
        if (!get_permission('system_student_field', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['sub_page'] = 'system_student_field/index';
        $this->data['title'] = translate('system_student_field');
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
    }

    public function save()
    {
        if ($_POST !== []) {
            if (!get_permission('system_student_field', 'is_edit')) {
                ajax_access_denied();
            }

            $branchID = $this->applicationModel->get_branch_id();
            $systemFields = $this->request->getPost('system_fields');
            foreach ($systemFields as $key => $value) {
                $isStatus = isset($value['status']) ? 1 : 0;
                $isRequired = isset($value['required']) ? 1 : 0;
                $arrayData = ['fields_id' => $key, 'branch_id' => $branchID, 'status' => $isStatus, 'required' => $isRequired];
                $existPrivileges = $db->table('student_admission_fields')->get('student_admission_fields')->num_rows();
                if ($existPrivileges > 0) {
                    $this->db->table('student_admission_fields')->update();
                } else {
                    $this->db->table('student_admission_fields')->insert();
                }
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
        }
    }

    public function save_profile()
    {
        if ($_POST !== []) {
            if (!get_permission('system_student_field', 'is_edit')) {
                ajax_access_denied();
            }

            $branchID = $this->applicationModel->get_branch_id();
            $systemFields = $this->request->getPost('system_fields');
            foreach ($systemFields as $key => $value) {
                $isStatus = isset($value['status']) ? 1 : 0;
                $isRequired = isset($value['required']) ? 1 : 0;
                $arrayData = ['fields_id' => $key, 'branch_id' => $branchID, 'status' => $isStatus, 'required' => $isRequired];
                $existPrivileges = $db->table('student_profile_fields')->get('student_profile_fields')->num_rows();
                if ($existPrivileges > 0) {
                    $this->db->table('student_profile_fields')->update();
                } else {
                    $this->db->table('student_profile_fields')->insert();
                }
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
        }
    }
}
