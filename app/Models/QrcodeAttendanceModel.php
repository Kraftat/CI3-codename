<?php

namespace App\Models;

use CodeIgniter\Model;
class QrcodeAttendanceModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    public function getStudentDetailsByEid($enrollID = '')
    {
        $builder->select('s.first_name,s.last_name,s.register_no,s.email,s.photo,admission_date,birthday,enroll.student_id,enroll.branch_id,enroll.roll,class.name as class_name,section.name as section_name,student_category.name as cname');
        $builder->from('enroll');
        $builder->join('student as s', 's.id = enroll.student_id', 'inner');
        $builder->join('class', 'class.id = enroll.class_id', 'left');
        $builder->join('section', 'section.id = enroll.section_id', 'left');
        $builder->join('student_category', 'student_category.id = s.category_id', 'left');
        $builder->where('enroll.id', $enrollID);
        if (!is_superadmin_loggedin()) {
            $this->db->table('enroll.branch_id', get_loggedin_branch_id())->where();
        }
        $row = $builder->get()->row();
        return $row;
    }
    public function getSingleStaff($id = '')
    {
        $builder->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id,login_credential.active,login_credential.username, roles.name as role');
        $builder->from('staff');
        $builder->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "6" and login_credential.role != "7"', 'inner');
        $builder->join('roles', 'roles.id = login_credential.role', 'left');
        $builder->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $builder->join('staff_department', 'staff_department.id = staff.department', 'left');
        $builder->where('staff.id', $id);
        if (!is_superadmin_loggedin()) {
            $this->db->table('staff.branch_id', get_loggedin_branch_id())->where();
        }
        $query = $builder->get();
        return $query->row();
    }
    public function getStuListDT($postData)
    {
        $response = array();
        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length'];
        // Rows display per page
        $searchValue = $postData['search']['value'];
        // Search value
        // order
        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column'];
        // Column index
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'asc' : $postData['order'][0]['dir'];
        // asc or desc
        $column_order = array('`st`.`id`');
        $column_order[] = '`s`.`first_name`';
        $column_order[] = '`class`.`id`';
        $column_order[] = '`s`.`register_no`';
        $column_order[] = '`e`.`roll`';
        $column_order[] = '`st`.`date`';
        $column_order[] = '`st`.`in_time`';
        $column_order[] = '`st`.`out_time`';
        $search_arr = array();
        $searchQuery = "";
        if ($searchValue != '') {
            $search_arr[] = " (`s`.`register_no` like '%" . trim($searchValue) . "%' OR (`s`.`first_name` like '%" . trim($searchValue) . "%' OR `s`.`last_name` like '%" . trim($searchValue) . "%'))";
        }
        if (!is_superadmin_loggedin()) {
            $branch_id = $db->escape(get_loggedin_branch_id());
            $search_arr[] = " `st`.`branch_id` = {$branch_id} ";
        }
        if (count($search_arr) > 0) {
            $searchQuery = implode("AND", $search_arr);
        }
        $today = $db->escape(date('Y-m-d'));
        $sessionID = $db->escape(get_session_id());
        // Total number of records without filtering
        $branchID = $db->escape(get_loggedin_branch_id());
        $sql = "SELECT `st`.`id` FROM `student_attendance` as `st` INNER JOIN `enroll` as `e` ON `e`.`id` = `st`.`enroll_id` WHERE DATE(`st`.`date`) = {$today} AND `st`.`qr_code` = '1' AND `e`.`session_id` = {$sessionID}" . (!empty($branchID) ? " AND `st`.`branch_id` = {$branchID}" : '');
        $records = $db->query($sql)->result();
        $totalRecords = count($records);
        // Total number of record with filtering
        $sql = "SELECT `st`.`id` FROM `student_attendance` as `st` INNER JOIN `enroll` as `e` ON `e`.`id` = `st`.`enroll_id` INNER JOIN `student` as `s` ON `s`.`id` = `e`.`student_id` WHERE DATE(`st`.`date`) = {$today} AND `st`.`qr_code` = '1' AND `e`.`session_id` = {$sessionID}";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $db->query($sql)->result();
        $totalRecordwithFilter = count($records);
        // Fetch records
        $sql = "SELECT `e`.`id` as `enroll_id`, `e`.`roll`, `s`.`register_no`, CONCAT_WS(' ', `s`.`first_name`, `s`.`last_name`) as `fullname`, `class`.`name` as `class_name`, `section`.`name` as `section_name`, `s`.`register_no`, `st`.`date`, `st`.`in_time`, `st`.`out_time` FROM `student_attendance` as `st` INNER JOIN `enroll` as `e` ON `e`.`id` = `st`.`enroll_id` INNER JOIN `student` as `s` ON `s`.`id` = `e`.`student_id` LEFT JOIN `class` ON `class`.`id` = `e`.`class_id` LEFT JOIN `section` ON `section`.`id` = `e`.`section_id` WHERE `e`.`session_id` = {$sessionID} AND `st`.`qr_code` = '1' AND date(st.date) = {$today}";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY " . $column_order[$columnIndex] . " {$columnSortOrder} LIMIT {$start}, {$rowperpage}";
        $records = $db->query($sql)->result();
        $data = array();
        $count = $start + 1;
        foreach ($records as $record) {
            $row = array();
            $row[] = $count++;
            $row[] = $record->fullname;
            $row[] = $record->class_name . " (" . $record->section_name . ")";
            $row[] = $record->register_no;
            $row[] = empty($record->roll) ? '-' : $record->roll;
            $row[] = _d($record->date);
            $row[] = empty($record->in_time) ? '-' : date('h:i a', strtotime($record->in_time));
            $row[] = empty($record->out_time) ? '-' : date('h:i a', strtotime($record->out_time));
            $data[] = $row;
        }
        // Response
        $response = array("draw" => intval($draw), "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecordwithFilter, "data" => $data);
        return json_encode($response);
    }
    public function getStaffListDT($postData)
    {
        $response = array();
        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length'];
        // Rows display per page
        $searchValue = $postData['search']['value'];
        // Search value
        // order
        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column'];
        // Column index
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'asc' : $postData['order'][0]['dir'];
        // asc or desc
        $column_order = array('`st`.`id`');
        $column_order[] = '`s`.`name`';
        $column_order[] = '`class`.`id`';
        $column_order[] = '`role`';
        $column_order[] = '`st`.`date`';
        $column_order[] = '`st`.`in_time`';
        $column_order[] = '`st`.`out_time`';
        $search_arr = array();
        $searchQuery = "";
        if ($searchValue != '') {
            $search_arr[] = " (`s`.`name` like '%" . trim($searchValue) . "%' OR `s`.`staff_id` like '%" . trim($searchValue) . "%')";
        }
        if (!is_superadmin_loggedin()) {
            $branch_id = $db->escape(get_loggedin_branch_id());
            $search_arr[] = " `st`.`branch_id` = {$branch_id} ";
        }
        if (count($search_arr) > 0) {
            $searchQuery = implode("AND", $search_arr);
        }
        $today = $db->escape(date('Y-m-d'));
        $sessionID = $db->escape(get_session_id());
        // Total number of records without filtering
        $branchID = $db->escape(get_loggedin_branch_id());
        $sql = "SELECT `st`.`id` FROM `staff_attendance` as `st` WHERE DATE(`st`.`date`) = {$today} AND `st`.`qr_code` = '1'" . (!empty($branchID) ? " AND `st`.`branch_id` = {$branchID}" : '');
        $records = $db->query($sql)->result();
        $totalRecords = count($records);
        // Total number of record with filtering
        $sql = "SELECT `st`.`id` FROM `staff_attendance` as `st` INNER JOIN `staff` as `s` ON `s`.`id` = `st`.`staff_id` WHERE DATE(`st`.`date`) = {$today} AND `st`.`qr_code` = '1'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $db->query($sql)->result();
        $totalRecordwithFilter = count($records);
        // Fetch records
        $sql = "SELECT `s`.*,`st`.`date`,`st`.`in_time`,`st`.`out_time`, `roles`.`name` as `role` FROM `staff_attendance` as `st` INNER JOIN `staff` as `s` ON `s`.`id` = `st`.`staff_id` INNER JOIN `login_credential` ON `login_credential`.`user_id` = `s`.`id` and `login_credential`.`role` != '6' and `login_credential`.`role` != '7' LEFT JOIN `roles` ON `roles`.`id` = `login_credential`.`role` WHERE `st`.`qr_code` = '1' AND DATE(`st`.`date`) = {$today}";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY " . $column_order[$columnIndex] . " {$columnSortOrder} LIMIT {$start}, {$rowperpage}";
        $records = $db->query($sql)->result();
        $data = array();
        $count = $start + 1;
        foreach ($records as $record) {
            $row = array();
            $row[] = $count++;
            $row[] = $record->name;
            $row[] = $record->staff_id;
            $row[] = $record->role;
            $row[] = _d($record->date);
            $row[] = empty($record->in_time) ? '-' : date('h:i a', strtotime($record->in_time));
            $row[] = empty($record->out_time) ? '-' : date('h:i a', strtotime($record->out_time));
            $data[] = $row;
        }
        // Response
        $response = array("draw" => intval($draw), "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecordwithFilter, "data" => $data);
        return json_encode($response);
    }
    public function getDailyStudentReport($branchID = '', $classID = '', $sectionID = '', $date = '')
    {
        $builder->select('student_attendance.*,CONCAT_WS(" ",first_name, last_name) as fullname,register_no,roll,enroll.student_id');
        $builder->from('enroll');
        $builder->join('student_attendance', 'student_attendance.enroll_id = enroll.id', 'right');
        $builder->join('student', 'student.id = enroll.student_id', 'inner');
        $builder->where('enroll.class_id', $classID);
        $builder->where('enroll.section_id', $sectionID);
        $builder->where('student_attendance.qr_code', 1);
        $builder->where('student_attendance.date', $date);
        $builder->order_by('student_attendance.id', 'asc');
        return $builder->get()->getResult();
    }
    public function getDailyStaffReport($branchID = '', $staff_role = '', $date = '')
    {
        $builder->select('staff_attendance.*,staff.name,staff.staff_id as staffID,staff_department.name as department_name,roles.name as role_name');
        $builder->from('staff');
        $builder->join('staff_attendance', 'staff_attendance.staff_id = staff.id', 'right');
        $builder->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "6" and login_credential.role != "7"', 'inner');
        $builder->join('staff_department', 'staff_department.id = staff.department', 'left');
        $builder->join('roles', 'roles.id = login_credential.role', 'left');
        if ($staff_role != "") {
            $builder->where('login_credential.role', $staff_role);
        }
        $builder->where('staff_attendance.qr_code', 1);
        $builder->where('staff_attendance.date', $date);
        $builder->order_by('staff_attendance.id', 'asc');
        return $builder->get()->getResult();
    }
    public function getSettings($branchID = '')
    {
        $row = $db->table('qr_code_settings')->get('qr_code_settings')->row();
        $object = new stdClass();
        $object->confirmation_popup = !isset($row->confirmation_popup) ? 1 : $row->confirmation_popup;
        $object->auto_late_detect = !isset($row->auto_late_detect) ? 0 : $row->auto_late_detect;
        $object->camera = !isset($row->camera) ? 'environment' : $row->camera;
        $object->staff_in_time = !isset($row->staff_in_time) ? '00:00:00' : $row->staff_in_time;
        $object->staff_out_time = !isset($row->staff_out_time) ? '00:00:00' : $row->staff_out_time;
        $object->student_in_time = !isset($row->student_in_time) ? '00:00:00' : $row->student_in_time;
        $object->student_out_time = !isset($row->student_out_time) ? '00:00:00' : $row->student_out_time;
        return $object;
    }
}


