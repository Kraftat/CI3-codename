<?php

namespace App\Models;

use CodeIgniter\Model;
class CustomFieldModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function save($data, $defaultValue)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $required = isset($_POST['chk_required']) ? 1 : 0;
        $show_table = isset($_POST['chk_show_table']) ? 1 : 0;
        $status = isset($_POST['chk_active']) ? 1 : 0;
        $insertData = array('form_to' => $data['belongs_to'], 'field_label' => $data['field_label'], 'field_type' => $data['field_type'], 'default_value' => $defaultValue, 'required' => $required, 'status' => $status, 'show_on_table' => $show_table, 'field_order' => $data['field_order'], 'bs_column' => $data['bs_column'], 'branch_id' => $branchID);
        if (isset($data['custom_field_id'])) {
            $builder->where('id', $data['custom_field_id']);
            $builder->update('custom_field', $insertData);
        } else {
            $builder->insert('custom_field', $insertData);
        }
    }
}



