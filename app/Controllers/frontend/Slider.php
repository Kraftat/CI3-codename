<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Slider extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib'); 
$this->frontend = new \App\Models\FrontendModel();
    }
    private function slider_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['position' => ["label" => translate('position'), "rules" => 'trim|required']]);
        $this->validation->setRules(['button_text_1' => ["label" => 'Button Text 1', "rules" => 'trim|required']]);
        $this->validation->setRules(['button_url_1' => ["label" => 'Button Url 1', "rules" => 'trim|required']]);
        $this->validation->setRules(['button_text_2' => ["label" => 'Button Text 2', "rules" => 'trim|required']]);
        $this->validation->setRules(['button_url_2' => ["label" => 'Button Url 2', "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => translate('description'), "rules" => 'trim|required']]);
        $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'trim|callback_check_image']]);
    }
    // home slider
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_slider', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('frontend_slider', 'is_add')) {
                access_denied();
            }
            $this->slider_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_slider($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['sliderlist'] = $this->appLib->getTable('front_cms_home', ['item_type' => 'slider']);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/slider';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home slider edit
    public function edit($id = '')
    {
        // check access permission
        if (!get_permission('frontend_slider', 'is_edit')) {
            access_denied();
        }
        if ($_POST !== []) {
            $this->slider_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->frontendModel->save_slider($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/slider');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['slider'] = $this->frontendModel->get('front_cms_home', ['id' => $id, 'item_type' => 'slider'], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/slider_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home slider delete
    public function delete($id = '')
    {
        if (!get_permission('frontend_slider', 'is_delete')) {
            access_denied();
        }
        $image = $builder->getWhere('front_cms_home', ['id' => $id, 'item_type' => 'slider'])->row()->image;
        if ($this->db->where(['id' => $id, 'item_type' => 'slider'])->delete("front_cms_home")) {
            // delete gallery slider
            $destination = './uploads/frontend/slider/';
            if (file_exists($destination . $image)) {
                @unlink($destination . $image);
            }
        }
    }
    public function check_image()
    {
        if ($this->request->getPost('slider_id')) {
            if (!empty($_FILES['photo']['name'])) {
                $name = $_FILES['photo']['name'];
                $arr = explode('.', (string) $name);
                $ext = end($arr);
                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    return true;
                } else {
                    $this->validation->setRule('check_image', translate('select_valid_file_format'));
                    return false;
                }
            }
        } elseif (isset($_FILES['photo']['name']) && !empty($_FILES['photo']['name'])) {
            $name = $_FILES['photo']['name'];
            $arr = explode('.', (string) $name);
            $ext = end($arr);
            if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                return true;
            } else {
                $this->validation->setRule('check_image', translate('select_valid_file_format'));
                return false;
            }
        } else {
            $this->validation->setRule('check_image', 'The Photo is required.');
            return false;
        }
        return null;
    }
}