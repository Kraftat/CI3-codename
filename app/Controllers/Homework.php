<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SubjectModel;
use App\Models\SmsModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Homework.php
 * @copyright : Reserved RamomCoder Team
 */
class Homework extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\HomeworkModel
     */
    public $homework;

    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    public $applicationModel;

    public $input;

    public $load;

    public $validation;

    public $homeworkModel;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->homework = new \App\Models\HomeworkModel();
        $this->subject = new \App\Models\SubjectModel();
        $this->sms = new \App\Models\SmsModel();
        if (!moduleIsEnabled('homework')) {
            access_denied();
        }
    }

    public function index()
    {
        // check access permission
        if (!get_permission('homework', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $subjectID = $this->request->getPost('subject_id');
            $this->data['homeworklist'] = $this->homeworkModel->getList($classID, $sectionID, $subjectID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('homework');
        $this->data['sub_page'] = 'homework/index';
        $this->data['main_menu'] = 'homework';
        echo view('layout/index', $this->data);
    }

    public function add()
    {
        if (!get_permission('homework', 'is_add')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->homework_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $response = $this->homeworkModel->save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('homework');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('homework');
        $this->data['sub_page'] = 'homework/add';
        $this->data['main_menu'] = 'homework';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('homework', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->homework_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $response = $this->homeworkModel->save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('homework');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['homework'] = $this->appLib->getTable('homework', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('homework');
        $this->data['sub_page'] = 'homework/edit';
        $this->data['main_menu'] = 'homework';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        echo view('layout/index', $this->data);
    }

    public function evaluate($id = '')
    {
        // check access permission
        if (!get_permission('homework_evaluate', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['homeworklist'] = $this->homeworkModel->getEvaluate($id);
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('homework');
        $this->data['sub_page'] = 'homework/evaluate_list';
        $this->data['main_menu'] = 'homework';
        echo view('layout/index', $this->data);
    }

    public function evaluate_save()
    {
        // check access permission
        if (!get_permission('homework_evaluate', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $evaluate = $this->request->getPost('evaluate');
                $homeworkID = $this->request->getPost('homework_id');
                $date = date("Y-m-d", strtotime((string) $this->request->getPost('date')));
                foreach ($evaluate as $value) {
                    $attStatus = $value['status'] ?? "";
                    $arrayAttendance = ['homework_id' => $homeworkID, 'student_id' => $value['student_id'], 'status' => $attStatus, 'rank' => $value['rank'], 'remark' => $value['remark'], 'date' => $date];
                    if (empty($value['evaluation_id'])) {
                        $this->db->table('homework_evaluation')->insert();
                    } else {
                        $this->db->table('id')->where();
                        $this->db->table('homework_evaluation')->update();
                    }
                }

                $this->db->table('id')->where();
                $this->db->table('homework')->update();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'message' => translate('information_has_been_saved_successfully')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    public function evaluateModal()
    {
        $this->data['homeworkID'] = $this->request->getPost('homework_id');
        echo view('homework/evaluateModal', $this->data, true);
    }

    public function report()
    {
        // check access permission
        if (!get_permission('evaluation_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $subjectID = $this->request->getPost('subject_id');
            $this->data['homeworklist'] = $this->homeworkModel->getList($classID, $sectionID, $subjectID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('homework');
        $this->data['sub_page'] = 'homework/report';
        $this->data['main_menu'] = 'homework';
        echo view('layout/index', $this->data);
    }

    public function evaluateDetails()
    {
        $id = $this->request->getPost('homework_id');
        $this->data['homeworklist'] = $this->homeworkModel->getEvaluate($id);
        echo view('homework/evaluateDetails', $this->data, true);
    }

    public function download($id)
    {
        helper('download');
        $name = get_type_name_by_id('homework', $id, 'document');
        $ext = explode(".", (string) $name);
        $filepath = "./uploads/attachments/homework/" . $id . "." . $ext[1];
        $data = file_get_contents($filepath);
        return $this->response->download($name, $data);
    }

    public function download_submitted()
    {
        helper('download');
        $encryptName = urldecode((string) $this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            $fileName = $db->table('homework_submit')->get('homework_submit')->row()->file_name;
            if (!empty($fileName)) {
                return $this->response->download($fileName, file_get_contents('uploads/attachments/homework_submit/' . $encryptName));
            }
        }

        return null;
    }

    public function delete($id = '')
    {
        if (get_permission('homework', 'is_delete') && !empty($id)) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $name = get_type_name_by_id('homework', $id, 'document');
            $ext = explode(".", (string) $name);
            $this->db->table('id')->where();
            $this->db->table('homework')->delete();
            $filepath = "./uploads/attachments/homework/" . $id . "." . $ext[1];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    /* homework form validation rules */
    protected function homework_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
        $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'trim|required']]);
        $this->validation->setRules(['subject_id' => ["label" => translate('subject'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date_of_homework' => ["label" => translate('date_of_homework'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date_of_submission' => ["label" => translate('date_of_submission'), "rules" => 'trim|required']]);
        if (isset($_POST['published_later'])) {
            $this->validation->setRules(['schedule_date' => ["label" => translate('schedule_date'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['homework' => ["label" => translate('homework'), "rules" => 'trim|required']]);
        $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_handle_upload']]);
    }

    // upload file form validation
    public function handle_upload()
    {
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            $allowedExts = array_map('trim', array_map('strtolower', explode(',', (string) $this->data['global_config']['file_extension'])));
            $allowedSizeKB = $this->data['global_config']['file_size'];
            $allowedSize = floatval(1024 * $allowedSizeKB);
            $fileSize = $_FILES["attachment_file"]["size"];
            $fileName = $_FILES["attachment_file"]["name"];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES["attachment_file"]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setRule('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > $allowedSize) {
                    $this->validation->setRule('handle_upload', translate('file_size_shoud_be_less_than') . sprintf(' %s KB.', $allowedSizeKB));
                    return false;
                }
            } else {
                $this->validation->setRule('handle_upload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        if (isset($_POST['homework_id'])) {
            return true;
        }

        $this->validation->setRule('handle_upload', "The Attachment field is required.");
        return false;
    }
}
