<?php

namespace App\Models;

use CodeIgniter\Model;
class AwardModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function getList($id = '', $row = false)
    {
        $builder->select('award.*,roles.name as role_name');
        $builder->from('award');
        $builder->join('roles', 'roles.id = award.role_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id', get_loggedin_branch_id())->where();
        }
        $this->db->table('session_id', get_session_id())->where();
        if ($row == false) {
            $result = $builder->get()->result_array();
        } else {
            $builder->where('award.id', $id);
            $result = $builder->get()->row_array();
        }
        return $result;
    }
    public function save($data)
    {
        $insertData = array('name' => $data['award_name'], 'user_id' => $data['user_id'], 'role_id' => $data['role_id'], 'gift_item' => $data['gift_item'], 'award_amount' => $data['cash_price'], 'award_reason' => $data['award_reason'], 'given_date' => date("Y-m-d", strtotime($data['given_date'])), 'session_id' => get_session_id(), 'branch_id' => $this->applicationModel->get_branch_id());
        $award_id = $this->request->getPost('award_id');
        if (empty($award_id)) {
            $builder->insert('award', $insertData);
        } else {
            $builder->where('id', $award_id);
            $builder->update('award', $insertData);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}



