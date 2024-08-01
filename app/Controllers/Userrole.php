<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\LeaveModel;
use App\Models\FeesModel;
use App\Models\ExamModel;
use App\Models\AttendanceModel;
use App\Models\OnlineexamModel;
use App\Models\AttendancePeriodModel;
use App\Models\SubjectModel;
/**
 * @package : Ramom school management system
 * @version : 6.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Userrole.php
 * @copyright : Reserved RamomCoder Team
 */
class Userrole extends UserController

{
    /**
     * @var mixed
     */
    public $bigbluebuttonLib;
    /**
     * @var mixed
     */
    public $fees_model;
    protected $db;





    /**
     * @var App\Models\UserroleModel
     */
    public $userrole;

    /**
     * @var App\Models\LeaveModel
     */
    public $leave;

    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    /**
     * @var App\Models\ExamModel
     */
    public $exam;

    public $load;

    public $userroleModel;

    public $validation;

    public $input;

    public $applicationModel;

    public $upload;

    /**
     * @var App\Models\AttendanceModel
     */
    public $attendance;

    /**
     * @var App\Models\OnlineexamModel
     */
    public $onlineexam;

    public $onlineexamModel;

    public $feesModel;

    public $security;

    public $session;

    public $appLib;

    /**
     * @var App\Models\AttendancePeriodModel
     */
    public $attendancePeriod;

    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    public function __construct()
    {





        parent::__construct();


        $this->bigbluebuttonLib = service('bigbluebuttonLib');$this->appLib = service('appLib'); 
$this->userrole = new \App\Models\UserroleModel();
        $this->leave = new \App\Models\LeaveModel();
        $this->fees = new \App\Models\FeesModel();
        $this->exam = new \App\Models\ExamModel();
    }

    public function index()
    {
        redirect(base_url(), 'refresh');
    }

    /* getting all teachers list */
    public function teacher()
    {
        $this->data['title'] = translate('teachers');
        $this->data['getSchoolConfig'] = $this->appLib->getSchoolConfig('', 'teacher_mobile_visible,teacher_email_visible');
        $this->data['sub_page'] = 'userrole/teachers';
        $this->data['main_menu'] = 'teachers';
        echo view('layout/index', $this->data);
    }

    public function subject()
    {
        $this->data['title'] = translate('subject');
        $this->data['sub_page'] = 'userrole/subject';
        $this->data['main_menu'] = 'academic';
        echo view('layout/index', $this->data);
    }

    /*student or parent timetable preview page*/
    public function class_schedule()
    {
        $stu = $this->userroleModel->getStudentDetails();
        $arrayTimetable = ['class_id' => $stu['class_id'], 'section_id' => $stu['section_id'], 'session_id' => get_session_id()];
        $this->db->order_by('time_start', 'asc');
        $this->data['timetables'] = $builder->getWhere('timetable_class', $arrayTimetable)->result();
        $this->data['student'] = $stu;
        $this->data['title'] = translate('class') . " " . translate('schedule');
        $this->data['sub_page'] = 'userrole/class_schedule';
        $this->data['main_menu'] = 'academic';
        echo view('layout/index', $this->data);
    }

    public function leave_request()
    {
        $stu = $this->userroleModel->getStudentDetails();
        if (isset($_POST['save'])) {
            $this->validation->setRules(['leave_category' => ["label" => translate('leave_category'), "rules" => 'required|callback_leave_check']]);
            $this->validation->setRules(['daterange' => ["label" => translate('leave_date'), "rules" => 'trim|required|callback_date_check']]);
            $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_fileHandleUpload[attachment_file]']]);
            if ($this->validation->run() !== false) {
                $leaveTypeId = $this->request->getPost('leave_category');
                $branchId = $this->applicationModel->get_branch_id();
                $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
                $startDate = date("Y-m-d", strtotime($daterange[0]));
                $endDate = date("Y-m-d", strtotime($daterange[1]));
                $reason = $this->request->getPost('reason');
                $applyDate = date("Y-m-d H:i:s");
                $datetime1 = new DateTime($startDate);
                $datetime2 = new DateTime($endDate);
                $leaveDays = $datetime2->diff($datetime1)->format("%a") + 1;
                $origFileName = '';
                $encFileName = '';
                // upload attachment file
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config['upload_path'] = './uploads/attachments/leave/';
                    $config['allowed_types'] = "*";
                    $config['max_size'] = '2024';
                    $config['encrypt_name'] = true;
                    $file = $this->request->getFile('attachment_file'); $file->initialize($config);
                    $file = $this->request->getFile('attachment_file'); $file->do_upload("attachment_file");
                    $origFileName = $this->request->getFile('attachment_file');
                    $file = $origFileName; $file->data('orig_name');
                    $encFileName = $this->request->getFile('attachment_file');
                    $file = $encFileName; $file->data('file_name');
                }

                $arrayData = ['user_id' => $stu['student_id'], 'role_id' => 7, 'session_id' => get_session_id(), 'category_id' => $leaveTypeId, 'reason' => $reason, 'branch_id' => $branchId, 'start_date' => date("Y-m-d", strtotime($startDate)), 'end_date' => date("Y-m-d", strtotime($endDate)), 'leave_days' => $leaveDays, 'status' => 1, 'orig_file_name' => $origFileName, 'enc_file_name' => $encFileName, 'apply_date' => $applyDate];
                $this->db->table('leave_application')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('userrole/leave_request'));
            }
        }

        $where = ['la.user_id' => $stu['student_id'], 'la.role_id' => 7];
        $this->data['leavelist'] = $this->leaveModel->getLeaveList($where);
        $this->data['title'] = translate('leaves');
        $this->data['sub_page'] = 'userrole/leave_request';
        $this->data['main_menu'] = 'leave';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    // date check for leave request
    public function date_check($daterange)
    {
        $daterange = explode(' - ', (string) $daterange);
        $startDate = date("Y-m-d", strtotime($daterange[0]));
        $endDate = date("Y-m-d", strtotime($daterange[1]));
        $today = date('Y-m-d');
        if ($today === $startDate) {
            $this->validation->setRule('date_check', "You can not leave the current day.");
            return false;
        }

        if ($this->request->getPost('applicant_id')) {
            $applicantId = $this->request->getPost('applicant_id');
            $roleId = $this->request->getPost('user_role');
        } else {
            $applicantId = get_loggedin_user_id();
            $roleId = loggedin_role_id();
        }

        $getUserLeaves = $builder->getWhere('leave_application', ['user_id' => $applicantId, 'role_id' => $roleId])->result();
        if (!empty($getUserLeaves)) {
            foreach ($getUserLeaves as $userLeave) {
                $getDates = $this->user_leave_days($userLeave->start_date, $userLeave->end_date);
                $resultStart = in_array($startDate, $getDates, true);
                $resultEnd = in_array($endDate, $getDates, true);
                if ($resultStart || $resultEnd) {
                    $this->validation->setRule('date_check', 'Already have leave in the selected time.');
                    return false;
                }
            }
        }

        return true;
    }

    public function leave_check(string $str, string $fields, array $data): bool
    {
        $typeId = $data['type_id'] ?? null;  // Assuming type_id is passed through $data when called
        if (!empty($typeId)) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $startDate = new DateTime(date("Y-m-d", strtotime($daterange[0])));
            $endDate = new DateTime(date("Y-m-d", strtotime($daterange[1])));

            $applicantId = $this->request->getPost('applicant_id') ?: get_loggedin_user_id();
            $roleId = $this->request->getPost('user_role') ?: loggedin_role_id();

            if ($endDate > $startDate) {
                $db = db_connect(); // Get database connection
                $leaveTotal = get_type_name_by_id('leave_category', $typeId, 'days'); // Assuming this function fetches days correctly
                $totalSpent = $db->table('leave_application')
                                  ->where('user_id', $applicantId)
                                  ->where('role_id', $roleId)
                                  ->selectSum('total_days')
                                  ->get()
                                  ->getRow()
                                  ->total_days;

                $leaveDays = $endDate->diff($startDate)->format("%a") + 1;
                $leftLeave = $leaveTotal - $totalSpent;

                if ($leftLeave < $leaveDays) {
                    $this->validator->setError('daterange', sprintf('Applied for %s days, maximum allowed is %s days.', $leaveDays, $leftLeave));
                    return false;
                }

                return true;
            }
            $this->validator->setError('daterange', "Select a valid date range.");
            return false;
        }

        return true;
    }

    public function user_leave_days($startDate, $endDate)
    {
        $dates = [];
        $current = strtotime((string) $startDate);
        $endDate = strtotime((string) $endDate);
        while ($current <= $endDate) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }

    public function attachments()
    {
        $this->data['title'] = translate('attachments');
        $this->data['sub_page'] = 'userrole/attachments';
        $this->data['main_menu'] = 'attachments';
        echo view('layout/index', $this->data);
    }

    public function playVideo()
    {
        $id = $this->request->getPost('id');
        $file = get_type_name_by_id('attachments', $id, 'enc_name');
        echo '<video width="560" controls id="attachment_video">';
        echo '<source src="' . base_url('uploads/attachments/' . $file) . '" type="video/mp4">';
        echo 'Your browser does not support HTML video.';
        echo '</video>';
    }

    // file downloader
    public function download()
    {
        $encryptName = urldecode((string) $this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            $fileName = $db->table('attachments')->get('attachments')->row()->file_name;
            if (!empty($fileName)) {
                helper('download');
                return $this->response->download($fileName, file_get_contents('uploads/attachments/' . $encryptName));
            }
        }
        return null;
    }

    /* exam timetable preview page */
    public function exam_schedule()
    {
        $stu = $this->userroleModel->getStudentDetails();
        $this->data['student'] = $stu;
        $builder->select('*');
        $this->db->from('timetable_exam');
        $this->db->table('class_id')->where();
        $this->db->table('section_id')->where();
        $this->db->table('session_id')->where();
        $this->db->group_by('exam_id');
        $this->db->order_by('exam_id', 'asc');

        $results = $builder->get()->result_array();
        $this->data['exams'] = $results;
        $this->data['title'] = translate('exam') . " " . translate('schedule');
        $this->data['sub_page'] = 'userrole/exam_schedule';
        $this->data['main_menu'] = 'exam';
        echo view('layout/index', $this->data);
    }

    /* hostels user interface */
    public function hostels()
    {
        $this->data['student'] = $this->userroleModel->getStudentDetails();
        $this->data['title'] = translate('hostels');
        $this->data['sub_page'] = 'userrole/hostels';
        $this->data['main_menu'] = 'supervision';
        echo view('layout/index', $this->data);
    }

    /* route user interface */
    public function route()
    {
        $stu = $this->userroleModel->getStudentDetails();
        $this->data['route'] = $this->userroleModel->getRouteDetails($stu['route_id'], $stu['vehicle_id']);
        $this->data['title'] = translate('route_master');
        $this->data['sub_page'] = 'userrole/transport_route';
        $this->data['main_menu'] = 'supervision';
        echo view('layout/index', $this->data);
    }

    /* after login students or parents produced reports here */
    public function attendance()
    {
        $this->attendance = new \App\Models\AttendanceModel();
        if ($this->request->getPost('submit') == 'search') {
            $this->data['month'] = date('m', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['year'] = date('Y', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['days'] = cal_days_in_month(CAL_GREGORIAN, $this->data['month'], $this->data['year']);
            $this->data['student'] = $this->userroleModel->getStudentDetails();
        }

        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'userrole/attendance';
        $this->data['main_menu'] = 'attendance';
        echo view('layout/index', $this->data);
    }

    // book page
    public function book()
    {
        $this->data['booklist'] = $this->appLib->getTable('book');
        $this->data['title'] = translate('books');
        $this->data['sub_page'] = 'userrole/book';
        $this->data['main_menu'] = 'library';
        echo view('layout/index', $this->data);
    }

    public function book_request()
    {
        $stu = $this->userroleModel->getStudentDetails();
        if ($_POST !== []) {
            $this->validation->setRules(['book_id' => ["label" => translate('book_title'), "rules" => 'required|callback_validation_stock']]);
            $this->validation->setRules(['date_of_issue' => ["label" => translate('date_of_issue'), "rules" => 'trim|required']]);
            $this->validation->setRules(['date_of_expiry' => ["label" => translate('date_of_expiry'), "rules" => 'trim|required|callback_validation_date']]);
            if ($this->validation->run() !== false) {
                $arrayIssue = ['branch_id' => $stu['branch_id'], 'book_id' => $this->request->getPost('book_id'), 'user_id' => $stu['student_id'], 'role_id' => 7, 'date_of_issue' => date("Y-m-d", strtotime((string) $this->request->getPost('date_of_issue'))), 'date_of_expiry' => date("Y-m-d", strtotime((string) $this->request->getPost('date_of_expiry'))), 'issued_by' => get_loggedin_user_id(), 'status' => 0, 'session_id' => get_session_id()];
                $this->db->table('book_issues')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('userrole/book_request');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['stu'] = $stu;
        $this->data['title'] = translate('library');
        $this->data['sub_page'] = 'userrole/book_request';
        $this->data['main_menu'] = 'library';
        echo view('layout/index', $this->data);
    }

    // book date validation
    public function validation_date($date)
    {
        if ($date) {
            $date = strtotime((string) $date);
            $today = strtotime(date('Y-m-d'));
            if ($today >= $date) {
                $this->validation->setRule("validation_date", translate('today_or_the_previous_day_can_not_be_issued'));
                return false;
            }
            return true;
        }

        return null;
    }

    // validation book stock
    public function validation_stock($bookId)
    {
        $query = $db->table('book')->get('book')->row_array();
        $stock = $query['total_stock'];
        $issued = $query['issued_copies'];
        if ($stock == 0 || $issued >= $stock) {
            $this->validation->setRule("validation_stock", translate('the_book_is_not_available_in_stock'));
            return false;
        }
        return true;
    }

    public function event()
    {
        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('events');
        $this->data['sub_page'] = 'userrole/event';
        $this->data['main_menu'] = 'event';
        echo view('layout/index', $this->data);
    }

    /* invoice user interface with information are controlled here */
    public function invoice()
    {
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $stu = $this->userroleModel->getStudentDetails();
        $this->data['config'] = $this->get_payment_config();
        $this->data['getUser'] = $this->userroleModel->getUserDetails();
        $this->data['getOfflinePaymentsConfig'] = $this->userroleModel->getOfflinePaymentsConfig();
        $this->data['invoice'] = $this->feesModel->getInvoiceStatus($stu['enroll_id']);
        $this->data['basic'] = $this->feesModel->getInvoiceBasic($stu['enroll_id']);
        $this->data['title'] = translate('fees_history');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'userrole/collect';
        echo view('layout/index', $this->data);
    }

    /* invoice user interface with information are controlled here */
    public function report_card()
    {
        $this->data['stu'] = $this->userroleModel->getStudentDetails();
        $this->data['title'] = translate('exam_master');
        $this->data['main_menu'] = 'exam';
        $this->data['sub_page'] = 'userrole/report_card';
        echo view('layout/index', $this->data);
    }

    public function homework()
    {
        $stu = $this->userroleModel->getStudentDetails();
        $this->data['homeworklist'] = $this->userroleModel->getHomeworkList($stu['enroll_id']);
        $this->data['title'] = translate('homework');
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['main_menu'] = 'homework';
        $this->data['sub_page'] = 'userrole/homework';
        echo view('layout/index', $this->data);
    }

    public function getHomeworkAssignment()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $id = $this->request->getPost('id');
        $r = $this->db->table('homework_submit')->where(['homework_id' => $id, 'student_id' => get_loggedin_user_id()])->get()->getRowArray();
        $array = ['id' => $r['id'], 'message' => $r['message'], 'file_name' => $r['enc_name']];
        echo json_encode($array);
    }

    /* homework form validation rules */
    protected function homework_validation()
    {
        $this->validation->setRules(['message' => ["label" => translate('message'), "rules" => 'trim|required']]);
        $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_assignment_handle_upload']]);
    }

    // upload file form validation
    public function assignment_handle_upload()
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
        if (!empty($_POST['old_file'])) {
            return true;
        }

        $this->validation->setRule('assignment_handle_upload', "The Attachment field is required.");
        return false;
    }

    public function assignment_upload()
    {
        if ($_POST !== []) {
            $this->homework_validation();
            if ($this->validation->run() !== false) {
                $message = $this->request->getPost('message');
                $homeworkID = $this->request->getPost('homework_id');
                $assigmentID = $this->request->getPost('assigment_id');
                $arrayDB = [
                    'homework_id' => $homeworkID,
                    'student_id' => get_loggedin_user_id(),
                    'message' => $message
                ];
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config = [];
                    $config['upload_path'] = 'uploads/attachments/homework_submit/';
                    $config['encrypt_name'] = true;
                    $config['allowed_types'] = '*';
                    $file = $this->request->getFile('attachment_file');

                    if ($file->isValid() && !$file->hasMoved()) {
                        $file->move($config['upload_path']);
                        $encryptName = $this->request->getPost('old_file');
                        if (!empty($encryptName)) {
                            $fileName = $config['upload_path'] . $encryptName;
                            if (file_exists($fileName)) {
                                unlink($fileName);
                            }
                        }

                        $origName = $file->getClientName();
                        $encName = $file->getName();
                        $arrayDB['enc_name'] = $encName;
                        $arrayDB['file_name'] = $origName;
                    } else {
                        set_alert('error', strip_tags((string)$file->getErrorString()));
                    }
                }

                if (empty($assigmentID)) {
                    $this->db->table('homework_submit')->insert($arrayDB);
                } else {
                    $this->db->table('homework_submit')->where('id', $assigmentID)->update($arrayDB);
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('userrole/homework');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    public function live_class()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('live_class_rooms');
        $this->data['sub_page'] = 'userrole/live_class';
        $this->data['main_menu'] = 'live_class';
        echo view('layout/index', $this->data);
    }

    public function joinModal()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $this->data['meetingID'] = $this->request->getPost('meeting_id');
        echo view('userrole/live_classModal', $this->data, true);
    }

    public function livejoin()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $meetingID = $this->request->getGet('meeting_id', true);
        $liveID = $this->request->getGet('live_id', true);
        if (empty($meetingID) || empty($liveID)) {
            access_denied();
        }

        $getMeeting = $this->userroleModel->get('live_class', ['id' => $liveID, 'meeting_id' => $meetingID], true);
        if ($getMeeting['live_class_method'] == 1) {
            echo view('userrole/livejoin', $this->data);
        } else {
            $getStudent = $this->applicationModel->getStudentDetails(get_loggedin_user_id());
            $bbbConfig = json_decode((string) $getMeeting['bbb'], true);
            // get BBB api config
            $getConfig = $this->userroleModel->get('live_class_config', ['branch_id' => $getMeeting['branch_id']], true);
            $apiKeys = ['bbb_security_salt' => $getConfig['bbb_salt_key'], 'bbb_server_base_url' => $getConfig['bbb_server_base_url']];
            $this->bigbluebuttonLib = service('bigbluebuttonLib', $apiKeys);
            $arrayBBB = ['meeting_id' => $getMeeting['meeting_id'], 'title' => $getMeeting['title'], 'attendee_password' => $bbbConfig['attendee_password'], 'presen_name' => $getStudent['first_name'] . ' ' . $getStudent['last_name'] . ' (Roll - ' . $getStudent['roll'] . ')'];
            $response = $this->bigbluebuttonLib->joinMeeting($arrayBBB);
            redirect($response);
        }
    }

    public function live_atten()
    {
        $stuId = get_loggedin_user_id();
        $id = $this->request->getPost('live_id');
        $arrayInsert = ['live_class_id' => $id, 'student_id' => $stuId];
        $this->db->table($arrayInsert)->where();
        $query = $builder->get('live_class_reports');
        if ($query->num_rows() > 0) {
            $arrayInsert['created_at'] = date("Y-m-d H:i:s");
            $this->db->table('id')->where();
            $this->db->table('live_class_reports')->update();
        } else {
            $this->db->table('live_class_reports')->insert();
        }

        $array = ['status' => 1];
        echo json_encode($array);
    }

    /* Online exam controller */
    public function online_exam()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $this->onlineexam = new \App\Models\OnlineexamModel();
        $this->data['headerelements'] = ['js' => ['js/online-exam.js']];
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'userrole/online_exam';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
    }

    public function getExamListDT()
    {
        if ($_POST !== []) {
            $this->onlineexam = new \App\Models\OnlineexamModel();
            $postData = $this->request->getPost();
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->userroleModel->examListDT($postData, $currencySymbol);
        }
    }

    /* Online exam controller */
    public function onlineexam_take($id = '')
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $this->onlineexam = new \App\Models\OnlineexamModel();
        $this->data['headerelements'] = ['js' => ['js/online-exam.js']];
        $exam = $this->userroleModel->getExamDetails($id);
        if (empty($exam)) {
            return redirect()->to(base_url('userrole/online_exam'));
        }

        if ($exam->exam_type == 1 && $exam->payment_status == 0) {
            set_alert('error', "You have to make payment to attend this exam !");
            return redirect()->to(base_url('userrole/online_exam'));
        }

        $this->data['studentSubmitted'] = $this->onlineexamModel->getStudentSubmitted($exam->id);
        $this->data['exam'] = $exam;
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'onlineexam/take';
        $this->data['main_menu'] = 'onlineexam';
        echo view('layout/index', $this->data);
        return null;
    }

    public function ajaxQuestions()
    {
        $status = 0;
        $totalQuestions = 0;
        $message = "";
        $this->onlineexam = new \App\Models\OnlineexamModel();
        $examID = $this->request->getPost('exam_id');
        $exam = $this->userroleModel->getExamDetails($examID);
        $totalQuestions = $exam->questions_qty;
        $studentAttempt = $this->onlineexamModel->getStudentAttempt($exam->id);
        $examSubmitted = $this->onlineexamModel->getStudentSubmitted($exam->id);
        if (!empty($exam)) {
            $startTime = strtotime($exam->exam_start);
            $endTime = strtotime($exam->exam_end);
            $now = strtotime("now");
            if ($startTime <= $now && $now <= $endTime && empty($examSubmitted) && $exam->publish_status == 1) {
                if ($exam->limits_participation > $studentAttempt) {
                    $this->onlineexamModel->addStudentAttemts($exam->id);
                    $message = "";
                    $status = 1;
                } else {
                    $status = 0;
                    $message = "You already reach max exam attempt.";
                }
            } else {
                $message = "Maybe the test has expired or something wrong.";
            }
        }

        $data['exam'] = $exam;
        $data['questions'] = $this->onlineexamModel->getExamQuestions($exam->id, $exam->question_type);
        $pagContent = view('onlineexam/ajax_take', $data, true);
        echo json_encode(['status' => $status, 'total_questions' => $totalQuestions, 'message' => $message, 'page' => $pagContent]);
    }

    public function getStudent_result()
    {
        if ($_POST !== []) {
            $examID = $this->request->getPost('id');
            $this->onlineexam = new \App\Models\OnlineexamModel();
            $exam = $this->onlineexamModel->getExamDetails($examID);
            $data['exam'] = $exam;
            echo view('userrole/onlineexam_result', $data, true);
        }
    }

    public function getExamPaymentForm()
    {
        if ($_POST !== []) {
            $this->onlineexam = new \App\Models\OnlineexamModel();
            $status = 1;
            $pageData = "";
            $examID = $this->request->getPost('examID');
            $exam = $this->userroleModel->getExamDetails($examID);
            $message = "";
            if (empty($exam)) {
                $status = 0;
                $message = 'Exam not found.';
                echo json_encode(['status' => $status, 'message' => $message]);
                exit;
            }

            $data['config'] = $this->get_payment_config();
            $data['global_config'] = $this->data['global_config'];
            $data['getUser'] = $this->userroleModel->getUserDetails();
            $data['exam'] = $exam;
            if ($exam->payment_status == 0) {
                $status = 1;
                $pageData = view('userrole/getExamPaymentForm', $data, true);
            } else {
                $status = 0;
                $message = 'The fee has already been paid.';
            }

            echo json_encode(['status' => $status, 'message' => $message, 'data' => $pageData]);
        }
    }

    public function onlineexam_submit_answer()
    {
        if ($_POST !== []) {
            if (!is_student_loggedin()) {
                access_denied();
            }

            $studentID = get_loggedin_user_id();
            $onlineExamID = $this->request->getPost('online_exam_id');
            $variable = $this->request->getPost('answer');
            if (!empty($variable)) {
                $saveAnswer = [];
                foreach ($variable as $key => $value) {
                    if (isset($value[1])) {
                        $saveAnswer[] = ['student_id' => $studentID, 'online_exam_id' => $onlineExamID, 'question_id' => $key, 'answer' => $value[1], 'created_at' => date('Y-m-d H:i:s')];
                    }

                    if (isset($value[2])) {
                        $saveAnswer[] = ['student_id' => $studentID, 'online_exam_id' => $onlineExamID, 'question_id' => $key, 'answer' => json_encode($value[2]), 'created_at' => date('Y-m-d H:i:s')];
                    }

                    if (isset($value[3])) {
                        $saveAnswer[] = ['student_id' => $studentID, 'online_exam_id' => $onlineExamID, 'question_id' => $key, 'answer' => $value[3], 'created_at' => date('Y-m-d H:i:s')];
                    }

                    if (isset($value[4])) {
                        $saveAnswer[] = ['student_id' => $studentID, 'online_exam_id' => $onlineExamID, 'question_id' => $key, 'answer' => $value[4], 'created_at' => date('Y-m-d H:i:s')];
                    }
                }

                $this->db->insert_batch('online_exam_answer', $saveAnswer);
                $this->db->table('online_exam_submitted')->insert();
            }

            set_alert('success', translate('your_exam_has_been_successfully_submitted'));
            return redirect()->to(base_url('userrole/online_exam'));
        }
        return null;
    }

    public function offline_payments()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['fees_type' => ["label" => translate('fees_type'), "rules" => 'trim|required']]);
            $this->validation->setRules(['date_of_payment' => ["label" => translate('date_of_payment'), "rules" => 'trim|required']]);
            $this->validation->setRule('fee_amount', translate('amount'), ['trim', 'required', 'numeric', 'greater_than[0]', ['deposit_verify', [$this->fees_model, 'depositAmountVerify']]]);
            $this->validation->setRules(['payment_method' => ["label" => translate('payment_method'), "rules" => 'trim|required']]);
            $this->validation->setRules(['note' => ["label" => translate('note'), "rules" => 'trim|required']]);
            $this->validation->setRules(['proof_of_payment' => ["label" => translate('proof_of_payment'), "rules" => 'callback_fileHandleUpload[proof_of_payment]']]);
            if ($this->validation->run() !== false) {
                $feesType = explode("|", (string) $this->request->getPost('fees_type'));
                $dateOfPayment = $this->request->getPost('date_of_payment');
                $paymentMethod = $this->request->getPost('payment_method');
                $invoiceNo = $this->request->getPost('invoice_no');
                $encName = null;
                $origName = null;
                $config = [];
                $config['upload_path'] = 'uploads/attachments/offline_payments/';
                $config['encrypt_name'] = true;
                $config['allowed_types'] = '*';
                $file = $this->request->getFile('attachment_file'); $file->initialize($config);
                if ($this->upload->do_upload("proof_of_payment")) {
                    $origName = $this->request->getFile('attachment_file');
                    $file = $origName;
                    $file->data('orig_name');
                    $encName = $this->request->getFile('attachment_file');
                    $file = $encName;
                    $file->data('file_name');
                }

                $stu = $this->userroleModel->getStudentDetails();
                $arrayFees = ['fees_allocation_id' => $feesType[0], 'fees_type_id' => $feesType[1], 'invoice_no' => $invoiceNo, 'student_enroll_id' => $stu['enroll_id'], 'amount' => $this->request->getPost('fee_amount'), 'fine' => $this->request->getPost('fine_amount'), 'payment_method' => $paymentMethod, 'reference' => $this->request->getPost('reference'), 'note' => $this->request->getPost('note'), 'payment_date' => date('Y-m-d', strtotime((string) $dateOfPayment)), 'submit_date' => date('Y-m-d H:i:s'), 'enc_file_name' => $encName, 'orig_file_name' => $origName, 'status' => 1];
                $this->db->table('offline_fees_payments')->insert();
                set_alert('success', "We will review and notify your of your payment.");
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // get payments details modal
    public function getOfflinePaymentslDetails()
    {
        if ($_POST !== []) {
            $this->data['payments_id'] = $this->request->getPost('id');
            echo view('userrole/getOfflinePaymentslDetails', $this->data);
        }
    }

    public function getBalanceByType()
    {
        $input = $this->request->getPost('typeID');
        if (empty($input)) {
            $balance = 0;
            $fine = 0;
        } else {
            $feesType = explode("|", (string) $input);
            $fine = $this->feesModel->feeFineCalculation($feesType[0], $feesType[1]);
            $b = $this->feesModel->getBalance($feesType[0], $feesType[1]);
            $balance = $b['balance'];
            $fine = abs($fine - $b['fine']);
        }

        echo json_encode(['balance' => $balance, 'fine' => $fine]);
    }

    public function switchClass($enrollID = '')
    {
        $enrollID = $this->security->xss_clean($enrollID);
        if (!empty($enrollID) && is_student_loggedin()) {
            $getRow = $db->table('enroll')->get('enroll')->row();
            if (!empty($getRow) && $getRow->student_id == get_loggedin_user_id()) {
                $this->db->table('student_id')->where();
                $this->db->table('session_id')->where();
                $this->db->table('enroll')->update();
                $this->db->table('id')->where();
                $this->db->table('enroll')->update();
                session()->set('enrollID', $enrollID);
                if (!empty($_SERVER['HTTP_REFERER'])) {
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    redirect(base_url('dashboard'), 'refresh');
                }
            } else {
                redirect(base_url('dashboard'), 'refresh');
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function subject_wise_attendance()
    {
        $getAttendanceType = $this->appLib->getAttendanceType();
        if ($getAttendanceType != 2 && $getAttendanceType != 1) {
            access_denied();
        }

        $this->attendancePeriod = new \App\Models\AttendancePeriodModel();
        $this->subject = new \App\Models\SubjectModel();
        $this->attendance = new \App\Models\AttendanceModel();
        $getStudentDetails = $this->userroleModel->getStudentDetails();
        $branchID = $getStudentDetails['branch_id'];
        $this->data['class_id'] = $getStudentDetails['class_id'];
        $this->data['section_id'] = $getStudentDetails['section_id'];
        if ($_POST !== []) {
            $this->data['subject_id'] = $this->request->getPost('subject_id');
            $this->data['month'] = date('m', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['year'] = date('Y', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['studentDetails'] = $getStudentDetails;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('subject_wise_attendance');
        $this->data['sub_page'] = 'userrole/subject_wise_attendance';
        $this->data['main_menu'] = 'attendance';
        echo view('layout/index', $this->data);
    }
}
