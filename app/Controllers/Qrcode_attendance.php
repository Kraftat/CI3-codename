<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\AttendanceModel;
use App\Models\QrcodeAttendanceModel;
use App\Models\FrontendModel;
use App\Models\SmsModel;
/**
 * @package : Ramom School QR Attendance
 * @version : 2.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Qr_code_attendance.php
 * @copyright : Reserved RamomCoder Team
 */
class Qrcode_attendance extends AdminController

{
    public $appLib;

    protected $db;



    /**
     * @var App\Models\AttendanceModel
     */
    public $attendance;

    /**
     * @var App\Models\QrcodeAttendanceModel
     */
    public $qrcodeAttendance;

    /**
     * @var App\Models\FrontendModel
     */
    public $frontend;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    public $applicationModel;

    public $load;

    public $input;

    public $qrcode_attendanceModel;

    public $validation;

    public $attendanceModel;

    public $frontendModel;

    public function __construct()
    {



        parent::__construct();

        $this->appLib = service('appLib');if (!moduleIsEnabled('qr_code_attendance')) {
            access_denied();
        }

        $this->attendance = new \App\Models\AttendanceModel();
        $this->qrcodeAttendance = new \App\Models\QrcodeAttendanceModel();
        $this->frontend = new \App\Models\FrontendModel();
        $this->sms = new \App\Models\SmsModel();
    }

    public function index()
    {
        if (get_loggedin_id()) {
            redirect(base_url('dashboard'), 'refresh');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function take()
    {
        if (!get_permission('qr_code_employee_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['headerelements'] = ['css' => ['css/qr-code.css'], 'js' => ['vendor/qrcode/qrcode.min.js', 'js/qrcode_attendance.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['getSettings'] = $this->qrcode_attendanceModel->getSettings($branchID);
        $this->data['title'] = translate('attendance');
        $this->data['sub_page'] = 'qrcode_attendance/take';
        $this->data['main_menu'] = 'qr_attendance';
        echo view('layout/index', $this->data);
    }

    public function getUserByQrcode()
    {
        if ($_POST !== []) {
            $userData = trim(base64_decode((string) $this->request->getPost('data'), true));
            $userData = explode("-", $userData);
            if ($userData[0] !== 'e' && $userData[0] !== 's') {
                $data['status'] = 'failed';
                $data['message'] = "<i class='far fa-exclamation-triangle'></i> QR code is invalid.";
                echo json_encode($data);
                exit;
            }

            $staffID = $userData[1];
            $staffID = intval($staffID);
            if ($userData[0] === 'e') {
                if (!get_permission('qr_code_employee_attendance', 'is_add')) {
                    ajax_access_denied();
                }

                $data = [];
                $inOutTime = trim((string) $this->request->getPost('in_out_time'));
                if ($inOutTime === 'in_time') {
                    $this->db->table('in_time !=')->where();
                }

                if ($inOutTime === 'out_time') {
                    $this->db->table('out_time !=')->where();
                }

                $this->db->table(['staff_id' => $staffID, 'date' => date('Y-m-d')])->where();
                $attendance = $builder->get('staff_attendance')->row();
                if (!empty($attendance)) {
                    $data['status'] = 'failed';
                    if ($attendance->qr_code == 1) {
                        $data['message'] = "<i class='far fa-exclamation-triangle'></i> Attendance has already been taken.";
                    } else {
                        $data['message'] = "<i class='far fa-exclamation-triangle'></i> Attendance has already been taken by manually.";
                    }

                    echo json_encode($data);
                    exit;
                }

                //getting staff details
                $row = $this->qrcode_attendanceModel->getSingleStaff($staffID);
                if (empty($row)) {
                    $data['status'] = 'failed';
                    $data['message'] = "<i class='far fa-exclamation-triangle'></i> QR code is invalid / staff not found.";
                } else {
                    $data['userType'] = 'staff';
                    $data['status'] = 'successful';
                    $data['photo'] = get_image_url('staff', $row->photo);
                    $data['name'] = $row->name;
                    $data['role'] = $row->role;
                    $data['staff_id'] = $row->staff_id;
                    $data['joining_date'] = _d($row->joining_date);
                    $data['department'] = $row->department_name;
                    $data['designation'] = $row->designation_name;
                    $data['gender'] = ucfirst($row->sex);
                    $data['blood_group'] = empty($row->blood_group) ? '-' : $row->blood_group;
                    $data['email'] = $row->email;
                }

                echo json_encode($data);
            }

            if ($userData[0] === 's') {
                if (!get_permission('qr_code_student_attendance', 'is_add')) {
                    ajax_access_denied();
                }

                $enrollID = $userData[1];
                $enrollID = intval($enrollID);
                $data = [];
                $inOutTime = trim((string) $this->request->getPost('in_out_time'));
                if ($inOutTime === 'in_time') {
                    $this->db->table('in_time !=')->where();
                }

                if ($inOutTime === 'out_time') {
                    $this->db->table('out_time !=')->where();
                }

                $attendance = $db->table('student_attendance')->get('student_attendance')->row();
                if (!empty($attendance)) {
                    $data['status'] = 'failed';
                    if ($attendance->qr_code == 1) {
                        $data['message'] = "<i class='far fa-exclamation-triangle'></i> Attendance has already been taken.";
                    } else {
                        $data['message'] = "<i class='far fa-exclamation-triangle'></i> Attendance has already been taken by manually.";
                    }

                    echo json_encode($data);
                    exit;
                }

                //getting student details
                $row = $this->qrcode_attendanceModel->getStudentDetailsByEid($enrollID);
                if (empty($row)) {
                    $data['status'] = 'failed';
                    $data['message'] = "<i class='far fa-exclamation-triangle'></i> QR code is invalid / student not found.";
                } else {
                    $data['userType'] = 'student';
                    $data['status'] = 'successful';
                    $data['photo'] = get_image_url('student', $row->photo);
                    $data['full_name'] = $row->first_name . " " . $row->last_name;
                    $data['student_category'] = $row->cname;
                    $data['register_no'] = $row->register_no;
                    $data['roll'] = $row->roll;
                    $data['admission_date'] = empty($row->admission_date) ? "N/A" : _d($row->admission_date);
                    $data['birthday'] = empty($row->birthday) ? "N/A" : _d($row->birthday);
                    $data['class_name'] = $row->class_name;
                    $data['section_name'] = $row->section_name;
                    $data['email'] = $row->email;
                }

                echo json_encode($data);
            }
        }
    }

    // submitted attendance all data are prepared and stored in the database here
    public function setAttendanceByQrcode()
    {
        if ($_POST !== []) {
            $data = [];
            $userData = trim(base64_decode((string) $this->request->getPost('data'), true));
            $userData = explode("-", $userData);
            if ($userData[0] !== 'e' && $userData[0] !== 's') {
                $data['status'] = 'failed';
                $data['message'] = "<i class='far fa-exclamation-triangle'></i> QR code is invalid.";
                echo json_encode($data);
                exit;
            }

            $staffID = $userData[1];
            $staffID = intval($staffID);
            $inOutTime = trim((string) $this->request->getPost('in_out_time'));
            $attendanceRemark = $this->request->getPost('attendanceRemark');
            $table = "";
            $column = "";
            if ($userData[0] === 'e') {
                if (!get_permission('qr_code_employee_attendance', 'is_add')) {
                    ajax_access_denied();
                }

                $data['userType'] = 'staff';
                $table = "staff_attendance";
                $column = "staff_id";
                //getting student details
                $stuDetail = $this->qrcode_attendanceModel->getSingleStaff($staffID);
            } elseif ($userData[0] === 's') {
                if (!get_permission('qr_code_student_attendance', 'is_add')) {
                    access_denied();
                }

                $data['userType'] = 'student';
                $table = "student_attendance";
                $column = "enroll_id";
                //getting student details
                $stuDetail = $this->qrcode_attendanceModel->getStudentDetailsByEid($staffID);
            }

            // getting QR attendance settings
            $setting = $this->qrcode_attendanceModel->getSettings(empty($stuDetail->branch_id) ? 0 : $stuDetail->branch_id);
            if ($inOutTime === 'in_time') {
                if ($setting->auto_late_detect == 1) {
                    $attendanceStatus = strtotime($setting->staff_in_time) <= time() ? 'L' : 'P';
                } else {
                    $attendanceStatus = isset($_POST['late']) ? 'L' : 'P';
                }
            } elseif ($setting->auto_late_detect == 1) {
                $attendanceStatus = strtotime($setting->staff_out_time) >= time() ? 'HD' : '';
            } else {
                $attendanceStatus = isset($_POST['late']) ? 'HD' : '';
            }

            $attendance = $db->table($table)->get($table)->row();
            if (empty($attendance)) {
                $data['status'] = 1;
                $arrayAttendance = [$column => $staffID, 'status' => $attendanceStatus, 'qr_code' => "1", 'remark' => $attendanceRemark, 'date' => date('Y-m-d'), 'branch_id' => $stuDetail->branch_id];
                $arrayAttendance[$inOutTime] = date('H:i:s');
                $this->db->table($table)->insert();
            } else {
                $data['status'] = 1;
                $update = [];
                $update[$inOutTime] = date('H:i:s');
                if (!empty($attendanceRemark)) {
                    $update['remark'] = $attendanceRemark;
                }

                if ($attendanceStatus !== '' && $attendanceStatus !== '0') {
                    $update['status'] = $attendanceStatus;
                }

                $this->db->table('id')->update($table, $update)->where();
            }

            $data['message'] = translate('attendance_has_been_taken_successfully');
            echo json_encode($data);
        }
    }

    public function getStuListDT()
    {
        if ($_POST !== []) {
            $postData = $this->request->getPost();
            echo $this->qrcode_attendanceModel->getStuListDT($postData);
        }
    }

    public function getStaffListDT()
    {
        if ($_POST !== []) {
            $postData = $this->request->getPost();
            echo $this->qrcode_attendanceModel->getStaffListDT($postData);
        }
    }

    public function studentbydate()
    {
        if (!get_permission('qr_code_student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendanceModel->getHolidays($branchID);
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $this->data['class_id'] = $this->request->getPost('class_id');
                $this->data['section_id'] = $this->request->getPost('section_id');
                $this->data['date'] = $this->request->getPost('date');
                $this->data['attendancelist'] = $this->qrcode_attendanceModel->getDailyStudentReport($branchID, $this->data['class_id'], $this->data['section_id'], $this->data['date']);
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student') . ' ' . translate('daily_reports');
        $this->data['sub_page'] = 'qrcode_attendance/studentbydate';
        $this->data['main_menu'] = 'qr_attendance_report';
        echo view('layout/index', $this->data);
    }

    public function staffbydate()
    {
        if (!get_permission('qr_code_employee_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['getWeekends'] = $this->applicationModel->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendanceModel->getHolidays($branchID);
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_get_valid_date']]);
            if ($this->validation->run() == true) {
                $this->data['staff_role'] = $this->request->getPost('staff_role');
                $this->data['date'] = $this->request->getPost('date');
                $this->data['attendancelist'] = $this->qrcode_attendanceModel->getDailyStaffReport($branchID, $this->data['staff_role'], $this->data['date']);
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('employee') . ' ' . translate('daily_reports');
        $this->data['sub_page'] = 'qrcode_attendance/staffbydate';
        $this->data['main_menu'] = 'qr_attendance_report';
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

    public function settings()
    {
        // check access permission
        if (!get_permission('qr_code_settings', 'is_view')) {
            access_denied();
        }

        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            $branchId = $this->request->getPost('branch_id');
            return redirect()->to(base_url('qrcode_attendance/settings?branch_id=' . $branchId));
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js', 'vendor/moment/moment.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['setting'] = $this->qrcode_attendanceModel->get('qr_code_settings', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('qr_code') . " " . translate('attendance');
        $this->data['sub_page'] = 'qrcode_attendance/setting';
        $this->data['main_menu'] = 'qr_attendance';
        echo view('layout/index', $this->data);
        return null;
    }

    public function settings_save()
    {
        if (!get_permission('qr_code_settings', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['camera' => ["label" => translate('camera'), "rules" => 'trim|required']]);
            $this->validation->setRules(['staff_in_time' => ["label" => translate('staff_in_time'), "rules" => 'trim|required']]);
            $this->validation->setRules(['staff_out_time' => ["label" => translate('staff_out_time'), "rules" => 'trim|required']]);
            $this->validation->setRules(['student_in_time' => ["label" => translate('student_in_time'), "rules" => 'trim|required']]);
            $this->validation->setRules(['student_out_time' => ["label" => translate('student_out_time'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $qrSetting = ['branch_id' => $branchID, 'confirmation_popup' => $this->request->getPost('confirmation_popup'), 'auto_late_detect' => $this->request->getPost('auto_late_detect'), 'camera' => $this->request->getPost('camera'), 'staff_in_time' => date("H:i:s", strtotime((string) $this->request->getPost('staff_in_time'))), 'staff_out_time' => date("H:i:s", strtotime((string) $this->request->getPost('staff_out_time'))), 'student_in_time' => date("H:i:s", strtotime((string) $this->request->getPost('student_in_time'))), 'student_out_time' => date("H:i:s", strtotime((string) $this->request->getPost('student_out_time')))];
                // update all information in the database
                $this->db->table(['branch_id' => $branchID])->where();
                $get = $builder->get('qr_code_settings');
                if ($get->num_rows() > 0) {
                    $this->db->table('id')->where();
                    $this->db->table('qr_code_settings')->update();
                } else {
                    $this->db->table('qr_code_settings')->insert();
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }
}
