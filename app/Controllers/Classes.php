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
 * @filename : Classes.php
 * @copyright : Reserved RamomCoder Team
 */
class Classes extends AdminController

{
    public $appLib;

    protected $db;

    /**
     * @var App\Models\ClassesModel
     */
    public $classes;

    public $validation;

    public $input;

    public $applicationModel;

    public $load;

    public $classesModel;

    public function __construct()
    {

        parent::__construct();

        $this->appLib = service('appLib'); 
$this->classes = new \App\Models\ClassesModel();
    }

    /* class form validation rules */
    protected function class_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['name_numeric' => ["label" => translate('name_numeric'), "rules" => 'trim|numeric']]);
        $this->validation->setRules(['sections[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
    }

    public function index()
    {
        if (!get_permission('classes', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('classes', 'is_add')) {
            $this->class_validation();
            if ($this->validation->run() !== false) {
                $arrayClass = ['name' => $this->request->getPost('name'), 'name_numeric' => $this->request->getPost('name_numeric'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('class')->insert();
                $classId = $this->db->insert_id();
                $sections = $this->request->getPost('sections');
                foreach ($sections as $section) {
                    $arrayData = ['class_id' => $classId, 'section_id' => $section];
                    $query = $builder->getWhere("sections_allocation", $arrayData);
                    if ($query->num_rows() == 0) {
                        $this->db->table('sections_allocation')->insert();
                    }
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('classes');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['classlist'] = $this->appLib->getTable('class');
        $this->data['query_classes'] = $builder->get('class');
        $this->data['title'] = translate('control_classes');
        $this->data['sub_page'] = 'classes/index';
        $this->data['main_menu'] = 'classes';
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('classes', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->class_validation();
            if ($this->validation->run() !== false) {
                $id = $this->request->getPost('class_id');
                $arrayClass = ['name' => $this->request->getPost('name'), 'name_numeric' => $this->request->getPost('name_numeric'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('id')->where();
                $this->db->table('class')->update();
                $sections = $this->request->getPost('sections');
                foreach ($sections as $section) {
                    $query = $builder->getWhere("sections_allocation", ['class_id' => $id, 'section_id' => $section]);
                    if ($query->num_rows() == 0) {
                        $this->db->table('sections_allocation')->insert();
                    }
                }

                $this->db->where_not_in('section_id', $sections);
                $this->db->table('class_id')->where();
                $this->db->table('sections_allocation')->delete();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('classes');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['class'] = $this->appLib->getTable('class', ['t.id' => $id], true);
        $this->data['title'] = translate('control_classes');
        $this->data['sub_page'] = 'classes/edit';
        $this->data['main_menu'] = 'classes';
        echo view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('classes', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('class')->delete();
            if ($db->affectedRows() > 0) {
                $this->db->table('class_id')->where();
                $this->db->table('sections_allocation')->delete();
            }
        }
    }

    // class teacher allocation
    public function teacher_allocation()
    {
        if (!get_permission('assign_class_teacher', 'is_view')) {
            access_denied();
        }

        $branchId = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchId;
        $this->data['query'] = $this->classesModel->getTeacherAllocation($branchId);
        $this->data['title'] = translate('assign_class_teacher');
        $this->data['sub_page'] = 'classes/teacher_allocation';
        $this->data['main_menu'] = 'classes';
        echo view('layout/index', $this->data);
    }

    public function getAllocationTeacher()
    {
        if (get_permission('assign_class_teacher', 'is_edit')) {
            $allocationId = $this->request->getPost('id');
            $this->data['data'] = $this->appLib->get_table('teacher_allocation', $allocationId, true);
            echo view('classes/tallocation_modalEdit', $this->data);
        }
    }

    public function teacher_allocation_save()
    {
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required|callback_unique_sectionID']]);
            $this->validation->setRules(['staff_id' => ["label" => translate('teacher'), "rules" => 'required|callback_unique_teacherID']]);
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->classesModel->teacherAllocationSave($post);
                $url = base_url('classes/teacher_allocation');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function teacher_allocation_delete($id = '')
    {
        if (get_permission('assign_class_teacher', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('teacher_allocation')->delete();
        }
    }

    // validate here, if the check teacher allocated for this class
    public function unique_teacherID($teacherId)
    {
        if (!empty($teacherId)) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $allocationID = $this->request->getPost('allocation_id');
            if (!empty($allocationID)) {
                $this->db->where_not_in('id', $allocationID);
            }

            $this->db->table('teacher_id')->where();
            $this->db->table('class_id')->where();
            $this->db->table('section_id')->where();
            $query = $builder->get('teacher_allocation');
            if ($query->num_rows() > 0) {
                $this->validation->setRule("unique_teacherID", translate('class_teachers_are_already_allocated_for_this_class'));
                return false;
            }

            return true;
        }

        return null;
    }

    // validate here, if the check teacher allocated for this class
    public function unique_sectionID($sectionID)
    {
        if (!empty($sectionID)) {
            $classID = $this->request->getPost('class_id');
            $allocationID = $this->request->getPost('allocation_id');
            if (!empty($allocationID)) {
                $this->db->where_not_in('id', $allocationID);
            }

            $this->db->table('class_id')->where();
            $this->db->table('section_id')->where();
            $query = $builder->get('teacher_allocation');
            if ($query->num_rows() > 0) {
                $this->validation->setRule("unique_sectionID", translate('this_class_teacher_already_assigned'));
                return false;
            }

            return true;
        }

        return null;
    }
}
