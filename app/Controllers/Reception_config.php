<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Reception_config.php
 * @copyright : Reserved RamomCoder Team
 */
class Reception_config extends AdminController
{
    public $appLib;

    public $validation;

    public $input;

    public $applicationModel;

    public $db;

    public $load;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib');}

    public function index()
    {
        return redirect()->to(base_url('reception_config/reference'));
    }

    /* form validation rules */
    protected function f_Validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required']]);
    }

    public function reference()
    {
        if ($_POST !== [] && get_permission('config_reception', 'is_add')) {
            $this->f_Validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $arrayReference = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('enquiry_reference')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('config_reception', 'is_view')) {
            access_denied();
        }

        $this->data['result'] = $this->appLib->getTable('enquiry_reference');
        $this->data['title'] = translate('reference');
        $this->data['sub_page'] = 'reception_config/reference';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function response()
    {
        if ($_POST !== [] && get_permission('config_reception', 'is_add')) {
            $this->f_Validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $arrayReference = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('enquiry_response')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('config_reception', 'is_view')) {
            access_denied();
        }

        $this->data['result'] = $this->appLib->getTable('enquiry_response');
        $this->data['title'] = translate('response');
        $this->data['sub_page'] = 'reception_config/response';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function calling_purpose()
    {
        if ($_POST !== [] && get_permission('config_reception', 'is_add')) {
            $this->f_Validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $arrayReference = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('call_purpose')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('config_reception', 'is_view')) {
            access_denied();
        }

        $this->data['result'] = $this->appLib->getTable('call_purpose');
        $this->data['title'] = translate('calling_purpose');
        $this->data['sub_page'] = 'reception_config/calling_purpose';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function visiting_purpose()
    {
        if ($_POST !== [] && get_permission('config_reception', 'is_add')) {
            $this->f_Validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $arrayReference = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('visitor_purpose')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('config_reception', 'is_view')) {
            access_denied();
        }

        $this->data['result'] = $this->appLib->getTable('visitor_purpose');
        $this->data['title'] = translate('visiting_purpose');
        $this->data['sub_page'] = 'reception_config/visiting_purpose';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function complaint_type()
    {
        if ($_POST !== [] && get_permission('config_reception', 'is_add')) {
            $this->f_Validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $arrayReference = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('complaint_type')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        if (!get_permission('config_reception', 'is_view')) {
            access_denied();
        }

        $this->data['result'] = $this->appLib->getTable('complaint_type');
        $this->data['title'] = translate('complaint') . " " . translate('type');
        $this->data['sub_page'] = 'reception_config/complaint_type';
        $this->data['main_menu'] = 'reception';
        echo view('layout/index', $this->data);
    }

    public function edit($table = '')
    {
        if (!get_permission('config_reception', 'is_edit')) {
            ajax_access_denied();
        }

        $this->f_Validation();
        if ($this->validation->run() !== false) {
            $id = $this->request->getPost('id');
            $arrayData = ['name' => $this->request->getPost('name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table($table)->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    // get details send by ajax
    public function getDetails()
    {
        if (get_permission('config_reception', 'is_edit')) {
            $id = $this->request->getPost('id');
            $table = $this->request->getPost('table');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $query = $builder->get($table);
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function delete($table = '', $id = '')
    {
        if ((get_permission('config_reception', 'is_delete') & !empty($table)) !== 0) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table($table)->delete();
        }
    }
}
