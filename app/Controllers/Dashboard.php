<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\ApplicationModel; // Ensure you import all necessary models

class Dashboard extends BaseController
{
    /**
     * @var mixed
     */
    public $appLib;

    /**
     * @var \App\Models\DashboardModel
     */
    public $DashboardModel;

    /**
     * @var \App\Models\ApplicationModel
     */
    public $ApplicationModel;

    protected $dashboardModel;

    protected $applicationModel;

    public function __construct()
    {
        $this->appLib = service('appLib');// Make sure BaseController is properly set up
        $this->DashboardModel = new \App\Models\DashboardModel();
        $this->ApplicationModel = new \App\Models\ApplicationModel();
    }

    public function index()
    {
        $session = session();
        $data = []; // Use an array to gather data for the view

        // Check user roles and build data accordingly
        if (is_student_loggedin() || is_parent_loggedin()) {
            $studentID = 0;
            $name = $session->get('name');
            if (is_student_loggedin()) {
                $data['title'] = translate('welcome_to') . " " . $name;
                $studentID = get_loggedin_user_id(); // Ensure this function is adapted for CI4
            } elseif (is_parent_loggedin()) {
                $studentID = $session->get('myChildren_id');
                $data['title'] = empty($studentID) ? translate('welcome_to') . " " . $name : get_type_name_by_id('student', $studentID, 'first_name') . " - " . translate('dashboard');
            }

            $data['student_id'] = $studentID;
            $data['school_id'] = get_loggedin_branch_id();
            $data['sub_page'] = 'userrole/dashboard';
        } else {
            $schoolID = is_superadmin_loggedin() && $this->request->getGet('school_id') ? $this->request->getGet('school_id') : get_loggedin_branch_id();
            $data['title'] = get_type_name_by_id('branch', $schoolID) . " " . translate('dashboard');
            $data['school_id'] = $schoolID;
            $data['sqlMode'] = $this->applicationModel->getSQLMode();

            // Load various summaries and statistics
            if (!$data['sqlMode']) {
                $data['fees_summary'] = $this->dashboardModel->annualFeessummaryCharts($schoolID);
            } else {
                $data['fees_summary'] = ['total_fee' => 0, 'total_paid' => 0, 'total_due' => 0];
            }

            $data += [
                'student_by_class' => $this->dashboardModel->getStudentByClass($schoolID),
                'income_vs_expense' => $this->dashboardModel->getIncomeVsExpense($schoolID),
                'weekend_attendance' => $this->dashboardModel->getWeekendAttendance($schoolID),
                'monthly_admission' => $this->dashboardModel->getMonthlyAdmission($schoolID),
                'voucher' => $this->dashboardModel->getVoucher($schoolID),
                'transport_route' => $this->dashboardModel->getTransportRoute($schoolID),
                'total_student' => $this->dashboardModel->getTotalStudent($schoolID),
                'sub_page' => 'dashboard/index'
            ];
        }

        $language = $session->get('set_lang') != 'english' ? $this->dashboardModel->languageShortCodes($session->get('set_lang')) : 'en';
        $data['headerelements'] = [
            'css' => ['vendor/fullcalendar/fullcalendar.css'],
            'js' => [
                'vendor/chartjs/chart.min.js',
                'vendor/echarts/echarts.common.min.js',
                'vendor/moment/moment.js',
                'vendor/fullcalendar/fullcalendar.js',
                sprintf('vendor/fullcalendar/locale/%s.js', $language)
            ]
        ];
        $data['language'] = $language;
        $data['main_menu'] = 'dashboard';

        return view('layout/index', $data);
    }
}
