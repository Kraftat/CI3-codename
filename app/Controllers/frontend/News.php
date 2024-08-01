<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class News extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib');$config = ['field' => 'alias', 'title' => 'title', 'table' => 'front_cms_news_list', 'id' => 'id'];
        $this->slug = service('slug', $config);
        $this->news = new \App\Models\NewsModel();
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
    }
    private function news_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['news_title' => ["label" => translate('news_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => translate('description'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['image' => ["label" => translate('image'), "rules" => 'trim|callback_photoHandleUpload[image]']]);
        if (empty($_FILES['image']['name']) && empty($this->request->getPost('old_photo'))) {
            $this->validation->setRules(['image' => ["label" => translate('image'), "rules" => 'required']]);
        }
    }
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_news', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('frontend_news', 'is_add')) {
                access_denied();
            }
            $this->news_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->newsModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['newslist'] = $this->appLib->getTable('front_cms_news_list');
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/news';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function edit($id = '')
    {
        if (!get_permission('frontend_news', 'is_edit')) {
            access_denied();
        }
        if ($_POST !== []) {
            $this->news_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->newsModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/news/index');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['gallery'] = $this->newsModel->get('front_cms_news_list', ['id' => $id], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/news_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function delete($id = '')
    {
        if (!get_permission('frontend_news', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id', get_loggedin_branch_id())->where();
        }
        $row = $builder->getWhere('front_cms_news_list', ['id' => $id])->row();
        if (!empty($row) && $this->db->where(['id' => $id])->delete("front_cms_news_list")) {
            // delete news user image
            $destination = './uploads/frontend/news/';
            if (file_exists($destination . $row->image)) {
                @unlink($destination . $row->image);
            }
        }
    }
    // publish on show website
    public function show_website()
    {
        if ($_POST !== []) {
            $id = $this->request->getPost('id');
            $status = $this->request->getPost('status');
            $arrayData['show_web'] = $status == 'true' ? 1 : 0;
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $this->db->table('id', $id)->where();
            $this->db->table('front_cms_news_list', $arrayData)->update();
            $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
            echo json_encode($return);
        }
    }
}