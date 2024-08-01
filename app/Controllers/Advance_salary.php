<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\AdvancesalaryModel;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Advance_salary.php
 * @copyright : Reserved RamomCoder Team
 */
class Advance_salary extends AdminController

{
    protected $db;




    /**
     * @var App\Models\AdvancesalaryModel
     */
    public $advancesalary;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $input;

    public $emailModel;

    public $applicationModel;

    public $load;

    public $validation;

    public $appLib;

    public $uri;

    public $advancesalaryModel;

    public function __construct()
    {




        parent::__construct();

        $this->appLib = service('appLib');$this->advancesalary = new \App\Models\AdvancesalaryModel();
        $this->email = new \App\Models\EmailModel();
    }

    public function index()
    {
        if (!get_permission('advance_salary_manage', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['update'])) {
            if (!get_permission('advance_salary_manage', 'is_add')) {
                access_denied();
            }

            $arrayAdvance = ['issued_by' => get_loggedin_user_id(), 'paid_date' => date("Y-m-d H:i:s"), 'status' => $this->request->getPost('status'), 'comments' => $this->request->getPost('comments')];
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $this->db->table('advance_salary')->update();
            // getting information for send email alert
            $getApplication = $db->table('advance_salary')->get('advance_salary')->row();
            $getStaff = $db->table('staff')->get('staff')->row();
            $arrayAdvance['branch_id'] = $getStaff->branch_id;
            $arrayAdvance['staff_name'] = $getStaff->name;
            $arrayAdvance['email'] = $getStaff->email;
            $arrayAdvance['amount'] = $getApplication->amount;
            $arrayAdvance['deduct_motnh'] = $getApplication->year . '-' . $getApplication->deduct_month;
            $this->emailModel->sentAdvanceSalary($arrayAdvance);
            set_alert('success', translate('information_has_been_updated_successfully'));
            return redirect()->to(base_url('advance_salary'));
        }

        $month = '';
        $year = '';
        if (isset($_POST['search'])) {
            $monthYear = $this->request->getPost('month_year');
            $month = date("m", strtotime((string) $monthYear));
            $year = date("Y", strtotime((string) $monthYear));
        }

        $branchId = $this->applicationModel->get_branch_id();
        $this->data['advanceslist'] = $this->advancesalaryModel->getAdvanceSalaryList($month, $year, $branchId);
        $this->data['title'] = translate('advance_salary');
        $this->data['sub_page'] = 'advance_salary/index';
        $this->data['main_menu'] = 'advance_salary';
        echo view('layout/index', $this->data);
        return null;
    }

    public function save()
    {
        if (!get_permission('advance_salary_manage', 'is_add')) {
            ajax_access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => 'Branch', "rules" => 'required']]);
        }

        $this->validation->setRules(['staff_role' => ["label" => translate('staff_role'), "rules" => 'required']]);
        $this->validation->setRules(['staff_id' => ["label" => translate('applicant'), "rules" => 'required']]);
        $this->validation->setRules(['amount' => ["label" => translate('amount'), "rules" => 'required|numeric|greater_than[0]|callback_check_salary']]);
        $this->validation->setRules(['month_year' => ["label" => translate('deduct_month'), "rules" => 'required|callback_check_advance_month']]);
        if ($this->validation->run() == true) {
            $branchId = $this->applicationModel->get_branch_id();
            $insertData = ['staff_id' => $this->request->getPost('staff_id'), 'deduct_month' => date("m", strtotime((string) $this->request->getPost('month_year'))), 'year' => date("Y", strtotime((string) $this->request->getPost('month_year'))), 'amount' => $this->request->getPost('amount'), 'reason' => $this->request->getPost('reason'), 'issued_by' => get_loggedin_user_id(), 'paid_date' => date("Y-m-d H:i:s"), 'request_date' => date("Y-m-d H:i:s"), 'status' => 2, 'branch_id' => $branchId];
            $this->db->table('advance_salary')->insert();
            // getting information for send email alert
            $getStaff = $db->table('staff')->get('staff')->row();
            $insertData['comments'] = $insertData['reason'];
            $insertData['staff_name'] = $getStaff->name;
            $insertData['email'] = $getStaff->email;
            $insertData['deduct_motnh'] = $insertData['year'] . '-' . $insertData['deduct_month'];
            $this->emailModel->sentAdvanceSalary($insertData);
            $url = base_url('advance_salary');
            $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            set_alert('success', translate('information_has_been_saved_successfully'));
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'url' => '', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function delete($id = '')
    {
        if (get_permission('advance_salary_manage', 'is_delete')) {
            // Check branch restrictions
            $this->appLib->check_branch_restrictions('advance_salary', $id);
            $this->db->table('id')->where();
            $this->db->table('advance_salary')->delete();
        }
    }

    public function request()
    {
        if (!get_permission('advance_salary_request', 'is_view')) {
            access_denied();
        }

        $month = '';
        $year = '';
        $staffId = get_loggedin_user_id();
        if (isset($_POST['search'])) {
            $monthYear = $this->request->getPost('month_year');
            $month = date("m", strtotime((string) $monthYear));
            $year = date("Y", strtotime((string) $monthYear));
        }

        $this->data['advanceslist'] = $this->advancesalaryModel->getAdvanceSalaryList($month, $year, '', $staffId);
        $this->data['title'] = translate('advance_salary');
        $this->data['sub_page'] = 'advance_salary/request';
        $this->data['main_menu'] = 'advance_salary';
        echo view('layout/index', $this->data);
    }

    public function request_save()
    {
        if (!get_permission('advance_salary_request', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $this->validation->setRules(['amount' => ["label" => translate('amount'), "rules" => 'required|callback_check_salary']]);
            $this->validation->setRules(['month_year' => ["label" => translate('deduct_month'), "rules" => 'required|callback_check_advance_month']]);
            if ($this->validation->run() == true) {
                $insertData = ['staff_id' => get_loggedin_user_id(), 'deduct_month' => date("m", strtotime((string) $this->request->getPost('month_year'))), 'year' => date("Y", strtotime((string) $this->request->getPost('month_year'))), 'amount' => $this->request->getPost('amount'), 'reason' => $this->request->getPost('reason'), 'request_date' => date("Y-m-d H:i:s"), 'branch_id' => get_loggedin_branch_id(), 'status' => 1];
                $this->db->table('advance_salary')->insert();
                $url = base_url('advance_salary/request');
                $array = ['status' => 'success', 'url' => $url];
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function request_delete($id = '')
    {
        if (get_permission('advance_salary_request', 'is_delete')) {
            $this->db->table('staff_id')->where();
            $this->db->table('id')->where();
            $this->db->table('status')->where();
            $this->db->table('advance_salary')->delete();
        }
    }

    public function getRequestDetails()
    {
        if (get_permission('advance_salary_request', 'is_view')) {
            $this->data['salary_id'] = $this->request->getPost('id');
            echo view('advance_salary/modal_request_details', $this->data);
        }
    }

    // employee salary allocation validation checking
    public function check_salary($amount)
    {
        if ($amount) {
            $staffId = $this->uri->segment(2) == 'request_save' ? get_loggedin_user_id() : $this->request->getPost('staff_id');
            $getSalary = $this->advancesalaryModel->getBasicSalary($staffId, $amount);
            if ($getSalary == 1) {
                $this->validation->setRule('check_salary', 'This Employee Is Not Allocated Salary !');
                return false;
            }

            if ($getSalary == 2) {
                $this->validation->setRule('check_salary', 'Your Advance Amount Exceeds Basic Salary !');
                return false;
            }

            if ($getSalary == 3) {
                return true;
            }
        }

        return null;
    }

    // verification of payment to employees salary this month
    public function check_advance_month($month)
    {
        $staffId = $this->request->getPost('staff_id');
        $getValidation = $this->advancesalaryModel->getAdvanceValidMonth($staffId, $month);
        if ($getValidation == true) {
            return true;
        }

        $this->validation->setRule('check_advance_month', 'This Month Salary Already Paid Or Requested !');
        return false;
    }
}
