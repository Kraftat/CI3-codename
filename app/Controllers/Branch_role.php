<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\RoleModel;
class Branch_role extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\RoleModel
     */
    public $role;

    public $validation;

    public $input;

    public $session;

    public $roleModel;

    public $load;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->role = new \App\Models\RoleModel();
        // Check if the user is a superadmin or if the 'branch_role_permission' module is enabled
        if (!is_superadmin_loggedin() && !moduleIsEnabled('branch_role_permission')) {
            access_denied();
        }
    }

    // Role form validation rules
    protected function role_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['role' => ["label" => translate('role_name'), "rules" => 'required|trim|callback_unique_name']]);
    }

    public function index()
    {
        if (is_superadmin_loggedin() && $this->request->getPost('search')) {
            $branchId = $this->request->getPost('branch_id');
            session()->set('selected_branch_id', $branchId);
            redirect('branch_role');
        }

        if ($_POST !== []) {
            $this->role_validation();
            if ($this->validation->run() !== false) {
                $data = $this->request->getPost();
                $data['branch_id'] = is_superadmin_loggedin() ? $this->request->getPost('branch_id') : get_loggedin_branch_id();
                $data['created_by'] = get_loggedin_user_id();
                $this->roleModel->save_roles($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('branch_role'));
            }
        }

        $branchId = is_superadmin_loggedin() ? session()->get('selected_branch_id') : get_loggedin_branch_id();
        $this->data['roles'] = $this->roleModel->getBranchRoleList($branchId);
        $this->data['title'] = 'Branch Specific Roles';
        $this->data['sub_page'] = 'branch_role/index';
        $this->data['main_menu'] = 'branch_roles';
        echo view('layout/index', $this->data);
        return null;
    }

    public function unique_name($str)
    {
        $roleId = $this->request->getPost('id');
        // Assuming 'id' is posted if it's an edit operation
        get_loggedin_branch_id();
        $this->db->table('name')->where();
        $this->db->table('branch_id')->where();
        if (!empty($roleId)) {
            $this->db->table('id !=')->where();
        }

        $query = $builder->get('roles');
        if ($query->num_rows() > 0) {
            $this->validation->setRule('unique_name', 'The %s is already taken in this branch.');
            return FALSE;
        }

        return TRUE;
    }

    public function edit($id)
    {
        $branchId = is_superadmin_loggedin() ? session()->get('selected_branch_id') : get_loggedin_branch_id();
        $roles = $this->roleModel->getBranchRoleList($branchId);
        $role = array_filter($roles, fn($role) => $role['id'] == $id);
        $role = array_shift($role);
        // Assuming the first result is the desired role
        // Check for system roles and branch match
        if ($role === null || $role['is_system'] || !is_superadmin_loggedin() && $role['branch_id'] != $branchId) {
            access_denied();
        }

        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $this->role_validation();
            if ($this->validation->run() !== false) {
                $data['branch_id'] = is_superadmin_loggedin() ? session()->get('selected_branch_id') : $branchId;
                $data['created_by'] = get_loggedin_user_id();
                $this->roleModel->save_roles($data);
                set_alert('success', 'Role updated successfully');
                redirect('branch_role');
            }
        }

        $this->data['role'] = $role;
        $this->data['title'] = 'Edit Branch Role';
        $this->data['sub_page'] = 'branch_role/edit';
        $this->data['main_menu'] = 'branch_roles';
        echo view('layout/index', $this->data);
    }

    public function delete($id)
    {
        $branchId = get_loggedin_branch_id();
        $roles = $this->roleModel->getBranchRoleList($branchId);
        $role = array_filter($roles, fn($role) => $role['id'] == $id);
        $role = array_shift($role);
        // Assuming the first result is the desired role
        // Check for system roles and branch match
        if ($role === null || $role['is_system'] || !is_superadmin_loggedin() && $role['branch_id'] != $branchId) {
            access_denied();
        }

        $this->roleModel->delete_role($id);
        set_alert('success', 'Role deleted successfully');
        redirect('branch_role');
    }

    public function permission($roleId)
    {
        $branchId = is_superadmin_loggedin() ? session()->get('selected_branch_id') : get_loggedin_branch_id();
        $roles = $this->roleModel->getBranchRoleList($branchId);
        // Find the role in the retrieved list
        $role = null;
        foreach ($roles as $r) {
            if ($r['id'] == $roleId) {
                $role = $r;
                break;
            }
        }

        // Ensure the role is not null and belongs to the current branch, or allow superadmin access
        if ($role === null || !is_superadmin_loggedin() && $role['branch_id'] != $branchId) {
            access_denied();
        }

        if ($this->request->getPost()) {
            $privileges = $this->request->getPost('privileges');
            foreach ($privileges as $key => $value) {
                $isAdd = isset($value['add']) ? 1 : 0;
                $isEdit = isset($value['edit']) ? 1 : 0;
                $isView = isset($value['view']) ? 1 : 0;
                $isDelete = isset($value['delete']) ? 1 : 0;
                $arrayData = ['role_id' => $roleId, 'permission_id' => $key, 'is_add' => $isAdd, 'is_edit' => $isEdit, 'is_view' => $isView, 'is_delete' => $isDelete];
                $existPrivileges = $db->table('staff_privileges')->get('staff_privileges')->num_rows();
                if ($existPrivileges > 0) {
                    $this->db->table('staff_privileges')->update();
                } else {
                    $this->db->table('staff_privileges')->insert();
                }
            }

            set_alert('success', 'Permissions updated successfully');
            redirect('branch_role/permission/' . $roleId);
        }

        // Filter the modules list to show only the active modules for the branch
        $allModules = $this->roleModel->getModulesList();
        $activeModules = array_filter($allModules, fn($module) => moduleIsEnabled($module['prefix']));
        // Filter permissions for the active modules
        $filteredPermissions = [];
        foreach ($activeModules as $module) {
            $permissions = $this->roleModel->check_permissions($module['id'], $roleId, loggedin_role_id());
            foreach ($permissions as $permission) {
                if ($permission['show_view'] && $permission['admin_is_view'] || $permission['show_add'] && $permission['admin_is_add'] || $permission['show_edit'] && $permission['admin_is_edit'] || $permission['show_delete'] && $permission['admin_is_delete']) {
                    $filteredPermissions[] = $permission;
                }
            }
        }

        $this->data['role_id'] = $roleId;
        $this->data['permissions'] = $filteredPermissions;
        $this->data['modules'] = $activeModules;
        $this->data['title'] = 'Manage Permissions for Branch Roles';
        $this->data['sub_page'] = 'branch_role/permission';
        $this->data['main_menu'] = 'branch_roles';
        echo view('layout/index', $this->data);
    }
}
