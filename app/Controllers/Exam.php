<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SubjectModel;
use App\Models\SmsModel;
use App\Models\EmailModel;
use App\Models\MarksheetTemplateModel;
use App\Models\ExamProgressModel;
/**
 * @package : Ramom school management system
 * @version : 6.6
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Exam.php
 * @copyright : Reserved RamomCoder Team
 */
class Exam extends AdminController

{
    /**
     * @var mixed
     */
    public $Html2pdf;

    public $appLib;

    protected $db;



    /**
     * @var App\Models\ExamModel
     */
    public $exam;

    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\MarksheetTemplateModel
     */
    public $marksheetTemplate;

    /**
     * @var App\Models\ExamProgressModel
     */
    public $examProgress;

    public $validation;

    public $input;

    public $examModel;

    public $load;

    public $applicationModel;

    public $smsModel;

    public $html2pdf;

    public $emailModel;

    public function __construct()
    {



        parent::__construct();


        $this->html2pdf = service('html2pdf');$this->appLib = service('appLib'); 
$this->exam = new \App\Models\ExamModel();
        $this->subject = new \App\Models\SubjectModel();
        $this->sms = new \App\Models\SmsModel();
        $this->email = new \App\Models\EmailModel();
        $this->marksheetTemplate = new \App\Models\MarksheetTemplateModel();
        $this->examProgress = new \App\Models\ExamProgressModel();
    }

    /* exam form validation rules */
    protected function exam_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['type_id' => ["label" => translate('exam_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['mark_distribution[]' => ["label" => translate('mark_distribution'), "rules" => 'trim|required']]);
    }

    public function index()
    {
        if (!get_permission('exam', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('exam', 'is_view')) {
                ajax_access_denied();
            }

            $this->exam_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->examModel->exam_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('exam');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['examlist'] = $this->examModel->getExamList();
        $this->data['title'] = translate('exam_list');
        $this->data['sub_page'] = 'exam/index';
        $this->data['main_menu'] = 'exam';
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('exam', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->exam_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->examModel->exam_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('exam');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['exam'] = $this->appLib->getTable('exam', ['t.id' => $id], true);
        $this->data['title'] = translate('exam_list');
        $this->data['sub_page'] = 'exam/edit';
        $this->data['main_menu'] = 'exam';
        echo view('layout/index', $this->data);
    }

    // exam information delete stored in the database here
    public function delete($id)
    {
        if (!get_permission('exam', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('exam')->delete();
    }

    /* term form validation rules */
    protected function term_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['term_name' => ["label" => translate('name'), "rules" => 'trim|required|callback_unique_term']]);
    }

    // exam term information are prepared and stored in the database here
    public function term()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('exam_term', 'is_add')) {
                access_denied();
            }

            $this->term_validation();
            if ($this->validation->run() !== false) {
                //save exam term information in the database file
                $this->examModel->termSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }

        $this->data['termlist'] = $this->appLib->getTable('exam_term');
        $this->data['sub_page'] = 'exam/term';
        $this->data['main_menu'] = 'exam';
        $this->data['title'] = translate('exam_term');
        echo view('layout/index', $this->data);
    }

    public function term_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('exam_term', 'is_edit')) {
                ajax_access_denied();
            }

            $this->term_validation();
            if ($this->validation->run() !== false) {
                //save exam term information in the database file
                $this->examModel->termSave($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/term');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function term_delete($id)
    {
        if (!get_permission('exam_term', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('exam_term')->delete();
    }

    /* unique valid exam term name verification is done here */
    public function unique_term($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $termId = $this->request->getPost('term_id');
        if (!empty($termId)) {
            $this->db->where_not_in('id', $termId);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('exam_term')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_term", translate('already_taken'));
        return false;
    }

    public function mark_distribution()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('mark_distribution', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                // save mark distribution information in the database file
                $arrayDistribution = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('exam_mark_distribution')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }

        $this->data['termlist'] = $this->appLib->getTable('exam_mark_distribution');
        $this->data['sub_page'] = 'exam/mark_distribution';
        $this->data['main_menu'] = 'exam';
        $this->data['title'] = translate('mark_distribution');
        echo view('layout/index', $this->data);
    }

    public function mark_distribution_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('mark_distribution', 'is_edit')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                // save mark distribution information in the database file
                $arrayDistribution = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('id')->where();
                $this->db->table('exam_mark_distribution')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/mark_distribution');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function mark_distribution_delete($id)
    {
        if (!get_permission('mark_distribution', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('exam_mark_distribution')->delete();
    }

    /* hall form validation rules */
    protected function hall_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['hall_no' => ["label" => translate('hall_no'), "rules" => 'trim|required|callback_unique_hall_no']]);
        $this->validation->setRules(['no_of_seats' => ["label" => translate('no_of_seats'), "rules" => 'trim|required|numeric']]);
    }

    /* exam hall information moderator and page */
    public function hall($action = '', $id = '')
    {
        if (isset($_POST['save'])) {
            if (!get_permission('exam_hall', 'is_add')) {
                access_denied();
            }

            $this->hall_validation();
            if ($this->validation->run() !== false) {
                //save exam hall information in the database file
                $this->examModel->hallSave($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }

        $this->data['halllist'] = $this->appLib->getTable('exam_hall');
        $this->data['title'] = translate('exam_hall');
        $this->data['sub_page'] = 'exam/hall';
        $this->data['main_menu'] = 'exam';
        echo view('layout/index', $this->data);
    }

    public function hall_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('exam_hall', 'is_edit')) {
                ajax_access_denied();
            }

            $this->hall_validation();
            if ($this->validation->run() !== false) {
                //save exam hall information in the database file
                $this->examModel->hallSave($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/hall');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function hall_delete($id)
    {
        if (!get_permission('exam_hall', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('exam_hall')->delete();
    }

    /* exam hall number exists validation */
    public function unique_hall_no($hallNo)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $termId = $this->request->getPost('term_id');
        if (!empty($termId)) {
            $this->db->where_not_in('id', $termId);
        }

        $this->db->table(['hall_no' => $hallNo, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('exam_hall')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_hall_no", translate('already_taken'));
        return false;
    }

    /* exam mark information are prepared and stored in the database here */
    public function mark_entry()
    {
        if (!get_permission('exam_mark', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $classID = $this->request->getPost('class_id');
        $sectionID = $this->request->getPost('section_id');
        $subjectID = $this->request->getPost('subject_id');
        $examID = $this->request->getPost('exam_id');
        $this->data['branch_id'] = $branchID;
        $this->data['class_id'] = $classID;
        $this->data['section_id'] = $sectionID;
        $this->data['subject_id'] = $subjectID;
        $this->data['exam_id'] = $examID;
        if (isset($_POST['search'])) {
            $this->data['timetable_detail'] = $this->examModel->getTimetableDetail($classID, $sectionID, $examID, $subjectID);
            $this->data['student'] = $this->examModel->getMarkAndStudent($branchID, $classID, $sectionID, $examID, $subjectID);
        }

        $this->data['sub_page'] = 'exam/marks_register';
        $this->data['main_menu'] = 'mark';
        $this->data['title'] = translate('mark_entries');
        echo view('layout/index', $this->data);
    }

    public function mark_save()
    {
        if ($_POST !== []) {
            if (!get_permission('exam_mark', 'is_add')) {
                ajax_access_denied();
            }

            $inputMarks = $this->request->getPost('mark');
            foreach ($inputMarks as $key => $value) {
                if (!isset($value['absent'])) {
                    foreach ($value['assessment'] as $i => $row) {
                        $field = sprintf('mark[%s][assessment][%s]', $key, $i);
                        $this->validation->setRules([$field => ["label" => translate('mark'), "rules" => sprintf('trim|numeric|callback_valid_Mark[%s]', $i)]]);
                    }
                }
            }

            if ($this->validation->run() !== false) {
                $branchID = $this->applicationModel->get_branch_id();
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $subjectID = $this->request->getPost('subject_id');
                $examID = $this->request->getPost('exam_id');
                $inputMarks = $this->request->getPost('mark');
                foreach ($inputMarks as $value) {
                    $assMark = [];
                    foreach ($value['assessment'] as $i => $row) {
                        $assMark[$i] = $row;
                    }

                    $arrayMarks = ['student_id' => $value['student_id'], 'exam_id' => $examID, 'class_id' => $classID, 'section_id' => $sectionID, 'subject_id' => $subjectID, 'branch_id' => $branchID, 'session_id' => get_session_id()];
                    $inputMark = isset($value['absent']) ? null : json_encode($assMark);
                    $absent = isset($value['absent']) ? 'on' : '';
                    $query = $builder->getWhere('mark', $arrayMarks);
                    if ($query->num_rows() > 0) {
                        if ((in_array('', $assMark, true) & !isset($value['absent'])) !== 0) {
                            $this->db->table('id')->where();
                            $this->db->table('mark')->delete();
                        } else {
                            $this->db->table('id')->where();
                            $this->db->table('mark')->update();
                        }
                    } elseif (!in_array('', $assMark, true) || isset($value['absent'])) {
                        $arrayMarks['mark'] = $inputMark;
                        $arrayMarks['absent'] = $absent;
                        $this->db->table('mark')->insert();
                        // send exam results sms
                        $this->smsModel->send_sms($arrayMarks, 5);
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

    //exam mark register validation check
    public function valid_Mark($val, $i)
    {
        $fullMark = $this->request->getPost('max_mark_' . $i);
        if ($fullMark < $val) {
            $this->validation->setRule("valid_Mark", translate("invalid_marks"));
            return false;
        }

        return true;
    }

    /* exam grade form validation rules */
    protected function grade_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['grade_point' => ["label" => translate('grade_point'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['lower_mark' => ["label" => translate('mark_from'), "rules" => 'trim|required']]);
        $this->validation->setRules(['upper_mark' => ["label" => translate('mark_upto'), "rules" => 'trim|required']]);
    }

    /* exam grade information are prepared and stored in the database here */
    public function grade($action = '')
    {
        if (!get_permission('exam_grade', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('exam_grade', 'is_view')) {
                ajax_access_denied();
            }

            $this->grade_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->examModel->gradeSave($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('exam/grade');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['title'] = translate('grades_range');
        $this->data['sub_page'] = 'exam/grade';
        $this->data['main_menu'] = 'mark';
        echo view('layout/index', $this->data);
    }

    // exam grade information updating here
    public function grade_edit($id = '')
    {
        if (!get_permission('exam_grade', 'is_edit')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $this->grade_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $this->examModel->gradeSave($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('exam/grade');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['grade'] = $this->appLib->getTable('grade', ['t.id' => $id], true);
        $this->data['sub_page'] = 'exam/grade_edit';
        $this->data['title'] = translate('grades_range');
        $this->data['main_menu'] = 'exam';
        echo view('layout/index', $this->data);
    }

    public function grade_delete($id = '')
    {
        if (get_permission('exam_grade', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('grade')->delete();
        }
    }

    public function marksheet()
    {
        if (!get_permission('report_card', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['session_id' => ["label" => translate('academic_year'), "rules" => 'trim|required']]);
            $this->validation->setRules(['exam_id' => ["label" => translate('exam'), "rules" => 'trim|required']]);
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'trim|required']]);
            $this->validation->setRules(['template_id' => ["label" => translate('marksheet') . " " . translate('template'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $sessionID = $this->request->getPost('session_id');
                $examID = $this->request->getPost('exam_id');
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $builder->select('e.roll,e.id as enrollID,s.*,c.name as category');
                $this->db->from('enroll as e');
                $builder->join('student as s', 'e.student_id = s.id', 'inner');
                $builder->join('mark as m', 's.id = m.student_id', 'inner');
                $builder->join('student_category as c', 'c.id = s.category_id', 'left');
                $builder->join('exam_rank as r', 'r.exam_id = m.exam_id and r.enroll_id = e.id', 'left');
                $this->db->table('e.session_id')->where();
                $this->db->table('m.session_id')->where();
                $this->db->table('m.class_id')->where();
                $this->db->table('m.section_id')->where();
                $this->db->table('e.branch_id')->where();
                $this->db->table('m.exam_id')->where();
                $this->db->group_by('m.student_id');
                $this->db->order_by('r.rank', 'ASC');
                $this->data['student'] = $builder->get()->result_array();
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'exam/marksheet';
        $this->data['main_menu'] = 'exam_reports';
        $this->data['title'] = translate('report_card');
        echo view('layout/index', $this->data);
    }

    public function reportCardPrint()
    {
        if ($_POST !== []) {
            if (!get_permission('report_card', 'is_view')) {
                ajax_access_denied();
            }

            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['examID'] = $this->request->getPost('exam_id');
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['sessionID'] = $this->request->getPost('session_id');
            $this->data['templateID'] = $this->request->getPost('template_id');
            $this->data['branchID'] = $this->applicationModel->get_branch_id();
            echo view('exam/reportCard', $this->data, true);
        }
    }

    public function reportCardPdf()
    {
        if ($_POST !== []) {
            if (!get_permission('report_card', 'is_view')) {
                ajax_access_denied();
            }

            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['examID'] = $this->request->getPost('exam_id');
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['sessionID'] = $this->request->getPost('session_id');
            $this->data['templateID'] = $this->request->getPost('template_id');
            $this->data['branchID'] = $this->applicationModel->get_branch_id();
            $this->data['marksheet_template'] = $this->marksheet_templateModel->getTemplate($this->data['templateID'], $this->data['branchID']);
            $html = view('exam/reportCard_PDF', $this->data, true);
            $this->Html2pdf = service('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/pdf-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang = true;
            $this->html2pdf->mpdf->baseScript = 1;
            $this->html2pdf->mpdf->autoLangToFont = true;
            return $this->html2pdf->mpdf->Output(time() . '.pdf', "I");
        }

        return null;
    }

    public function pdf_sendByemail()
    {
        if ($_POST !== []) {
            if (!get_permission('report_card', 'is_view')) {
                ajax_access_denied();
            }

            $enrollID = $this->request->getPost('enrollID');
            $this->data['student_array'] = [$this->request->getPost('student_id')];
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['examID'] = $this->request->getPost('exam_id');
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['sessionID'] = $this->request->getPost('session_id');
            $this->data['templateID'] = $this->request->getPost('template_id');
            $this->data['branchID'] = $this->applicationModel->get_branch_id();
            $this->data['marksheet_template'] = $this->marksheet_templateModel->getTemplate($this->data['templateID'], $this->data['branchID']);
            $html = view('exam/reportCard_PDF', $this->data, true);
            $this->Html2pdf = service('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/pdf-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang = true;
            $this->html2pdf->mpdf->baseScript = 1;
            $this->html2pdf->mpdf->autoLangToFont = true;
            $file = $this->html2pdf->mpdf->Output(time() . '.pdf', "S");
            $data['exam_name'] = get_type_name_by_id('exam', $this->data['examID']);
            $data['file'] = $file;
            $data['enroll_id'] = $enrollID;
            $response = $this->emailModel->emailPDFexam_marksheet($data);
            if ($response == true) {
                $array = ['status' => 'success', 'message' => translate('mail_sent_successfully')];
            } else {
                $array = ['status' => 'error', 'message' => translate('something_went_wrong')];
            }

            echo json_encode($array);
        }
    }

    /* tabulation sheet report generating here */
    public function tabulation_sheet()
    {
        if (!get_permission('tabulation_sheet', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        if (!empty($this->request->getPost('submit'))) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $examID = $this->request->getPost('exam_id');
            $sessionID = $this->request->getPost('session_id');
            $this->data['students_list'] = $this->examModel->searchExamStudentsByRank($classID, $sectionID, $sessionID, $examID, $branchID);
            $this->data['exam_details'] = $this->examModel->getExamByID($examID);
            $this->data['get_subjects'] = $this->examModel->getSubjectList($examID, $classID, $sectionID, $sessionID);
        }

        $this->data['title'] = translate('tabulation_sheet');
        $this->data['sub_page'] = 'exam/tabulation_sheet';
        $this->data['main_menu'] = 'exam_reports';
        echo view('layout/index', $this->data);
    }

    public function getDistributionByBranch()
    {
        $html = "";
        $this->request->getPost('table');
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $result = $db->table('exam_mark_distribution')->get('exam_mark_distribution')->result_array();
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            }
        }

        echo $html;
    }

    // exam publish status
    public function publish_status()
    {
        if (get_permission('exam', 'is_add')) {
            $id = $this->request->getPost('id');
            $status = $this->request->getPost('status');
            $arrayData['status'] = $status == 'true' ? 1 : 0;
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('exam')->update();
            $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
            echo json_encode($return);
        }
    }

    // exam result publish status
    public function publish_result_status()
    {
        if (get_permission('exam', 'is_add')) {
            $id = $this->request->getPost('id');
            $status = $this->request->getPost('status');
            $arrayData['publish_result'] = $status == 'true' ? 1 : 0;
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('exam')->update();
            $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
            echo json_encode($return);
        }
    }

    public function class_position()
    {
        if (!get_permission('generate_position', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        if (!empty($this->request->getPost('submit'))) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $examID = $this->request->getPost('exam_id');
            $sessionID = $this->request->getPost('session_id');
            $this->data['students_list'] = $this->examModel->searchExamStudentsByRank($classID, $sectionID, $sessionID, $examID, $branchID);
            $this->data['exam_details'] = $this->examModel->getExamByID($examID);
            $this->data['get_subjects'] = $this->examModel->getSubjectList($examID, $classID, $sectionID, $sessionID);
        }

        $this->data['title'] = translate('class_position');
        $this->data['sub_page'] = 'exam/class_position';
        $this->data['main_menu'] = 'mark';
        echo view('layout/index', $this->data);
    }

    public function save_position()
    {
        if ($_POST !== []) {
            if (!get_permission('generate_position', 'is_view')) {
                ajax_access_denied();
            }

            $rank = $this->request->getPost('rank');
            foreach ($rank as $key => $value) {
                $this->validation->setRules(['rank[' . $key . '][position]' => ["label" => translate('position'), "rules" => 'trim|numeric|required']]);
            }

            if ($this->validation->run() == true) {
                $examID = $this->request->getPost('exam_id');
                foreach ($rank as $value) {
                    $q = $db->table('exam_rank')->get('exam_rank');
                    if ($q->num_rows() == 0) {
                        $arrayRank = ['rank' => $value['position'], 'teacher_comments' => $value['teacher_comments'], 'principal_comments' => $value['principal_comments'], 'enroll_id' => $value['enroll_id'], 'exam_id' => $examID];
                        $this->db->table('exam_rank')->insert();
                    } else {
                        $this->db->table('id')->where();
                        $this->db->table('exam_rank')->update();
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
}
