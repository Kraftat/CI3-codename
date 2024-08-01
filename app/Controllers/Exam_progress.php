<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\ExamProgressModel;
use App\Models\MarksheetTemplateModel;
use App\Models\SubjectModel;
use App\Models\SmsModel;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 6.6
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Exam_progress.php
 * @copyright : Reserved RamomCoder Team
 */
class Exam_progress extends AdminController

{
    /**
     * @var mixed
     */
    public $Html2pdf;

    public $appLib;

    protected $db;



    /**
     * @var App\Models\ExamProgressModel
     */
    public $examProgress;

    /**
     * @var App\Models\MarksheetTemplateModel
     */
    public $marksheetTemplate;

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

    public $applicationModel;

    public $validation;

    public $input;

    public $load;

    public $html2pdf;

    public $emailModel;

    public function __construct()
    {



        parent::__construct();


        $this->html2pdf = service('html2pdf');$this->appLib = service('appLib'); 
$this->examProgress = new \App\Models\ExamProgressModel();
        $this->marksheetTemplate = new \App\Models\MarksheetTemplateModel();
        $this->subject = new \App\Models\SubjectModel();
        $this->sms = new \App\Models\SmsModel();
        $this->email = new \App\Models\EmailModel();
    }

    public function marksheet()
    {
        if (!get_permission('progress_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required']]);
            $this->validation->setRules(['exam_id[]' => ["label" => translate('exam'), "rules" => 'required']]);
            $this->validation->setRules(['session_id' => ["label" => translate('academic_year'), "rules" => 'required']]);
            $this->validation->setRules(['template_id' => ["label" => translate('marksheet') . " " . translate('template'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $sessionID = $this->request->getPost('session_id');
                $examID = $this->request->getPost('exam_id[]');
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $builder->select('e.roll,e.id as enrollID,s.*,c.name as category');
                $this->db->from('enroll as e');
                $builder->join('student as s', 'e.student_id = s.id', 'inner');
                $builder->join('mark as m', 'm.student_id = s.id', 'inner');
                $builder->join('student_category as c', 'c.id = s.category_id', 'left');
                $this->db->table('e.session_id')->where();
                $this->db->table('e.class_id')->where();
                $this->db->table('e.section_id')->where();
                $this->db->table('e.branch_id')->where();
                $this->db->where_in('m.exam_id', $examID);
                $this->db->group_by('m.student_id');
                $this->db->order_by('e.id', 'ASC');
                $this->data['examIDArr'] = $examID;
                $this->data['student'] = $builder->get()->result_array();
            }
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-select/dist/css/bootstrap-select.min.css'], 'js' => ['vendor/bootstrap-select/dist/js/bootstrap-select.min.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'exam_progress/marksheet';
        $this->data['main_menu'] = 'exam_reports';
        $this->data['title'] = translate('progress') . " " . translate('progress_reports');
        echo view('layout/index', $this->data);
    }

    public function reportCardPrint()
    {
        if ($_POST !== []) {
            if (!get_permission('progress_reports', 'is_view')) {
                ajax_access_denied();
            }

            $this->data['examArray'] = $this->request->getPost('exam_id[]');
            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['remarks_array'] = $this->request->getPost('remarks');
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['sessionID'] = $this->request->getPost('session_id');
            $this->data['templateID'] = $this->request->getPost('template_id');
            $this->data['branchID'] = $this->applicationModel->get_branch_id();
            echo view('exam_progress/reportCard', $this->data, true);
        }
    }

    public function reportCardPdf()
    {
        if ($_POST !== []) {
            if (!get_permission('progress_reports', 'is_view')) {
                ajax_access_denied();
            }

            $this->data['examArray'] = $this->request->getPost('exam_id[]');
            $this->data['student_array'] = $this->request->getPost('student_id');
            $this->data['remarks_array'] = $this->request->getPost('remarks');
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['sessionID'] = $this->request->getPost('session_id');
            $this->data['templateID'] = $this->request->getPost('template_id');
            $this->data['branchID'] = $this->applicationModel->get_branch_id();
            $html = view('exam_progress/reportCard_PDF', $this->data, true);
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
            $this->data['examArray'] = $this->request->getPost('exam_id[]');
            $this->data['student_array'] = [$this->request->getPost('student_id')];
            $this->data['remarks_array'] = $this->request->getPost('remarks');
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['print_date'] = $this->request->getPost('print_date');
            $this->data['sessionID'] = $this->request->getPost('session_id');
            $this->data['templateID'] = $this->request->getPost('template_id');
            $this->data['branchID'] = $this->applicationModel->get_branch_id();
            $html = view('exam_progress/reportCard_PDF', $this->data, true);
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
            $data['exam_name'] = "Progress Reports";
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

    // get exam list based on the branch
    public function getExamByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $builder->select('id,name,term_id');
            $this->db->table(['branch_id' => $branchID, 'session_id' => get_session_id()])->where();
            $this->db->order_by('id', 'asc');
            $result = $builder->get('exam')->result_array();
            if (count($result) > 0) {
                foreach ($result as $row) {
                    if ($row['term_id'] != 0) {
                        $term = $db->table('exam_term')->get('exam_term')->row()->name;
                        $name = $row['name'] . ' (' . $term . ')';
                    } else {
                        $name = $row['name'];
                    }

                    $html .= '<option value="' . $row['id'] . '">' . $name . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
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
}
