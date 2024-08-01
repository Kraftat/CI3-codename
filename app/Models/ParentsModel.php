<?php

namespace App\Models;

use CodeIgniter\Model;
class ParentsModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    // moderator parents all information
    public function save($data, $getBranch = array())
    {
        $inser_data1 = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['name'], 'relation' => $data['relation'], 'father_name' => $data['father_name'], 'mother_name' => $data['mother_name'], 'occupation' => $data['occupation'], 'income' => $data['income'], 'education' => $data['education'], 'email' => $data['email'], 'mobileno' => $data['mobileno'], 'address' => $data['address'], 'city' => $data['city'], 'state' => $data['state'], 'photo' => $this->uploadImage('parent'), 'facebook_url' => $data['facebook'], 'linkedin_url' => $data['linkedin'], 'twitter_url' => $data['twitter']);
        if (!isset($data['parent_id']) && empty($data['parent_id'])) {
            // save employee information in the database
            $builder->insert('parent', $inser_data1);
            $parent_id = $builder->insert_id();
            // save guardian login credential information in the database
            if ($getBranch['grd_generate'] == 1) {
                $username = $getBranch['grd_username_prefix'] . $parent_id;
                $password = $getBranch['grd_default_password'];
            } else {
                $username = $data['username'];
                $password = $data['password'];
            }
            $inser_data2 = array('username' => $username, 'role' => 6, 'active' => 1, 'user_id' => $parent_id, 'password' => $this->appLib->passHashed($password));
            $builder->insert('login_credential', $inser_data2);
            // send account activate email
            $emailData = array('name' => $data['name'], 'username' => $username, 'password' => $password, 'user_role' => 6, 'email' => $data['email']);
            $this->emailModel->sentStaffRegisteredAccount($emailData);
            return $parent_id;
        } else {
            $builder->where('id', $data['parent_id']);
            $builder->update('parent', $inser_data1);
            // update login credential information in the database
            $this->db->table(array('role' => 6, 'user_id' => $data['parent_id']))->where();
            $this->db->table('login_credential', array('username' => $data['username']))->update();
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getSingleParent($id)
    {
        $builder->select('parent.*,login_credential.role as role_id,login_credential.active,login_credential.username,login_credential.id as login_id, roles.name as role');
        $builder->from('parent');
        $builder->join('login_credential', 'login_credential.user_id = parent.id and login_credential.role = "6"', 'inner');
        $builder->join('roles', 'roles.id = login_credential.role', 'left');
        $builder->where('parent.id', $id);
        if (!is_superadmin_loggedin()) {
            $this->db->table('parent.branch_id', get_loggedin_branch_id())->where();
        }
        $query = $builder->get();
        if ($query->num_rows() == 0) {
            show_404();
        }
        return $query->row_array();
    }
    public function childsResult($parent_id)
    {
        $builder->select('s.id,s.photo, CONCAT_WS(" ",s.first_name, s.last_name) as fullname,c.name as class_name,se.name as section_name');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('login_credential as l', 'l.user_id = s.id and l.role = 7', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id=se.id', 'left');
        $builder->where('s.parent_id', $parent_id);
        $builder->where('l.active', 1);
        $this->db->table('e.session_id', get_session_id())->where();
        return $builder->get()->result_array();
    }
    // get parent all details
    public function getParentList($branchID = null, $active = 1)
    {
        $builder->select('parent.*,login_credential.active as active');
        $builder->from('parent');
        $builder->join('login_credential', 'login_credential.user_id = parent.id and login_credential.role = "6"', 'inner');
        $builder->where('login_credential.active', $active);
        if (!empty($branchID)) {
            $builder->where('parent.branch_id', $branchID);
        }
        $builder->order_by('parent.id', 'ASC');
        return $builder->get()->getResult();
    }
}



