<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SubjectModel;
use App\Models\SmsModel;
/**
 * @package : Ramom school management system
 * @version : 6.8
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Attendance.php
 * @copyright : Reserved RamomCoder Team
 */
class Attendance extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\AttendanceModel
     */
    public $attendance;

    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    public $applicationModel;

    public $validation;

    public $input;

    public $db;

    public $smsModel;

    public $load;

    public $attendanceModel;

    protected $getAttendanceType;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
        $this->attendance = new \App\Models\AttendanceModel();
        $this->subject = new \App\Models\SubjectModel();
        $this->sms = new \App\Models\SmsModel();
        if (!moduleIsEnabled('attendance')) {
            access_denied();
        }

        $this->getAttendanceType = $this->appLib->getAttendanceType();
    }

    public function index()
    {
        if (get_loggedin_id()) {
            redirect(base_url('dashboard'), 'refresh');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    // student submitted attendance all data are prepared and stored in the database here
    public function student_entry()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required']]);
            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $date = $this->request->getPost('date');
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendanceModel->getStudentAttendence($classID, $sectionID, $date, $branchID);
            }
        }

        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendanceModel->getHolidays($branchID);
        if (isset($_POST['save'])) {
            $attendance = $this->request->getPost('attendance');
            $date = $this->request->getPost('date');
            foreach ($attendance as $value) {
                $attStatus = $value['status'] ?? "";
                $studentID = $value['student_id'];
                $arrayAttendance = ['enroll_id' => $value['enroll_id'], 'status' => $attStatus, 'remark' => $value['remark'], 'date' => $date, 'branch_id' => $branchID];
                if (empty($value['attendance_id'])) {
                    $this->db->table('student_attendance')->insert();
                } else {
                    $this->db->table('id')->where();
                    $this->db->table('student_attendance')->update();
                }

                // send student absent then sms
                if ($attStatus == 'A') {
                    $arrayAttendance['student_id'] = $studentID;
                    $this->smsModel->send_sms($arrayAttendance, 3);
                }
            }

            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance/student_entries';
        $this->data['main_menu'] = 'attendance';
        echo view('layout/index', $this->data);
    }

    public function getWeekendsHolidays()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->request->getPost('branch_id');
            $getWeekends = $this->applicationModel->getWeekends($branchID);
            $getHolidays = $this->attendanceModel->getHolidays($branchID);
            echo json_encode(['getWeekends' => $getWeekends, 'getHolidays' => '["' . $getHolidays . '"]']);
        }
    }

    // employees submitted attendance all data are prepared and stored in the database here
    public function employees_entry()
    {
        if (!get_permission('employee_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['staff_role' => ["label" => translate('role'), "rules" => 'required']]);
            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $roleID = $this->request->getPost('staff_role');
                $date = $this->request->getPost('date');
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendanceModel->getStaffAttendence($roleID, $date, $branchID);
            }
        }

        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        if (isset($_POST['save'])) {
            $attendance = $this->request->getPost('attendance');
            $date = $this->request->getPost('date');
            foreach ($attendance as $value) {
                $attStatus = $value['status'] ?? "";
                $arrayAttendance = ['staff_id' => $value['staff_id'], 'status' => $attStatus, 'remark' => $value['remark'], 'date' => $date, 'branch_id' => $branchID];
                if (empty($value['attendance_id'])) {
                    $this->db->table('staff_attendance')->insert();
                } else {
                    $this->db->table('id')->where();
                    $this->db->table('staff_attendance')->update();
                }

                // send student absent then sms
                if ($attStatus == 'A') {
                    $this->smsModel->send_sms($arrayAttendance, 3);
                }
            }

            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('employee_attendance');
        $this->data['sub_page'] = 'attendance/employees_entries';
        $this->data['main_menu'] = 'attendance';
        echo view('layout/index', $this->data);
    }

    // exam submitted attendance all data are prepared and stored in the database here
    public function exam_entry()
    {
        if (!get_permission('exam_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['exam_id' => ["label" => translate('exam'), "rules" => 'required']]);
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required']]);
            $this->validation->setRules(['subject_id' => ["label" => translate('subject'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $examID = $this->request->getPost('exam_id');
                $subjectID = $this->request->getPost('subject_id');
                $this->data['class_id'] = $classID;
                $this->data['section_id'] = $sectionID;
                $this->data['exam_id'] = $examID;
                $this->data['subject_id'] = $subjectID;
                $this->data['attendencelist'] = $this->attendanceModel->getExamAttendence($classID, $sectionID, $examID, $subjectID, $branchID);
            }
        }

        if (isset($_POST['save'])) {
            $attendance = $this->request->getPost('attendance');
            $subjectID = $this->request->getPost('subject_id');
            $examID = $this->request->getPost('exam_id');
            foreach ($attendance as $value) {
                $attStatus = $value['status'] ?? "";
                $arrayAttendance = ['student_id' => $value['student_id'], 'status' => $attStatus, 'remark' => $value['remark'], 'exam_id' => $examID, 'subject_id' => $subjectID, 'branch_id' => $branchID];
                if (empty($value['attendance_id'])) {
                    $this->db->table('exam_attendance')->insert();
                } else {
                    $this->db->table('id')->where();
                    $this->db->table('exam_attendance')->update();
                }

                // send student absent then sms
                if ($attStatus == 'A') {
                    $this->smsModel->send_sms($arrayAttendance, 4);
                }
            }

            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('exam_attendance');
        $this->data['sub_page'] = 'attendance/exam_entries';
        $this->data['main_menu'] = 'attendance';
        echo view('layout/index', $this->data);
    }

    // student attendance reports are produced here
    public function studentwise_report()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['month'] = date('m', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['year'] = date('Y', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['studentlist'] = $this->attendanceModel->getStudentList($branchID, $this->data['class_id'], $this->data['section_id']);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance/student_report';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    public function student_classreport()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $this->data['date'] = $this->request->getPost('date');
                $this->data['attendancelist'] = $this->attendanceModel->getDailyStudentReport($branchID, $this->data['date']);
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student') . ' ' . translate('daily_reports');
        $this->data['sub_page'] = 'attendance/student_classreport';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    public function studentwise_overview()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($this->getAttendanceType != 2 && $this->getAttendanceType != 0) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['attendance_type' => ["label" => translate('attendance_type'), "rules" => 'required']]);
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required']]);
            $this->validation->setRules(['daterange' => ["label" => translate('date'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
                $start = date("Y-m-d", strtotime($daterange[0]));
                $end = date("Y-m-d", strtotime($daterange[1]));
                $this->data['class_id'] = $this->request->getPost('class_id');
                $this->data['section_id'] = $this->request->getPost('section_id');
                $this->data['start'] = $start;
                $this->data['end'] = $end;
                $this->data['studentlist'] = $this->applicationModel->getStudentListByClassSection($this->data['class_id'], $this->data['section_id'], $branchID);
            }
        }

        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance/studentwise_overview';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    /* employees attendance reports are produced here */
    public function employeewise_report()
    {
        if (!get_permission('employee_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->data['branch_id'] = $this->applicationModel->get_branch_id();
            $this->data['role_id'] = $this->request->getPost('staff_role');
            $this->data['month'] = date('m', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['year'] = date('Y', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['stafflist'] = $this->attendanceModel->getStaffList($this->data['branch_id'], $this->data['role_id']);
        }

        $this->data['title'] = translate('employee_attendance');
        $this->data['sub_page'] = 'attendance/employees_report';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    /* student exam attendance reports are produced here */
    public function examwise_report()
    {
        if (!get_permission('exam_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['exam_id'] = $this->request->getPost('exam_id');
            $this->data['subject_id'] = $this->request->getPost('subject_id');
            $this->data['branch_id'] = $this->applicationModel->get_branch_id();
            $this->data['examreport'] = $this->attendanceModel->getExamReport($this->data);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('exam_attendance');
        $this->data['sub_page'] = 'attendance/exam_report';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    public function get_valid_date($date)
    {
        $presentDate = date('Y-m-d');
        $date = date("Y-m-d", strtotime((string) $date));
        if ($date > $presentDate) {
            $this->validation->setRule("get_valid_date", "Please Enter Correct Date");
            return false;
        }

        return true;
    }

    public function check_holiday($date)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $getHolidays = $this->attendanceModel->getHolidays($branchID);
        $getHolidaysArray = explode('","', (string) $getHolidays);
        if (in_array($date, $getHolidaysArray, true)) {
            $this->validation->setRule('check_holiday', 'You have selected a holiday.');
            return false;
        }

        return true;
    }

    public function check_weekendday($date)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $getWeekendDays = $this->attendanceModel->getWeekendDaysSession($branchID);
        if (!empty($getWeekendDays)) {
            if (in_array($date, $getWeekendDays, true)) {
                $this->validation->setRule('check_weekendday', "You have selected a weekend date.");
                return false;
            }

            return true;
        }

        return true;
    }
}
