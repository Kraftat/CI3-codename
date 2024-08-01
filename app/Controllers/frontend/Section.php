<?php

namespace App\Controllers\frontend;

use App\Models;
use App\Controllers\AdminController;

class Section extends AdminController
 
{
    public $appLib;
    protected $db;



    public function __construct()
    {



        parent::__construct();
        
        $this->appLib = service('appLib'); 
$this->frontend = new \App\Models\FrontendModel();
        $this->studentFields = new \App\Models\StudentFieldsModel();
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/dropify/css/dropify.min.css', 'vendor/jquery-asColorPicker-master/css/asColorPicker.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/dropify/js/dropify.min.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asColor.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asGradient.js', 'vendor/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js']];
        if (!get_permission('frontend_section', 'is_view')) {
            access_denied();
        }
    }
    public function index()
    {
        $this->home();
    }
    // home features
    public function home()
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['wellcome'] = $this->frontendModel->get('front_cms_home', ['item_type' => 'wellcome', 'branch_id' => $branchID], true);
        $this->data['home_seo'] = $this->frontendModel->get('front_cms_home_seo', ['branch_id' => $branchID], true);
        $this->data['teachers'] = $this->frontendModel->get('front_cms_home', ['item_type' => 'teachers', 'branch_id' => $branchID], true);
        $this->data['testimonial'] = $this->frontendModel->get('front_cms_home', ['item_type' => 'testimonial', 'branch_id' => $branchID], true);
        $this->data['services'] = $this->frontendModel->get('front_cms_home', ['item_type' => 'services', 'branch_id' => $branchID], true);
        $this->data['statistics'] = $this->frontendModel->get('front_cms_home', ['item_type' => 'statistics', 'branch_id' => $branchID], true);
        $this->data['cta'] = $this->frontendModel->get('front_cms_home', ['item_type' => 'cta', 'branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_home';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function home_wellcome()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['wel_title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['subtitle' => ["label" => 'Subtitle', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'trim|required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayWellcome = ['branch_id' => $branchID, 'title' => $this->request->getPost('wel_title'), 'subtitle' => $this->request->getPost('subtitle'), 'active' => isset($_POST['isvisible']) ? 1 : 0, 'description' => $this->request->getPost('description'), 'color1' => $this->request->getPost('title_text_color'), 'elements' => json_encode(['image' => $this->uploadImage('wellcome' . $branchID, 'home_page')])];
                // save information in the database
                $this->saveHome('wellcome', $branchID, $arrayWellcome);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    public function home_teachers()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['tea_title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['tea_description' => ["label" => 'Description', "rules" => 'trim|required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayTeacher = ['branch_id' => $branchID, 'title' => $this->request->getPost('tea_title'), 'description' => $this->request->getPost('tea_description'), 'active' => isset($_POST['isvisible']) ? 1 : 0, 'elements' => json_encode(['teacher_start' => $this->request->getPost('teacher_start'), 'image' => $this->uploadImage('featured-parallax' . $branchID, 'home_page')]), 'color1' => $this->request->getPost('title_text_color'), 'color2' => $this->request->getPost('description_text_color')];
                // save information in the database
                $this->saveHome('teachers', $branchID, $arrayTeacher);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    function home_testimonial()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['tes_title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['tes_description' => ["label" => 'Description', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayTestimonial = ['branch_id' => $branchID, 'title' => $this->request->getPost('tes_title'), 'active' => isset($_POST['isvisible']) ? 1 : 0, 'description' => $this->request->getPost('tes_description')];
                // save information in the database
                $this->saveHome('testimonial', $branchID, $arrayTestimonial);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    function home_services()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['ser_title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['ser_description' => ["label" => 'Description', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayServices = ['branch_id' => $branchID, 'title' => $this->request->getPost('ser_title'), 'color1' => $this->request->getPost('title_text_color'), 'color2' => $this->request->getPost('background_color'), 'active' => isset($_POST['isvisible']) ? 1 : 0, 'description' => $this->request->getPost('ser_description')];
                // save information in the database
                $this->saveHome('services', $branchID, $arrayServices);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    function home_statistics()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['sta_title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['sta_description' => ["label" => 'Description', "rules" => 'trim|required']]);
            for ($i = 1; $i < 5; $i++) {
                $this->validation->setRules(['widget_title_' . $i => ["label" => 'Widget Title', "rules" => 'trim|required']]);
                $this->validation->setRules(['widget_icon_' . $i => ["label" => 'Widget Icon', "rules" => 'trim|required']]);
                $this->validation->setRules(['statistics_type_' . $i => ["label" => 'Statistics Type', "rules" => 'trim|required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $elements = [];
                $elements['image'] = $this->uploadImage('counter-parallax' . $branchID, 'home_page');
                for ($i = 1; $i < 5; $i++) {
                    $elements['widget_title_' . $i] = $this->request->getPost('widget_title_' . $i);
                    $elements['widget_icon_' . $i] = $this->request->getPost('widget_icon_' . $i);
                    $elements['type_' . $i] = $this->request->getPost('statistics_type_' . $i);
                }
                $arrayServices = ['branch_id' => $branchID, 'title' => $this->request->getPost('sta_title'), 'color1' => $this->request->getPost('title_text_color'), 'color2' => $this->request->getPost('description_text_color'), 'active' => isset($_POST['isvisible']) ? 1 : 0, 'description' => $this->request->getPost('sta_description'), 'elements' => json_encode($elements)];
                // save information in the database
                $this->saveHome('statistics', $branchID, $arrayServices);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    function home_cta()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['cta_title' => ["label" => 'Cta Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['mobile_no' => ["label" => 'Mobile No', "rules" => 'trim|required']]);
            $this->validation->setRules(['button_text' => ["label" => 'Button Text', "rules" => 'trim|required']]);
            $this->validation->setRules(['button_url' => ["label" => 'Button Url', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $elements_data = ['mobile_no' => $this->request->getPost('mobile_no'), 'button_text' => $this->request->getPost('button_text'), 'button_url' => $this->request->getPost('button_url')];
                $array_cta = ['branch_id' => $branchID, 'title' => $this->request->getPost('cta_title'), 'color1' => $this->request->getPost('background_color'), 'color2' => $this->request->getPost('text_color'), 'active' => isset($_POST['isvisible']) ? 1 : 0, 'elements' => json_encode($elements_data)];
                // save information in the database
                $this->saveHome('cta', $branchID, $array_cta);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    function home_options()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arraySeo = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'meta_keyword' => $this->request->getPost('meta_keyword', true), 'meta_description' => $this->request->getPost('meta_description', true)];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_home_seo');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_home_seo', $arraySeo)->update();
                } else {
                    $this->db->table('front_cms_home_seo', $arraySeo)->insert();
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
    public function teachers()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('teachers' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_teachers');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_teachers', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_teachers', $arrayData)->insert();
                }
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
        $this->data['teachers'] = $this->frontendModel->get('front_cms_teachers', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_teachers';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function events()
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['events'] = $this->frontendModel->get('front_cms_events', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_events';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function eventsSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'title' => $this->request->getPost('title'), 'description' => $this->request->getPost('description', false)];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_events');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_events', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_events', $arrayData)->insert();
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
    public function eventsOptionSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['page_title' => $this->request->getPost('page_title'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('event' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_events');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_events', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_events', $arrayData)->insert();
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
    public function about()
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['about'] = $this->frontendModel->get('front_cms_about', ['branch_id' => $branchID], true);
        $this->data['service'] = $this->frontendModel->get('front_cms_services', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_about';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function aboutSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['subtitle' => ["label" => 'Subtitle', "rules" => 'trim|required']]);
            $this->validation->setRules(['content' => ["label" => 'Content', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $branchID = $this->frontendModel->getBranchID();
                // save information in the database
                $arrayData = ['title' => $this->request->getPost('title'), 'subtitle' => $this->request->getPost('subtitle'), 'content' => $this->request->getPost('content', false), 'about_image' => $this->uploadImage('about' . $branchID, 'about'), 'branch_id' => $branchID];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_about');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_about', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_about', $arrayData)->insert();
                }
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    public function aboutServiceSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['subtitle' => ["label" => 'Subtitle', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $branchID = $this->frontendModel->getBranchID();
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'title' => $this->request->getPost('title'), 'subtitle' => $this->request->getPost('subtitle'), 'parallax_image' => $this->uploadImage('service_parallax' . $branchID, 'about')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_services');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_services', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_services', $arrayData)->insert();
                }
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }
            echo json_encode($array);
        }
    }
    public function aboutCtaSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['cta_title' => ["label" => 'Cta Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['button_text' => ["label" => 'Button Text', "rules" => 'trim|required']]);
            $this->validation->setRules(['button_url' => ["label" => 'Button Url', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $array_cta = ['cta_title' => $this->request->getPost('cta_title'), 'button_text' => $this->request->getPost('button_text'), 'button_url' => $this->request->getPost('button_url')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_about');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_about', ['elements' => json_encode($array_cta)])->update();
                } else {
                    $this->db->table('front_cms_about', ['elements' => json_encode($array_cta)])->insert();
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
    public function aboutOptionsSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['page_title' => $this->request->getPost('page_title'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('about' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_about');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_about', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_about', $arrayData)->insert();
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
    public function faq()
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['faq'] = $this->frontendModel->get('front_cms_faq', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_faq';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function faqSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'title' => $this->request->getPost('title'), 'description' => $this->request->getPost('description', false)];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_faq');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_faq', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_faq', $arrayData)->insert();
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
    public function faqOptionSave()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['page_title' => $this->request->getPost('page_title'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('faq' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_faq');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_faq', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_faq', $arrayData)->insert();
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
    public function admission()
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['admission'] = $this->frontendModel->get('front_cms_admission', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_admission';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function saveAdmission()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['title' => ["label" => 'Title', "rules" => 'trim|required']]);
            $items = $this->request->getPost('addmissionfee');
            if (!empty($items)) {
                foreach ($items as $key => $value) {
                    if ($value['status'] == 1) {
                        $this->validation->setRules(['addmissionfee[' . $key . '][amount]' => ["label" => translate('amount'), "rules" => 'trim|numeric|required']]);
                    }
                }
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $feeElements = [];
                if (!empty($items)) {
                    foreach ($items as $value) {
                        if ($value['status'] == 1) {
                            $classID = $value['class_id'];
                            $feeElements[$classID] = ['fee_status' => $value['status'], 'amount' => $value['amount']];
                        }
                    }
                }
                $arrayData = ['branch_id' => $branchID, 'title' => $this->request->getPost('title'), 'description' => $this->request->getPost('description', false), 'terms_conditions_title' => $this->request->getPost('terms_conditions_title'), 'terms_conditions_description' => $this->request->getPost('terms_conditions_description', false), 'fee_elements' => json_encode($feeElements)];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_admission');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_admission', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_admission', $arrayData)->insert();
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
    public function saveAdmissionOption()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'meta_description' => $this->request->getPost('meta_description'), 'banner_image' => $this->uploadImage('admission' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_admission');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_admission', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_admission', $arrayData)->insert();
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
    public function saveOnlineAdmissionFields()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $systemFields = $this->request->getPost('system_fields');
            foreach ($systemFields as $key => $value) {
                $is_status = isset($value['status']) ? 1 : 0;
                $is_required = isset($value['required']) ? 1 : 0;
                $arrayData = ['fields_id' => $key, 'branch_id' => $branchID, 'system' => 1, 'status' => $is_status, 'required' => $is_required];
                $exist_privileges = $db->table('online_admission_fields')->get('online_admission_fields')->num_rows();
                if ($exist_privileges > 0) {
                    $this->db->table('online_admission_fields', $arrayData, ['fields_id' => $key, 'branch_id' => $branchID, 'system' => 1])->update();
                } else {
                    $this->db->table('online_admission_fields', $arrayData)->insert();
                }
            }
            $customFields = $this->request->getPost('custom_fields');
            foreach ($customFields as $key => $value) {
                $is_status = isset($value['status']) ? 1 : 0;
                $is_required = isset($value['required']) ? 1 : 0;
                $arrayData = ['fields_id' => $key, 'branch_id' => $branchID, 'system' => 0, 'status' => $is_status, 'required' => $is_required];
                $exist_privileges = $db->table('online_admission_fields')->get('online_admission_fields')->num_rows();
                if ($exist_privileges > 0) {
                    $this->db->table('online_admission_fields', $arrayData, ['fields_id' => $key, 'branch_id' => $branchID, 'system' => 0])->update();
                } else {
                    $this->db->table('online_admission_fields', $arrayData)->insert();
                }
            }
            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
        }
    }
    public function contact()
    {
        $branchID = $this->frontendModel->getBranchID();
        $this->data['branch_id'] = $branchID;
        $this->data['contact'] = $this->frontendModel->get('front_cms_contact', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_contact';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function contactSave()
    {
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['box_title' => ["label" => 'Box Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['box_description' => ["label" => 'Box Description', "rules" => 'trim|required']]);
            $this->validation->setRules(['form_title' => ["label" => 'Form Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['address' => ["label" => 'Address', "rules" => 'trim|required']]);
            $this->validation->setRules(['phone' => ["label" => 'Phone', "rules" => 'trim|required']]);
            $this->validation->setRules(['email' => ["label" => 'Email', "rules" => 'trim|required']]);
            $this->validation->setRules(['submit_text' => ["label" => 'Submit Text', "rules" => 'trim|required']]);
            $this->validation->setRules(['map_iframe' => ["label" => 'Map Iframe', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'box_title' => $this->request->getPost('box_title'), 'box_description' => $this->request->getPost('box_description'), 'form_title' => $this->request->getPost('form_title'), 'address' => $this->request->getPost('address'), 'phone' => $this->request->getPost('phone'), 'email' => $this->request->getPost('email'), 'submit_text' => $this->request->getPost('submit_text'), 'map_iframe' => $this->request->getPost('map_iframe', false)];
                // upload box image
                if (isset($_FILES["photo"]) && !empty($_FILES["photo"]['name'])) {
                    $imageNmae = $_FILES['photo']['name'];
                    $extension = pathinfo((string) $imageNmae, PATHINFO_EXTENSION);
                    $newLogoName = "contact-info-box{$branchID}." . $extension;
                    $image_path = './uploads/frontend/images/' . $newLogoName;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $image_path)) {
                        $arrayData['box_image'] = $newLogoName;
                    }
                }
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_contact');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_contact', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_contact', $arrayData)->insert();
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
    function contactOptionSave()
    {
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                access_denied();
            }
            $branchID = $this->frontendModel->getBranchID();
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $array_about = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('contact' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_contact');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_contact', $array_about)->update();
                } else {
                    $this->db->table('front_cms_contact', $array_about)->insert();
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
    // upload image
    public function uploadImage($img_name, $path)
    {
        $prev_image = $this->request->getPost('old_photo');
        $image = $_FILES['photo']['name'];
        $return_image = '';
        if ($image != '') {
            $destination = './uploads/frontend/' . $path . '/';
            $extension = pathinfo((string) $image, PATHINFO_EXTENSION);
            $image_path = $img_name . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], $destination . $image_path);
            // need to unlink previous slider
            if ($prev_image != $image_path && file_exists($destination . $prev_image)) {
                @unlink($destination . $prev_image);
            }
            $return_image = $image_path;
        } else {
            $return_image = $prev_image;
        }
        return $return_image;
    }
    private function saveHome($item, $branch_id, $data)
    {
        $this->db->table(['item_type' => $item, 'branch_id' => $branch_id])->where();
        $get = $builder->get('front_cms_home');
        if ($get->num_rows() > 0) {
            $this->db->table('id', $get->row()->id)->where();
            $this->db->table('front_cms_home', $data)->update();
        } else {
            $data['item_type'] = $item;
            $this->db->table('front_cms_home', $data)->insert();
        }
    }
    public function admit_card()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            $this->validation->setRules(['templete_id' => ["label" => 'Default Template', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'description' => $this->request->getPost('description', false), 'templete_id' => $this->request->getPost('templete_id'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('admit_card' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_admitcard');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_admitcard', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_admitcard', $arrayData)->insert();
                }
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
        $this->data['admitcard'] = $this->frontendModel->get('front_cms_admitcard', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_admit_card';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function exam_results()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'description' => $this->request->getPost('description', false), 'grade_scale' => isset($_POST['grade_scale']) ? 1 : 0, 'attendance' => isset($_POST['attendance']) ? 1 : 0, 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('exam_results' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_exam_results');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_exam_results', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_exam_results', $arrayData)->insert();
                }
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
        $this->data['admitcard'] = $this->frontendModel->get('front_cms_exam_results', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_exam_results';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function certificates()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'description' => $this->request->getPost('description', false), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('certificates' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_certificates');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_certificates', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_certificates', $arrayData)->insert();
                }
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
        $this->data['admitcard'] = $this->frontendModel->get('front_cms_certificates', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_certificates';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function gallery()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('gallery' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_gallery');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_gallery', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_gallery', $arrayData)->insert();
                }
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
        $this->data['admitcard'] = $this->frontendModel->get('front_cms_gallery', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_gallery';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
    public function news()
    {
        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            if (!get_permission('frontend_section', 'is_add')) {
                ajax_access_denied();
            }
            $this->validation->setRules(['page_title' => ["label" => 'Page Title', "rules" => 'trim|required']]);
            $this->validation->setRules(['description' => ["label" => 'Description', "rules" => 'required']]);
            $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[photo]']]);
            if (isset($_FILES["photo"]) && empty($_FILES["photo"]['name']) && empty($_POST['old_photo'])) {
                $this->validation->setRules(['photo' => ["label" => translate('photo'), "rules" => 'required']]);
            }
            if ($this->validation->run() == true) {
                // save information in the database
                $arrayData = ['branch_id' => $branchID, 'page_title' => $this->request->getPost('page_title'), 'description' => $this->request->getPost('description', false), 'meta_description' => $this->request->getPost('meta_description'), 'meta_keyword' => $this->request->getPost('meta_keyword'), 'banner_image' => $this->uploadImage('news' . $branchID, 'banners')];
                $this->db->table('branch_id', $branchID)->where();
                $get = $builder->get('front_cms_news');
                if ($get->num_rows() > 0) {
                    $this->db->table('id', $get->row()->id)->where();
                    $this->db->table('front_cms_news', $arrayData)->update();
                } else {
                    $this->db->table('front_cms_news', $arrayData)->insert();
                }
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
        $this->data['admitcard'] = $this->frontendModel->get('front_cms_news', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('website_page');
        $this->data['sub_page'] = 'frontend/section_news';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
    }
}