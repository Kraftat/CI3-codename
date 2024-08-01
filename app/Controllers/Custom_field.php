<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\CustomFieldModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Custom_field.php
 * @copyright : Reserved RamomCoder Team
 */
class Custom_field extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\CustomFieldModel
     */
    public $customField;

    public $load;

    public $validation;

    public $input;

    public $custom_fieldModel;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->customField = new \App\Models\CustomFieldModel();
        $this->load->helpers('custom_fields');
    }

    public function index()
    {
        if (!get_permission('custom_field', 'is_view')) {
            access_denied();
        }

        $this->data['customfield'] = $this->appLib->getTable('custom_field');
        $this->data['sub_page'] = 'custom_field/index';
        $this->data['main_menu'] = 'settings';
        $this->data['title'] = translate('custom_field');
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('custom_field', 'is_edit')) {
            access_denied();
        }

        $this->data['customfield'] = $this->appLib->getTable('custom_field', ['t.id' => $id], true);
        $this->data['sub_page'] = 'custom_field/edit';
        $this->data['main_menu'] = 'settings';
        $this->data['title'] = translate('custom_field');
        echo view('layout/index', $this->data);
    }

    public function save()
    {
        if (isset($data['custom_field_id'])) {
            if (!get_permission('custom_field', 'is_edit')) {
                ajax_access_denied();
            }
        } elseif (!get_permission('custom_field', 'is_add')) {
            ajax_access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['belongs_to' => ["label" => translate('belongs_to'), "rules" => 'trim|required']]);
        $this->validation->setRules(['field_label' => ["label" => translate('field_label'), "rules" => 'trim|required']]);
        $this->validation->setRules(['field_type' => ["label" => translate('field_type'), "rules" => 'trim|required']]);
        $this->validation->setRules(['bs_column' => ["label" => translate('bs_column'), "rules" => 'trim|required']]);
        $this->validation->setRules(['field_order' => ["label" => translate('field_order'), "rules" => 'trim|required|numeric']]);

        $fieldType = $this->request->getPost('field_type');
        if ($fieldType == 'dropdown') {
            $this->validation->setRules(['dropdown_default_value' => ["label" => translate('default_value'), "rules" => 'trim|required']]);
            $defaultValue = $this->request->getPost('dropdown_default_value');
        } elseif ($fieldType == 'checkbox') {
            $defaultValue = $this->request->getPost('checkbox_default_value');
        } else {
            $defaultValue = $this->request->getPost('com_default_value');
        }

        if ($this->validation->run() !== false) {
            $this->custom_fieldModel->save($this->request->getPost(), $defaultValue);
            set_alert('success', translate('information_has_been_saved_successfully'));
            $url = base_url('custom_field');
            $array = ['status' => 'success', 'url' => $url];
        } else {
            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }

    public function delete($id = '')
    {
        // check access permission
        if (get_permission('custom_field', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('custom_field')->delete();
            $this->db->table('field_id')->where();
            $this->db->table('custom_fields_values')->delete();
        } else {
            set_alert('error', translate('access_denied'));
        }
    }

    public function getFieldsByBranch()
    {
        $belongsTo = $this->request->getPost('belongs_to');
        echo render_custom_Fields($belongsTo);
    }

    public function status()
    {
        if (!get_permission('custom_field', 'is_edit')) {
            ajax_access_denied();
        }

        $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $arrayData['status'] = $status == 'true' ? 1 : 0;
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('custom_field')->update();
        $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
        echo json_encode($return);
    }
}
