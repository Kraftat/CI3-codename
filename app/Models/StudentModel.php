<?php

namespace App\Models;

use CodeIgniter\Model;
class StudentModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    // moderator student all information
    public function save($data = array(), $getBranch = array())
    {
        $hostelID = empty($data['hostel_id']) ? 0 : $data['hostel_id'];
        $roomID = empty($data['room_id']) ? 0 : $data['room_id'];
        $previous_details = array('school_name' => $this->request->getPost('school_name'), 'qualification' => $this->request->getPost('qualification'), 'remarks' => $this->request->getPost('previous_remarks'));
        if (empty($previous_details)) {
            $previous_details = "";
        } else {
            $previous_details = json_encode($previous_details);
        }
        $inser_data1 = array('register_no' => $this->request->getPost('register_no'), 'admission_date' => !empty($data['admission_date']) ? date("Y-m-d", strtotime($data['admission_date'])) : "", 'first_name' => $this->request->getPost('first_name'), 'last_name' => $this->request->getPost('last_name'), 'gender' => $this->request->getPost('gender'), 'birthday' => !empty($data['birthday']) ? date("Y-m-d", strtotime($data['birthday'])) : "", 'religion' => $this->request->getPost('religion'), 'caste' => $this->request->getPost('caste'), 'blood_group' => $this->request->getPost('blood_group'), 'mother_tongue' => $this->request->getPost('mother_tongue'), 'current_address' => $this->request->getPost('current_address'), 'permanent_address' => $this->request->getPost('permanent_address'), 'city' => $this->request->getPost('city'), 'state' => $this->request->getPost('state'), 'mobileno' => $this->request->getPost('mobileno'), 'category_id' => isset($data['category_id']) ? $data['category_id'] : 0, 'email' => $this->request->getPost('email'), 'parent_id' => $this->request->getPost('parent_id'), 'route_id' => empty($this->request->getPost('route_id')) ? 0 : $this->request->getPost('route_id'), 'vehicle_id' => empty($this->request->getPost('vehicle_id')) ? 0 : $this->request->getPost('vehicle_id'), 'hostel_id' => $hostelID, 'room_id' => $roomID, 'previous_details' => $previous_details, 'photo' => $this->uploadImage('student'));
        // moderator guardian all information
        if (!isset($data['student_id']) && empty($data['student_id'])) {
            if (!isset($data['guardian_chk'])) {
                // add new guardian all information in db
                if (!empty($data['grd_name']) || !empty($data['father_name'])) {
                    $arrayParent = array('name' => $this->request->getPost('grd_name'), 'relation' => $this->request->getPost('grd_relation'), 'father_name' => $this->request->getPost('father_name'), 'mother_name' => $this->request->getPost('mother_name'), 'occupation' => $this->request->getPost('grd_occupation'), 'income' => $this->request->getPost('grd_income'), 'education' => $this->request->getPost('grd_education'), 'email' => $this->request->getPost('grd_email'), 'mobileno' => $this->request->getPost('grd_mobileno'), 'address' => $this->request->getPost('grd_address'), 'city' => $this->request->getPost('grd_city'), 'state' => $this->request->getPost('grd_state'), 'branch_id' => $this->applicationModel->get_branch_id(), 'photo' => $this->uploadImage('parent', 'guardian_photo'));
                    $builder->insert('parent', $arrayParent);
                    $parentID = $builder->insert_id();
                    // save guardian login credential information in the database
                    if ($getBranch['grd_generate'] == 1) {
                        $grd_username = $getBranch['grd_username_prefix'] . $parentID;
                        $grd_password = $getBranch['grd_default_password'];
                    } else {
                        $grd_username = $data['grd_username'];
                        $grd_password = $data['grd_password'];
                    }
                    $parent_credential = array('username' => $grd_username, 'role' => 6, 'user_id' => $parentID, 'password' => $this->appLib->passHashed($grd_password));
                    $builder->insert('login_credential', $parent_credential);
                } else {
                    $parentID = 0;
                }
            } else {
                $parentID = $data['parent_id'];
            }
            $inser_data1['parent_id'] = $parentID;
            // insert student all information in the database
            $builder->insert('student', $inser_data1);
            $student_id = $builder->insert_id();
            // save student login credential information in the database
            if ($getBranch['stu_generate'] == 1) {
                $stu_username = $getBranch['stu_username_prefix'] . $student_id;
                $stu_password = $getBranch['stu_default_password'];
            } else {
                $stu_username = $data['username'];
                $stu_password = $data['password'];
            }
            $inser_data2 = array('user_id' => $student_id, 'username' => $stu_username, 'role' => 7, 'password' => $this->appLib->passHashed($stu_password));
            $builder->insert('login_credential', $inser_data2);
            // return student information
            $studentData = array('student_id' => $student_id, 'email' => $this->request->getPost('email'), 'username' => $stu_username, 'password' => $stu_password);
            if (!empty($data['grd_name']) || !empty($data['father_name'])) {
                // send parent account activate email
                $emailData = array('name' => $this->request->getPost('grd_name'), 'username' => $grd_username, 'password' => $grd_password, 'user_role' => 6, 'email' => $this->request->getPost('grd_email'));
                $this->emailModel->sentStaffRegisteredAccount($emailData);
            }
            return $studentData;
        } else {
            // update student all information in the database
            $inser_data1['parent_id'] = $data['parent_id'];
            $builder->where('id', $data['student_id']);
            $builder->update('student', $inser_data1);
            // update login credential information in the database
            $builder->where('user_id', $data['student_id']);
            $builder->where('role', 7);
            $this->db->table('login_credential', array('username' => $data['username']))->update();
        }
    }
    public function csvImport($row = array(), $classID = '', $sectionID = '', $branchID = '')
    {
        // getting existing father data
        if ($row['GuardianUsername'] !== '') {
            $getParent = $builder->select('parent.id')->from('login_credential')->join('parent', 'parent.id = login_credential.user_id', 'left')->where(array('parent.branch_id' => $branchID, 'login_credential.username' => $row['GuardianUsername']))->get()->row_array();
        }
        // getting branch settings
        $getSettings = $builder->select('*')->where('id', $branchID)->from('branch')->get()->row_array();
        if (isset($getParent) && count($getParent)) {
            $parentID = $getParent['id'];
        } else {
            // add new guardian all information in db
            $arrayParent = array('name' => $row['GuardianName'], 'relation' => $row['GuardianRelation'], 'father_name' => $row['FatherName'], 'mother_name' => $row['MotherName'], 'occupation' => $row['GuardianOccupation'], 'mobileno' => $row['GuardianMobileNo'], 'address' => $row['GuardianAddress'], 'email' => $row['GuardianEmail'], 'branch_id' => $branchID, 'photo' => 'defualt.png');
            $builder->insert('parent', $arrayParent);
            $parentID = $builder->insert_id();
            // save guardian login credential information in the database
            if ($getSettings['grd_generate'] == 1) {
                $grd_username = $getSettings['grd_username_prefix'] . $parentID;
                $grd_password = $getSettings['grd_default_password'];
            } else {
                $grd_username = $row['GuardianUsername'];
                $grd_password = $row['GuardianPassword'];
            }
            $parent_credential = array('username' => $grd_username, 'role' => 6, 'user_id' => $parentID, 'password' => $this->appLib->passHashed($grd_password));
            $builder->insert('login_credential', $parent_credential);
        }
        $inser_data1 = array('first_name' => $row['FirstName'], 'last_name' => $row['LastName'], 'blood_group' => $row['BloodGroup'], 'gender' => $row['Gender'], 'birthday' => date("Y-m-d", strtotime($row['Birthday'])), 'mother_tongue' => $row['MotherTongue'], 'religion' => $row['Religion'], 'parent_id' => $parentID, 'caste' => $row['Caste'], 'mobileno' => $row['Phone'], 'city' => $row['City'], 'state' => $row['State'], 'current_address' => $row['PresentAddress'], 'permanent_address' => $row['PermanentAddress'], 'category_id' => $row['CategoryID'], 'admission_date' => date("Y-m-d", strtotime($row['AdmissionDate'])), 'register_no' => $row['RegisterNo'], 'photo' => 'defualt.png', 'email' => $row['StudentEmail']);
        //save all student information in the database file
        $builder->insert('student', $inser_data1);
        $studentID = $builder->insert_id();
        // save student login credential information in the database
        if ($getSettings['stu_generate'] == 1) {
            $stu_username = $getSettings['stu_username_prefix'] . $studentID;
            $stu_password = $getSettings['stu_default_password'];
        } else {
            $stu_username = $row['StudentUsername'];
            $stu_password = $row['StudentPassword'];
        }
        //save student login credential
        $inser_data2 = array('username' => $stu_username, 'role' => 7, 'user_id' => $studentID, 'password' => $this->appLib->passHashed($stu_password));
        $builder->insert('login_credential', $inser_data2);
        //save student enroll information in the database file
        $arrayEnroll = array('student_id' => $studentID, 'class_id' => $classID, 'section_id' => $sectionID, 'branch_id' => $branchID, 'roll' => $row['Roll'], 'session_id' => get_session_id());
        $builder->insert('enroll', $arrayEnroll);
    }
    public function getFeeProgress($id)
    {
        $builder->select('IFNULL(SUM(gd.amount), 0) as totalfees,IFNULL(SUM(p.amount), 0) as totalpay,IFNULL(SUM(p.discount),0) as totaldiscount');
        $builder->from('fee_allocation as a');
        $builder->join('fee_groups_details as gd', 'gd.fee_groups_id = a.group_id', 'inner');
        $builder->join('fee_payment_history as p', 'p.allocation_id = a.id and p.type_id = gd.fee_type_id', 'left');
        $builder->where('a.student_id', $id);
        $this->db->table('a.session_id', get_session_id())->where();
        $r = $builder->get()->row_array();
        $total_amount = floatval($r['totalfees']);
        $total_paid = floatval($r['totalpay'] + $r['totaldiscount']);
        if ($total_paid != 0) {
            $percentage = $total_paid / $total_amount * 100;
            return number_format($percentage);
        } else {
            return 0;
        }
    }
    public function getStudentList($classID = '', $sectionID = '', $branchID = '', $deactivate = false, $start = '', $end = '')
    {
        $builder->select('e.*,s.photo, CONCAT_WS(" ", s.first_name, s.last_name) as fullname,s.register_no,s.gender,s.admission_date,s.parent_id,s.email,s.blood_group,s.birthday,l.active,c.name as class_name,se.name as section_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('login_credential as l', 'l.user_id = s.id and l.role = 7', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id=se.id', 'left');
        if (!empty($classID)) {
            $builder->where('e.class_id', $classID);
        }
        if (!empty($start) && !empty($end)) {
            $builder->where('s.admission_date >=', $start);
            $builder->where('s.admission_date <=', $end);
        }
        $builder->where('e.branch_id', $branchID);
        $this->db->table('e.session_id', get_session_id())->where();
        $builder->order_by('s.id', 'ASC');
        if ($sectionID != 'all' && !empty($sectionID)) {
            $builder->where('e.section_id', $sectionID);
        }
        if ($deactivate == true) {
            $builder->where('l.active', 0);
        }
        return $builder->get();
    }
    public function getSearchStudentList($search_text)
    {
        $builder->select('e.*,s.photo,s.first_name,s.last_name,s.register_no,s.parent_id,s.email,s.blood_group,s.birthday,c.name as class_name,se.name as section_name,sp.name as parent_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'left');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id=se.id', 'left');
        $builder->join('parent as sp', 'sp.id = s.parent_id', 'left');
        $this->db->table('e.session_id', get_session_id())->where();
        if (!is_superadmin_loggedin()) {
            $this->db->table('e.branch_id', get_loggedin_branch_id())->where();
        }
        $builder->group_start();
        $builder->like('s.first_name', $search_text);
        $builder->or_like('s.last_name', $search_text);
        $builder->or_like('s.register_no', $search_text);
        $builder->or_like('s.email', $search_text);
        $builder->or_like('e.roll', $search_text);
        $builder->or_like('s.blood_group', $search_text);
        $builder->or_like('sp.name', $search_text);
        $builder->group_end();
        $builder->order_by('s.id', 'desc');
        return $builder->get();
    }
    public function getSingleStudent($id = '', $enroll = false)
    {
        $builder->select('s.*,l.username,l.active,e.class_id,e.section_id,e.id as enrollid,e.roll,e.branch_id,e.session_id,c.name as class_name,se.name as section_name,sc.name as category_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'left');
        $builder->join('login_credential as l', 'l.user_id = s.id and l.role = 7', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id = se.id', 'left');
        $builder->join('student_category as sc', 's.category_id=sc.id', 'left');
        if ($enroll == true) {
            $builder->where('e.id', $id);
        } else {
            $builder->where('s.id', $id);
        }
        $this->db->table('e.session_id', get_session_id())->where();
        if (!is_superadmin_loggedin()) {
            $this->db->table('e.branch_id', get_loggedin_branch_id())->where();
        }
        $query = $builder->get();
        if ($query->num_rows() == 0) {
            show_404();
        }
        return $query->row_array();
    }
    public function regSerNumber($school_id = '')
    {
        $registerNoPrefix = '';
        if (!empty($school_id)) {
            $schoolconfig = $db->table('branch')->get('branch')->row();
            if ($schoolconfig->reg_prefix_enable == 1) {
                $registerNoPrefix = $schoolconfig->institution_code . $schoolconfig->reg_start_from;
                $last_registerNo = $this->appLib->studentLastRegID($school_id);
                if (!empty($last_registerNo)) {
                    $last_registerNo_digit = str_replace($schoolconfig->institution_code, "", $last_registerNo->register_no);
                    if (!is_numeric($last_registerNo_digit)) {
                        $last_registerNo_digit = $schoolconfig->reg_start_from;
                    } else {
                        $last_registerNo_digit = $last_registerNo_digit + 1;
                    }
                    $registerNoPrefix = $schoolconfig->institution_code . sprintf("%0" . $schoolconfig->reg_prefix_digit . "d", $last_registerNo_digit);
                } else {
                    $registerNoPrefix = $schoolconfig->institution_code . sprintf("%0" . $schoolconfig->reg_prefix_digit . "d", $schoolconfig->reg_start_from);
                }
            }
            return $registerNoPrefix;
        } else {
            $config = $db->table('global_settings')->get('global_settings')->row();
            if ($config->reg_prefix == 'on') {
                $prefix = $config->institution_code;
            }
            $result = $builder->select("max(id) as id")->get('student')->row_array();
            $id = $result["id"];
            if (!empty($id)) {
                $maxNum = str_pad($id + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $maxNum = '00001';
            }
            return $prefix . $maxNum;
        }
    }
    public function getDisableReason($student_id = '')
    {
        $builder->select("rd.*,disable_reason.name as reason");
        $builder->from('disable_reason_details as rd');
        $builder->join('disable_reason', 'disable_reason.id = rd.reason_id', 'left');
        $builder->where('student_id', $student_id);
        $builder->order_by('rd.id', 'DESC');
        $builder->limit(1);
        $row = $builder->get()->row();
        return $row;
    }
    public function getSiblingList($parent_id = '', $student_id = '')
    {
        $builder->select('s.photo, s.register_no, CONCAT_WS(" ",s.first_name, s.last_name) as fullname,s.gender,s.mobileno,e.roll,e.branch_id,c.name as class_name,se.name as section_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id = se.id', 'left');
        $builder->where_not_in('s.id', $student_id);
        $builder->where('s.parent_id', $parent_id);
        $builder->order_by('s.id', 'ASC');
        $query = $builder->get();
        return $query->getResult();
    }
    public function getParentList($class_id = '', $section_id = '', $branch_id = '')
    {
        $builder->select('p.name as g_name,p.father_name,p.mother_name,p.occupation,count(s.parent_id) as child,p.mobileno,s.parent_id');
        $builder->from('student as s');
        $builder->join('enroll as e', 'e.student_id = s.id', 'inner');
        $builder->join('parent as p', 'p.id = s.parent_id', 'inner');
        $builder->where('e.class_id', $class_id);
        if ($section_id != 'all') {
            $builder->where('e.section_id', $section_id);
        }
        $builder->where('e.branch_id', $branch_id);
        $this->db->table('e.session_id', get_session_id())->where();
        $builder->order_by('s.id', 'ASC');
        $builder->group_by('p.id');
        $query = $builder->get();
        return $query->getResultArray();
    }
    public function getSiblingListByClass($parent_id = '', $class_id = '', $section_id = '')
    {
        $builder->select('s.register_no,e.id as enroll_id,CONCAT_WS(" ",s.first_name, s.last_name) as fullname,s.gender,c.name as class_name,se.name as section_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id = se.id', 'left');
        $builder->where('e.class_id', $class_id);
        if ($section_id != 'all') {
            $builder->where('e.section_id', $section_id);
        }
        $this->db->table('e.session_id', get_session_id())->where();
        $builder->where('s.parent_id', $parent_id);
        $builder->order_by('s.id', 'ASC');
        $query = $builder->get();
        return $query->getResult();
    }
}



