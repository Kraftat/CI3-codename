<?php

namespace App\Models;

use CodeIgniter\Model;
class LeaveModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    // get leave list
    public function getLeaveList($where = '', $single = false)
    {
        $builder->select('la.*,c.name as category_name,r.name as role');
        $builder->from('leave_application as la');
        $builder->join('leave_category as c', 'c.id = la.category_id', 'left');
        $builder->join('roles as r', 'r.id = la.role_id', 'left');
        $this->db->table('session_id', get_session_id())->where();
        if (!empty($where)) {
            $builder->where($where);
        }
        if ($single == false) {
            $builder->order_by('la.id', 'DESC');
            return $builder->get()->result_array();
        } else {
            return $builder->get()->row_array();
        }
    }
}



