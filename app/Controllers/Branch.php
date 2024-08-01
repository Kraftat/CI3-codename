<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Branch.php
 * @copyright : Reserved RamomCoder Team
 */
class Branch extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\BranchModel
     */
    public $branch;

    public $input;

    public $validation;

    public $branchModel;

    public $load;

    public $session;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->branch = new \App\Models\BranchModel();
    }

    /* branch all data are prepared and stored in the database here */
    public function index()
    {
        if (is_superadmin_loggedin()) {
            if ($this->request->getPost('submit') == 'save') {
                $this->validation->setRules(['branch_name' => ["label" => translate('branch_name'), "rules" => 'required|callback_unique_name']]);
                $this->validation->setRules(['school_name' => ["label" => translate('school_name'), "rules" => 'required']]);
                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'required|valid_email']]);
                $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'required']]);
                $this->validation->setRules(['currency' => ["label" => translate('currency'), "rules" => 'required']]);
                $this->validation->setRules(['currency_symbol' => ["label" => translate('currency_symbol'), "rules" => 'required']]);
                if ($this->validation->run() == true) {
                    $post = $this->request->getPost();
                    $response = $this->branchModel->save($post);
                    if ($response) {
                        set_alert('success', translate('information_has_been_saved_successfully'));
                    }

                    return redirect()->to(base_url('branch'));
                }

                $this->data['validation_error'] = true;
            }

            $this->data['title'] = translate('branch');
            $this->data['sub_page'] = 'branch/add';
            $this->data['main_menu'] = 'branch';
            $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
            echo view('layout/index', $this->data);
        } else {
            session()->set('last_page', current_url());
            redirect(base_url(), 'refresh');
        }

        return null;
    }

    /* branch information update here */
    public function edit($id = '')
    {
        if (is_superadmin_loggedin()) {
            if ($this->request->getPost('submit') == 'save') {
                $this->validation->setRules(['branch_name' => ["label" => translate('branch_name'), "rules" => 'required|callback_unique_name']]);
                $this->validation->setRules(['school_name' => ["label" => translate('school_name'), "rules" => 'required']]);
                $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'required|valid_email']]);
                $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'required']]);
                $this->validation->setRules(['currency' => ["label" => translate('currency'), "rules" => 'required']]);
                $this->validation->setRules(['currency_symbol' => ["label" => translate('currency_symbol'), "rules" => 'required']]);
                if ($this->validation->run() == true) {
                    $post = $this->request->getPost();
                    $response = $this->branchModel->save($post, $id);
                    if ($response) {
                        set_alert('success', translate('information_has_been_updated_successfully'));
                    }

                    return redirect()->to(base_url('branch'));
                }
            }

            $this->data['data'] = $this->branchModel->getSingle('branch', $id, true);
            $this->data['title'] = translate('branch');
            $this->data['sub_page'] = 'branch/edit';
            $this->data['main_menu'] = 'branch';
            $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
            echo view('layout/index', $this->data);
        } else {
            session()->set('last_page', current_url());
            redirect(base_url(), 'refresh');
        }

        return null;
    }

    /* delete information */
    public function delete_data($id = '')
    {
        if (is_superadmin_loggedin()) {
            $this->db->table('id')->where();
            $this->db->table('branch')->delete();
            //delete branch all staff
            $result = $db->table('staff')->get('staff')->getResult();
            foreach ($result as $value) {
                $this->db->table('user_id')->where();
                $this->db->table('login_credential')->delete();
                $this->db->table('id')->where();
                $this->db->table('staff')->delete();
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    /* unique valid branch name verification is done here */
    public function unique_name($name)
    {
        $branchId = $this->request->getPost('branch_id');
        if (!empty($branchId)) {
            $this->db->where_not_in('id', $branchId);
        }

        $this->db->table('name')->where();
        $name = $builder->get('branch')->num_rows();
        if ($name == 0) {
            return true;
        }

        $this->validation->setRule("unique_name", translate('already_taken'));
        return false;
    }
}
