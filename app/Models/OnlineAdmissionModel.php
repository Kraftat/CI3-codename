<?php

namespace App\Models;

use CodeIgniter\Model;
class OnlineAdmissionModel extends MYModel
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
        $existStudent_photo = $this->request->getPost('exist_student_photo');
        $existGuardian_photo = $this->request->getPost('exist_guardian_photo');
        if (empty($existStudent_photo)) {
            $studentPhoto = $this->uploadImage('student', 'student_photo');
        } else {
            $studentPhoto = $existStudent_photo;
        }
        if (empty($existGuardian_photo)) {
            $guardianPhoto = $this->uploadImage('parent', 'guardian_photo');
        } else {
            $guardianPhoto = $existGuardian_photo;
        }
        $hostelID = empty($data['hostel_id']) ? 0 : $data['hostel_id'];
        $roomID = empty($data['room_id']) ? 0 : $data['room_id'];
        $previous_details = array('school_name' => $this->request->getPost('school_name'), 'qualification' => $this->request->getPost('qualification'), 'remarks' => $this->request->getPost('previous_remarks'));
        if (empty($previous_details)) {
            $previous_details = "";
        } else {
            $previous_details = json_encode($previous_details);
        }
        $inser_data1 = array('register_no' => $this->request->getPost('register_no'), 'admission_date' => isset($data['admission_date']) ? date("Y-m-d", strtotime($data['admission_date'])) : "", 'first_name' => $this->request->getPost('first_name'), 'last_name' => $this->request->getPost('last_name'), 'gender' => $this->request->getPost('gender'), 'birthday' => isset($data['birthday']) ? date("Y-m-d", strtotime($data['birthday'])) : "", 'religion' => $this->request->getPost('religion'), 'caste' => $this->request->getPost('caste'), 'blood_group' => $this->request->getPost('blood_group'), 'mother_tongue' => $this->request->getPost('mother_tongue'), 'current_address' => $this->request->getPost('current_address'), 'permanent_address' => $this->request->getPost('permanent_address'), 'city' => $this->request->getPost('city'), 'state' => $this->request->getPost('state'), 'mobileno' => $this->request->getPost('mobileno'), 'category_id' => isset($data['category_id']) ? $data['category_id'] : 0, 'email' => $this->request->getPost('email'), 'parent_id' => "", 'route_id' => $this->request->getPost('route_id'), 'vehicle_id' => $this->request->getPost('vehicle_id'), 'hostel_id' => $hostelID, 'room_id' => $roomID, 'previous_details' => $previous_details, 'photo' => $studentPhoto);
        // add new guardian all information in db
        if (!empty($data['grd_name']) || !empty($data['father_name'])) {
            $arrayParent = array('name' => $this->request->getPost('grd_name'), 'relation' => $this->request->getPost('grd_relation'), 'father_name' => $this->request->getPost('father_name'), 'mother_name' => $this->request->getPost('mother_name'), 'occupation' => $this->request->getPost('grd_occupation'), 'income' => $this->request->getPost('grd_income'), 'education' => $this->request->getPost('grd_education'), 'email' => $this->request->getPost('grd_email'), 'mobileno' => $this->request->getPost('grd_mobileno'), 'address' => $this->request->getPost('grd_address'), 'city' => $this->request->getPost('grd_city'), 'state' => $this->request->getPost('grd_state'), 'branch_id' => $getBranch['id'], 'photo' => $guardianPhoto);
            $builder->insert('parent', $arrayParent);
            $parentID = $builder->insert_id();
            // save guardian login credential information in the database
            if ($getBranch['grd_generate'] == 1) {
                $grd_username = $getBranch['grd_username_prefix'] . $parentID;
                $grd_password = $getBranch['grd_default_password'];
            } else {
                $grd_username = $this->request->getPost('grd_username');
                $grd_password = $this->request->getPost('grd_password');
            }
            $parent_credential = array('username' => $grd_username, 'role' => 6, 'user_id' => $parentID, 'password' => $this->appLib->passHashed($grd_password));
            $builder->insert('login_credential', $parent_credential);
            // insert student all information in the database
            $inser_data1['parent_id'] = $parentID;
        } else {
            $inser_data1['parent_id'] = 0;
        }
        $builder->insert('student', $inser_data1);
        $student_id = $builder->insert_id();
        // save student login credential information in the database
        if ($getBranch['stu_generate'] == 1) {
            $stu_username = $getBranch['stu_username_prefix'] . $student_id;
            $stu_password = $getBranch['stu_default_password'];
        } else {
            $stu_username = $this->request->getPost('username');
            $stu_password = $this->request->getPost('password');
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
    }
    public function getOnlineAdmission($class_id = '', $branch_id = '')
    {
        $builder->select('oa.*,c.name as class_name,se.name as section_name');
        $builder->from('online_admission as oa');
        $builder->join('class as c', 'oa.class_id = c.id', 'left');
        $builder->join('section as se', 'oa.section_id = se.id', 'left');
        $builder->where('oa.class_id', $class_id);
        $builder->where('oa.branch_id', $branch_id);
        $builder->order_by('oa.id', 'ASC');
        $query = $builder->get();
        return $query->getResultArray();
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
}



