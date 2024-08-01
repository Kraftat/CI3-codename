<?php

namespace App\Controllers\frontend;

use App\Controllers\AdminController;
use App\Models;

class Content extends AdminController
{
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        $this->appLib = service('appLib'); 
$this->content = new \App\Models\ContentModel();
    }
    private function content_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }
        $this->validation->setRules(['title' => ["label" => translate('page_title'), "rules" => 'trim|required|xss_clean']]);
        $this->validation->setRules(['menu_id' => ["label" => translate('select_menu'), "rules" => 'trim|required|xss_clean|callback_unique_menu']]);
        $this->validation->setRules(['content' => ["label" => translate('content'), "rules" => 'required']]);
        $this->validation->setRules(['meta_keyword' => ["label" => translate('meta_keyword'), "rules" => 'xss_clean']]);
        $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'trim|xss_clean|callback_check_image']]);
        $this->validation->setRules(['meta_description' => ["label" => translate('meta_description'), "rules" => 'xss_clean']]);
    }
    public function index()
    {
        // check access permission
        if (!get_permission('manage_page', 'is_view')) {
            access_denied();
        }
        if ($_POST !== []) {
            if (!get_permission('manage_page', 'is_add')) {
                access_denied();
            }
            $this->content_validation();
            if ($this->validation->run() !== false) {
                // save information in the database
                $arrayData = ['branch_id' => $this->applicationModel->get_branch_id(), 'page_title' => $this->request->getPost('title'), 'menu_id' => $this->request->getPost('menu_id'), 'content' => $this->request->getPost('content', false), 'banner_image' => $this->contentModel->uploadBanner('page_' . $this->request->getPost('menu_id'), 'banners'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword')];
                $this->contentModel->save_content($arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/summernote/summernote.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/summernote/summernote.js']];
        $this->data['pagelist'] = $this->contentModel->get_page_list();
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/content';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function edit($id = '')
    {
        if (!get_permission('manage_page', 'is_edit')) {
            access_denied();
        }
        if ($this->request->getPost()) {
            $this->content_validation();
            if ($this->validation->run() !== false) {
                // update information in the database
                $page_id = $this->request->getPost('page_id');
                $arrayData = ['branch_id' => $this->applicationModel->get_branch_id(), 'page_title' => $this->request->getPost('title'), 'menu_id' => $this->request->getPost('menu_id'), 'content' => $this->request->getPost('content', false), 'banner_image' => $this->contentModel->uploadBanner('page_' . $this->request->getPost('menu_id'), 'banners'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword')];
                $this->contentModel->save_content($arrayData, $page_id);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('frontend/content');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
            exit;
        }
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/summernote/summernote.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/summernote/summernote.js']];
        $this->data['content'] = $this->appLib->getTable('front_cms_pages', ['t.id' => $id], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/content_edit';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function delete($id = '')
    {
        if (!get_permission('manage_page', 'is_delete')) {
            access_denied();
        }
        $this->db->table(['id' => $id])->delete("front_cms_pages")->where();
    }
    public function check_image()
    {
        $prev_image = $this->request->getPost('old_photo');
        if ($prev_image == "") {
            if (isset($_FILES['photo']['name']) && !empty($_FILES['photo']['name'])) {
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
        } else {
            return true;
        }
    }
    // unique valid menu verification is done here
    public function unique_menu($id)
    {
        if ($this->request->getPost('page_id')) {
            $page_id = $this->request->getPost('page_id');
            $this->db->where_not_in('id', $page_id);
        }
        $this->db->table('menu_id', $id)->where();
        $query = $builder->get('front_cms_pages');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_menu", "This menu has already been allocated.");
            return false;
        } else {
            return true;
        }
    }
    // get menu list based on the branch
    public function getMenuBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $this->db->order_by('ordering', 'asc');
            $this->db->table('system', 0)->where();
            $this->db->table('branch_id', $branchID)->where();
            $result = $builder->get('front_cms_menu')->result_array();
            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }
        echo $html;
    }
}