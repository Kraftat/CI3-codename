<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SubjectModel;
use App\Models\AttendanceModel;
use App\Models\AttendancePeriodModel;
use App\Models\SmsModel;
use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Attendance_period.php
 * @copyright : Reserved RamomCoder Team
 */
class Attendance_period extends AdminController
{
    /**
     * @var App\Models\SubjectModel
     */
    public $subject;

    /**
     * @var App\Models\AttendanceModel
     */
    public $attendance;

    /**
     * @var App\Models\AttendancePeriodModel
     */
    public $attendancePeriod;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    public $appLib;

    public $applicationModel;

    public $validation;

    public $input;

    public $db;

    public $smsModel;

    public $load;

    public $attendanceModel;

    public $attendance_periodModel;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->subject = new \App\Models\SubjectModel();
        $this->attendance = new \App\Models\AttendanceModel();
        $this->attendancePeriod = new \App\Models\AttendancePeriodModel();
        $this->sms = new \App\Models\SmsModel();
        if (!moduleIsEnabled('attendance')) {
            access_denied();
        }

        $getAttendanceType = $this->appLib->getAttendanceType();
        if ($getAttendanceType != 2 && $getAttendanceType != 1) {
            access_denied();
        }
    }

    // student submitted attendance all data are prepared and stored in the database here
    public function index()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required']]);
            $this->validation->setRules(['subject_timetable_id' => ["label" => translate('subject'), "rules" => 'required']]);
            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $subjectTimetableID = $this->request->getPost('subject_timetable_id');
                $date = $this->request->getPost('date');
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendance_periodModel->getStudentAttendence($classID, $sectionID, $date, $subjectTimetableID, $branchID);
            }
        }

        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendanceModel->getHolidays($branchID);
        if (isset($_POST['save'])) {
            $attendance = $this->request->getPost('attendance');
            $date = $this->request->getPost('date');
            $subjectTimetableId = $this->request->getPost('subject_timetable_id');
            foreach ($attendance as $value) {
                $attStatus = $value['status'] ?? "";
                $studentID = $value['student_id'];
                $arrayAttendance = ['enroll_id' => $value['enroll_id'], 'subject_timetable_id' => $subjectTimetableId, 'status' => $attStatus, 'remark' => $value['remark'], 'date' => $date, 'branch_id' => $branchID];
                if (empty($value['attendance_id'])) {
                    $this->db->table('student_subject_attendance')->insert();
                } else {
                    $this->db->table('id')->where();
                    $this->db->table('student_subject_attendance')->update();
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
        $this->data['sub_page'] = 'attendance_period/index';
        $this->data['main_menu'] = 'attendance';
        echo view('layout/index', $this->data);
    }

    public function reportsbydate()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendanceModel->getHolidays($branchID);
        if ($_POST !== []) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['date'] = $this->request->getPost('date');
            $date = date('l', strtotime((string) $this->data['date']));
            $date = strtolower($date);
            $this->data['subjectByClassSection'] = $this->attendance_periodModel->getSubjectByClassSection($this->data['class_id'], $this->data['section_id'], $date);
            $this->data['studentlist'] = $this->attendanceModel->getStudentList($branchID, $this->data['class_id'], $this->data['section_id']);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/reportsbydate';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    public function reportbymonth()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['subject_id'] = $this->request->getPost('subject_id');
            $this->data['month'] = date('m', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['year'] = date('Y', strtotime((string) $this->request->getPost('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['studentlist'] = $this->attendanceModel->getStudentList($branchID, $this->data['class_id'], $this->data['section_id']);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/reportbymonth';
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

    public function reports()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'required']]);
            $this->validation->setRules(['section_id' => ["label" => translate('section'), "rules" => 'required']]);
            $this->validation->setRules(['subject_timetable_id' => ["label" => translate('subject'), "rules" => 'required']]);
            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $classID = $this->request->getPost('class_id');
                $sectionID = $this->request->getPost('section_id');
                $subjectTimetableID = $this->request->getPost('subject_timetable_id');
                $date = $this->request->getPost('date');
                $this->data['class_id'] = $classID;
                $this->data['section_id'] = $sectionID;
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendance_periodModel->getSubjectAttendanceReport($classID, $sectionID, $date, $subjectTimetableID, $branchID);
            }
        }

        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendanceModel->getHolidays($branchID);
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/reports';
        $this->data['main_menu'] = 'attendance_report';
        echo view('layout/index', $this->data);
    }

    // get subject list based on class section
    public function getByClassSection()
    {
        $html = '';
        $classID = $this->request->getPost('classID');
        $sectionID = $this->request->getPost('sectionID');
        $selectPOST = $this->request->getPost('selectPOST');
        $date = date('l', strtotime((string) $this->request->getPost('date')));
        $date = strtolower($date);
        if (!empty($classID)) {
            $query = $this->attendance_periodModel->getSubjectByClassSection($classID, $sectionID, $date);
            if ($query->num_rows() > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                $subjects = $query->getResultArray();
                foreach ($subjects as $row) {
                    $select = "";
                    if ($selectPOST == $row['id']) {
                        $select = "selected=selected";
                    }

                    $html .= '<option ' . $select . ' value="' . $row['id'] . '">' . $row['subjectname'] . " (" . date("g:i A", strtotime((string) $row['time_start'])) . " - " . date("g:i A", strtotime((string) $row['time_end'])) . ')</option>';
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
