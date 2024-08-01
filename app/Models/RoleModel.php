<?php

namespace App\Models;

use CodeIgniter\Model;
class RoleModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    function getRoleList()
    {
        $builder->select('*');
        $builder->where_not_in('id', [1, 6, 7]);
        // Excluding certain system roles for all users
        return $builder->get('roles')->result_array();
    }
    function getBranchRoleList($branch_id)
    {
        $builder->select('*');
        $builder->from('roles');
        $builder->group_start();
        $builder->where('branch_id', $branch_id);
        $builder->or_where('branch_id', NULL);
        $builder->where_not_in('id', [1, 2, 6, 7]);
        // Excluding specific system roles
        $builder->group_end();
        return $builder->get()->result_array();
    }
    function getModulesList()
    {
        $builder->order_by('sorted', 'ASC');
        return $builder->get('permission_modules')->result_array();
    }
    // Role save and update function
    public function save_roles($data)
    {
        $insertData = array('name' => $data['role'], 'prefix' => strtolower(str_replace(' ', '', $data['role'])), 'branch_id' => isset($data['branch_id']) ? $data['branch_id'] : null, 'is_system' => isset($data['is_system']) ? $data['is_system'] : 0, 'created_by' => get_loggedin_user_id());
        if (!isset($data['id']) || empty($data['id'])) {
            $builder->insert('roles', $insertData);
        } else {
            $builder->where('id', $data['id']);
            $builder->update('roles', $insertData);
        }
    }
    // Check permissions function
    // public function check_permissions($module_id = '', $role_id = '')
    // {
    //     $sql = "SELECT permission.*, staff_privileges.id as staff_privileges_id, staff_privileges.is_add, staff_privileges.is_edit, staff_privileges.is_view, staff_privileges.is_delete 
    //             FROM permission 
    //             JOIN staff_privileges ON staff_privileges.permission_id = permission.id AND staff_privileges.role_id = " . $db->escape($role_id) . " 
    //             WHERE permission.module_id = " . $db->escape($module_id) . " 
    //             ORDER BY permission.id ASC";
    //     $query = $db->query($sql);
    //     return $query->getResultArray();
    // }
    // check permissions function
    // public function check_permissions($module_id = '', $role_id = '')
    // {
    //     $sql = "SELECT permission.*, staff_privileges.id as staff_privileges_id,staff_privileges.is_add,staff_privileges.is_edit,staff_privileges.is_view,staff_privileges.is_delete FROM permission LEFT JOIN staff_privileges ON staff_privileges.permission_id = permission.id and staff_privileges.role_id = " . $db->escape($role_id) . " WHERE permission.module_id = " . $db->escape($module_id) . " ORDER BY permission.id ASC";
    //     $query = $db->query($sql);
    //     return $query->getResultArray();
    // }
    public function check_permissions($module_id = '', $role_id = '', $admin_role_id = '')
    {
        $sql = "SELECT permission.*, \r\n                       staff_privileges.id as staff_privileges_id,\r\n                       staff_privileges.is_add, \r\n                       staff_privileges.is_edit, \r\n                       staff_privileges.is_view, \r\n                       staff_privileges.is_delete,\r\n                       admin_privileges.is_add as admin_is_add,\r\n                       admin_privileges.is_edit as admin_is_edit,\r\n                       admin_privileges.is_view as admin_is_view,\r\n                       admin_privileges.is_delete as admin_is_delete\r\n                FROM permission \r\n                LEFT JOIN staff_privileges \r\n                    ON staff_privileges.permission_id = permission.id \r\n                    AND staff_privileges.role_id = " . $db->escape($role_id) . " \r\n                LEFT JOIN staff_privileges as admin_privileges \r\n                    ON admin_privileges.permission_id = permission.id \r\n                    AND admin_privileges.role_id = " . $db->escape($admin_role_id) . " \r\n                WHERE permission.module_id = " . $db->escape($module_id) . " \r\n                ORDER BY permission.id ASC";
        $query = $db->query($sql);
        return $query->getResultArray();
    }
    public function get_permissions($role_id, $branch_id)
    {
        $sql = "SELECT permission.*, staff_privileges.is_add, staff_privileges.is_edit, staff_privileges.is_view, staff_privileges.is_delete \r\n                FROM permission \r\n                JOIN staff_privileges ON staff_privileges.permission_id = permission.id \r\n                JOIN roles ON roles.id = staff_privileges.role_id\r\n                WHERE roles.id = ? AND (roles.branch_id = ? OR roles.branch_id IS NULL)";
        return $db->query($sql, array($role_id, $branch_id))->result_array();
    }
    public function delete_role($id)
    {
        // Delete the role record from the roles table
        $builder->where('id', $id);
        $builder->delete('roles');
        // Optionally, delete associated permissions in the staff_privileges table
        $builder->where('role_id', $id);
        $builder->delete('staff_privileges');
    }
}



