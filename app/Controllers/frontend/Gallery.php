<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Gallery extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib');$config = ['field' => 'alias', 'title' => 'title', 'table' => 'front_cms_gallery_content', 'id' => 'id'];
        $this->slug = service('slug', $config);
        $this->gallery = new \App\Models\GalleryModel();
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
    }
    private function slider_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['gallery_title' => ["label" => translate('gallery_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['description' => ["label" => translate('description'), "rules" => 'trim|required']]);
        $this->validation->setRules(['category_id' => ["label" => translate('category'), "rules" => 'trim|required']]);
        $this->validation->setRules(['thumb_image' => ["label" => translate('thumb_image'), "rules" => 'trim|callback_check_image']]);
    }
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_gallery', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('frontend_gallery', 'is_add')) {
                access_denied();
            }
            $this->slider_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->galleryModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['gallerylist'] = $this->appLib->getTable('front_cms_gallery_content');
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/gallery';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home slider edit
    public function edit($id = '')
    {
        if (!get_permission('frontend_gallery', 'is_edit')) {
            access_denied();
        }
        if ($_POST !== []) {
            $this->slider_validation();
            if ($this->validation->run() !== false) {
                // save information in the database file
                $this->galleryModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/gallery/index');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['gallery'] = $this->galleryModel->get('front_cms_gallery_content', ['id' => $id], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/gallery_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // home slider delete
    public function delete($id = '')
    {
        if (!get_permission('frontend_gallery', 'is_delete')) {
            access_denied();
        }
        $image = $builder->getWhere('front_cms_gallery_content', ['id' => $id])->row()->image;
        if ($this->db->where(['id' => $id])->delete("front_cms_gallery_content")) {
            // delete gallery user image
            $destination = './uploads/frontend/gallery/';
            if (file_exists($destination . $image)) {
                @unlink($destination . $image);
            }
        }
    }
    public function check_image()
    {
        if ($this->request->getPost('gallery_id')) {
            if (!empty($_FILES['thumb_image']['name'])) {
                $name = $_FILES['thumb_image']['name'];
                $arr = explode('.', (string) $name);
                $ext = end($arr);
                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    return true;
                } else {
                    $this->validation->setRule('check_image', translate('select_valid_file_format'));
                    return false;
                }
            }
        } elseif (isset($_FILES['thumb_image']['name']) && !empty($_FILES['thumb_image']['name'])) {
            $name = $_FILES['thumb_image']['name'];
            $arr = explode('.', (string) $name);
            $ext = end($arr);
            if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                return true;
            } else {
                $this->validation->setRule('check_image', translate('select_valid_file_format'));
                return false;
            }
        } else {
            $this->validation->setRule('check_image', 'The thumb image is required.');
            return false;
        }
        return null;
    }
    public function album($id = '')
    {
        // check access permission
        if (!get_permission('frontend_gallery', 'is_edit')) {
            access_denied();
        }
        $this->data['gallery'] = $this->appLib->getTable('front_cms_gallery_content', ['t.id' => $id], TRUE);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/gallery_album';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function upload()
    {
        // check access permission
        if (!get_permission('frontend_gallery', 'is_edit')) {
            ajax_access_denied();
        }
        $type = $this->request->getPost('type');
        $video_url = null;
        $this->validation->setRules(['type' => ["label" => translate('type'), "rules" => 'trim|required']]);
        if ($type == 2) {
            $video_url = $this->request->getPost('video_url');
            $this->validation->setRules(['video_url' => ["label" => translate('video_url'), "rules" => 'trim|required']]);
        }
        $this->validation->setRules(['thumb_image' => ["label" => translate('photo'), "rules" => 'trim|callback_check_image']]);
        if ($this->validation->run() !== false) {
            $album_id = $this->request->getPost('album_id');
            $getData = $this->appLib->getTable('front_cms_gallery_content', ['t.id' => $album_id], TRUE);
            $arr = [];
            $count = 1;
            if (!empty($getData['elements'])) {
                $getJson = json_decode((string) $getData['elements'], TRUE);
                if (array_keys($getJson) !== []) {
                    $count = max(array_keys($getJson)) + 1;
                }
                foreach ($getJson as $key => $value) {
                    $arr[$key] = ['image' => $value['image'], 'type' => $value['type'], 'date' => $value['date'], 'video_url' => $value['video_url']];
                }
            }
            $arr[$count] = ['image' => $this->galleryModel->upload_image('album'), 'type' => $type, 'video_url' => $video_url, 'date' => date("Y-m-d H:i:s")];
            $insertGallery = ['elements' => json_encode($arr)];
            $this->db->table('id', $album_id)->where();
            $this->db->table('front_cms_gallery_content', $insertGallery)->update();
            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }
        echo json_encode($array);
    }
    public function upload_delete($id = '', $elem_id = '')
    {
        if (!get_permission('frontend_gallery', 'is_delete')) {
            access_denied();
        }
        $getData = $this->appLib->getTable('front_cms_gallery_content', ['t.id' => $id], TRUE);
        if (!empty($getData['elements'])) {
            $getJson = json_decode((string) $getData['elements'], TRUE);
            foreach ($getJson as $key => $value) {
                if ($key == $elem_id) {
                    unset($getJson[$key]);
                    // delete gallery user image
                    $destination = './uploads/frontend/gallery/';
                    $image = $value['image'];
                    if (file_exists($destination . $image)) {
                        @unlink($destination . $image);
                    }
                }
            }
            $insertGallery = ['elements' => json_encode($getJson)];
            $this->db->table('id', $id)->where();
            $this->db->table('front_cms_gallery_content', $insertGallery)->update();
        }
    }
    // publish on show website
    public function show_website()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $arrayData['show_web'] = $status == 'true' ? 1 : 0;
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id', get_loggedin_branch_id())->where();
        }
        $this->db->table('id', $id)->where();
        $this->db->table('front_cms_gallery_content', $arrayData)->update();
        $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
        echo json_encode($return);
    }
    // add new student category
    public function category()
    {
        if (isset($_POST['category'])) {
            if (!get_permission('frontend_gallery_category', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }
            $this->validation->setRules(['category_name' => ["label" => translate('category_name'), "rules" => 'trim|required|callback_unique_category']]);
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('category_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('front_cms_gallery_category', $arrayData)->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('frontend/gallery/category'));
            }
        }
        $this->data['categorylist'] = $this->appLib->getTable('front_cms_gallery_category');
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/gallery_category';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    // update existing student category
    public function category_edit()
    {
        if (!get_permission('frontend_gallery_category', 'is_edit')) {
            ajax_access_denied();
        }
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['category_name' => ["label" => translate('category_name'), "rules" => 'trim|required|callback_unique_category']]);
        if ($this->validation->run() !== false) {
            $category_id = $this->request->getPost('category_id');
            $arrayData = ['name' => $this->request->getPost('category_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id', $category_id)->where();
            $this->db->table('front_cms_gallery_category', $arrayData)->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }
        echo json_encode($array);
    }
    // delete student category from database
    public function category_delete($id)
    {
        if (get_permission('frontend_gallery_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $this->db->table('id', $id)->where();
            $this->db->table('front_cms_gallery_category')->delete();
        }
    }
    /* validate here, if the check student category name */
    public function unique_category($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $category_id = $this->request->getPost('category_id');
        if (!empty($category_id)) {
            $this->db->where_not_in('id', $category_id);
        }
        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniform_row = $builder->get('front_cms_gallery_category')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->validation->setRule("unique_category", translate('already_taken'));
            return false;
        }
    }
}