<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Testimonial extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib'); 
$this->testimonial = new \App\Models\TestimonialModel();
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
    }
    private function slider_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['name' => ["label" => 'name', "rules" => 'trim|required']]);
        $this->validation->setRules(['surname' => ["label" => 'Surname', "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'trim|required']]);
        $this->validation->setRules(['rank' => ["label" => 'Rank', "rules" => 'trim|required']]);
        $this->validation->setRules(['photo' => ["label" => 'Photo', "rules" => 'trim|callback_check_image']]);
    }
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_testimonial', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('frontend_testimonial', 'is_add')) {
                access_denied();
            }
            $this->slider_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->testimonialModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['testimoniallist'] = $this->appLib->getTable('front_cms_testimonial');
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/testimonial';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home slider edit
    public function edit($id = '')
    {
        if (!get_permission('frontend_testimonial', 'is_edit')) {
            access_denied();
        }
        if ($_POST !== []) {
            $this->slider_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->testimonialModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/testimonial');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['testimonial'] = $this->testimonialModel->get('front_cms_testimonial', ['id' => $id], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/testimonial_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home slider delete
    public function delete($id = '')
    {
        if (!get_permission('frontend_testimonial', 'is_delete')) {
            access_denied();
        }
        $image = $builder->getWhere('front_cms_testimonial', ['id' => $id])->row()->image;
        if ($this->db->where(['id' => $id])->delete("front_cms_testimonial")) {
            // delete testimonial user image
            $destination = './uploads/frontend/testimonial/';
            if (file_exists($destination . $image)) {
                @unlink($destination . $image);
            }
        }
    }
    public function check_image()
    {
        if ($this->request->getPost('testimonial_id')) {
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