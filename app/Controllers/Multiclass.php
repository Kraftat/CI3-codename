<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Multiclass.php
 * @copyright : Reserved RamomCoder Team
 */
class Multiclass extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\MulticlassModel
     */
    public $multiclass;

    public $applicationModel;

    public $input;

    public $load;

    public $validation;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->multiclass = new \App\Models\MulticlassModel();
        if (!moduleIsEnabled('multi_class')) {
            access_denied();
        }
    }

    public function index()
    {
        // check access permission
        if (!get_permission('multi_class', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['students'] = $this->multiclassModel->getStudentListByClassSection($classID, $sectionID, $branchID, false, true);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_list');
        $this->data['main_menu'] = 'admission';
        $this->data['sub_page'] = 'multiclass/index';
        $this->data['headerelements'] = ['js' => ['js/student.js']];
        echo view('layout/index', $this->data);
    }

    // student details
    public function ajaxClassList()
    {
        $id = $this->request->getPost('student_id');
        $this->data['student_id'] = $id;
        echo view('multiclass/ajax', $this->data, true);
    }

    public function saveData()
    {
        if (!get_permission('multi_class', 'is_add')) {
            ajax_access_denied();
        }

        $items = $this->request->getPost('multiclass');
        $studentId = $this->request->getPost('student_id');
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->validation->setRules(['multiclass[' . $key . '][class_id]' => ["label" => translate('class'), "rules" => sprintf('required|callback_validClasss[%s]', $key)]]);
                $this->validation->setRules(['multiclass[' . $key . '][section_id]' => ["label" => translate('section'), "rules" => 'required']]);
            }
        }

        if ($this->validation->run() == true) {
            if (!empty($items)) {
                $notDelarray = [];
                foreach ($items as $value) {
                    $arrayInsert = ['class_id' => $value['class_id'], 'section_id' => $value['section_id'], 'session_id' => get_session_id(), 'student_id' => $studentId, 'branch_id' => $branchID];
                    $this->db->table($arrayInsert)->where();
                    $q = $builder->get('enroll');
                    if ($q->num_rows() > 0) {
                        $notDelarray[] = $q->row()->id;
                    } else {
                        $this->db->table('enroll')->insert();
                        $notDelarray[] = $this->db->insert_id();
                    }
                }

                if ($notDelarray !== []) {
                    $this->db->table('session_id')->where();
                    $this->db->table('student_id')->where();
                    $this->db->table('branch_id')->where();
                    $this->db->where_not_in('id', $notDelarray);
                    $this->db->table('enroll')->delete();
                }
            }

            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success', 'url' => '', 'error' => ''];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'url' => '', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function validClasss($id, $row)
    {
        $duplicateArray = [];
        $multiClass = $this->request->getPost('multiclass');
        foreach ($multiClass as $value) {
            $duplicateArray[] = $value['class_id'] . "-" . $value['section_id'];
        }

        $duplicateRecord = 0;
        foreach (array_count_values($duplicateArray) as $c) {
            if ($c > 1) {
                $duplicateRecord = 1;
                break;
            }
        }

        if ($duplicateRecord !== 0 && count($multiClass) == $row + 1) {
            $this->validation->setRule("validClasss", "Duplicate Class Select.");
            return false;
        }

        return true;
    }
}
