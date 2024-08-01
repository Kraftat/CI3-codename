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
 * @filename : Subject.php
 * @copyright : Reserved RamomCoder Team
 */
class Subject extends AdminController
{
    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    public $load;

    public $validation;

    public $input;

    public $applicationModel;

    public $db;

    public $appLib;

    public $subjectModel;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->subject = new \App\Models\SubjectModel();
    }

    public function index()
    {
        if (!get_permission('subject', 'is_view')) {
            access_denied();
        }

        $this->data['subjectlist'] = $this->appLib->getTable('subject');
        $this->data['title'] = translate('subject');
        $this->data['sub_page'] = 'subject/index';
        $this->data['main_menu'] = 'subject';
        echo view('layout/index', $this->data);
    }

    // subject edit page
    public function edit($id = '')
    {
        if (!get_permission('subject', 'is_edit')) {
            access_denied();
        }

        $this->data['subject'] = $this->appLib->getTable('subject', ['t.id' => $id], true);
        $this->data['title'] = translate('subject');
        $this->data['sub_page'] = 'subject/edit';
        $this->data['main_menu'] = 'subject';
        echo view('layout/index', $this->data);
    }

    // moderator subject all information
    public function save()
    {
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['name' => ["label" => translate('subject_name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['subject_code' => ["label" => translate('subject_code'), "rules" => 'trim|required']]);
            $this->validation->setRules(['subject_type' => ["label" => translate('subject_type'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $arraySubject = ['name' => $this->request->getPost('name'), 'subject_code' => $this->request->getPost('subject_code'), 'subject_type' => $this->request->getPost('subject_type'), 'subject_author' => $this->request->getPost('subject_author'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $subjectID = $this->request->getPost('subject_id');
                if (empty($subjectID)) {
                    if (get_permission('subject', 'is_add')) {
                        $this->db->table('subject')->insert();
                    }

                    set_alert('success', translate('information_has_been_saved_successfully'));
                } else {
                    if (get_permission('subject', 'is_edit')) {
                        if (!is_superadmin_loggedin()) {
                            $this->db->table('branch_id')->where();
                        }

                        $this->db->table('id')->where();
                        $this->db->table('subject')->update();
                    }

                    set_alert('success', translate('information_has_been_updated_successfully'));
                }

                $url = base_url('subject/index');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function delete($id = '')
    {
        if (get_permission('subject', 'is_delete')) {
            $this->appLib->check_branch_restrictions('subject', $id);
            $this->db->table('id')->where();
            $this->db->table('subject')->delete();
            $this->db->table('subject_id')->where();
            $this->db->table('subject_assign')->delete();
        }
    }

    // add subject assign information and delete
    public function class_assign()
    {
        if (!get_permission('subject_class_assign', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['assignlist'] = $this->subjectModel->getAssignList();
        $this->data['title'] = translate('class_assign');
        $this->data['sub_page'] = 'subject/class_assign';
        $this->data['main_menu'] = 'subject';
        echo view('layout/index', $this->data);
    }

    // moderator class assign save all information
    public function class_assign_save()
    {
        if ($_POST !== [] && get_permission('subject_class_assign', 'is_add')) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required|callback_unique_subject_assign']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'trim|required']]);
            $this->validation->setRules(['subjects[]' => ["label" => translate('subject'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $branchID = $this->applicationModel->get_branch_id();
                $arraySubject = ['class_id' => $this->request->getPost('class_id'), 'section_id' => $this->request->getPost('section_id'), 'session_id' => get_session_id(), 'branch_id' => $branchID];
                // get class teacher details
                $getTeacher = $this->subjectModel->get('teacher_allocation', $arraySubject, true);
                $subjects = $this->request->getPost('subjects');
                foreach ($subjects as $subject) {
                    $arraySubject['subject_id'] = $subject;
                    $query = $builder->getWhere("subject_assign", $arraySubject);
                    if ($query->num_rows() == 0) {
                        $arraySubject['teacher_id'] = empty($getTeacher) ? 0 : $getTeacher['teacher_id'];
                        $this->db->table('subject_assign')->insert();
                    }
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('subject/class_assign');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // subject assign information edit
    public function class_assign_edit()
    {
        if ($_POST !== [] && get_permission('subject_class_assign', 'is_edit')) {
            $this->validation->setRules(['subjects[]' => ["label" => translate('subject'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $sessionID = get_session_id();
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $branchID = $this->applicationModel->get_branch_id();
                $arraySubject = ['class_id' => $classID, 'section_id' => $sectionID, 'session_id' => $sessionID, 'branch_id' => $branchID];
                // get class teacher details
                $getTeacher = $this->subjectModel->get('teacher_allocation', $arraySubject, true);
                $subjects = $this->request->getPost('subjects');
                foreach ($subjects as $subject) {
                    $arraySubject['subject_id'] = $subject;
                    $query = $builder->getWhere("subject_assign", $arraySubject);
                    if ($query->num_rows() == 0) {
                        $arraySubject['teacher_id'] = empty($getTeacher) ? 0 : $getTeacher['teacher_id'];
                        $this->db->table('subject_assign')->insert();
                    }
                }

                $this->db->where_not_in('subject_id', $subjects);
                $this->db->table('class_id')->where();
                $this->db->table('section_id')->where();
                $this->db->table('session_id')->where();
                $this->db->table('branch_id')->where();
                $this->db->table('subject_assign')->delete();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('subject/class_assign');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function class_assign_delete($classId = '', $sectionId = '')
    {
        if (!get_permission('subject_class_assign', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('class_id')->where();
        $this->db->table('section_id')->where();
        $this->db->table('session_id')->where();
        $this->db->table('subject_assign')->delete();
    }

    // validate here, if the check class assign
    public function unique_subject_assign($classId)
    {
        $where = ['class_id' => $classId, 'section_id' => $this->request->getPost('section_id'), 'session_id' => get_session_id()];
        $q = $builder->getWhere('subject_assign', $where)->num_rows();
        if ($q == 0) {
            return true;
        }
        $this->validation->setRule('unique_subject_assign', 'This class and section is already assigned.');
        return false;
    }

    // teacher assign view page
    public function teacher_assign()
    {
        if (!get_permission('subject_teacher_assign', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('subject_teacher_assign', 'is_add')) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['staff_id' => ["label" => translate('teacher'), "rules" => 'trim|required']]);
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'trim|required']]);
            $this->validation->setRules(['subject_id' => ["label" => translate('subject'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $sessionID = get_session_id();
                $branchID = $this->applicationModel->get_branch_id();
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $subjectID = $this->request->getPost('subject_id');
                $teacherID = $this->request->getPost('staff_id');
                $query = $builder->getWhere("subject_assign", ['class_id' => $classID, 'section_id' => $sectionID, 'subject_id' => $subjectID, 'session_id' => $sessionID, 'branch_id' => $branchID]);
                if ($query->num_rows() != 0) {
                    $this->db->table('id')->where();
                    $this->db->table('subject_assign')->update();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('subject/teacher_assign');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['assignlist'] = $this->subjectModel->getTeacherAssignList();
        $this->data['title'] = translate('teacher_assign');
        $this->data['sub_page'] = 'subject/teacher_assign';
        $this->data['main_menu'] = 'subject';
        echo view('layout/index', $this->data);
    }

    // teacher assign information moderator
    public function teacher_assign_delete($id = '')
    {
        if (get_permission('subject_teacher_assign', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('subject_assign')->update();
        }
    }

    // get subject list based on class section
    public function getByClassSection()
    {
        $html = '';
        $classID = $this->request->getPost('classID');
        $sectionID = $this->request->getPost('sectionID');
        if (!empty($classID)) {
            $query = $this->subjectModel->getSubjectByClassSection($classID, $sectionID);
            if ($query->num_rows() > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                $subjects = $query->getResultArray();
                foreach ($subjects as $row) {
                    $html .= '<option value="' . $row['subject_id'] . '">' . $row['subjectname'] . " (" . $row['subject_code'] . ')</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }

        echo $html;
    }
}
