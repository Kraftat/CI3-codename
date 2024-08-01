<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SmsModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Birthday.php
 * @copyright : Reserved RamomCoder Team
 */
class Birthday extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\BirthdayModel
     */
    public $birthday;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    public $applicationModel;

    public $input;

    public $load;

    public $smsModel;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->birthday = new \App\Models\BirthdayModel();
        $this->sms = new \App\Models\SmsModel();
    }

    public function index()
    {
        return redirect()->to(base_url('birthday/student'));
    }

    /* showing student list by birthday */
    public function student()
    {
        // check access permission
        if (!get_permission('student_birthday_wishes', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['students'] = $this->birthdayModel->getStudentListByBirthday($branchID, $start, $end);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student') . " " . translate('birthday') . " " . translate('list');
        $this->data['main_menu'] = 'sendsmsmail';
        $this->data['sub_page'] = 'birthday/student';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function studentWishes()
    {
        if ($_POST !== []) {
            $status = 'success';
            $message = "All birthday wishes sent via sms.";
            if (get_permission('student_birthday_wishes', 'is_view')) {
                $arrayID = $this->request->getPost('array_id');
                if (!empty($arrayID)) {
                    foreach ($arrayID as $row) {
                        $this->smsModel->sendBirthdayStudentWishes(['student_id' => $row]);
                    }
                }
            } else {
                $message = translate('access_denied');
                $status = 'error';
            }

            echo json_encode(['status' => $status, 'message' => $message]);
        }
    }

    /* showing staff list by birthday */
    public function staff()
    {
        // check access permission
        if (!get_permission('staff_birthday_wishes', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['students'] = $this->birthdayModel->getStaffListByBirthday($branchID, $start, $end);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('staff') . " " . translate('birthday') . " " . translate('list');
        $this->data['main_menu'] = 'sendsmsmail';
        $this->data['sub_page'] = 'birthday/staff';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function staffWishes()
    {
        if ($_POST !== []) {
            $status = 'success';
            $message = "All birthday wishes sent via sms.";
            if (get_permission('staff_birthday_wishes', 'is_view')) {
                $arrayID = $this->request->getPost('array_id');
                if (!empty($arrayID)) {
                    foreach ($arrayID as $row) {
                        $this->smsModel->sendBirthdayStaffWishes(['staff_id' => $row]);
                    }
                }
            } else {
                $message = translate('access_denied');
                $status = 'error';
            }

            echo json_encode(['status' => $status, 'message' => $message]);
        }
    }
}
