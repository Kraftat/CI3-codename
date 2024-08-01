<?php

namespace App\Models;

use CodeIgniter\Model;
class UserroleModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    public function getTeachersList($branchID = '')
    {
        $builder->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id, roles.name as role');
        $builder->from('staff');
        $builder->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "6" and login_credential.role != "7"', 'inner');
        $builder->join('roles', 'roles.id = login_credential.role', 'left');
        $builder->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $builder->join('staff_department', 'staff_department.id = staff.department', 'left');
        if ($branchID != "") {
            $builder->where('staff.branch_id', $branchID);
        }
        $builder->where('login_credential.role', 3);
        $builder->where('login_credential.active', 1);
        $builder->order_by('staff.id', 'ASC');
        return $builder->get()->getResult();
    }
    // get route information by route id and vehicle id
    public function getRouteDetails($routeID, $vehicleID)
    {
        $builder->select('ta.route_id,ta.stoppage_id,ta.vehicle_id,r.name as route_name,r.start_place,r.stop_place,sp.stop_position,sp.stop_time,sp.route_fare,v.vehicle_no,v.driver_name,v.driver_phone');
        $builder->from('transport_assign as ta');
        $builder->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $builder->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $builder->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $builder->where('ta.route_id', $routeID);
        $builder->where('ta.vehicle_id', $vehicleID);
        return $builder->get()->row_array();
    }
    public function getAssignList($branch_id = '')
    {
        $builder->select('ta.route_id,ta.stoppage_id,ta.branch_id,r.name,r.start_place,r.stop_place,sp.stop_position,sp.stop_time,sp.route_fare');
        $builder->from('transport_assign as ta');
        $builder->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $builder->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $this->db->group_by(array('ta.route_id', 'ta.stoppage_id', 'ta.branch_id'));
        if (!empty($branch_id)) {
            $builder->where('ta.branch_id', $branch_id);
        }
        return $builder->get()->result_array();
    }
    // get vehicle list by route_id
    public function getVehicleList($route_id)
    {
        $builder->select('ta.vehicle_id,v.vehicle_no');
        $builder->from('transport_assign as ta');
        $builder->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $builder->where('ta.route_id', $route_id);
        $vehicles = $builder->get()->getResult();
        $name_list = '';
        foreach ($vehicles as $row) {
            $name_list .= '- ' . $row->vehicle_no . '<br>';
        }
        return $name_list;
    }
    // get hostel information by hostel id and room id
    public function getHostelDetails($hostelID, $roomID)
    {
        $builder->select('h.name as hostel_name,h.watchman,h.category_id,h.address,hc.name as hcategory_name,rc.name as rcategory_name,hr.name as room_name,hr.no_beds,hr.bed_fee');
        $builder->from('hostel as h');
        $builder->join('hostel_category as hc', 'hc.id = h.category_id', 'left');
        $builder->join('hostel_room as hr', 'hr.hostel_id = h.id', 'left');
        $builder->join('hostel_category as rc', 'rc.id = hr.category_id', 'left');
        $builder->where('hr.id', $roomID);
        $builder->where('h.id', $hostelID);
        return $builder->get()->row();
    }
    // check attendance by staff id and date
    public function get_attendance_by_date($enroll_id, $date)
    {
        $sql = "SELECT `student_attendance`.* FROM `student_attendance` WHERE `enroll_id` = " . $db->escape($enroll_id) . " AND `date` = " . $db->escape($date);
        return $db->query($sql)->row_array();
    }
    public function getStudentDetails()
    {
        $sessionID = get_session_id();
        if (is_student_loggedin()) {
            $enrollID = $this->session->userdata('enrollID');
        } elseif (is_parent_loggedin()) {
            $enrollID = $this->session->userdata('enrollID');
        }
        $builder->select('CONCAT_WS(" ",s.first_name, s.last_name) as fullname,s.email as student_email,s.register_no,e.branch_id,e.id as enroll_id,e.student_id,s.hostel_id,s.room_id,s.route_id,s.vehicle_id,e.class_id,e.section_id,c.name as class_name,se.name as section_name,b.school_name,b.email as school_email,b.mobileno as school_mobileno,b.address as school_address');
        $builder->from('enroll as e');
        $builder->join('student as s', 's.id = e.student_id', 'inner');
        $builder->join('branch as b', 'b.id = e.branch_id', 'left');
        $builder->join('class as c', 'c.id = e.class_id', 'left');
        $builder->join('section as se', 'se.id = e.section_id', 'left');
        $builder->where('e.id', $enrollID);
        $builder->where('e.session_id', $sessionID);
        return $builder->get()->row_array();
    }
    public function getHomeworkList($enrollID = '')
    {
        $builder->select('homework.*,CONCAT_WS(" ",s.first_name, s.last_name) as fullname,s.register_no,e.student_id, e.roll,subject.name as subject_name,class.name as class_name,section.name as section_name,he.id as ev_id,he.status as ev_status,he.remark as ev_remarks,he.rank,hs.message,hs.enc_name,hs.file_name');
        $builder->from('homework');
        $builder->join('enroll as e', 'e.class_id=homework.class_id and e.section_id = homework.section_id and e.session_id = homework.session_id', 'inner');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('homework_evaluation as he', 'he.homework_id = homework.id and he.student_id = e.student_id', 'left');
        $builder->join('subject', 'subject.id = homework.subject_id', 'left');
        $builder->join('homework_submit as hs', 'hs.homework_id = homework.id and hs.student_id = e.student_id', 'left');
        $builder->join('class', 'class.id = homework.class_id', 'left');
        $builder->join('section', 'section.id = homework.section_id', 'left');
        $builder->where('e.id', $enrollID);
        $builder->where('homework.status', 0);
        $this->db->table('homework.session_id', get_session_id())->where();
        $builder->group_by('homework.id');
        $builder->order_by('homework.id', 'desc');
        return $builder->get()->result_array();
    }
    public function getUserDetails()
    {
        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
            $builder->select('*,CONCAT_WS(" ",first_name, last_name) as name, current_address as address');
            $builder->from('student');
        } elseif (is_parent_loggedin()) {
            $builder->select('*');
            $builder->from('parent');
        }
        $this->db->table('id', get_loggedin_user_id())->where();
        return $builder->get()->row_array();
    }
    public function examListDT($postData, $currency_symbol = '')
    {
        $response = array();
        $sessionID = get_session_id();
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
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'DESC' : $postData['order'][0]['dir'];
        // asc or desc
        $column_order = array('`online_exam`.`id`');
        $search_arr = array();
        $searchQuery = "";
        if ($searchValue != '') {
            $search_arr[] = " (`online_exam`.`title` like '%" . $searchValue . "%' OR `online_exam`.`exam_start` like '%" . $searchValue . "%' OR `online_exam`.`exam_end` like '%" . $searchValue . "%') ";
        }
        $enrollID = $this->session->userdata('enrollID');
        $enroll = $db->table('enroll')->get('enroll')->row();
        $branch_id = $db->escape(get_loggedin_branch_id());
        $search_arr[] = " `online_exam`.`branch_id` = {$branch_id} AND `online_exam`.`class_id` = " . $db->escape($enroll->class_id);
        // order
        $column_order[] = '`online_exam`.`title`';
        $column_order[] = '`class`.`id`';
        $column_order[] = '';
        $column_order[] = '`questions_qty`';
        $column_order[] = '`online_exam`.`exam_start`';
        $column_order[] = '`online_exam`.`exam_end`';
        $column_order[] = '`online_exam`.`duration`';
        if (count($search_arr) > 0) {
            $searchQuery = implode(" AND ", $search_arr);
        }
        // Total number of records without filtering
        $totalRecords = 0;
        // Total number of record with filtering
        $sql = "SELECT `section_id` FROM `online_exam` WHERE `publish_status` = '1'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $db->query($sql)->result();
        $count = 0;
        foreach ($records as $key => $value) {
            $array = json_decode($value->section_id, true);
            if (in_array($enroll->section_id, $array)) {
                $count++;
            }
        }
        $totalRecordwithFilter = $count;
        // Fetch records
        $studentID = $db->escape(get_loggedin_user_id());
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`, (SELECT COUNT(`id`) FROM `online_exam_payment` WHERE `online_exam_payment`.`exam_id`=`online_exam`.`id` AND `online_exam_payment`.`student_id`= {$studentID}) as `payment_status`,`branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `publish_status` = '1'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY " . $column_order[$columnIndex] . " {$columnSortOrder} LIMIT {$start}, {$rowperpage}";
        $records = $db->query($sql)->result();
        $data = array();
        $count = $start + 1;
        foreach ($records as $record) {
            $array = json_decode($record->section_id, true);
            if (in_array($enroll->section_id, $array)) {
                $startTime = strtotime($record->exam_start);
                $endTime = strtotime($record->exam_end);
                $now = strtotime("now");
                $examSubmitted = $this->onlineexamModel->getStudentSubmitted($record->id);
                $status = '';
                $labelmode = '';
                $takeExam = 0;
                // exam status
                if ($record->publish_result == 1 && !empty($examSubmitted)) {
                    $status = translate('result_published');
                    $labelmode = 'label-success-custom';
                } else if (!empty($examSubmitted)) {
                    $status = '<i class="far fa-check fa-fw"></i> ' . translate('already_submitted');
                    $labelmode = 'label-success-custom';
                } elseif ($startTime <= $now && $now <= $endTime) {
                    $status = translate('live');
                    $labelmode = 'label-warning-custom';
                    $takeExam = 1;
                } elseif ($startTime >= $now && $now <= $endTime) {
                    $status = '<i class="far fa-clock"></i> ' . translate('waiting');
                    $labelmode = 'label-info-custom';
                } elseif ($now >= $endTime) {
                    $status = translate('closed');
                    $labelmode = 'label-danger-custom';
                }
                $row = array();
                $action = "";
                $paymentStatus = 0;
                if ($record->exam_type == 1 && $record->payment_status == 0) {
                    $paymentStatus = 1;
                }
                if ($takeExam == 1) {
                    $url = base_url('userrole/onlineexam_take/' . $record->id);
                    if ($paymentStatus == 1) {
                        $action .= '<a href="javascript:void(0);" onclick="paymentModal(' . $db->escape($record->id) . ')" class="btn btn-circle btn-default"> <i class="far fa-credit-card"></i> ' . translate('pay') . " & " . translate('take_exam') . '</a>';
                    } else {
                        $action .= '<a href="' . $url . '" class="btn btn-circle btn-default"> <i class="far fa-users-between-lines"></i> ' . translate('take_exam') . '</a>';
                    }
                } else if ($record->publish_result == 1 && !empty($examSubmitted)) {
                    $action .= '<a href="javascript:void(0);" onclick="getStudentResult(' . $db->escape($record->id) . ')" class="btn btn-circle btn-default"> <i class="far fa-users-viewfinder"></i> ' . translate('view') . " " . translate('result') . '</a>';
                } else {
                    $action .= '<a href="javascript:void(0);" disabled class="btn btn-circle btn-default"> <i class="far fa-users-between-lines"></i> ' . translate('take_exam') . '</a>';
                }
                $row[] = $count++;
                $row[] = $record->title;
                $row[] = $record->class_name . " (" . $this->onlineexamModel->getSectionDetails($record->section_id) . ")";
                $row[] = $this->onlineexamModel->getSubjectDetails($record->subject_id);
                $row[] = $record->questions_qty;
                $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
                $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
                $row[] = $record->duration;
                $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;
                $row[] = "<span class='label " . $labelmode . " '>" . $status . "</span>";
                $row[] = $action;
                $data[] = $row;
            }
        }
        // Response
        $response = array("draw" => intval($draw), "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecordwithFilter, "data" => $data);
        return json_encode($response);
    }
    public function getExamDetails($onlineexamID)
    {
        $student = $this->getStudentDetails();
        $classID = $student['class_id'];
        $sectionID = $student['section_id'];
        $onlineexamID = $db->escape($onlineexamID);
        $sessionID = $db->escape(get_session_id());
        $branchID = $db->escape(get_loggedin_branch_id());
        $studentID = $db->escape(get_loggedin_user_id());
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`,(SELECT COUNT(`id`) FROM `online_exam_payment` WHERE `online_exam_payment`.`exam_id`=`online_exam`.`id` AND `online_exam_payment`.`student_id`={$studentID}) as `payment_status`, `branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `online_exam`.`session_id` = {$sessionID} AND `online_exam`.`publish_status` = '1' AND `online_exam`.`id` = {$onlineexamID} AND `online_exam`.`branch_id` = {$branchID} AND `online_exam`.`class_id` = {$classID}";
        $records = $db->query($sql)->row();
        $sectionList = json_decode($records->section_id, true);
        if (in_array($sectionID, $sectionList)) {
            return $records;
        } else {
            return [];
        }
    }
    public function getOfflinePaymentsList($where = array(), $single = false)
    {
        $student = $this->getStudentDetails();
        $builder->select('op.*,CONCAT_WS(" ",student.first_name, student.last_name) as fullname,student.email,student.mobileno,student.register_no,class.name as class_name,section.name as section_name,branch.name as branchname');
        $builder->from('offline_fees_payments as op');
        $builder->join('enroll', 'enroll.id = op.student_enroll_id', 'left');
        $builder->join('branch', 'branch.id = enroll.branch_id', 'left');
        $builder->join('student', 'student.id = enroll.student_id', 'left');
        $builder->join('class', 'class.id = enroll.class_id', 'left');
        $builder->join('section', 'section.id = enroll.section_id', 'left');
        $builder->where('op.student_enroll_id', $student['enroll_id']);
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
    public function getOfflinePaymentsConfig()
    {
        $branchID = get_loggedin_branch_id();
        $row = $db->table('branch')->get('branch')->row()->offline_payments;
        return $row;
    }
}



