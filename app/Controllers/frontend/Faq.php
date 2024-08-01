<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Faq extends AdminController
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
        if (!get_permission('frontend_faq', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('frontend_faq', 'is_add')) {
                access_denied();
            }
            $this->services_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_faq($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['faqlist'] = $this->appLib->getTable('front_cms_faq_list');
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/faq';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home features edit
    public function edit($id = '')
    {
        if (!get_permission('frontend_faq', 'is_edit')) {
            access_denied();
        }
        if ($_POST !== []) {
            $this->services_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_faq($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/faq');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['faq'] = $this->appLib->getTable('front_cms_faq_list', ['t.id' => $id], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/faq_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home features delete
    public function delete($id = '')
    {
        if (!get_permission('frontend_faq', 'is_delete')) {
            access_denied();
        }
        $this->db->table(['id' => $id])->delete("front_cms_faq_list")->where();
    }
    private function services_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => translate('description'), "rules" => 'trim|required']]);
    }
}