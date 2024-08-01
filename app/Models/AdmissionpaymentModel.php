<?php

namespace App\Models;

use CodeIgniter\Model;
class AdmissionpaymentModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
        $this->model('sms_model');
    }
    // bug fixed fahad
    public function getStudentDetails($referenceNo)
    {
        $amount = 0;
        $status = 0;
        $builder->select('online_admission.*,branch.name as branch_name,branch.symbol,branch.currency,class.name as class_name,section.name as section_name,front_cms_admission.fee_elements');
        $builder->from('online_admission');
        $builder->join('branch', 'branch.id = online_admission.branch_id', 'inner');
        $builder->join('class', 'class.id = online_admission.class_id', 'left');
        $builder->join('section', 'section.id = online_admission.section_id', 'left');
        $builder->join('front_cms_admission', 'front_cms_admission.branch_id = online_admission.branch_id', 'left');
        $builder->where('online_admission.reference_no', $referenceNo);
        $q = $builder->get()->row_array();
        $classID = $q['class_id'];
        $elements = empty($q['fee_elements']) ? [] : json_decode((string) $q['fee_elements'], true);
        if (isset($elements[$classID]) && !empty($elements[$classID])) {
            $status = $elements[$classID]['fee_status'];
            $amount = $elements[$classID]['amount'];
        }
        $q['fee_elements'] = ['amount' => $amount, 'status' => $status];
        return $q;
    }
    // voucher transaction save function
    public function saveTransaction($data)
    {
        $branchID = $data['branch_id'];
        $accountID = $data['account_id'];
        $date = $data['date'];
        $amount = $data['amount'];
        // get the current balance of the selected account
        $qbal = $this->appLib->get_table('accounts', $accountID, true);
        $cbal = $qbal['balance'];
        $bal = $cbal + $amount;
        // query system voucher head / insert
        $arrayHead = ['name' => 'Online Admission Fees Collection', 'type' => 'income', 'system' => 1, 'branch_id' => $branchID];
        $builder->where($arrayHead);
        $query = $builder->get('voucher_head');
        if ($query->num_rows() > 0) {
            $voucher_headID = $query->row()->id;
        } else {
            $builder->insert('voucher_head', $arrayHead);
            $voucher_headID = $builder->insert_id();
        }
        // query system transactions / insert
        $arrayTransactions = ['account_id' => $accountID, 'voucher_head_id' => $voucher_headID, 'type' => 'deposit', 'system' => 1, 'date' => date("Y-m-d", strtotime((string) $date)), 'branch_id' => $branchID];
        $builder->where($arrayTransactions);
        $query = $builder->get('transactions');
        if ($query->num_rows() == 1) {
            $builder->set('amount', 'amount+' . $amount, FALSE);
            $builder->set('cr', 'cr+' . $amount, FALSE);
            $builder->set('bal', $bal);
            $this->db->table('id', $query->row()->id)->where();
            $builder->update('transactions');
        } else {
            $arrayTransactions['ref'] = '';
            $arrayTransactions['amount'] = $amount;
            $arrayTransactions['dr'] = 0;
            $arrayTransactions['cr'] = $amount;
            $arrayTransactions['bal'] = $bal;
            $arrayTransactions['pay_via'] = 5;
            $arrayTransactions['description'] = date("d-M-Y", strtotime((string) $date)) . " Total Fees Collection";
            $builder->insert('transactions', $arrayTransactions);
        }
        $builder->where('id', $accountID);
        $builder->update('accounts', ['balance' => $bal]);
    }
}



