<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Setting extends AdminController
{
    public $recaptcha;
    public $appLib;
    public function __construct()
    {
        parent::__construct();
        
        
        $this->recaptcha = service('recaptcha');$this->appLib = service('appLib'); 
$this->frontend = new \App\Models\FrontendModel();
    }
    public function index()
    {
        // check access permission
        if (!get_permission('frontend_setting', 'is_view')) {
            access_denied();
        }
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            $branch_id = $this->request->getPost('branch_id');
            return redirect()->to(base_url('frontend/setting?branch_id=' . $branch_id));
        }
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/jquery-asColorPicker-master/css/asColorPicker.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asColor.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asGradient.js', 'vendor/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['setting'] = $this->frontendModel->get('front_cms_setting', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/setting';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function save()
    {
        if (!get_permission('frontend_setting', 'is_add')) {
            ajax_access_denied();
        }
        if ($_POST !== []) {
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['application_title' => ["label" => 'Cms Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['url_alias' => ["label" => 'Cms Url Alias', "rules" => 'trim|required|callback_unique_url']]);
            $this->validation->setRules(['receive_email_to' => ["label" => 'Receive Email To', "rules" => 'trim|required|valid_email']]);
            $this->validation->setRules(['working_hours' => ["label" => 'Working Hours', "rules" => 'trim|required']]);
            $this->validation->setRules(['address' => ["label" => 'Address', "rules" => 'trim|required']]);
            $this->validation->setRules(['mobile_no' => ["label" => 'Mobile No', "rules" => 'trim|required']]);
            $this->validation->setRules(['email' => ["label" => 'Email', "rules" => 'trim|required|valid_email']]);
            $this->validation->setRules(['fax' => ["label" => 'Fax', "rules" => 'trim|required']]);
            $this->validation->setRules(['footer_about_text' => ["label" => 'Footer About Text', "rules" => 'trim|required']]);
            $this->validation->setRules(['copyright_text' => ["label" => 'Copyright Text', "rules" => 'trim|required']]);
            // theme options
            $this->validation->setRules(['primary_color' => ["label" => 'Primary Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['menu_color' => ["label" => 'Menu Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['btn_hover' => ["label" => 'Button Hover Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['text_color' => ["label" => 'Text Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['secondary_text_color' => ["label" => 'Text Secondary Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['footer_bg_color' => ["label" => 'Footer Background Color ', "rules" => 'trim|required']]);
            $this->validation->setRules(['footer_text_color' => ["label" => 'Footer Text Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['copyright_bg_color' => ["label" => 'Copyright BG Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['copyright_text_color' => ["label" => 'Copyright Text Color', "rules" => 'trim|required']]);
            $this->validation->setRules(['border_radius' => ["label" => 'Border Radius', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $cms_setting = ['branch_id' => $branchID, 'application_title' => $this->request->getPost('application_title'), 'url_alias' => strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $this->request->getPost('url_alias'))), 'cms_active' => $this->request->getPost('cms_frontend_status'), 'primary_color' => $this->request->getPost('primary_color'), 'menu_color' => $this->request->getPost('menu_color'), 'hover_color' => $this->request->getPost('btn_hover'), 'text_color' => $this->request->getPost('text_color'), 'text_secondary_color' => $this->request->getPost('secondary_text_color'), 'footer_background_color' => $this->request->getPost('footer_bg_color'), 'footer_text_color' => $this->request->getPost('footer_text_color'), 'copyright_bg_color' => $this->request->getPost('copyright_bg_color'), 'copyright_text_color' => $this->request->getPost('copyright_text_color'), 'border_radius' => $this->request->getPost('border_radius'), 'online_admission' => $this->request->getPost('online_admission'), 'captcha_status' => $this->request->getPost('captcha_status'), 'recaptcha_site_key' => $this->request->getPost('recaptcha_site_key'), 'recaptcha_secret_key' => $this->request->getPost('recaptcha_secret_key'), 'address' => $this->request->getPost('address'), 'mobile_no' => $this->request->getPost('mobile_no'), 'fax' => $this->request->getPost('fax'), 'receive_contact_email' => $this->request->getPost('receive_email_to'), 'email' => $this->request->getPost('email'), 'footer_about_text' => $this->request->getPost('footer_about_text'), 'copyright_text' => $this->request->getPost('copyright_text'), 'working_hours' => $this->request->getPost('working_hours'), 'google_analytics' => $this->request->getPost('google_analytics', false), 'facebook_url' => $this->request->getPost('facebook_url'), 'twitter_url' => $this->request->getPost('twitter_url'), 'youtube_url' => $this->request->getPost('youtube_url'), 'google_plus' => $this->request->getPost('google_plus'), 'linkedin_url' => $this->request->getPost('linkedin_url'), 'pinterest_url' => $this->request->getPost('pinterest_url'), 'instagram_url' => $this->request->getPost('instagram_url')];
                // upload logo
                if (isset($_FILES["logo"]) && !empty($_FILES["logo"]['name'])) {
                    $imageNmae = $_FILES['logo']['name'];
                    $extension = pathinfo((string) $imageNmae, PATHINFO_EXTENSION);
                    $newLogoName = "logo{$branchID}." . $extension;
                    $image_path = './uploads/frontend/images/' . $newLogoName;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $image_path)) {
                        $cms_setting['logo'] = $newLogoName;
                    }
                }
                // upload fav icon
                if (isset($_FILES["fav_icon"]) && !empty($_FILES["fav_icon"]['name'])) {
                    $imageNmae = $_FILES['fav_icon']['name'];
                    $extension = pathinfo((string) $imageNmae, PATHINFO_EXTENSION);
                    $newLogoName = "fav_icon{$branchID}." . $extension;
                    $image_path = './uploads/frontend/images/' . $newLogoName;
                    if (move_uploaded_file($_FILES['fav_icon']['tmp_name'], $image_path)) {
                        $cms_setting['fav_icon'] = $newLogoName;
                    }
                }
                // update all information in the database
                $this->db->table(['branch_id' => $branchID])->where();
                $get = $builder->get('front_cms_setting');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_setting', $cms_setting)->update();
                } else {
                    $this->db->table('front_cms_setting', $cms_setting)->insert();
                }
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    public function unique_url($alias)
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->db->where_not_in('branch_id', $branchID);
        $this->db->table('url_alias', $alias)->where();
        $query = $builder->get('front_cms_setting');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_url", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }
}