<?php

namespace App\Models;

use CodeIgniter\Model;
class FeesModel extends MYModel
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
        $this->model('sms_model');
    }
    public function getPreviousSessionBalance($enrollID = '', $session_id = '', $with_fine = 0)
    {
        $total_balance = 0;
        $total_fine = 0;
        $variable = $db->table('fee_allocation')->get('fee_allocation')->getResult();
        foreach ($variable as $key => $allocation) {
            $groupsDetails = $db->table('fee_groups_details')->get('fee_groups_details')->getResult();
            foreach ($groupsDetails as $k => $type) {
                $fine = $this->feeFineCalculation($allocation->id, $type->fee_type_id);
                $b = $this->getBalance($allocation->id, $type->fee_type_id);
                $total_balance += $b['balance'];
                $total_fine += abs($fine - $b['fine']);
            }
        }
        if ($with_fine == 1) {
            return round($total_balance + $total_fine);
        } else {
            return $total_balance;
        }
    }
    public function feeFineCalculation($allocationID, $typeID)
    {
        $builder->select('fd.amount,fd.due_date,f.*');
        $builder->from('fee_allocation as a');
        $builder->join('fee_groups_details as fd', 'fd.fee_groups_id = a.group_id and fd.fee_type_id = ' . $db->escape($typeID), 'left');
        $builder->join('fee_fine as f', 'f.group_id = fd.fee_groups_id and f.type_id = fd.fee_type_id', 'inner');
        $builder->where('a.id', $allocationID);
        $this->db->table('f.session_id', get_session_id())->where();
        $getDB = $builder->get()->row_array();
        if (is_array($getDB) && count($getDB)) {
            $dueDate = $getDB['due_date'];
            if (strtotime($dueDate) < strtotime(date('Y-m-d'))) {
                $feeAmount = $getDB['amount'];
                $feeFrequency = $getDB['fee_frequency'];
                $fineValue = $getDB['fine_value'];
                if ($getDB['fine_type'] == 1) {
                    $fineAmount = $fineValue;
                } else {
                    $fineAmount = $feeAmount / 100 * $fineValue;
                }
                $now = time();
                // or your date as well
                $dueDate = strtotime($dueDate);
                $datediff = $now - $dueDate;
                $overDay = round($datediff / (60 * 60 * 24));
                if ($feeFrequency != 0) {
                    $fineAmount = $overDay / $feeFrequency * $fineAmount;
                }
                return $fineAmount;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    public function getStudentAllocationList($classID = '', $sectionID = '', $groupID = '', $branchID = '')
    {
        $sql = "SELECT e.*, s.photo, CONCAT_WS(' ',s.first_name, s.last_name) as fullname, s.gender, s.register_no, s.parent_id, s.email, s.mobileno, IFNULL(fa.id, 0) as allocation_id FROM enroll as e INNER JOIN student as s ON e.student_id = s.id LEFT JOIN login_credential as l ON l.user_id = s.id AND l.role = '7' LEFT JOIN fee_allocation as fa ON fa.student_id = e.id AND fa.group_id = " . $db->escape($groupID) . " AND fa.session_id= " . $db->escape(get_session_id()) . " WHERE e.class_id = " . $db->escape($classID) . " AND e.branch_id = " . $db->escape($branchID) . " AND e.session_id = " . $db->escape(get_session_id());
        if ($sectionID != 'all') {
            $sql .= " AND e.section_id =" . $db->escape($sectionID);
        }
        $sql .= " ORDER BY s.id ASC";
        return $db->query($sql)->result_array();
    }
    public function getInvoiceStatus($enrollID = '')
    {
        $status = "";
        $sql = "SELECT SUM(`fee_groups_details`.`amount` + `fee_allocation`.`prev_due`) as `total`, min(`fee_allocation`.`id`) as `inv_no` FROM `fee_allocation` LEFT JOIN `fee_groups_details` ON `fee_groups_details`.`fee_groups_id` = `fee_allocation`.`group_id` LEFT JOIN `fees_type` ON `fees_type`.`id` = `fee_groups_details`.`fee_type_id` WHERE `fee_allocation`.`student_id` = " . $db->escape($enrollID) . " AND `fee_allocation`.`session_id` = " . $db->escape(get_session_id());
        $balance = $db->query($sql)->row_array();
        $invNo = str_pad($balance['inv_no'], 4, '0', STR_PAD_LEFT);
        $sql = "SELECT IFNULL(SUM(`fee_payment_history`.`amount`), 0) as `amount`, IFNULL(SUM(`fee_payment_history`.`discount`), 0) as `discount`, IFNULL(SUM(`fee_payment_history`.`fine`), 0) as `fine` FROM `fee_payment_history` LEFT JOIN `fee_allocation` ON `fee_payment_history`.`allocation_id` = `fee_allocation`.`id` WHERE `fee_allocation`.`student_id` = " . $db->escape($enrollID) . " AND `fee_allocation`.`session_id` = " . $db->escape(get_session_id());
        $paid = $db->query($sql)->row_array();
        if ($paid['amount'] == 0) {
            $status = 'unpaid';
        } elseif ($balance['total'] == $paid['amount'] + $paid['discount']) {
            $status = 'total';
        } elseif ($paid['amount'] > 1) {
            $status = 'partly';
        }
        return array('status' => $status, 'invoice_no' => $invNo);
    }
    public function getInvoiceDetails($enrollID = '')
    {
        $sql = "SELECT `fee_allocation`.`group_id`,`fee_allocation`.`prev_due`,`fee_allocation`.`id` as `allocation_id`, `fees_type`.`name`, `fees_type`.`system`, `fee_groups_details`.`amount`, `fee_groups_details`.`due_date`, `fee_groups_details`.`fee_type_id` FROM `fee_allocation` LEFT JOIN\r\n        `fee_groups_details` ON `fee_groups_details`.`fee_groups_id` = `fee_allocation`.`group_id` LEFT JOIN `fees_type` ON `fees_type`.`id` = `fee_groups_details`.`fee_type_id` WHERE\r\n        `fee_allocation`.`student_id` = " . $db->escape($enrollID) . " AND `fee_allocation`.`session_id` = " . $db->escape(get_session_id()) . " ORDER BY `fee_allocation`.`group_id` ASC, `fees_type`.`id` ASC";
        $student = array();
        $r = $db->query($sql)->result_array();
        foreach ($r as $key => $value) {
            if ($value['system'] == 1) {
                $value['amount'] = $value['prev_due'];
            }
            $student[] = $value;
        }
        return $student;
    }
    public function getInvoiceBasic($enrollID = '')
    {
        $sessionID = get_session_id();
        $builder->select('s.id,s.register_no,e.branch_id,e.id as enroll_id,s.first_name,s.last_name,s.email as student_email,s.current_address as student_address,c.name as class_name,b.school_name,b.email as school_email,b.mobileno as school_mobileno,b.address as school_address,p.father_name,se.name as section_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 's.id = e.student_id', 'inner');
        $builder->join('class as c', 'c.id = e.class_id', 'left');
        $builder->join('section as se', 'se.id = e.section_id', 'left');
        $builder->join('parent as p', 'p.id = s.parent_id', 'left');
        $builder->join('branch as b', 'b.id = e.branch_id', 'left');
        $builder->where('e.id', $enrollID);
        $builder->where('e.session_id', $sessionID);
        return $builder->get()->row_array();
    }
    public function getStudentFeeDeposit($allocationID, $typeID)
    {
        $sqlDeposit = "SELECT IFNULL(SUM(`amount`), '0.00') as `total_amount`, IFNULL(SUM(`discount`), '0.00') as `total_discount`, IFNULL(SUM(`fine`), '0.00') as `total_fine` FROM `fee_payment_history` WHERE `allocation_id` = " . $db->escape($allocationID) . " AND `type_id` = " . $db->escape($typeID);
        return $db->query($sqlDeposit)->row_array();
    }
    public function getPaymentHistory($allocationID, $groupID)
    {
        $builder->select('h.*,t.name,t.fee_code,pt.name as payvia');
        $builder->from('fee_payment_history as h');
        $builder->join('fees_type as t', 't.id = h.type_id', 'left');
        $builder->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
        $builder->where('h.allocation_id', $allocationID);
        $builder->order_by('h.id', 'asc');
        return $builder->get()->result_array();
    }
    public function typeSave($data = array())
    {
        $arrayData = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['type_name'], 'fee_code' => strtolower(str_replace(' ', '-', $data['type_name'])), 'description' => $data['description']);
        if (!isset($data['type_id'])) {
            $builder->insert('fees_type', $arrayData);
        } else {
            $builder->where('id', $data['type_id']);
            $builder->update('fees_type', $arrayData);
        }
    }
    // add partly of the fee
    public function add_fees($data = array(), $id = '')
    {
        $total_due = get_type_name_by_id('fee_invoice', $id, 'total_due');
        $payment_amount = $data['amount'];
        if ($payment_amount <= $total_due && $payment_amount > 0) {
            $arrayHistory = array('fee_invoice_id' => $id, 'collect_by' => get_user_stamp(), 'remarks' => $data['remarks'], 'method' => $data['method'], 'amount' => $payment_amount, 'date' => date("Y-m-d"), 'session_id' => get_session_id());
            $builder->insert('payment_history', $arrayHistory);
            if ($total_due <= $payment_amount) {
                $builder->where('id', $id);
                $this->db->table('fee_invoice', array('status' => 2))->update();
            } else {
                $builder->where('id', $id);
                $this->db->table('fee_invoice', array('status' => 1))->update();
            }
            $builder->where('id', $id);
            $builder->set('total_paid', 'total_paid + ' . $payment_amount, false);
            $builder->set('total_due', 'total_due - ' . $payment_amount, false);
            $builder->update('fee_invoice');
            // send payment confirmation sms
            $arrayHistory['student_id'] = $data['student_id'];
            $arrayHistory['timestamp'] = date("Y-m-d");
            $this->smsModel->send_sms($arrayHistory, 2);
            return true;
        } else {
            return false;
        }
    }
    public function getInvoiceList($class_id = '', $section_id = '', $branch_id = '')
    {
        $builder->select('e.id as enroll_id,e.student_id,e.roll,e.id as enroll_id,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name');
        $builder->from('fee_allocation as fa');
        $builder->join('enroll as e', 'e.id = fa.student_id and e.session_id = fa.session_id', 'inner');
        $builder->join('student as s', 's.id = e.student_id', 'left');
        $builder->join('class as c', 'c.id = e.class_id', 'left');
        $builder->join('section as se', 'se.id = e.section_id', 'left');
        $builder->where('fa.branch_id', $branch_id);
        $this->db->table('fa.session_id', get_session_id())->where();
        $builder->where('e.class_id', $class_id);
        if ($section_id != 'all') {
            $builder->where('e.section_id', $section_id);
        }
        $builder->group_by('fa.student_id');
        $builder->order_by('e.id', 'asc');
        $result = $builder->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['feegroup'] = $this->getfeeGroup($value['enroll_id']);
        }
        return $result;
    }
    public function getDueInvoiceList($class_id = '', $section_id = '', $feegroup_id = '', $fee_feetype_id = '')
    {
        $sql = "SELECT IFNULL(SUM(h.amount), '0') as `total_amount`, IFNULL(SUM(h.discount), '0') as `total_discount`, gd.amount as `full_amount`, fa.prev_due as `prev_due`, gd.`due_date`, e.student_id, e.id as enroll_id,e.roll, s.first_name, s.last_name, s.register_no, s.mobileno, c.name as `class_name`, se.name as `section_name` FROM fee_allocation as fa LEFT JOIN fee_payment_history as h ON h.allocation_id = fa.id and h.type_id = " . $db->escape($fee_feetype_id) . " INNER JOIN fee_groups_details as gd ON gd.fee_groups_id = fa.group_id and gd.fee_type_id = " . $db->escape($fee_feetype_id) . " INNER JOIN\r\n        enroll as e ON e.id = fa.student_id LEFT JOIN student as s ON s.id = e.student_id LEFT JOIN class as c ON c.id = e.class_id LEFT JOIN section as se ON se.id = e.section_id WHERE\r\n        fa.group_id = " . $db->escape($feegroup_id) . " AND fa.session_id = " . $db->escape(get_session_id()) . " AND e.class_id = " . $db->escape($class_id);
        if ($section_id != 'all') {
            $sql .= " AND e.section_id = " . $db->escape($section_id);
        }
        $sql .= " GROUP BY  fa.student_id ORDER BY e.id ASC";
        $result = $db->query($sql)->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['feegroup'] = $this->getfeeGroup($value['enroll_id']);
        }
        return $result;
    }
    public function getDueReport($class_id = '', $section_id = '')
    {
        $builder->select('fa.id as allocation_id,sum(gd.amount + fa.prev_due) as total_fees,e.id as enroll_id,e.roll,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name');
        $builder->from('fee_allocation as fa');
        $builder->join('fee_groups_details as gd', 'gd.fee_groups_id = fa.group_id', 'left');
        $builder->join('enroll as e', 'e.id = fa.student_id and e.session_id = ' . $db->escape(get_session_id()), 'inner');
        $builder->join('student as s', 's.id = e.student_id', 'left');
        $builder->join('class as c', 'c.id = e.class_id', 'left');
        $builder->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->table('fa.session_id', get_session_id())->where();
        $builder->where('e.class_id', $class_id);
        if (!empty($section_id)) {
            $builder->where('e.section_id', $section_id);
        }
        $builder->group_by('fa.student_id');
        $builder->order_by('e.roll', 'asc');
        $result = $builder->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['payment'] = $this->getPaymentDetails($value['enroll_id']);
        }
        return $result;
    }
    public function getPaymentDetails($student_id = '')
    {
        $builder->select('IFNULL(SUM(amount), 0) as total_paid, IFNULL(SUM(discount), 0) as total_discount, IFNULL(SUM(fine), 0) as total_fine');
        $builder->from('fee_allocation');
        $builder->join('fee_payment_history', 'fee_payment_history.allocation_id = fee_allocation.id', 'left');
        $builder->where('fee_allocation.student_id', $student_id);
        return $builder->get()->row_array();
    }
    public function getStuPaymentHistory($classID = '', $SectionID = '', $paymentVia = '', $start = '', $end = '', $branchID = '', $onlyFine = false)
    {
        $builder->select('h.*,ft.name as type_name,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name,pt.name as pay_via');
        $builder->from('fee_payment_history as h');
        $builder->join('fee_allocation as fa', 'fa.id = h.allocation_id', 'inner');
        $builder->join('fees_type as ft', 'ft.id = h.type_id', 'left');
        $builder->join('enroll as e', 'e.id = fa.student_id', 'inner');
        $builder->join('student as s', 's.id = e.student_id', 'inner');
        $builder->join('class as c', 'c.id = e.class_id', 'left');
        $builder->join('section as se', 'se.id = e.section_id', 'left');
        $builder->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
        $this->db->table('fa.session_id', get_session_id())->where();
        $this->db->table('e.session_id', get_session_id())->where();
        $builder->where('h.date  >=', $start);
        $builder->where('h.date <=', $end);
        $builder->where('e.branch_id', $branchID);
        if ($onlyFine == true) {
            $builder->where('h.fine !=', 0);
        }
        if (!empty($classID)) {
            $builder->where('e.class_id', $classID);
        }
        if (!empty($SectionID)) {
            $builder->where('e.section_id', $SectionID);
        }
        if ($paymentVia != 'all') {
            if ($paymentVia == 'online') {
                $builder->where('h.collect_by', 'online');
            } else {
                $builder->where('h.collect_by !=', 'online');
            }
        }
        $builder->order_by('h.id', 'asc');
        $result = $builder->get()->result_array();
        return $result;
    }
    public function getStuPaymentReport($classID = '', $sectionID = '', $enrollID = '', $typeID = '', $start = '', $end = '', $branchID = '')
    {
        $builder->select('h.*,gd.due_date,ft.name as type_name,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,pt.name as pay_via');
        $builder->from('fee_payment_history as h');
        $builder->join('fee_allocation as fa', 'fa.id = h.allocation_id', 'inner');
        $builder->join('fees_type as ft', 'ft.id = h.type_id', 'left');
        $builder->join('fee_groups_details as gd', 'gd.fee_groups_id = fa.group_id and gd.fee_type_id = h.type_id', 'left');
        $builder->join('enroll as e', 'e.id = fa.student_id and e.session_id =  ' . $db->escape(get_session_id()), 'inner');
        $builder->join('student as s', 's.id = e.student_id', 'inner');
        $builder->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
        $this->db->table('fa.session_id', get_session_id())->where();
        $builder->where('h.date >=', $start);
        $builder->where('h.date <=', $end);
        $builder->where('e.branch_id', $branchID);
        $builder->where('e.class_id', $classID);
        if (!empty($typeID)) {
            $typeID = explode("|", $typeID);
            $builder->where('h.type_id', $typeID[1]);
        }
        if (!empty($enrollID)) {
            $builder->where('e.id', $enrollID);
        }
        $builder->where('e.section_id', $sectionID);
        $builder->order_by('h.id', 'asc');
        $result = $builder->get()->result_array();
        return $result;
    }
    public function getfeeGroup($studentID = '')
    {
        $builder->select('g.name');
        $builder->from('fee_allocation as fa');
        $builder->join('fee_groups as g', 'g.id = fa.group_id', 'inner');
        $builder->where('fa.student_id', $studentID);
        $this->db->table('fa.session_id', get_session_id())->where();
        return $builder->get()->result_array();
    }
    public function reminderSave($data = array())
    {
        $arrayData = array('frequency' => $data['frequency'], 'days' => $data['days'], 'student' => isset($data['chk_student']) ? 1 : 0, 'guardian' => isset($data['chk_guardian']) ? 1 : 0, 'message' => $data['message'], 'dlt_template_id' => $data['dlt_template_id'], 'branch_id' => $data['branch_id']);
        if (!isset($data['reminder_id'])) {
            $builder->insert('fees_reminder', $arrayData);
        } else {
            $builder->where('id', $data['reminder_id']);
            $builder->update('fees_reminder', $arrayData);
        }
    }
    public function getFeeReminderByDate($date = '', $branch_id = '')
    {
        $builder->select('fee_groups_details.*,fees_type.name');
        $builder->from('fee_groups_details');
        $builder->join('fees_type', 'fees_type.id = fee_groups_details.fee_type_id', 'inner');
        $builder->where('fee_groups_details.due_date', $date);
        $builder->where('fees_type.branch_id', $branch_id);
        $builder->order_by('fee_groups_details.id', 'asc');
        return $builder->get()->result_array();
    }
    public function getStudentsListReminder($groupID = '', $typeID = '')
    {
        $sessionID = get_type_name_by_id('global_settings', 1, 'session_id');
        $builder->select('a.id as allocation_id,CONCAT_WS(" ",s.first_name, s.last_name) as child_name,s.mobileno as child_mobileno,pr.name as guardian_name,pr.mobileno as guardian_mobileno');
        $builder->from('fee_allocation as a');
        $builder->join('student as s', 's.id = a.student_id', 'inner');
        $builder->join('parent as pr', 'pr.id = s.parent_id', 'left');
        $builder->where('a.group_id', $groupID);
        $builder->where('a.session_id', $sessionID);
        $result = $builder->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['payment'] = $this->getPaymentDetailsByTypeID($value['allocation_id'], $typeID);
        }
        return $result;
    }
    public function getPaymentDetailsByTypeID($allocationID, $typeID)
    {
        $builder->select('IFNULL(SUM(amount), 0) as total_paid, IFNULL(SUM(discount), 0) as total_discount');
        $builder->from('fee_payment_history');
        $builder->where('allocation_id', $allocationID);
        $builder->where('type_id', $typeID);
        return $builder->get()->row_array();
    }
    public function depositAmountVerify($amount = '')
    {
        if ($amount != "") {
            $typeID = $this->request->getPost('fees_type');
            if (empty($typeID)) {
                return true;
            }
            $feesType = explode("|", $typeID);
            $remainAmount = $this->getBalance($feesType[0], $feesType[1]);
            if ($remainAmount['balance'] < $amount) {
                $this->form_validation->set_message('deposit_verify', '{field} cannot be greater than the remaining.');
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
    public function getBalance($allocationID, $typeID)
    {
        $groupsID = get_type_name_by_id('fee_allocation', $allocationID, 'group_id');
        $systemFeesType = get_type_name_by_id('fees_type', $typeID, 'system');
        if ($systemFeesType == 1) {
            $totalAmount = get_type_name_by_id('fee_allocation', $allocationID, 'prev_due');
        } else {
            $totalAmount = $db->table('fee_groups_details')->get('fee_groups_details')->row_array();
            $totalAmount = $totalAmount['amount'];
        }
        $builder->select('IFNULL(sum(p.amount), 0) as total_amount,IFNULL(sum(p.discount), 0) as total_discount,IFNULL(sum(p.fine), 0) as total_fine');
        $builder->from('fee_payment_history as p');
        $builder->where('p.allocation_id', $allocationID);
        $builder->where('p.type_id', $typeID);
        $paid = $builder->get()->row_array();
        $balance = $totalAmount - ($paid['total_amount'] + $paid['total_discount']);
        $total_fine = $paid['total_fine'];
        return array('balance' => $balance, 'fine' => $total_fine);
    }
    // voucher transaction save function
    public function saveTransaction($data = array(), $payment_historyID = '')
    {
        $branchID = $this->applicationModel->get_branch_id();
        $accountID = $data['account_id'];
        $date = $data['date'];
        $amount = $data['amount'];
        // get the current balance of the selected account
        $qbal = $this->appLib->get_table('accounts', $accountID, true);
        $cbal = $qbal['balance'];
        $bal = $cbal + $amount;
        // query system voucher head / insert
        $arrayHead = array('name' => 'Student Fees Collection', 'type' => 'income', 'system' => 1, 'branch_id' => $branchID);
        $builder->where($arrayHead);
        $query = $builder->get('voucher_head');
        if ($query->num_rows() > 0) {
            $voucher_headID = $query->row()->id;
        } else {
            $builder->insert('voucher_head', $arrayHead);
            $voucher_headID = $builder->insert_id();
        }
        // query system transactions / insert
        $arrayTransactions = array('account_id' => $accountID, 'voucher_head_id' => $voucher_headID, 'type' => 'deposit', 'system' => 1, 'date' => date("Y-m-d", strtotime($date)), 'branch_id' => $branchID);
        $builder->where($arrayTransactions);
        $query = $builder->get('transactions');
        if ($query->num_rows() == 1) {
            $transactionsID = $query->row()->id;
            $builder->set('amount', 'amount+' . $amount, false);
            $builder->set('cr', 'cr+' . $amount, false);
            $builder->set('bal', $bal);
            $builder->where('id', $transactionsID);
            $builder->update('transactions');
        } else {
            $arrayTransactions['ref'] = '';
            $arrayTransactions['amount'] = $amount;
            $arrayTransactions['dr'] = 0;
            $arrayTransactions['cr'] = $amount;
            $arrayTransactions['bal'] = $bal;
            $arrayTransactions['pay_via'] = 5;
            $arrayTransactions['description'] = date("d-M-Y", strtotime($date)) . " Total Fees Collection";
            $builder->insert('transactions', $arrayTransactions);
            $transactionsID = $builder->insert_id();
        }
        $builder->where('id', $accountID);
        $this->db->table('accounts', array('balance' => $bal))->update();
        // insert transactions links details in DB
        $arrayLinkDetails = array('payment_id' => $payment_historyID, 'transactions_id' => $transactionsID);
        $builder->insert('transactions_links_details', $arrayLinkDetails);
    }
    public function carryForwardDue($data = array())
    {
        $type_name = "Previous Session Balance";
        $group_name = "Due Record";
        $branchID = $data['branch_id'];
        $sessionID = $data['session_id'];
        $fee_type_id = 0;
        $fee_group_id = 0;
        $arrayType = array('name' => $type_name, 'branch_id' => $branchID, 'system' => 1);
        $fee_type_exists = $this->checkExistsData('fees_type', $arrayType);
        if (!$fee_type_exists) {
            $arrayType['fee_code'] = 'previous-balance';
            $builder->insert('fees_type', $arrayType);
            $fee_type_id = $builder->insert_id();
        } else {
            $fee_type_id = $fee_type_exists->id;
        }
        $arrayGroup = array('name' => $group_name, 'branch_id' => $branchID, 'session_id' => $sessionID, 'system' => 1);
        $fee_group_exists = $this->checkExistsData('fee_groups', $arrayGroup);
        if (!$fee_group_exists) {
            $builder->insert('fee_groups', $arrayGroup);
            $fee_group_id = $builder->insert_id();
        } else {
            $fee_group_id = $fee_group_exists->id;
        }
        $arrayGroupsDetails = array('fee_groups_id' => $fee_group_id, 'fee_type_id' => $fee_type_id);
        $fee_group_details_exists = $this->checkExistsData('fee_groups_details', $arrayGroupsDetails);
        if (!$fee_group_details_exists) {
            $arrayGroupsDetails['amount'] = 0;
            $arrayGroupsDetails['due_date'] = $data['due_date'];
            $builder->insert('fee_groups_details', $arrayGroupsDetails);
        }
        $arrayAllocation = array('student_id' => $data['student_id'], 'group_id' => $fee_group_id, 'branch_id' => $branchID, 'session_id' => $sessionID);
        $fee_allocation_exists = $this->checkExistsData('fee_allocation', $arrayAllocation);
        if (!$fee_allocation_exists) {
            $arrayAllocation['prev_due'] = $data['prev_due'];
            $builder->insert('fee_allocation', $arrayAllocation);
        } else {
            $arrayAllocation['prev_due'] = $data['prev_due'];
            $builder->where('id', $fee_allocation_exists->id);
            $builder->update('fee_allocation', $arrayAllocation);
        }
    }
    function checkExistsData($table = '', $data = array())
    {
        $builder->where($data);
        $query = $builder->get($table);
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }
}



