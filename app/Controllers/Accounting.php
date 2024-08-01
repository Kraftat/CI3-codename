<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AccountingModel;
use App\Models\EmailModel;
class Accounting extends AdminController

{
    public $accounting_model;

    public $appLib;

    protected $db;

    /**
     * @var App\Models\AccountingModel
     */
    public $accounting;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $validation;

    public $accountingModel;

    public $input;

    public $load;

    public $applicationModel;

    public function __construct()
    {

        parent::__construct();

        $this->appLib = service('appLib'); 
        $this->accounting = new \App\Models\AccountingModel();
        $this->email = new \App\Models\EmailModel();
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        if (!moduleIsEnabled('office_accounting')) {
            access_denied();
        }
    }

    /* account form validation rules */
    protected function account_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->set_rules('branch_id', translate('branch'), 'required');
        }

        $this->validation->set_rules('account_name', translate('account_name'), ['trim', 'required', ['unique_account_name', [$this->accounting_model, 'unique_account_name']]]);
        $this->validation->set_rules('opening_balance', translate('opening_balance'), 'trim|numeric');
    }

    // add new account for office accounting
    public function index()
    {
        // check access permission
        if (!get_permission('account', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('account', 'is_add')) {
                access_denied();
            }

            $this->account_validation();
            if ($this->validation->run() !== false) {
                $data = $this->request->getPost();
                $this->accountingModel->saveAccounts($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = $_SERVER['HTTP_REFERER'];
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['accountslist'] = $this->appLib->getTable('accounts');
        $this->data['sub_page'] = 'accounting/index';
        $this->data['main_menu'] = 'accounting';
        $this->data['title'] = translate('office_accounting');
        $this->view('layout/index', $this->data);
    }

    // update existing account if passed id
    public function edit($id = '')
    {
        if (!get_permission('account', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->account_validation();
            if ($this->validation->run() !== false) {
                $data = $this->request->getPost();
                $this->accountingModel->saveAccounts($data);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('accounting');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['account'] = $this->appLib->getTable('accounts', ['t.id' => $id], true);
        $this->data['sub_page'] = 'accounting/edit';
        $this->data['main_menu'] = 'accounting';
        $this->data['title'] = translate('office_accounting');
        $this->view('layout/index', $this->data);
    }

    // delete account from database
    public function delete($id = '')
    {
        if (!get_permission('account', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('accounts')->delete();
        if ($db->affectedRows() > 0) {
            $this->db->table('account_id')->where();
            $this->db->table('transactions')->delete();
        }
    }

    // add new voucher head for voucher
    public function voucher_head()
    {
        if ($_POST !== []) {
            if (!get_permission('voucher_head', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->set_rules('branch_id', translate('branch'), 'required');
            }

            $this->validation->set_rules('voucher_head', translate('name'), ['trim', 'required', ['unique_voucher_head', [$this->accounting_model, 'unique_voucher_head']]]);
            $this->validation->set_rules('type', translate('type'), 'trim|required');
            if ($this->validation->run() !== false) {
                $arrayHead = ['branch_id' => $this->applicationModel->get_branch_id(), 'name' => $this->request->getPost('voucher_head'), 'type' => $this->request->getPost('type')];
                $this->db->table('voucher_head')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }

        $this->data['productlist'] = $this->appLib->getTable('voucher_head', ['system' => 0]);
        $this->data['title'] = translate('office_accounting');
        $this->data['sub_page'] = 'accounting/voucher_head';
        $this->data['main_menu'] = 'accounting';
        $this->view('layout/index', $this->data);
    }

    // update existing voucher head if passed id
    public function voucher_head_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('voucher_head', 'is_edit')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->set_rules('branch_id', translate('branch'), 'required');
            }

            $this->validation->set_rules('voucher_head', translate('name'), ['trim', 'required', ['unique_voucher_head', [$this->accounting_model, 'unique_voucher_head']]]);
            if ($this->validation->run() !== false) {
                $voucherHeadId = $this->request->getPost('voucher_head_id');
                $arrayHead = ['name' => $this->request->getPost('voucher_head')];
                $this->db->table('id')->where();
                $this->db->table('voucher_head')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('accounting/voucher_head');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function voucherHeadDetails()
    {
        if (get_permission('voucher_head', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('voucher_head');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    // delete voucher head from database
    public function voucher_head_delete($id)
    {
        if (!get_permission('voucher_head', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('voucher_head')->delete();
    }

    // this function is used to add voucher data
    public function voucher_deposit()
    {
        if (!get_permission('deposit', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['voucherlist'] = $this->accountingModel->getVoucherList('deposit');
        $this->data['sub_page'] = 'accounting/voucher_deposit';
        $this->data['main_menu'] = 'accounting';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['title'] = translate('office_accounting');
        $this->view('layout/index', $this->data);
    }

    // this function is used to add voucher data
    public function voucher_expense()
    {
        if (!get_permission('expense', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['voucherlist'] = $this->accountingModel->getVoucherList('expense');
        $this->data['sub_page'] = 'accounting/voucher_expense';
        $this->data['main_menu'] = 'accounting';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['title'] = translate('office_accounting');
        $this->view('layout/index', $this->data);
    }

    public function voucher_save()
    {
        if ($_POST !== []) {
            $type = $this->request->getPost('voucher_type');
            if ($type == 'deposit' && !get_permission('deposit', 'is_add')) {
                ajax_access_denied();
            }

            if ($type == 'expense' && !get_permission('expense', 'is_add')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->set_rules('branch_id', translate('branch'), 'required');
            }

            $this->validation->set_rules('account_id', translate('account'), 'trim|required');
            $this->validation->set_rules('voucher_head_id', translate('voucher_head'), 'trim|required');
            $this->validation->set_rules('amount', translate('amount'), 'trim|required|numeric');
            $this->validation->set_rules('date', translate('date'), 'trim|required|callback_get_valid_date');
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save data into table
                $insertId = $this->accountingModel->saveVoucher($post);
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $ext = pathinfo((string) $_FILES["attachment_file"]["name"], PATHINFO_EXTENSION);
                    $fileName = $insertId . '.' . $ext;
                    move_uploaded_file($_FILES["attachment_file"]["tmp_name"], "./uploads/attachments/voucher/" . $fileName);
                    $this->db->table('id')->where();
                    $this->db->table('transactions')->update();
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function all_transactions()
    {
        if (!get_permission('all_transactions', 'is_view')) {
            access_denied();
        }

        $this->data['voucherlist'] = $this->accountingModel->getVoucherList();
        $this->data['sub_page'] = 'accounting/all_transactions';
        $this->data['main_menu'] = 'accounting';
        $this->data['title'] = translate('office_accounting');
        $this->view('layout/index', $this->data);
    }

    // this function is used to voucher data update
    public function voucher_deposit_edit($id = '')
    {
        if (!get_permission('deposit', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->validation->set_rules('voucher_head_id', translate('voucher_head'), 'trim|required');
            $this->validation->set_rules('date', translate('date'), 'trim|required');
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                // update data into table
                $insertId = $this->accountingModel->voucherEdit($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('accounting/voucher_deposit');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['deposit'] = $this->appLib->getTable('transactions', ['t.id' => $id], true);
        $this->data['sub_page'] = 'accounting/voucher_deposit_edit';
        $this->data['main_menu'] = 'accounting';
        $this->data['title'] = translate('office_accounting');
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->view('layout/index', $this->data);
    }

    // this function is used to voucher data update
    public function voucher_expense_edit($id = '')
    {
        if (!get_permission('expense', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->validation->set_rules('voucher_head_id', translate('voucher_head'), 'trim|required');
            $this->validation->set_rules('date', translate('date'), 'trim|required');
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                // update data into table
                $insertId = $this->accountingModel->voucherEdit($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('accounting/voucher_expense');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['expense'] = $this->appLib->getTable('transactions', ['t.id' => $id], true);
        $this->data['sub_page'] = 'accounting/voucher_expense_edit';
        $this->data['main_menu'] = 'accounting';
        $this->data['title'] = translate('office_accounting');
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->view('layout/index', $this->data);
    }

    // delete into voucher table by voucher id
    public function voucher_delete($id)
    {
        $q = $db->table('transactions')->get('transactions')->row_array();
        if ($q['type'] == 'expense') {
            if (!get_permission('expense', 'is_delete')) {
                access_denied();
            }

            $sql = "UPDATE accounts SET balance = balance + " . $q['amount'] . " WHERE id = " . $db->escape($q['account_id']);
            $db->query($sql);
        } elseif ($q['type'] == 'deposit') {
            if (!get_permission('deposit', 'is_delete')) {
                access_denied();
            }

            $sql = "UPDATE accounts SET balance = balance - " . $q['amount'] . " WHERE id = " . $db->escape($q['account_id']);
            $db->query($sql);
        }

        $filepath = FCPATH . 'uploads/attachments/voucher/' . $q['attachments'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $this->db->table('id')->where();
        $this->db->table('transactions')->delete();
    }

    // account statement by date to date
    public function account_statement()
    {
        if (!get_permission('accounting_reports', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $accountId = $this->request->getPost('account_id');
            $type = $this->request->getPost('type');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->accountingModel->getStatementReport($accountId, $type, $start, $end);
        }

        $this->data['title'] = translate('financial_reports');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['sub_page'] = 'accounting/account_statement';
        $this->data['main_menu'] = 'accounting_repots';
        $this->view('layout/index', $this->data);
    }

    // income repots by date to date
    public function income_repots()
    {
        if (!get_permission('accounting_reports', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->accountingModel->getIncomeExpenseRepots($branchID, $start, $end, 'deposit');
        }

        $this->data['title'] = translate('financial_reports');
        $this->data['sub_page'] = 'accounting/income_repots';
        $this->data['main_menu'] = 'accounting_repots';
        $this->view('layout/index', $this->data);
    }

    public function expense_repots()
    {
        if (!get_permission('accounting_reports', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->accountingModel->getIncomeExpenseRepots($branchID, $start, $end, 'expense');
        }

        $this->data['title'] = translate('financial_reports');
        $this->data['sub_page'] = 'accounting/expense_repots';
        $this->data['main_menu'] = 'accounting_repots';
        $this->view('layout/index', $this->data);
    }

    // account balance sheet
    public function balance_sheet()
    {
        if (!get_permission('accounting_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['results'] = $this->accountingModel->get_balance_sheet($branchID);
        $this->data['title'] = translate('financial_reports');
        $this->data['sub_page'] = 'accounting/balance_sheet';
        $this->data['main_menu'] = 'accounting_repots';
        $this->view('layout/index', $this->data);
    }

    // income vs expense repots by date to date
    public function incomevsexpense()
    {
        if (!get_permission('accounting_reports', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->accountingModel->get_incomevsexpense($branchID, $start, $end);
        }

        $this->data['title'] = translate('financial_reports');
        $this->data['sub_page'] = 'accounting/income_vs_expense';
        $this->data['main_menu'] = 'accounting_repots';
        $this->view('layout/index', $this->data);
    }

    public function transitions_repots()
    {
        if (!get_permission('accounting_reports', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->accountingModel->getTransitionsRepots($branchID, $start, $end);
        }

        $this->data['title'] = translate('financial_reports');
        $this->data['sub_page'] = 'accounting/transitions_repots';
        $this->data['main_menu'] = 'accounting_repots';
        $this->view('layout/index', $this->data);
    }

    public function getVoucherHead()
    {
        $html = "";
        $branchId = $this->applicationModel->get_branch_id();
        $this->request->getPost('type');
        $db = \Config\Database::connect(); // Ensure database connection is initialized
    
        if (!empty($branchId)) {
            $result = $db->table('voucher_head')->get()->getResultArray();
            if (!empty($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }
    
        echo $html;
    }
    

    public function get_valid_date($date)
    {
        $presentDate = date('Y-m-d');
        $date = date("Y-m-d", strtotime((string) $date));
        if ($date > $presentDate) {
            $this->validation->set_message("get_valid_date", "Please Enter Correct Date");
            return false;
        }

        return true;
    }

    public function deposit_download()
    {
        if (get_permission('deposit', 'is_view')) {
            helper('download');
            $encryptName = html_escape(urldecode((string) $this->request->getGet('id')));
            if (!empty($encryptName)) {
                if (!is_superadmin_loggedin()) {
                    $this->db->table('branch_id')->where();
                }

                $fileName = $db->table('transactions')->get('transactions')->row()->attachments;
                if (!empty($fileName)) {
                    force_download($fileName, file_get_contents('uploads/attachments/voucher/' . $fileName));
                }
            }
        }
    }

    public function expense_download()
    {
        if (get_permission('expense', 'is_view')) {
            helper('download');
            $encryptName = html_escape(urldecode((string) $this->request->getGet('id')));
            if (!empty($encryptName)) {
                if (!is_superadmin_loggedin()) {
                    $this->db->table('branch_id')->where();
                }

                $fileName = $db->table('transactions')->get('transactions')->row()->attachments;
                if (!empty($fileName)) {
                    force_download($fileName, file_get_contents('uploads/attachments/voucher/' . $fileName));
                }
            }
        }
    }
}
