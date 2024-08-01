<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\EmailModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Award.php
 * @copyright : Reserved RamomCoder Team
 */
class Award extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\AwardModel
     */
    public $award;

    /**
     * @var App\Models\EmailModel
     */
    public $email;

    public $validation;

    public $input;

    public $awardModel;

    public $emailModel;

    public $load;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->award = new \App\Models\AwardModel();
        $this->email = new \App\Models\EmailModel();
    }

    /* award form validation rules */
    protected function award_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'trim|required']]);
        $this->validation->setRules(['user_id' => ["label" => translate('winner'), "rules" => 'trim|required']]);
        $this->validation->setRules(['award_name' => ["label" => translate('award_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['gift_item' => ["label" => translate('gift_item'), "rules" => 'trim|required']]);
        $this->validation->setRules(['award_reason' => ["label" => translate('award_reason'), "rules" => 'trim|required']]);
        $this->validation->setRules(['given_date' => ["label" => translate('given_date'), "rules" => 'trim|required']]);

        $roleID = $this->request->getPost('role_id');
        if ($roleID == 7) {
            $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
        }
    }

    public function index()
    {
        if (!get_permission('award', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('award', 'is_add')) {
            $roleID = $this->request->getPost('role_id');
            $this->award_validation();
            if ($this->validation->run() !== false) {
                $data = $this->request->getPost();
                $this->awardModel->save($data);
                $this->emailModel->sentAward($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('award');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['awardlist'] = $this->awardModel->getList();
        $this->data['title'] = translate('award');
        $this->data['sub_page'] = 'award/index';
        $this->data['main_menu'] = 'award';
        echo view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('award', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('award')->delete();
        }
    }

    public function edit($id = '')
    {
        if (!get_permission('award', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->award_validation();
            if ($this->validation->run() !== false) {
                $data = $this->request->getPost();
                $this->awardModel->save($data);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('award');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['award'] = $this->awardModel->getList($id, true);
        $this->data['title'] = translate('award');
        $this->data['sub_page'] = 'award/edit';
        $this->data['main_menu'] = 'award';
        echo view('layout/index', $this->data);
    }
}
