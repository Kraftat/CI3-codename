<?php

namespace App\Models;

use CodeIgniter\Model;
class EmployeeModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    // moderator employee all information
    public function save($data, $role = null, $id = null)
    {
        $inser_data1 = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['name'], 'sex' => $data['sex'], 'religion' => $data['religion'], 'blood_group' => $data['blood_group'], 'birthday' => $data["birthday"], 'mobileno' => $data['mobile_no'], 'present_address' => $data['present_address'], 'permanent_address' => $data['permanent_address'], 'photo' => $this->uploadImage('staff'), 'designation' => $data['designation_id'], 'department' => $data['department_id'], 'joining_date' => date("Y-m-d", strtotime($data['joining_date'])), 'qualification' => $data['qualification'], 'experience_details' => $data['experience_details'], 'total_experience' => $data['total_experience'], 'email' => $data['email'], 'facebook_url' => $data['facebook'], 'linkedin_url' => $data['linkedin'], 'twitter_url' => $data['twitter']);
        $inser_data2 = array('username' => $data["username"], 'role' => $data["user_role"]);
        if (!isset($data['staff_id']) && empty($data['staff_id'])) {
            // RANDOM STAFF ID GENERATE
            $inser_data1['staff_id'] = substr(app_generate_hash(), 3, 7);
            // SAVE EMPLOYEE INFORMATION IN THE DATABASE
            $builder->insert('staff', $inser_data1);
            $employeeID = $builder->insert_id();
            // SAVE EMPLOYEE LOGIN CREDENTIAL INFORMATION IN THE DATABASE
            $inser_data2['active'] = 1;
            $inser_data2['user_id'] = $employeeID;
            $inser_data2['password'] = $this->appLib->passHashed($data["password"]);
            $builder->insert('login_credential', $inser_data2);
            // SAVE USER BANK INFORMATION IN THE DATABASE
            if (!isset($data['chkskipped'])) {
                $data['staff_id'] = $employeeID;
                $this->bankSave($data);
            }
            return $employeeID;
        } else {
            $inser_data1['staff_id'] = $data['staff_id_no'];
            // UPDATE ALL INFORMATION IN THE DATABASE
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $data['staff_id']);
            $builder->update('staff', $inser_data1);
            // UPDATE LOGIN CREDENTIAL INFORMATION IN THE DATABASE
            $builder->where('user_id', $data['staff_id']);
            $this->db->where_not_in('role', array(6, 7));
            $builder->update('login_credential', $inser_data2);
        }
    }
    // GET SINGLE EMPLOYEE DETAILS
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
        if ($query->num_rows() == 0) {
            show_404();
        }
        return $query->row_array();
    }
    // get staff all list
    public function getStaffList($branchID = '', $role_id = '', $active = 1)
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
        $builder->where('login_credential.role', $role_id);
        $builder->where('login_credential.active', $active);
        $builder->order_by('staff.id', 'ASC');
        return $builder->get()->getResult();
    }
    public function get_schedule_by_id($id)
    {
        $builder->select('timetable_class.*,subject.name as subject_name,class.name as class_name,section.name as section_name');
        $builder->from('timetable_class');
        $builder->join('subject', 'subject.id = timetable_class.subject_id', 'inner');
        $builder->join('class', 'class.id = timetable_class.class_id', 'inner');
        $builder->join('section', 'section.id = timetable_class.section_id', 'inner');
        $builder->where('timetable_class.teacher_id', $id);
        $this->db->table('timetable_class.session_id', get_session_id())->where();
        return $builder->get();
    }
    public function bankSave($data)
    {
        $inser_data = array('staff_id' => $data['staff_id'], 'bank_name' => $data['bank_name'], 'holder_name' => $data['holder_name'], 'bank_branch' => $data['bank_branch'], 'bank_address' => $data['bank_address'], 'ifsc_code' => $data['ifsc_code'], 'account_no' => $data['account_no']);
        if (isset($data['bank_id'])) {
            $builder->where('id', $data['bank_id']);
            $builder->update('staff_bank_account', $inser_data);
        } else {
            $builder->insert('staff_bank_account', $inser_data);
        }
    }
    public function csvImport($row, $branchID, $userRole, $designationID, $departmentID)
    {
        $inser_data1 = array('name' => $row['Name'], 'sex' => $row['Gender'], 'religion' => $row['Religion'], 'blood_group' => $row['BloodGroup'], 'birthday' => date("Y-m-d", strtotime($row['DateOfBirth'])), 'joining_date' => date("Y-m-d", strtotime($row['JoiningDate'])), 'qualification' => $row['Qualification'], 'mobileno' => $row['MobileNo'], 'present_address' => $row['PresentAddress'], 'permanent_address' => $row['PermanentAddress'], 'email' => $row['Email'], 'designation' => $designationID, 'department' => $departmentID, 'branch_id' => $branchID, 'photo' => 'defualt.png');
        $inser_data2 = array('username' => $row["Email"], 'role' => $userRole);
        // RANDOM STAFF ID GENERATE
        $inser_data1['staff_id'] = substr(app_generate_hash(), 3, 7);
        // SAVE EMPLOYEE INFORMATION IN THE DATABASE
        $builder->insert('staff', $inser_data1);
        $employeeID = $builder->insert_id();
        // SAVE EMPLOYEE LOGIN CREDENTIAL INFORMATION IN THE DATABASE
        $inser_data2['active'] = 1;
        $inser_data2['user_id'] = $employeeID;
        $inser_data2['password'] = $this->appLib->passHashed($row["Password"]);
        $builder->insert('login_credential', $inser_data2);
        return true;
    }
}



