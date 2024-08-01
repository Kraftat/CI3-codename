<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Parents.php
 * @copyright : Reserved RamomCoder Team
 */
class Parents extends AdminController

{
    public $bulk;

    protected $db;


    public $load;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    /**
     * @var App\Models\ParentsModel
     */
    public $parents;

    public $validation;

    public $router;

    public $input;

    public $appLib;

    public $parentsModel;

    public $session;

    public function __construct()
    {


        parent::__construct();


        $this->bulk = service('bulk');$this->appLib = service('appLib'); 
$this->load->helpers('custom_fields');
        $this->email = new \App\Models\EmailModel();
        $this->parents = new \App\Models\ParentsModel();
    }

    public function index()
    {
        return redirect()->to(base_url('parents/view'));
    }

    /* parent form validation rules */
    protected function parent_validation()
    {
        $getBranch = $this->getBranchDetails();
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['relation' => ["label" => translate('relation'), "rules" => 'trim|required']]);
        $this->validation->setRules(['occupation' => ["label" => translate('occupation'), "rules" => 'trim|required']]);
        $this->validation->setRules(['income' => ["label" => translate('income'), "rules" => 'trim|numeric']]);
        $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'trim|required']]);
        $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|valid_email']]);
        $this->validation->setRules(['user_photo' => ["label" => translate('profile_picture'), "rules" => 'callback_photoHandleUpload[user_photo]']]);
        $this->validation->setRules(['facebook' => ["label" => 'Facebook', "rules" => 'valid_url']]);
        $this->validation->setRules(['twitter' => ["label" => 'Twitter', "rules" => 'valid_url']]);
        $this->validation->setRules(['linkedin' => ["label" => 'Linkedin', "rules" => 'valid_url']]);
        if ($getBranch['grd_generate'] == 0 || isset($_POST['parent_id'])) {
            $this->validation->setRules(['username' => ["label" => translate('username'), "rules" => 'trim|required|callback_unique_username']]);
            if (!isset($_POST['parent_id'])) {
                $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
                $this->validation->setRules(['retype_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[password]']]);
            }
        }

        // custom fields validation rules
        $classSlug = $this->router->fetch_class();
        $customFields = getCustomFields($classSlug);
        foreach ($customFields as $fieldsValue) {
            if ($fieldsValue['required']) {
                $fieldsID = $fieldsValue['id'];
                $fieldLabel = $fieldsValue['field_label'];
                $this->validation->setRules(["custom_fields[parents][" . $fieldsID . "]" => ["label" => $fieldLabel, "rules" => 'trim|required']]);
            }
        }
    }

    /* parents list user interface  */
    public function view()
    {
        // check access permission
        if (!get_permission('parent', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('parents_list');
        $this->data['sub_page'] = 'parents/view';
        $this->data['main_menu'] = 'parents';
        echo view('layout/index', $this->data);
    }

    /* user all information are prepared and stored in the database here */
    public function add()
    {
        if (!get_permission('parent', 'is_add')) {
            access_denied();
        }

        $getBranch = $this->getBranchDetails();
        if ($this->request->getPost('submit') == 'save') {
            // check saas parents add limit
            if ($this->appLib->isExistingAddon('saas') && !checkSaasLimit('parent')) {
                set_alert('error', translate('update_your_package'));
                redirect(site_url('dashboard'));
            }

            $this->parent_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                //save all employee information in the database
                $parentID = $this->parentsModel->save($post, $getBranch);
                // handle custom fields data
                $classSlug = $this->router->fetch_class();
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $parentID);
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('parents/add'));
            }
        }

        $this->data['getBranch'] = $getBranch;
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('add_parent');
        $this->data['sub_page'] = 'parents/add';
        $this->data['main_menu'] = 'parents';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    /* parents deactivate list user interface  */
    public function disable_authentication()
    {
        // check access permission
        if (!get_permission('parent_disable_authentication', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['auth'])) {
            if (!get_permission('parent_disable_authentication', 'is_add')) {
                access_denied();
            }

            $stafflist = $this->request->getPost('views_bulk_operations');
            if (isset($stafflist)) {
                foreach ($stafflist as $id) {
                    $this->db->table(['role' => 6, 'user_id' => $id])->where();
                    $this->db->table('login_credential')->update();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                set_alert('error', 'Please select at least one item');
            }

            return redirect()->to(base_url('parents/disable_authentication'));
        }

        $this->data['parentslist'] = $this->parentsModel->getParentList('', 0);
        $this->data['title'] = translate('deactivate_account');
        $this->data['sub_page'] = 'parents/disable_authentication';
        $this->data['main_menu'] = 'parents';
        echo view('layout/index', $this->data);
        return null;
    }

    /* profile preview and information are controlled here */
    public function profile($id = '')
    {
        if (!get_permission('parent', 'is_edit')) {
            access_denied();
        }

        if (isset($_POST['update'])) {
            $this->parent_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                //save all employee information in the database
                $this->parentsModel->save($post);
                // handle custom fields data
                $classSlug = $this->router->fetch_class();
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFields($customField, $id);
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                session()->set_flashdata('profile_tab', 1);
                return redirect()->to(base_url('parents/profile/' . $id));
            }

            session()->set_flashdata('profile_tab', 1);
        }

        $this->data['student_id'] = $id;
        $this->data['parent'] = $this->parentsModel->getSingleParent($id);
        $this->data['title'] = translate('parents_profile');
        $this->data['main_menu'] = 'parents';
        $this->data['sub_page'] = 'parents/profile';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    /* parents delete  */
    public function delete($id = '')
    {
        // check access permission
        if (!get_permission('parent', 'is_delete')) {
            access_denied();
        }

        // delete from parent table
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('parent')->delete();
        if ($db->affectedRows() > 0) {
            $this->db->table(['user_id' => $id, 'role' => 6])->where();
            $this->db->table('login_credential')->delete();
        }
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if (empty($username)) {
            return true;
        }

        $parentId = $this->request->getPost('parent_id');
        if (!empty($parentId)) {
            $loginId = $this->appLib->getCredentialId($parentId, 'parent');
            $this->db->where_not_in('id', $loginId);
        }

        $this->db->table('username')->where();
        $query = $builder->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_username", translate('already_taken'));
            return false;
        }

        return true;
    }

    /* password change here */
    public function change_password()
    {
        if (!get_permission('parent', 'is_edit')) {
            ajax_access_denied();
        }

        if (!isset($_POST['authentication'])) {
            $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim|required|min_length[4]']]);
        } else {
            $this->validation->setRules(['password' => ["label" => translate('password'), "rules" => 'trim']]);
        }

        if ($this->validation->run() !== false) {
            $parentID = $this->request->getPost('parent_id');
            $password = $this->request->getPost('password');
            $this->db->table('role')->where();
            $this->db->table('user_id')->where();
            $this->db->table('login_credential')->update();

            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    /* to set the children id in the session after the parent login */
    public function select_child($id = '')
    {
        if (is_parent_loggedin()) {
            $builder->select('e.student_id,e.id');
            $this->db->from('enroll as e');
            $builder->join('student as s', 's.id = e.student_id', 'inner');
            $this->db->table('s.parent_id')->where();
            $this->db->table('e.id')->where();
            $this->db->table('e.session_id')->where();
            $r = $builder->get()->row();
            if (!empty($r)) {
                session()->set('myChildren_id', $r->student_id);
                session()->set('enrollID', $r->id);
            }

            redirect($_SERVER['HTTP_REFERER']);
        } else {
            session()->set('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
    }

    public function my_children($id = '')
    {
        if (is_parent_loggedin()) {
            session()->set('myChildren_id', '');
            return redirect()->to(base_url('dashboard'));
        }

        session()->set('last_page', current_url());
        redirect(base_url(), 'refresh');
        return null;
    }
}
