<?php

namespace App\Models;

use CodeIgniter\Model;
class AccountingModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    // account save and update function
    public function saveAccounts($data)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $obal = empty($data['opening_balance']) ? 0 : $data['opening_balance'];
        $insert_account = ['branch_id' => $branchID, 'name' => $data['account_name'], 'number' => $data['account_number'], 'description' => $data['description'], 'updated_at' => date('Y-m-d H:i:s')];
        if (isset($data['account_id']) && !empty($data['account_id'])) {
            $builder->where('id', $data['account_id']);
            $builder->update('accounts', $insert_account);
            $builder->where('id', $data['account_id']);
            $builder->update('transactions', ['branch_id' => $branchID]);
        } else {
            $insert_account['balance'] = $obal;
            $builder->insert('accounts', $insert_account);
            $insertID = $builder->insert_id();
            if ($obal > 0) {
                $insertTransaction = ['account_id' => $insertID, 'branch_id' => $branchID, 'voucher_head_id' => 0, 'type' => 'deposit', 'amount' => $obal, 'dr' => 0, 'cr' => $obal, 'bal' => $obal, 'date' => date('Y-m-d'), 'description' => 'Opening Balance'];
                $builder->insert('transactions', $insertTransaction);
            }
        }
    }
    // voucher save function
    public function saveVoucher($data)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $accountID = $data['account_id'];
        $voucher_headID = $data['voucher_head_id'];
        $voucherType = $data['voucher_type'];
        $ref_no = $data['ref_no'];
        $amount = $data['amount'];
        $date = $data['date'];
        $pay_via = $data['pay_via'];
        $description = $data['description'];
        $qbal = $this->appLib->get_table('accounts', $accountID, true);
        $cbal = $qbal['balance'];
        if ($voucherType == 'deposit') {
            $cr = $amount;
            $dr = 0;
            $bal = $cbal + $amount;
        } elseif ($voucherType == 'expense') {
            $cr = 0;
            $dr = $amount;
            $bal = $cbal - $amount;
        }
        $insertTransaction = ['account_id' => $accountID, 'voucher_head_id' => $voucher_headID, 'type' => $voucherType, 'ref' => $ref_no, 'amount' => $amount, 'dr' => $dr, 'cr' => $cr, 'bal' => $bal, 'date' => date("Y-m-d", strtotime((string) $date)), 'pay_via' => $pay_via, 'description' => $description, 'branch_id' => $branchID];
        $builder->insert('transactions', $insertTransaction);
        $insert_id = $builder->insert_id();
        $builder->where('id', $accountID);
        $builder->update('accounts', ['balance' => $bal]);
        return $insert_id;
    }
    // voucher update function
    public function voucherEdit($data)
    {
        $voucher_headID = $data['voucher_head_id'];
        $refNo = $data['ref_no'];
        $date = $data['date'];
        $payVia = $data['pay_via'];
        $description = $data['description'];
        $insertTransaction = ['voucher_head_id' => $voucher_headID, 'ref' => $refNo, 'date' => date("Y-m-d", strtotime((string) $date)), 'pay_via' => $payVia, 'description' => $description];
        if (isset($data['voucher_old_id']) && !empty($data['voucher_old_id'])) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $insert_id = $data['voucher_old_id'];
            if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                $ext = pathinfo((string) $_FILES["attachment_file"]["name"], PATHINFO_EXTENSION);
                $file_name = $insert_id . '.' . $ext;
                move_uploaded_file($_FILES["attachment_file"]["tmp_name"], "./uploads/attachments/voucher/" . $file_name);
                $builder->where('id', $insert_id);
                $builder->update('transactions', ['attachments' => $file_name]);
            }
            $builder->where('id', $insert_id);
            $builder->update('transactions', $insertTransaction);
        }
    }
    // get voucher list function
    public function getVoucherList($type = '')
    {
        $builder->select('transactions.*, accounts.name as ac_name, voucher_head.name as v_head, payment_types.name as via_name');
        $builder->from('transactions');
        $builder->join('accounts', 'accounts.id = transactions.account_id', 'left');
        $builder->join('voucher_head', 'voucher_head.id = transactions.voucher_head_id', 'left');
        $builder->join('payment_types', 'payment_types.id = transactions.pay_via', 'left');
        if (!empty($type)) {
            $builder->where('transactions.type', $type);
        }
        if (!is_superadmin_loggedin()) {
            $this->db->table('transactions.branch_id', get_loggedin_branch_id())->where();
        }
        return $builder->get()->result_array();
    }
    // get statement report function
    public function getStatementReport($account_id = '', $type = '', $start = '', $end = '')
    {
        $builder->select('transactions.*,voucher_head.name as v_head');
        $builder->from('transactions');
        $builder->join('voucher_head', 'voucher_head.id = transactions.voucher_head_id', 'left');
        $builder->where('transactions.account_id', $account_id);
        $builder->where('transactions.date >=', $start);
        $builder->where('transactions.date <=', $end);
        if ($type != 'all') {
            $builder->where('transactions.type', $type);
        }
        $builder->order_by('transactions.id', 'ASC');
        return $builder->get()->result_array();
    }
    // get income expense report function
    public function getIncomeExpenseRepots($branchID, $start = '', $end = '', $type = '')
    {
        $builder->select('transactions.*,accounts.name as ac_name,voucher_head.name as v_head,payment_types.name as via_name');
        $builder->from('transactions');
        $builder->join('accounts', 'accounts.id = transactions.account_id', 'left');
        $builder->join('voucher_head', 'voucher_head.id = transactions.voucher_head_id', 'left');
        $builder->join('payment_types', 'payment_types.id = transactions.pay_via', 'left');
        if ($type != '') {
            $builder->where('transactions.type', $type);
        }
        $builder->where('transactions.branch_id', $branchID);
        $builder->where('transactions.date >=', $start);
        $builder->where('transactions.date <=', $end);
        $builder->order_by('transactions.id', 'ASC');
        return $builder->get()->result_array();
    }
    // get account balance sheet report
    public function get_balance_sheet($branchID)
    {
        $builder->select('transactions.*,IFNULL(SUM(transactions.dr), 0) as total_dr,IFNULL(SUM(transactions.cr),0) as total_cr,accounts.name as ac_name,accounts.balance as fbalance');
        $builder->from('accounts');
        $builder->join('transactions', 'transactions.account_id = accounts.id', 'left');
        $builder->group_by('transactions.account_id');
        $builder->order_by('accounts.balance', 'DESC');
        $builder->where('accounts.branch_id', $branchID);
        return $builder->get()->result_array();
    }
    // get income vs expense report
    public function get_incomevsexpense($branchID, $start = '', $end = '')
    {
        $sql = "SELECT transactions.*, voucher_head.name as v_head, IFNULL(SUM(transactions.dr), 0) as total_dr, IFNULL(SUM(transactions.cr), 0) as total_cr FROM voucher_head LEFT JOIN\r\n        transactions ON transactions.voucher_head_id = voucher_head.id WHERE transactions.date >= " . $db->escape($start) . " AND transactions.date <= " . $db->escape($end) . " AND transactions.branch_id = " . $db->escape($branchID) . " GROUP BY transactions.voucher_head_id ORDER BY transactions.id ASC";
        return $db->query($sql)->result_array();
    }
    // get transitions repots
    public function getTransitionsRepots($branchID, $start = '', $end = '')
    {
        $sql = "SELECT transactions.*, accounts.name as ac_name, voucher_head.name as v_head, payment_types.name as via_name FROM transactions LEFT JOIN\r\n        accounts ON accounts.id = transactions.account_id LEFT JOIN voucher_head ON voucher_head.id = transactions.voucher_head_id LEFT JOIN\r\n        payment_types ON payment_types.id = transactions.pay_via WHERE transactions.date >= " . $db->escape($start) . " AND\r\n        transactions.date <= " . $db->escape($end) . " AND transactions.branch_id = " . $db->escape($branchID) . " ORDER BY transactions.id ASC";
        return $db->query($sql)->result_array();
    }
    // duplicate voucher head check in db
    public function unique_voucher_head($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $voucher_head_id = $this->request->getPost('voucher_head_id');
        if (!empty($voucher_head_id)) {
            $builder->where_not_in('id', $voucher_head_id);
        }
        $builder->where(['name' => $name, 'branch_id' => $branchID]);
        $query = $builder->get('voucher_head');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("unique_voucher_head", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }
    // duplicate account name check in db
    public function unique_account_name($name)
    {
        $account_id = $this->request->getPost('account_id');
        if (!empty($account_id)) {
            $builder->where_not_in('id', $account_id);
        }
        $builder->where('name', $name);
        $query = $builder->get('accounts');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("unique_account_name", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }
}



