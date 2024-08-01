<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
use App\Models\SmsModel;
use App\Models\SubjectModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Onlineexam.php
 * @copyright : Reserved RamomCoder Team
 */
class Onlineexam extends AdminController

{
    public $appLib;

    protected $db;




    /**
     * @var App\Models\OnlineexamModel
     */
    public $onlineexam;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    public $load;

    public $input;

    public $onlineexamModel;

    public $validation;

    public $applicationModel;

    public $emailModel;

    public function __construct()
    {




        parent::__construct();

        $this->appLib = service('appLib'); 
$this->onlineexam = new \App\Models\OnlineexamModel();
        $this->email = new \App\Models\EmailModel();
        $this->sms = new \App\Models\SmsModel();
        $this->subject = new \App\Models\SubjectModel();
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/bootstrap-timepicker/bootstrap-timepicker.js', 'js/online-exam.js']];
        if (!moduleIsEnabled('online_exam')) {
            access_denied();
        }
    }

    /* online exam controller */
    public function index()
    {
        // check access permission
        if (!get_permission('online_exam', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['examList'] = $this->onlineexamModel->examList();
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'onlineexam/index';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    /* online exam table list controller */
    public function getExamListDT()
    {
        if ($_POST !== []) {
            $postData = $this->request->getPost();
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->onlineexamModel->examListDT($postData, $currencySymbol);
        }
    }

    /* online exam edit controller */
    public function edit($id = '')
    {
        // check access permission
        if (!get_permission('online_exam', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->exam_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $branchID = $this->applicationModel->get_branch_id();
                $this->onlineexamModel->saveExam($post, $branchID);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('onlineexam');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['onlineexam'] = $this->appLib->getTable('online_exam', ['t.id' => $id], true);
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'onlineexam/edit';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    protected function exam_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
        $this->validation->setRules(['section[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
        $this->validation->setRules(['subject[]' => ["label" => translate('subject'), "rules" => 'trim|required']]);
        $this->validation->setRules(['start_date' => ["label" => translate('start_date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['end_date' => ["label" => translate('end_date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['start_time' => ["label" => translate('start_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['end_time' => ["label" => translate('end_time'), "rules" => 'trim|required']]);
        $this->validation->setRules(['duration' => ["label" => translate('duration'), "rules" => 'trim|required|callback_validate_duration']]);
        $this->validation->setRules(['participation_limit' => ["label" => translate('limits_of_participation'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['mark_type' => ["label" => translate('mark_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['passing_mark' => ["label" => translate('passing_mark'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['instruction' => ["label" => translate('instruction'), "rules" => 'trim|required']]);
        $this->validation->setRules(['question_type' => ["label" => translate('question_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['publish_result' => ["label" => translate('result_publish'), "rules" => 'trim|required']]);
        $this->validation->setRules(['exam_type' => ["label" => translate('exam_type'), "rules" => 'trim|required']]);

        $examType = $this->request->getPost('exam_type');
        if (!empty($examType) && $examType == 1) {
            $this->validation->setRules(['exam_fee' => ["label" => translate('exam_fee'), "rules" => 'trim|required|numeric']]);
        }
    }

    public function validate_duration($value)
    {
        if (!empty($value)) {
            if ($value != "0:00") {
                if (!preg_match('/^(?(?=\d{2})(?:2[0-3]|[01]\d)|\d):[0-5]\d$/', (string) $value)) {
                    $this->validation->setRule('validate_duration', 'The %s field must be H:mm');
                    return false;
                }
            } else {
                $this->validation->setRule('validate_duration', 'The %s field can not be 0:00.');
                return false;
            }

            return true;
        }

        return true;
    }

    /* online exam save in DB controller */
    public function exam_save()
    {
        if ($_POST !== []) {
            $this->exam_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $branchID = $this->applicationModel->get_branch_id();
                //online exam save in DB
                $this->onlineexamModel->saveExam($post, $branchID);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    /* online exam delete in DB controller */
    public function delete($id = '')
    {
        if (get_permission('online_exam', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('online_exam')->delete();
            $done = $db->affectedRows();
            if ($done == true) {
                $this->db->table('onlineexam_id')->where();
                $this->db->table('questions_manage')->delete();
                $this->db->table('online_exam_id')->where();
                $this->db->table('online_exam_submitted')->delete();
                $this->db->table('online_exam_id')->where();
                $this->db->table('online_exam_attempts')->delete();
                $this->db->table('online_exam_id')->where();
                $this->db->table('online_exam_answer')->delete();
                $this->db->table('exam_id')->where();
                $this->db->table('online_exam_payment')->delete();
            }
        }
    }

    public function question_list($id = '')
    {
        if (!get_permission('online_exam', 'is_view')) {
            access_denied();
        }

        $exam = $this->onlineexamModel->getExamDetails($id, false);
        if (empty($exam)) {
            access_denied();
        }

        $this->data['exam'] = $exam;
        $this->data['title'] = translate('view') . " " . translate('question');
        $this->data['sub_page'] = 'onlineexam/question_list';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    public function remove_question($id = '')
    {
        if (get_permission('online_exam', 'is_edit')) {
            $builder->select('questions_manage.id');
            $this->db->from('questions_manage');
            $builder->join('online_exam', 'online_exam.id = questions_manage.onlineexam_id', 'inner');
            $this->db->table('questions_manage.id')->where();
            $this->db->table('online_exam.session_id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('online_exam.branch_id')->where();
            }

            $row = $builder->get();
            if ($row->num_rows() > 0) {
                $this->db->table('id')->where();
                $this->db->table('questions_manage')->delete();
            }
        }
    }

    /* Online exam question controller */
    public function question()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('question');
        $this->data['sub_page'] = 'onlineexam/question';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    public function getQuestionListDT()
    {
        if ($_POST !== []) {
            $postData = $this->request->getPost();
            echo $this->onlineexamModel->questionListDT($postData);
        }
    }

    public function question_add()
    {
        if (!get_permission('question_bank', 'is_add')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('question');
        $this->data['sub_page'] = 'onlineexam/question_add';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    public function question_edit($id = '')
    {
        if (!get_permission('question_bank', 'is_edit')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['questions'] = $this->appLib->getTable('questions', ['t.id' => $id], true);
        $this->data['title'] = translate('question_edit');
        $this->data['sub_page'] = 'onlineexam/question_edit';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    public function question_edit_save($id = '')
    {
        if (!get_permission('question_bank', 'is_edit')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $this->question_validation();
            if ($this->validation->run() == true) {
                $this->onlineexamModel->saveQuestions();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('onlineexam/question');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    protected function question_validation()
    {
        $questionType = $this->request->getPost('question_type');
        $this->validation->setRules(['question_level' => ["label" => translate('question_level'), "rules" => 'trim|required']]);
        $this->validation->setRules(['group_id' => ["label" => translate('question') . " " . translate('group'), "rules" => 'trim|required']]);
        $this->validation->setRules(['mark' => ["label" => translate('mark'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['question' => ["label" => translate('question'), "rules" => 'trim|required']]);
        if ($questionType == 1) {
            $this->validation->setRules(['option1' => ["label" => translate('option') . " " . 1, "rules" => 'trim|required']]);
            $this->validation->setRules(['option2' => ["label" => translate('option') . " " . 2, "rules" => 'trim|required']]);
            $this->validation->setRules(['answer' => ["label" => translate('answer'), "rules" => 'trim|required']]);
        }

        if ($questionType == 2) {
            $this->validation->setRules(['option1' => ["label" => translate('option') . " " . 1, "rules" => 'trim|required']]);
            $this->validation->setRules(['option2' => ["label" => translate('option') . " " . 2, "rules" => 'trim|required']]);
            $this->validation->setRules(['option3' => ["label" => translate('option') . " " . 3, "rules" => 'trim|required']]);
            $this->validation->setRules(['option4' => ["label" => translate('option') . " " . 4, "rules" => 'trim|required']]);
            $this->validation->setRules(['answer[]' => ["label" => translate('answer'), "rules" => 'trim|required']]);
        }

        if ($questionType == 3 || $questionType == 4) {
            $this->validation->setRules(['answer' => ["label" => translate('answer'), "rules" => 'trim|required']]);
        }
    }

    public function question_save()
    {
        if (!get_permission('question_bank', 'is_add')) {
            ajax_access_denied();
        }

        $this->question_validation();
        if ($this->validation->run() == true) {
            $this->onlineexamModel->saveQuestions();
            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function getQuestion()
    {
        $id = $this->request->getPost('id');
        $this->data['questions'] = $this->onlineexamModel->get('questions', ['id' => $id], true);
        echo view('onlineexam/question_view', $this->data);
    }

    public function question_delete($id = '')
    {
        if (get_permission('question_bank', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('questions')->delete();
        }
    }

    public function manage_question($examid = '')
    {
        if (!get_permission('add_questions', 'is_add')) {
            access_denied();
        }

        $this->data['questionType'] = $this->request->getPost('question_type');
        $this->data['questionLevel'] = $this->request->getPost('question_level');
        $this->data['classID'] = $this->request->getPost('class_id');
        $this->data['sectionID'] = $this->request->getPost('section_id');
        $this->data['subjectID'] = $this->request->getPost('subject_id');
        $exam = $this->onlineexamModel->get('online_exam', ['id' => $examid], true);
        $this->data['exam'] = $exam;
        $this->data['title'] = translate('manage') . " " . translate('question');
        $this->data['sub_page'] = 'onlineexam/manage_question';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    public function getQuestionDT()
    {
        if ($_POST !== []) {
            $postData = $this->request->getPost();
            echo $this->onlineexamModel->questionList($postData);
        }
    }

    public function question_assign()
    {
        if (!get_permission('add_questions', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $inputQuestions = $this->request->getPost('question');
            $examID = $this->request->getPost('exam_id');
            $negMark = $db->table('online_exam')->get('online_exam')->row()->neg_mark;
            foreach ($inputQuestions as $key => $value) {
                $this->validation->setRules([sprintf('question[%s][marks]', $key) => ["label" => translate('marks'), "rules" => 'trim|required|numeric']]);
                if ($negMark == 1) {
                    $this->validation->setRules([sprintf('question[%s][negative_marks]', $key) => ["label" => translate('negative_marks'), "rules" => 'trim|required|numeric']]);
                }
            }

            if ($this->validation->run() == true) {
                $questionsID = [];
                $cbQuestionsID = [];
                $insertData = [];
                foreach ($inputQuestions as $value) {
                    $questionsID[] = $value['id'];
                    if (isset($value['cb_id'])) {
                        $questionID = $value['cb_id'];
                        $cbQuestionsID[] = $questionID;
                        $this->db->table(['question_id' => $questionID, 'onlineexam_id' => $examID])->where();
                        $query = $builder->get('questions_manage');
                        $result = $query->num_rows();
                        if ($result > 0) {
                            $updateData = ['marks' => $value['marks'], 'neg_marks' => empty($value['negative_marks']) ? 0 : $value['negative_marks']];
                            $this->db->table('id')->where();
                            $this->db->table('questions_manage')->update();
                        } else {
                            $insertData[] = ['question_id' => $questionID, 'onlineexam_id' => $examID, 'marks' => $value['marks'], 'neg_marks' => empty($value['negative_marks']) ? 0 : $value['negative_marks']];
                        }
                    }
                }

                if ($insertData !== []) {
                    $this->db->insert_batch('questions_manage', $insertData);
                }

                $result = array_diff($questionsID, $cbQuestionsID);
                if ($result !== []) {
                    $this->db->table('onlineexam_id')->where();
                    $this->db->where_in('question_id', $result);
                    $this->db->table('questions_manage')->delete();
                }

                $array = ['status' => 'success', 'message' => translate('information_has_been_saved_successfully')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // add new question group
    public function question_group()
    {
        if (!get_permission('question_group', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['group'])) {
            if (!get_permission('question_group', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['group_name' => ["label" => translate('group') . " " . translate('name'), "rules" => 'trim|required|callback_unique_group']]);
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('group_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('question_group')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('onlineexam/question_group'));
            }
        }

        $this->data['title'] = translate('question') . " " . translate('group');
        $this->data['sub_page'] = 'onlineexam/question_group';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
        return null;
    }

    // update existing question group
    public function group_edit()
    {
        if (!get_permission('question_group', 'is_edit')) {
            ajax_access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['group_name' => ["label" => translate('group') . " " . translate('name'), "rules" => 'trim|required|callback_unique_group']]);
        if ($this->validation->run() !== false) {
            $categoryId = $this->request->getPost('group_id');
            $arrayData = ['name' => $this->request->getPost('group_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id')->where();
            $this->db->table('question_group')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    // delete question group from database
    public function group_delete($id)
    {
        if (get_permission('question_group', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('question_group')->delete();
        }
    }

    // question group details send by ajax
    public function groupDetails()
    {
        if (get_permission('question_group', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $query = $builder->get('question_group');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    /* validate here, if the check unique group name */
    public function unique_group($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $groupId = $this->request->getPost('group_id');
        if (!empty($groupId)) {
            $this->db->where_not_in('id', $groupId);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('question_group')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_group", translate('already_taken'));
        return false;
    }

    public function exam_status()
    {
        $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $arrayData['publish_status'] = $status == 'true' ? 1 : 0;
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('online_exam')->update();
        if ($status == 'true') {
            $onlineExam = $db->table('online_exam')->get('online_exam')->row();
            $percent = $onlineExam->mark_type == 1 ? "%" : "";
            $examFee = $onlineExam->exam_type == 1 ? $onlineExam->fee : "Free";
            $sectionArr = json_decode($onlineExam->section_id, true);
            //send online exam sms/email notification
            foreach ($sectionArr as $value) {
                $stuList = $this->applicationModel->getStudentListByClassSection($onlineExam->class_id, $value, $onlineExam->branch_id);
                foreach ($stuList as $row) {
                    $row['exam_title'] = $onlineExam->title;
                    $row['start_time'] = _d($onlineExam->exam_start) . " - " . date("h:i A", strtotime($onlineExam->exam_start));
                    $row['end_time'] = _d($onlineExam->exam_end) . " - " . date("h:i A", strtotime($onlineExam->exam_end));
                    $row['time_duration'] = $onlineExam->duration;
                    $row['attempt'] = $onlineExam->limits_participation;
                    $row['passing_mark'] = $onlineExam->passing_mark . $percent;
                    $row['exam_fee'] = $examFee;
                    /* $this->smsModel->sendOnlineExam($row);*/
                    $this->emailModel->onlineExamPublish($row);
                }
            }
        }

        $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
        echo json_encode($return);
    }

    public function make_result_publish($id = '')
    {
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('online_exam')->update();
    }

    // get subject list based on class
    public function getByClass()
    {
        $html = '';
        $classID = $this->request->getPost('classID');
        if (!empty($classID)) {
            $query = $this->onlineexamModel->getSubjectByClass($classID);
            if ($query->num_rows() > 0) {
                $subjects = $query->getResultArray();
                foreach ($subjects as $row) {
                    $html .= '<option value="' . $row['subject_id'] . '">' . $row['subjectname'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }

        echo $html;
    }

    public function getExamByClass()
    {
        $html = '';
        $classID = $this->request->getPost('class_id');
        if (!empty($classID)) {
            $this->db->table('class_id')->where();
            $this->db->table('session_id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
                $this->db->table('created_by')->where();
            }

            $this->db->table('publish_status')->where();
            $this->db->table('publish_result')->where();
            $query = $builder->get('online_exam');
            if ($query->num_rows() > 0) {
                $subjects = $query->getResult();
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($subjects as $row) {
                    $html .= '<option value="' . $row->id . '">' . $row->title . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }

        echo $html;
    }

    public function result()
    {
        // check access permission
        if (!get_permission('exam_result', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $examID = $this->request->getPost('exam_id');
            $exam = $this->onlineexamModel->getExamDetails($examID);
            $this->data['exam'] = $exam;
            $positionOrder = 0;
            if ($exam->position_generated == 1) {
                $positionOrder = 1;
            }

            $this->data['result'] = $this->onlineexamModel->examReport($examID, $classID, $branchID, $positionOrder);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('online_exam') . " " . translate('result');
        $this->data['main_menu'] = 'onlineexam';
        $this->data['sub_page'] = 'onlineexam/result';
        echo view('layout/index', $this->data);
    }

    public function position_generate()
    {
        // check access permission
        if (!get_permission('position_generate', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $examID = $this->request->getPost('exam_id');
            $this->data['exam'] = $this->onlineexamModel->getExamDetails($examID);
            $this->data['result'] = $this->onlineexamModel->examReport($examID, $classID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('position') . " " . translate('generate');
        $this->data['main_menu'] = 'onlineexam';
        $this->data['sub_page'] = 'onlineexam/position_generate';
        echo view('layout/index', $this->data);
    }

    public function save_position()
    {
        if ($_POST !== []) {
            if (!get_permission('position_generate', 'is_add')) {
                ajax_access_denied();
            }

            $remark = $this->request->getPost('remark');
            foreach ($remark as $key => $value) {
                $this->validation->setRules(['remark[' . $key . '][position]' => ["label" => translate('position'), "rules" => 'trim|numeric|required']]);
            }

            if ($this->validation->run() == true) {
                $examID = $this->request->getPost('exam_id');
                foreach ($remark as $value) {
                    $array = [];
                    if (!empty($value['position'])) {
                        $array['position'] = $value['position'];
                    }

                    $array['remark'] = empty($value['remark']) ? NULL : $value['remark'];
                    if (!empty($value['student_id'])) {
                        $this->db->table('online_exam_id')->where();
                        $this->db->table('student_id')->where();
                        $this->db->table('online_exam_submitted')->update();
                    }
                }

                $this->db->table('id')->where();
                $this->db->table('online_exam')->update();
                $message = translate('information_has_been_saved_successfully');
                $array = ['status' => 'success', 'message' => $message];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function getStudent_result()
    {
        if (get_permission('exam_result', 'is_view') && $_POST) {
            $examID = $this->request->getPost('examID');
            $studentID = $this->request->getPost('studentID');
            $exam = $this->onlineexamModel->getExamDetails($examID);
            $data['exam'] = $exam;
            $data['studentID'] = $studentID;
            echo view('onlineexam/student_result', $data, true);
        }
    }

    /* sample csv downloader */
    public function csv_Sampledownloader()
    {
        helper('download');
        $data = file_get_contents('uploads/import_question_sample.csv');
        return $this->response->download("import_question_sample.csv", $data);
    }

    /* csv file to import question page */
    public function question_import()
    {
        // check access permission
        if (!get_permission('question_bank', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('question') . " " . translate('import');
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'onlineexam/question_import';
        $this->data['main_menu'] = 'onlineexam';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    /* csv file to import question stored in the database here */
    public function questionCsvImport()
    {
        if ($_POST !== []) {
            if (!get_permission('question_bank', 'is_add')) {
                ajax_access_denied();
            }

            $branchID = $this->applicationModel->get_branch_id();
            // form validation rules
            if (is_superadmin_loggedin() == true) {
                $this->validation->setRules(['branch_id' => ["label" => 'Branch', "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => 'Class', "rules" => 'trim|required']]);
            $this->validation->setRules(['section_id' => ["label" => 'Section', "rules" => 'trim|required']]);
            $this->validation->setRules(['subject_id' => ["label" => 'Subject', "rules" => 'trim|required']]);
            $this->validation->setRules(['userfile' => ["label" => 'CSV File', "rules" => 'callback_csvfileHandleUpload[userfile]']]);
            if (isset($_FILES["userfile"]) && empty($_FILES['userfile']['name'])) {
                $this->validation->setRules(['userfile' => ["label" => 'CSV File', "rules" => 'required']]);
            }

            if ($this->validation->run() == true) {
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $subjectID = $this->request->getPost('subject_id');
                $questionsExam = [];
                if (isset($_FILES["userfile"]) && !empty($_FILES['userfile']['name']) && $_FILES["userfile"]["size"] > 0) {
                    $fileName = $_FILES["userfile"]["tmp_name"];
                    $file = fopen($fileName, "r");
                    $num = true;
                    $count = 0;
                    while (($column = fgetcsv($file, 10000, ",")) !== false) {
                        if ($num) {
                            $num = false;
                            continue;
                        }

                        if (!empty($column['0']) && !empty($column['1']) && !empty($column['2']) && !empty($column['3']) && !empty($column['4'])) {
                            $count++;
                            $questionLevel = trim((string) $column['2']);
                            $answer = trim((string) $column['9']);
                            if ($questionLevel === 'easy') {
                                $questionLevel = 1;
                            }

                            if ($questionLevel == 'medium') {
                                $questionLevel = 2;
                            }

                            if ($questionLevel == 'hard') {
                                $questionLevel = 3;
                            }

                            $questionType = trim((string) $column['0']);
                            if ($questionType === 'single_choice') {
                                $questionType = 1;
                            }

                            if ($questionType == 'multi_choice') {
                                $questionType = 2;
                            }

                            if ($questionType == 'true_false') {
                                $questionType = 3;
                                $answer = strtolower($answer) == true ? 1 : 2;
                            }

                            if ($questionType == 'descriptive') {
                                $questionType = 4;
                            }

                            $answer = str_replace("option_", "", $answer);
                            $questionsExam[] = ['class_id' => $classID, 'section_id' => $sectionID, 'subject_id' => $subjectID, 'branch_id' => $branchID, 'type' => $questionType, 'level' => $questionLevel, 'group_id' => trim((string) $column['1']), 'question' => trim((string) $column['3']), 'mark' => trim((string) $column['4']), 'opt_1' => trim((string) $column['5']), 'opt_2' => trim((string) $column['6']), 'opt_3' => trim((string) $column['7']), 'opt_4' => trim((string) $column['8']), 'answer' => $answer];
                        }
                    }

                    if ($questionsExam !== []) {
                        $this->db->insert_batch('questions', $questionsExam);
                    }

                    if ($count == 0) {
                        $url = base_url('onlineexam/question_import');
                        set_alert('error', "No questions found.");
                    } else {
                        $url = base_url('onlineexam/question');
                        set_alert('success', $count . ' Questions added successfully');
                    }
                } else {
                    $url = base_url('onlineexam/question_import');
                    set_alert('error', 'Question import failed.');
                }

                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function csvfileHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', 'csv')));
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $fileSize = $_FILES[$fields]["size"];
            $fileName = $_FILES[$fields]["name"];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES[$fields]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setRule('fileHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
            } else {
                $this->validation->setRule('fileHandleUpload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }
}
