<?php

namespace App\Models;

use CodeIgniter\Model;
class OfflinePaymentsModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function typeSave($data = array())
    {
        $arrayData = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['type_name'], 'note' => $data['note']);
        if (!isset($data['type_id'])) {
            $builder->insert('offline_payment_types', $arrayData);
        } else {
            $builder->where('id', $data['type_id']);
            $builder->update('offline_payment_types', $arrayData);
        }
    }
    public function getOfflinePaymentsList($where = array(), $single = false)
    {
        $builder->select('op.*,CONCAT_WS(" ",student.first_name, student.last_name) as fullname,student.email,student.mobileno,student.register_no,class.name as class_name,section.name as section_name,branch.name as branchname');
        $builder->from('offline_fees_payments as op');
        $builder->join('enroll', 'enroll.id = op.student_enroll_id', 'left');
        $builder->join('branch', 'branch.id = enroll.branch_id', 'left');
        $builder->join('student', 'student.id = enroll.student_id', 'left');
        $builder->join('class', 'class.id = enroll.class_id', 'left');
        $builder->join('section', 'section.id = enroll.section_id', 'left');
        if (!empty($where)) {
            $builder->where($where);
        }
        if ($single == true) {
            $result = $builder->get()->row_array();
        } else {
            $builder->order_by('op.id', 'ASC');
            $result = $builder->get()->getResult();
        }
        return $result;
    }
    public function update($id = '')
    {
        $r = $db->table('offline_fees_payments')->get('offline_fees_payments')->row();
        $arrayFees = array('allocation_id' => $r->fees_allocation_id, 'type_id' => $r->fees_type_id, 'amount' => $r->amount, 'fine' => $r->fine, 'collect_by' => "", 'discount' => 0, 'pay_via' => 15, 'collect_by' => 'online', 'remarks' => "Fees deposits via offline Payments Trx ID: " . $id, 'date' => date("Y-m-d"));
        // insert in DB
        $builder->insert('fee_payment_history', $arrayFees);
        // transaction voucher save function
        $getSeeting = $this->feesModel->get('transactions_links', array('branch_id' => get_loggedin_branch_id()), true);
        if ($getSeeting['status']) {
            $arrayTransaction = array('account_id' => $getSeeting['deposit'], 'amount' => $arrayFees['amount'] + $arrayFees['fine'], 'date' => $arrayFees['date']);
            $this->feesModel->saveTransaction($arrayTransaction);
        }
    }
}



