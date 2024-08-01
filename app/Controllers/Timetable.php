<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.2
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Timetable.php
 * @copyright : Reserved RamomCoder Team
 */
class Timetable extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\TimetableModel
     */
    public $timetable;

    public $applicationModel;

    public $input;

    public $db;

    public $load;

    public $validation;

    public $timetableModel;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->timetable = new \App\Models\TimetableModel();
    }

    public function index()
    {
        if (get_loggedin_id()) {
            return redirect()->to(base_url('timetable/view_classwise'));
        }
        redirect(base_url(), 'refresh');
        return null;
    }

    /* class timetable view page */
    public function viewclass()
    {
        if (!get_permission('class_timetable', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $arrayTimetable = ['branch_id' => $branchID, 'class_id' => $classID, 'section_id' => $sectionID, 'session_id' => get_session_id()];
            $this->db->order_by('time_start', 'asc');
            $this->data['timetables'] = $builder->getWhere('timetable_class', $arrayTimetable)->result();
            $this->data['class_id'] = $classID;
            $this->data['section_id'] = $sectionID;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('class') . " " . translate('schedule');
        $this->data['sub_page'] = 'timetable/viewclass';
        $this->data['main_menu'] = 'timetable';
        echo view('layout/index', $this->data);
    }

    /* class timetable view page */
    public function teacherview()
    {
        if (!get_permission('teacher_timetable', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $teacherID = $this->request->getPost('staff_id');
            $arrayTimetable = ['branch_id' => $branchID, 'teacher_id' => $teacherID, 'session_id' => get_session_id()];
            $this->db->order_by('time_start', 'asc');
            $this->data['timetables'] = $builder->getWhere('timetable_class', $arrayTimetable)->result();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('teacher') . " " . translate('schedule');
        $this->data['sub_page'] = 'timetable/teacherview';
        $this->data['main_menu'] = 'timetable';
        echo view('layout/index', $this->data);
    }

    /* class timetable information are prepared and stored in the database here */
    public function set_classwise()
    {
        if (!get_permission('class_timetable', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['day'] = $this->request->getPost('day');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['branch_id'] = $branchID;
            $this->data['exist_data'] = $this->timetableModel->get('timetable_class', ['class_id' => $this->data['class_id'], 'section_id' => $this->data['section_id'], 'day' => $this->data['day'], 'session_id' => get_session_id()], false, true);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('add') . " " . translate('schedule');
        $this->data['sub_page'] = 'timetable/set_classwise';
        $this->data['main_menu'] = 'timetable';
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js', 'vendor/moment/moment.js']];
        echo view('layout/index', $this->data);
    }

    /* class timetable updating here */
    public function update_classwise()
    {
        if (!get_permission('class_timetable', 'is_edit')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['class_id'] = $this->request->getPost('class_id');
        $this->data['section_id'] = $this->request->getPost('section_id');
        $this->data['day'] = $this->request->getPost('day');
        $timetableArray = ['branch_id' => $this->data['branch_id'], 'class_id' => $this->data['class_id'], 'section_id' => $this->data['section_id'], 'day' => $this->data['day'], 'session_id' => get_session_id()];
        $this->db->order_by('time_start', 'asc');
        $this->data['timetables'] = $builder->getWhere('timetable_class', $timetableArray)->result();
        $this->data['title'] = translate('class') . " " . translate('schedule');
        $this->data['sub_page'] = 'timetable/update_classwise';
        $this->data['main_menu'] = 'timetable';
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function class_save($mode = '')
    {
        if ($_POST !== []) {
            if (!get_permission('class_timetable', 'is_add')) {
                ajax_access_denied();
            }

            $items = $this->request->getPost('timetable');
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
            if (!empty($items)) {
                foreach ($items as $key => $value) {
                    $this->validation->setRules(['timetable[' . $key . '][time_start]' => ["label" => translate('starting_time'), "rules" => 'required']]);
                    $this->validation->setRules(['timetable[' . $key . '][time_end]' => ["label" => translate('ending_time'), "rules" => 'required']]);
                    if (!isset($value['break'])) {
                        $this->validation->setRules(['timetable[' . $key . '][subject]' => ["label" => translate('subject'), "rules" => 'trim|required']]);
                        $this->validation->setRules(['timetable[' . $key . '][teacher]' => ["label" => translate('teacher'), "rules" => 'trim|required']]);
                    }
                }
            }

            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->timetableModel->classwise_save($post, $mode);
                $message = translate('information_has_been_saved_successfully');
                $array = ['status' => 'success', 'message' => $message, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // exam timetable preview page
    public function viewexam()
    {
        if (!get_permission('exam_timetable', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['examlist'] = $this->timetableModel->getExamTimetableList($classID, $sectionID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('exam') . " " . translate('schedule');
        $this->data['sub_page'] = 'timetable/viewexam';
        $this->data['main_menu'] = 'exam_timetable';
        echo view('layout/index', $this->data);
    }

    // exam timetable information are prepared and stored in the database here
    public function set_examwise()
    {
        if (!get_permission('exam_timetable', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $examID = $this->request->getPost('exam_id');
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['exam_id'] = $examID;
            $this->data['class_id'] = $classID;
            $this->data['section_id'] = $sectionID;
            $this->data['subjectassign'] = $this->timetableModel->getSubjectExam($classID, $sectionID, $examID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('add') . " " . translate('schedule');
        $this->data['sub_page'] = 'timetable/set_examwise';
        $this->data['main_menu'] = 'exam_timetable';
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js', 'vendor/moment/moment.js']];
        echo view('layout/index', $this->data);
    }

    public function exam_create()
    {
        if (!get_permission('exam_timetable', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            // form validation rules
            $items = $this->request->getPost('timetable');
            foreach ($items as $key => $value) {
                $this->validation->setRules(['timetable[' . $key . '][date]' => ["label" => translate('date'), "rules" => 'required']]);
                $this->validation->setRules(['timetable[' . $key . '][time_start]' => ["label" => translate('starting_time'), "rules" => 'required']]);
                $this->validation->setRules(['timetable[' . $key . '][time_end]' => ["label" => translate('ending_time'), "rules" => 'required']]);
                $this->validation->setRules(['timetable[' . $key . '][hall_id]' => ["label" => translate('hall_room'), "rules" => 'required|callback_check_hallseat_capacity']]);
                foreach ($value['full_mark'] as $i => $id) {
                    $this->validation->setRules(['timetable[' . $key . '][full_mark][' . $i . ']' => ["label" => translate('full_mark'), "rules" => 'required|numeric']]);
                    $this->validation->setRules(['timetable[' . $key . '][pass_mark][' . $i . ']' => ["label" => translate('pass_mark'), "rules" => 'required|numeric']]);
                }
            }

            if ($this->validation->run() !== false) {
                $branchID = $this->applicationModel->get_branch_id();
                $examID = $this->request->getPost('exam_id');
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $timetable = $this->request->getPost('timetable');
                foreach ($timetable as $value) {
                    // distribution array
                    $distribution = [];
                    foreach ($value['full_mark'] as $id => $mark) {
                        $distribution[$id]['full_mark'] = $mark;
                    }

                    foreach ($value['pass_mark'] as $id => $mark) {
                        $distribution[$id]['pass_mark'] = $mark;
                    }

                    $arrayData = ['exam_id' => $examID, 'class_id' => $classID, 'section_id' => $sectionID, 'subject_id' => $value['subject_id'], 'time_start' => $value['time_start'], 'time_end' => $value['time_end'], 'hall_id' => $value['hall_id'], 'exam_date' => $value['date'], 'mark_distribution' => json_encode($distribution), 'branch_id' => $branchID, 'session_id' => get_session_id()];
                    $this->db->table('exam_id')->where();
                    $this->db->table('class_id')->where();
                    $this->db->table('section_id')->where();
                    $this->db->table('subject_id')->where();
                    $this->db->table('session_id')->where();
                    $q = $builder->get('timetable_exam');
                    if ($q->num_rows() > 0) {
                        $result = $q->row_array();
                        $this->db->table('id')->where();
                        $this->db->table('timetable_exam')->update();
                    } else {
                        $this->db->table('timetable_exam')->insert();
                    }
                }

                $message = translate('information_has_been_saved_successfully');
                $array = ['status' => 'success', 'message' => $message];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function exam_delete($examID, $classID, $sectionID)
    {
        if (get_permission('exam_timetable', 'is_delete')) {
            $this->db->table('exam_id')->where();
            $this->db->table('class_id')->where();
            $this->db->table('section_id')->where();
            $this->db->table('session_id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('timetable_exam')->delete();
        }
    }

    public function getExamTimetableM()
    {
        $examID = $this->request->getPost('exam_id');
        $classID = $this->request->getPost('class_id');
        $sectionID = $this->request->getPost('section_id');
        $this->data['exam_id'] = $examID;
        $this->data['class_id'] = $classID;
        $this->data['section_id'] = $sectionID;
        $this->data['timetables'] = $this->timetableModel->getExamTimetableByModal($examID, $classID, $sectionID);
        echo view('timetable/examTimetableM', $this->data);
    }

    // check exam hall room capacity
    public function check_hallseat_capacity($hallid)
    {
        if ($hallid) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $seats = $builder->getWhere('exam_hall', ['id' => $hallid])->row()->seats;
            $stuCount = $builder->getWhere('enroll', ['class_id' => $classID, 'section_id' => $sectionID, 'session_id' => get_session_id()])->num_rows();
            if ($stuCount > $seats) {
                $this->validation->setRule("check_hallseat_capacity", "The seats capacity is exceeded.");
                return false;
            }
            return true;
        }

        return null;
    }
}
