<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Menu extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib'); 
$this->frontend = new \App\Models\FrontendModel();
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['js/frontend.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
    }
    private function menu_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required|callback_unique_title']]);
        $this->validation->setRules(['position' => ["label" => translate('position'), "rules" => 'trim|required|numeric']]);
        if ($this->request->getPost('external_url')) {
            $this->validation->setRules(['external_link' => ["label" => 'External Link', "rules" => 'trim|required']]);
        }
    }
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_menu', 'is_view')) {
            access_denied();
        }
        $branchID = $this->frontendModel->getBranchID();
        if ($this->request->getPost()) {
            if (!get_permission('frontend_menu', 'is_add')) {
                access_denied();
            }
            $this->menu_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_menus($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['branch_id'] = $branchID;
        $this->data['headerelements'] = ['js' => ['js/frontend.js', 'vendor/jquery-nestable/jquery-nestable.js']];
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/menu';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function edit($id = '')
    {
        // check access permission
        if (!get_permission('frontend_menu', 'is_edit')) {
            access_denied();
        }
        if ($this->request->getPost()) {
            $this->menu_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_menus($this->request->getPost());
                $url = base_url('frontend/menu');
                $array = ['status' => 'success', 'url' => $url];
                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['menu'] = $this->appLib->get_table('front_cms_menu', $id, true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/menu_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function delete($id = '')
    {
        if (!get_permission('frontend_menu', 'is_delete')) {
            access_denied();
        }
        $this->db->table(['id' => $id, 'system' => 0])->delete("front_cms_menu")->where();
    }
    public function status()
    {
        if (!get_permission('frontend_menu', 'is_edit')) {
            access_denied();
        }
        $id = $this->request->getPost('menu_id');
        $status = $this->request->getPost('status');
        $branch_id = $this->applicationModel->get_branch_id();
        $getMenu = $builder->select('system')->from("front_cms_menu")->where('id', $id)->get()->row_array();
        if ($getMenu['system']) {
            if ($status == 'true') {
                $array_data['invisible'] = 0;
                $message = translate('published_on_website');
            } else {
                $array_data['invisible'] = 1;
                $message = translate('unpublished_on_website');
            }
            $query = $builder->select('id')->from("front_cms_menu_visible")->where(['menu_id' => $id, 'branch_id' => $branch_id])->get();
            if ($query->num_rows() == 0) {
                $array_data['parent_id'] = null;
                $array_data['ordering'] = null;
                $array_data['name'] = null;
                $array_data['menu_id'] = $id;
                $array_data['branch_id'] = $branch_id;
                $this->db->table('front_cms_menu_visible', $array_data)->insert();
            } else {
                $this->db->table('id', $query->row()->id)->where();
                $this->db->table('front_cms_menu_visible', $array_data)->update();
            }
        } else {
            if ($status == 'true') {
                $array_data['publish'] = 1;
                $message = translate('published_on_website');
            } else {
                $array_data['publish'] = 0;
                $message = translate('unpublished_on_website');
            }
            $this->db->table('id', $id)->where();
            $this->db->table('front_cms_menu', $array_data)->update();
        }
        echo $message;
    }
    // unique valid menu title verification is done here
    public function unique_title($title)
    {
        if ($this->request->getPost('menu_id')) {
            $menu_id = $this->request->getPost('menu_id');
            $this->db->where_not_in('id', $menu_id);
        }
        $branch_id = $this->applicationModel->get_branch_id();
        $this->db->table('branch_id', $branch_id)->where();
        $this->db->table('title', $title)->where();
        $this->db->table('system', 0)->where();
        $query = $builder->get('front_cms_menu');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_title", "This title has already been used.");
            return false;
        } else {
            return true;
        }
    }
}