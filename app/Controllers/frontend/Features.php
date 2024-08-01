<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Features extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib'); 
$this->frontend = new \App\Models\FrontendModel();
    }
    // home features
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_features', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('frontend_features', 'is_add')) {
                access_denied();
            }
            $this->features_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_features($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['featureslist'] = $this->appLib->getTable('front_cms_home', ['t.item_type' => 'features']);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/features';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home features edit
    public function edit($id = '')
    {
        if (!get_permission('frontend_features', 'is_edit')) {
            access_denied();
        }
        if ($_POST !== []) {
            $this->features_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_features($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/features');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['features'] = $this->appLib->getTable('front_cms_home', ['t.id' => $id, 't.item_type' => 'features'], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/features_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home features delete
    public function delete($id = '')
    {
        if (!get_permission('frontend_features', 'is_delete')) {
            access_denied();
        }
        $this->db->table(['id' => $id, 'item_type' => 'features'])->delete("front_cms_home")->where();
    }
    private function features_validation()
    {
        $this->validation->setRules(['title' => ["label" => 'Title', "rules" => 'trim|required|xss_clean']]);
        $this->validation->setRules(['button_text' => ["label" => 'Button Text', "rules" => 'trim|required|xss_clean']]);
        $this->validation->setRules(['button_url' => ["label" => 'Button Url', "rules" => 'trim|required|xss_clean']]);
        $this->validation->setRules(['icon' => ["label" => 'Icon', "rules" => 'trim|required|xss_clean']]);
        $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'trim|required|xss_clean']]);
    }
}