<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Payroll.php
 * @copyright : Reserved RamomCoder Team
 */
class Payroll extends AdminController
{
    /**
     * @var App\Models\PayrollModel
     */
    public $payroll;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $input;

    public $applicationModel;

    public $load;

    public $appLib;

    public $payrollModel;

    public $validation;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->payroll = new \App\Models\PayrollModel();
        $this->email = new \App\Models\EmailModel();
        if (!moduleIsEnabled('human_resource')) {
            access_denied();
        }
    }

    public function index()
    {
        if (!get_permission('salary_payment', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            $monthYear = $this->request->getPost('month_year');
            $staffRole = $this->request->getPost('staff_role');
            $branchId = $this->applicationModel->get_branch_id();
            $this->data['month'] = date("m", strtotime((string) $monthYear));
            $this->data['year'] = date("Y", strtotime((string) $monthYear));
            $this->data['stafflist'] = $this->payrollModel->getEmployeePaymentList($branchId, $staffRole, $this->data['month'], $this->data['year']);
        }

        $this->data['sub_page'] = 'payroll/salary_payment';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        echo view('layout/index', $this->data);
    }

    // add staff salary payslip in database
    public function create($id = '', $month = '', $year = '')
    {
        if (!get_permission('salary_payment', 'is_add')) {
            access_denied();
        }

        // check student restrictions
        $this->appLib->check_branch_restrictions('staff', $id);
        // save all information related to salary
        if (isset($_POST['paid'])) {
            $post = $this->request->getPost();
            $response = $this->payrollModel->save_payslip($post);
            if ($response['status'] == 'success') {
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect($response['uri']);
            } else {
                set_alert('error', "This Month Salary Already Paid !");
                return redirect()->to(base_url('payroll'));
            }
        }

        $this->data['month'] = $month;
        $this->data['year'] = $year;
        $this->data['staff'] = $this->payrollModel->getEmployeePayment($id, $this->data['month'], $this->data['year']);
        $this->data['payvia_list'] = $this->appLib->getSelectList('payment_types');
        $this->data['sub_page'] = 'payroll/create';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        echo view('layout/index', $this->data);
        return null;
    }

    // view staff salary payslip
    public function invoice($id = '', $hash = '')
    {
        if (!get_permission('salary_payment', 'is_view')) {
            access_denied();
        }

        check_hash_restrictions('payslip', $id, $hash);
        $this->data['salary'] = $this->payrollModel->getInvoice($id);
        $this->data['sub_page'] = 'payroll/invoice';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        echo view('layout/index', $this->data);
    }

    /* staff template form validation rules */
    protected function template_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['template_name' => ["label" => translate('salary_grade'), "rules" => 'required']]);
        $this->validation->setRules(['basic_salary' => ["label" => translate('basic_salary'), "rules" => 'required|numeric']]);
    }

    // add staff salary template
    public function salary_template()
    {
        if (!get_permission('salary_template', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('salary_template', 'is_add')) {
            // validate inputs
            $this->template_validation();
            if ($this->validation->run() == true) {
                $overtimeRate = empty($_POST['overtime_rate']) ? 0 : $_POST['overtime_rate'];
                // save salary template info
                $insertData = ['branch_id' => $this->applicationModel->get_branch_id(), 'name' => $this->request->getPost('template_name'), 'basic_salary' => $this->request->getPost('basic_salary'), 'overtime_salary' => $overtimeRate];
                $this->db->table('salary_template')->insert();
                $templateId = $this->db->insert_id();
                // save all allowance info
                $allowances = $this->request->getPost('allowance');
                foreach ($allowances as $value) {
                    if ($value["name"] != "" && $value["amount"] != "") {
                        $insertAllowance = ['salary_template_id' => $templateId, 'name' => $value["name"], 'amount' => $value["amount"], 'type' => 1];
                        $this->db->table('salary_template_details')->insert();
                    }
                }

                // save all deduction info
                $deductions = $this->request->getPost('deduction');
                foreach ($deductions as $value) {
                    if ($value["name"] != "" && $value["amount"] != "") {
                        $insertDeduction = ['salary_template_id' => $templateId, 'name' => $value["name"], 'amount' => $value["amount"], 'type' => 2];
                        $this->db->table('salary_template_details')->insert();
                    }
                }

                $url = base_url('payroll/salary_template');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['title'] = translate('payroll');
        $this->data['sub_page'] = 'payroll/salary_templete';
        $this->data['main_menu'] = 'payroll';
        echo view('layout/index', $this->data);
    }

    // salary template update by id
    public function salary_template_edit($id)
    {
        if (!get_permission('salary_template', 'is_edit')) {
            access_denied();
        }

        // Check branch restrictions
        $this->appLib->check_branch_restrictions('salary_template', $id);
        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $this->template_validation();
            if ($this->validation->run() == true) {
                $templateId = $this->request->getPost('salary_template_id');
                $overtimeRate = empty($_POST['overtime_rate']) ? 0 : $_POST['overtime_rate'];
                // update salary template info
                $insertData = ['name' => $this->request->getPost('template_name'), 'basic_salary' => $this->request->getPost('basic_salary'), 'overtime_salary' => $overtimeRate, 'branch_id' => $branchID];
                $this->db->table('id')->where();
                $this->db->table('salary_template')->update();
                // update all allowance info
                $allowances = $this->request->getPost('allowance');
                foreach ($allowances as $value) {
                    if ($value["name"] != "" && $value["amount"] != "") {
                        $insertAllowance = ['salary_template_id' => $templateId, 'name' => $value["name"], 'amount' => $value["amount"], 'type' => 1];
                        if (isset($value["old_allowance_id"])) {
                            $this->db->table('id')->where();
                            $this->db->table('salary_template_details')->update();
                        } else {
                            $this->db->table('salary_template_details')->insert();
                        }
                    }
                }

                // update all deduction info
                $deductions = $this->request->getPost('deduction');
                foreach ($deductions as $value) {
                    if ($value["name"] != "" && $value["amount"] != "") {
                        $insertDeduction = ['salary_template_id' => $templateId, 'name' => $value["name"], 'amount' => $value["amount"], 'type' => 2];
                        if (isset($value["old_deduction_id"])) {
                            $this->db->table('id')->where();
                            $this->db->table('salary_template_details')->update();
                        } else {
                            $this->db->table('salary_template_details')->insert();
                        }
                    }
                }

                $url = base_url('payroll/salary_template');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['template_id'] = $id;
        $this->data['allowances'] = $this->payrollModel->get('salary_template_details', ['type' => 1, 'salary_template_id' => $id]);
        $this->data['deductions'] = $this->payrollModel->get('salary_template_details', ['type' => 2, 'salary_template_id' => $id]);
        $this->data['template'] = $this->appLib->getTable('salary_template', ['t.id' => $id], true);
        $this->data['title'] = translate('payroll');
        $this->data['sub_page'] = 'payroll/salary_templete_edit';
        $this->data['main_menu'] = 'payroll';
        echo view('layout/index', $this->data);
    }

    // delete salary template from database
    public function salary_template_delete($id)
    {
        if (!get_permission('salary_template', 'is_delete')) {
            access_denied();
        }

        // Check student restrictions
        $this->appLib->check_branch_restrictions('salary_template', $id);
        $this->db->table('salary_template_id')->where();
        $this->db->table('salary_template_details')->delete();
        $this->db->table('id')->where();
        $this->db->table('salary_template')->delete();
    }

    // staff salary allocation
    public function salary_assign()
    {
        if (!get_permission('salary_assign', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $staffRole = $this->request->getPost('staff_role');
            $designationId = $this->request->getPost('designation_id');
            $this->data['stafflist'] = $this->payrollModel->getEmployeeList($branchID, $staffRole, $designationId);
        }

        if (isset($_POST['assign'])) {
            if (!get_permission('salary_assign', 'is_add')) {
                access_denied();
            }

            $stafflist = $this->request->getPost('stafflist');
            if (count($stafflist) > 0) {
                foreach ($stafflist as $value) {
                    $templateId = $value['template_id'];
                    if (empty($templateId)) {
                        $templateId = 0;
                    }

                    $this->db->table('id')->where();
                    $this->db->table('staff')->update();
                }
            }

            set_alert('success', translate('information_has_been_saved_successfully'));
            return redirect()->to(base_url('payroll/salary_assign'));
        }

        $this->data['title'] = translate('payroll');
        $this->data['designationlist'] = $this->appLib->getSelectByBranch('staff_designation', $branchID);
        $this->data['templatelist'] = $this->appLib->getSelectByBranch('salary_template', $branchID);
        $this->data['sub_page'] = 'payroll/salary_assign';
        $this->data['main_menu'] = 'payroll';
        echo view('layout/index', $this->data);
        return null;
    }

    // employees salary statement list
    public function salary_statement()
    {
        if (!get_permission('salary_summary_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $staffID = '';
            if (!get_permission('salary_payment', 'is_add')) {
                $staffID = get_loggedin_user_id();
            }

            $this->data['month'] = date("m", strtotime((string) $this->request->getPost('month_year')));
            $this->data['year'] = date("Y", strtotime((string) $this->request->getPost('month_year')));
            $this->data['payslip'] = $this->payrollModel->get_summary($branchID, $this->data['month'], $this->data['year'], $staffID);
        }

        $this->data['title'] = translate('payroll');
        $this->data['sub_page'] = 'payroll/salary_statement';
        $this->data['main_menu'] = 'payroll_reports';
        echo view('layout/index', $this->data);
    }

    public function payslipPrint()
    {
        if (!get_permission('salary_summary_report', 'is_view')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $this->data['payslip_array'] = $this->request->getPost('payslip_id');
            echo view('payroll/payslipPrint', $this->data, true);
        }
    }
}
