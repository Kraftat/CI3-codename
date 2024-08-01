<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom Diagnostic Management System
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Role.php
 */
class Role extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\RoleModel
     */
    public $role;

    public $validation;

    public $input;

    public $roleModel;

    public $load;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->role = new \App\Models\RoleModel();
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    // new role add
    public function index()
    {
        if (isset($_POST['save'])) {
            $rules = [['field' => 'role', 'label' => 'Role Name', 'rules' => 'required|callback_unique_name']];
            $this->validation->setRule($rules);
            if ($this->validation->run() == false) {
                $this->data['validation_error'] = true;
            } else {
                // update information in the database
                $data = $this->request->getPost();
                $this->roleModel->save_roles($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('role'));
            }
        }

        $this->data['roles'] = $this->roleModel->getRoleList();
        $this->data['title'] = translate('roles');
        $this->data['sub_page'] = 'role/index';
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
        return null;
    }

    // role edit
    public function edit($id)
    {
        if (isset($_POST['save'])) {
            $rules = [['field' => 'role', 'label' => 'Role Name', 'rules' => 'required|callback_unique_name']];
            $this->validation->setRule($rules);
            if ($this->validation->run() == false) {
                $this->data['validation_error'] = true;
            } else {
                // SAVE ROLE INFORMATION IN THE DATABASE
                $data = $this->request->getPost();
                $this->roleModel->save_roles($data);
                set_alert('success', translate('information_has_been_updated_successfully'));
                return redirect()->to(base_url('role'));
            }
        }

        $this->data['roles'] = $this->roleModel->get('roles', ['id' => $id], true);
        $this->data['title'] = translate('roles');
        $this->data['sub_page'] = 'role/edit';
        $this->data['main_menu'] = 'test';
        echo view('layout/index', $this->data);
        return null;
    }

    // check unique name
    public function unique_name($name)
    {
        $id = $this->request->getPost('id');
        $where = isset($id) ? ['name' => $name, 'id != ' => $id] : ['name' => $name];
        $q = $builder->getWhere('roles', $where);
        if ($q->num_rows() > 0) {
            $this->validation->setRule("unique_name", translate('already_taken'));
            return false;
        }

        return true;
    }

    // role delete in DB // fahad update
    // public function delete($role_id)
    // {
    //     $systemRole = array(1, 2, 3, 4, 5, 6, 7);
    //     if (!in_array($role_id, $systemRole)) {
    //         $this->db->table('id', $role_id)->where();
    //         $this->db->table('roles')->delete();
    //     }
    // }
    // role delete in DB
    public function delete($roleId)
    {
        $systemRole = [1, 2, 3, 4, 5, 6, 7];
        if (!in_array($roleId, $systemRole, true)) {
            // Call the model function to delete the role
            if ($this->roleModel->delete_role($roleId)) {
                // If delete was successful, set a success message
                set_alert('success', 'Role deleted successfully');
            } else {
                // If the delete operation failed, set an error message
                set_alert('error', 'Failed to delete role');
            }
        } else {
            // Setting an error message if trying to delete a system role
            set_alert('error', 'Cannot delete system role');
        }

        // Redirect to the role listing page
        redirect('role');
    }

    public function permission($roleId)
    {
        $roleList = $this->roleModel->getRoleList();
        $allowRole = array_column($roleList, 'id');
        if (!in_array($roleId, $allowRole, true)) {
            access_denied();
        }

        if (isset($_POST['save'])) {
            $roleId = $this->request->getPost('role_id');
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

            set_alert('success', translate('information_has_been_updated_successfully'));
            return redirect()->to(base_url('role/permission/' . $roleId));
        }

        $this->data['role_id'] = $roleId;
        $this->data['modules'] = $this->roleModel->getModulesList();
        $this->data['title'] = translate('roles');
        $this->data['sub_page'] = 'role/permission';
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
        return null;
    }
}
