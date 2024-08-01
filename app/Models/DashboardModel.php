<?php

namespace App\Models;

use CodeIgniter\Model;
class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    public function getMonthlyBookIssued($id = '')
    {
        $builder->select('id');
        $builder->from('leave_application');
        $this->db->table("start_date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE() AND status = '2' AND role_id = '7' AND user_id = " . $db->escape($id))->where();
        return $builder->get()->num_rows();
    }
    public function getStaffCounter($role = '', $branchID = '')
    {
        $builder->select('COUNT(staff.id) as snumber');
        $builder->from('staff');
        $builder->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
        $builder->where_not_in('login_credential.role', 1);
        if (!empty($role)) {
            $builder->where('login_credential.role', $role);
        } else {
            $this->db->where_not_in('login_credential.role', array(1, 3, 6, 7));
        }
        if (!empty($branchID)) {
            $builder->where('staff.branch_id', $branchID);
        }
        return $builder->get()->row_array();
    }
    public function getMonthlyPayment($id = '')
    {
        $builder->select('IFNULL(sum(h.amount),0) as amount');
        $builder->from('fee_allocation as fa');
        $builder->join('fee_payment_history as h', 'h.allocation_id = fa.id', 'left');
        $this->db->table("h.date BETWEEN DATE_SUB(CURDATE(),INTERVAL 1 MONTH) AND CURDATE() AND fa.student_id = " . $db->escape($id) . " AND fa.session_id = " . $db->escape(get_session_id()))->where();
        return $builder->get()->row()->amount;
    }
    /* annual academic fees summary charts */
    public function annualFeessummaryCharts($branchID = '', $studentID = '')
    {
        $total_fee = array();
        $total_paid = array();
        $total_due = array();
        $year = date("Y");
        for ($month = 1; $month <= 12; $month++) {
            $sql = "SELECT `fa`.`id` as `allocation_id`,`gd`.`fee_type_id`,`gd`.`amount` FROM `fee_allocation` as `fa` INNER JOIN `fee_groups_details` as `gd` ON `gd`.`fee_groups_id` = `fa`.`group_id` WHERE MONTH(`gd`.`due_date`) = " . $db->escape($month) . " AND YEAR(`gd`.`due_date`) = '{$year}' AND `fa`.`session_id` = " . $db->escape(get_session_id());
            if (!empty($branchID)) {
                $sql .= " AND `fa`.`branch_id` = " . $db->escape($branchID);
            }
            if (!empty($studentID)) {
                $sql .= " AND `fa`.`student_id` = " . $db->escape($studentID);
            }
            $total_amount = 0;
            $totalpaid = 0;
            $total_discount = 0;
            $result = $db->query($sql)->result();
            foreach ($result as $row) {
                $total_amount += $row->amount;
                $sql = "SELECT SUM(`h`.`amount`) AS `total_paid`, SUM(`h`.`discount`) AS `total_discount` FROM `fee_payment_history` as `h` WHERE `h`.`allocation_id` = " . $db->escape($row->allocation_id) . " AND  `h`.`type_id` = " . $db->escape($row->fee_type_id);
                $r = $db->query($sql)->row();
                $totalpaid += $r->total_paid;
                $total_discount += $r->total_discount;
            }
            $total_fee[] = floatval($total_amount);
            $total_paid[] = floatval($totalpaid);
            $total_due[] = floatval($total_amount - ($totalpaid + $total_discount));
        }
        return array('total_fee' => $total_fee, 'total_paid' => $total_paid, 'total_due' => $total_due);
    }
    /* student annual attendance charts */
    public function getStudentAttendance($studentID = '')
    {
        $total_present = array();
        $total_absent = array();
        $total_late = array();
        $enrollID = $db->table('enroll')->get('enroll')->row()->id;
        for ($month = 1; $month <= 12; $month++) {
            $total_present[] = $db->query("SELECT id FROM student_attendance WHERE MONTH(date) = " . $db->escape($month) . " AND YEAR(date) = YEAR(CURDATE()) AND status = 'P' AND enroll_id = " . $db->escape($enrollID))->num_rows();
            $total_absent[] = $db->query("SELECT id FROM student_attendance WHERE MONTH(date) = " . $db->escape($month) . " AND YEAR(date) = YEAR(CURDATE()) AND status = 'A' AND enroll_id = " . $db->escape($enrollID))->num_rows();
            $total_late[] = $db->query("SELECT id FROM student_attendance WHERE MONTH(date) = " . $db->escape($month) . " AND YEAR(date) = YEAR(CURDATE()) AND status = 'L' AND enroll_id = " . $db->escape($enrollID))->num_rows();
        }
        return array('total_present' => $total_present, 'total_absent' => $total_absent, 'total_late' => $total_late);
    }
    public function get_monthly_attachments($id = '')
    {
        $branchID = get_loggedin_branch_id();
        $classID = $db->table('enroll')->get('enroll')->row()->class_id;
        $builder->select('id');
        $builder->from('attachments');
        $this->db->table("date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE() AND (class_id = " . $db->escape($classID) . " OR class_id = 'unfiltered') AND branch_id = " . $db->escape($branchID))->where();
        return $builder->get()->num_rows();
    }
    /* annual academic fees summary charts */
    public function getWeekendAttendance($branchID = '')
    {
        $days = array();
        $employee_att = array();
        $student_att = array();
        $now = new DateTime("6 days ago");
        $interval = new DateInterval('P1D');
        // 1 Day interval
        $period = new DatePeriod($now, $interval, 6);
        // 7 Days
        foreach ($period as $day) {
            $days[] = $day->format("d-M");
            $builder->select('id');
            if (!empty($branchID)) {
                $builder->where('branch_id', $branchID);
            }
            $this->db->table('date = "' . $day->format('Y-m-d') . '" AND (status = "P" OR status = "L")')->where();
            $student_att[]['y'] = $builder->get('student_attendance')->num_rows();
            $builder->select('id');
            if (!empty($branchID)) {
                $builder->where('branch_id', $branchID);
            }
            $this->db->table('date = "' . $day->format('Y-m-d') . '" AND (status = "P" OR status = "L")')->where();
            $employee_att[]['y'] = $builder->get('staff_attendance')->num_rows();
        }
        return array('days' => $days, 'employee_att' => $employee_att, 'student_att' => $student_att);
    }
    /* monthly academic cash book transaction charts */
    public function getIncomeVsExpense($branchID = '')
    {
        $query = "SELECT IFNULL(SUM(dr),0) as dr, IFNULL(SUM(cr),0) as cr FROM transactions WHERE month(DATE) = MONTH(now()) AND year(DATE) = YEAR(now())";
        if (!empty($branchID)) {
            $query .= " AND branch_id = " . $db->escape($branchID);
        }
        $r = $db->query($query)->row_array();
        return array(['name' => translate("expense"), 'value' => $r['dr']], ['name' => translate("income"), 'value' => $r['cr']]);
    }
    /* total academic students strength classes divided into charts */
    public function getStudentByClass($branchID = '')
    {
        $builder->select('IFNULL(COUNT(e.student_id), 0) as total_student, c.name as class_name');
        $builder->from('enroll as e');
        $builder->join('class as c', 'c.id = e.class_id', 'inner');
        $builder->group_by('e.class_id');
        if (!empty($branchID)) {
            $builder->where('e.branch_id', $branchID);
        }
        $query = $builder->get();
        $data = array();
        if ($query->num_rows() > 0) {
            $students = $query->getResult();
            foreach ($students as $row) {
                $data[] = ['value' => floatval($row->total_student), 'name' => $row->class_name];
            }
        } else {
            $data[] = ['value' => 0, 'name' => translate('not_found_anything')];
        }
        return $data;
    }
    public function get_total_student($branchID = '')
    {
        $sessionID = get_session_id();
        $builder->select('IFNULL(COUNT(enroll.id), 0) as total_student');
        $builder->from('enroll');
        $builder->join('student', 'student.id = enroll.student_id', 'inner');
        $builder->where('enroll.session_id', $sessionID);
        if (!empty($branchID)) {
            $builder->where('enroll.branch_id', $branchID);
        }
        return $builder->get()->row()->total_student;
    }
    public function getMonthlyAdmission($branchID = '')
    {
        $builder->select('s.id');
        $builder->from('student as s');
        $builder->join('enroll as e', 'e.student_id = s.id', 'inner');
        $this->db->table('s.admission_date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE()')->where();
        if (!empty($branchID)) {
            $builder->where('e.branch_id', $branchID);
        }
        return $builder->get()->num_rows();
    }
    public function getVoucher($branchID = '')
    {
        $builder->select('id');
        if (!empty($branchID)) {
            $builder->where('branch_id', $branchID);
        }
        $this->db->table('date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE()')->where();
        return $builder->get('transactions')->num_rows();
    }
    public function get_transport_route($branchID = '')
    {
        if (!empty($branchID)) {
            $builder->where('branch_id', $branchID);
        }
        return $builder->get('transport_route')->num_rows();
    }
    public function languageShortCodes($lang = '')
    {
        $codes = array('english' => 'en', 'bengali' => 'bn', 'arabic' => 'ar', 'french' => 'fr', 'hindi' => 'hi', 'indonesian' => 'id', 'italian' => 'it', 'japanese' => 'ja', 'korean' => 'ko', 'portuguese' => 'pt', 'thai' => 'th', 'turkish' => 'tr', 'urdu' => 'ur', 'chinese' => 'zh', 'afrikaans' => 'af', 'german' => 'de', 'nepali' => 'ne', 'russian' => 'ru', 'danish' => 'da', 'armenian' => 'hy', 'georgian' => 'ka', 'marathi' => 'mr', 'malay' => 'ms', 'tamil' => 'ta', 'telugu' => 'te', 'swedish' => 'sv', 'dutch' => 'nl', 'greek' => 'el', 'spanish' => 'es', 'punjabi' => 'pa');
        return empty($codes[$lang]) ? '' : $codes[$lang];
    }
}



