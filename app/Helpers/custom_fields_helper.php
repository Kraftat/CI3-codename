<?php

if (!function_exists('render_custom_Fields')) {
    function render_custom_Fields($belongs_to, $branch_id = null, $edit_id = false, $col_sm = null)
    {
        $db = \Config\Database::connect();
        $applicationModel = new \App\Models\ApplicationModel();
        
        if (empty($branch_id)) {
            $branch_id = $applicationModel->get_branch_id();
        }

        $builder = $db->table('custom_field');
        $builder->where('status', 1);
        $builder->where('form_to', $belongs_to);
        $builder->where('branch_id', $branch_id);
        $builder->orderBy('field_order', 'asc');
        $fields = $builder->get()->getResultArray();

        if (count($fields)) {
            $html = '';
            foreach ($fields as $field) {
                $fieldLabel = ucfirst((string) $field['field_label']);
                $fieldType = $field['field_type'];
                $bsColumn = $field['bs_column'] ?: 12;
                $required = $field['required'];
                $formTo = $field['form_to'];
                $fieldID = $field['id'];
                $value = $field['default_value'];

                if ($edit_id !== false) {
                    $return = get_custom_field_value($edit_id, $fieldID, $formTo);
                    if (!empty($return)) {
                        $value = $return;
                    }
                }

                if (isset($_POST['custom_fields'][$formTo][$fieldID])) {
                    $value = $_POST['custom_fields'][$formTo][$fieldID];
                }

                $html .= '<div class="col-md-' . $bsColumn . ' mb-sm"><div class="form-group">';
                $html .= '<label class="control-label">' . $fieldLabel . ($required == 1 ? ' <span class="required">*</span>' : '') . '</label>';

                switch ($fieldType) {
                    case 'text':
                    case 'number':
                    case 'email':
                        $html .= '<input type="' . $fieldType . '" class="form-control" autocomplete="off" name="custom_fields[' . $formTo . '][' . $fieldID . ']" value="' . $value . '" />';
                        break;
                    case 'textarea':
                        $html .= '<textarea class="form-control" name="custom_fields[' . $formTo . '][' . $fieldID . ']">' . $value . '</textarea>';
                        break;
                    case 'dropdown':
                        $html .= '<select class="form-control" data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity" name="custom_fields[' . $formTo . '][' . $fieldID . ']">';
                        $html .= dropdownField($field['default_value'], $value);
                        $html .= '</select>';
                        break;
                    case 'date':
                        $html .= '<input type="text" class="form-control" data-plugin-datepicker autocomplete="off" name="custom_fields[' . $formTo . '][' . $fieldID . ']" value="' . $value . '" />';
                        break;
                    case 'checkbox':
                        $html .= '<div class="checkbox-replace"><label class="i-checks"><input type="checkbox" name="custom_fields[' . $formTo . '][' . $fieldID . ']" value="1" ' . ($value == 1 ? 'checked' : '') . '><i></i>' . $fieldLabel . '</label></div>';
                        break;
                }

                $html .= '<span class="error">' . \Config\Services::validation()->getError('custom_fields[' . $formTo . '][' . $fieldID . ']') . '</span>';
                $html .= '</div></div>';
            }
            return $html;
        }
        return '';
    }
}

if (!function_exists('dropdownField')) {
    function dropdownField($default, $value)
    {
        $options = explode(',', (string) $default);
        $input = '<option value="">Select</option>';
        foreach ($options as $option_value) {
            $input .= '<option value="' . slugify($option_value) . '" ' . (slugify($option_value) == $value ? 'selected' : '') . '>' . ucfirst($option_value) . '</option>';
        }
        return $input;
    }
}

if (!function_exists('getCustomFields')) {
    function getCustomFields($belong_to, $branchID = '')
    {
        $db = \Config\Database::connect();
        $applicationModel = new \App\Models\ApplicationModel();

        if (empty($branchID)) {
            $branchID = $applicationModel->get_branch_id();
        }

        $builder = $db->table('custom_field');
        $builder->where('status', 1);
        $builder->where('form_to', $belong_to);
        $builder->where('branch_id', $branchID);
        $builder->orderBy('field_order', 'asc');
        return $builder->get()->getResultArray();
    }
}

if (!function_exists('saveCustomFields')) {
    function saveCustomFields($post, $userID)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_fields_values');

        foreach ($post as $key => $value) {
            $insertData = [
                'field_id' => $key,
                'relid' => $userID,
                'value' => $value,
            ];

            $builder->where('relid', $userID);
            $builder->where('field_id', $key);
            $query = $builder->get();

            if ($query->getNumRows() > 0) {
                $results = $query->getRow();
                $builder->where('id', $results->id);
                $builder->update($insertData);
            } else {
                $builder->insert($insertData);
            }
        }
    }
}

if (!function_exists('get_custom_field_value')) {
    function get_custom_field_value($rel_id, $field_id, $belongs_to)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_field');
        $builder->select('custom_fields_values.value');
        $builder->join('custom_fields_values', 'custom_fields_values.field_id = custom_field.id AND custom_fields_values.relid = ' . $rel_id, 'inner');
        $builder->where('custom_field.form_to', $belongs_to);
        $builder->where('custom_fields_values.field_id', $field_id);
        $row = $builder->get()->getRowArray();

        return $row['value'] ?? null;
    }
}

if (!function_exists('custom_form_table')) {
    function custom_form_table($belong_to, $branch_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_field');
        $builder->where('status', 1);
        $builder->where('form_to', $belong_to);
        $builder->where('show_on_table', 1);
        $builder->where('branch_id', $branch_id);
        $builder->orderBy('field_order', 'asc');
        return $builder->get()->getResultArray();
    }
}

if (!function_exists('get_table_custom_field_value')) {
    function get_table_custom_field_value($field_id, $rel_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_fields_values');
        $builder->where('relid', $rel_id);
        $builder->where('field_id', $field_id);
        $row = $builder->get()->getRowArray();

        return $row['value'] ?? null;
    }
}

if (!function_exists('render_online_custom_fields')) {
    function render_online_custom_fields($belongs_to, $branch_id = null, $edit_id = false, $col_sm = null)
    {
        $db = \Config\Database::connect();
        $applicationModel = new \App\Models\ApplicationModel();

        if (empty($branch_id)) {
            $branch_id = $applicationModel->get_branch_id();
        }

        if ($edit_id === false) {
            $builder = $db->table('custom_field');
            $builder->select('custom_field.*, IF(oaf.status IS NULL, custom_field.status, oaf.status) AS fstatus, IF(oaf.required IS NULL, custom_field.required, oaf.required) AS required');
            $builder->join('online_admission_fields AS oaf', 'oaf.fields_id = custom_field.id AND oaf.system = 0 AND oaf.branch_id = ' . $branch_id, 'left');
            $builder->where('custom_field.status', 1);
            $builder->where('custom_field.form_to', $belongs_to);
            $builder->where('custom_field.branch_id', $branch_id);
            $builder->orderBy('custom_field.field_order', 'asc');
            $fields = $builder->get()->getResultArray();
        } else {
            $builder = $db->table('custom_field');
            $builder->select('*, status AS fstatus');
            $builder->where('form_to', $belongs_to);
            $builder->where('branch_id', $branch_id);
            $builder->orderBy('field_order', 'asc');
            $fields = $builder->get()->getResultArray();
        }

        if (count($fields)) {
            $html = '';
            foreach ($fields as $field) {
                if ($field['fstatus'] == 1) {
                    $fieldLabel = ucfirst((string) $field['field_label']);
                    $fieldType = $field['field_type'];
                    $bsColumn = $field['bs_column'] ?: 12;
                    $required = $field['required'];
                    $formTo = $field['form_to'];
                    $fieldID = $field['id'];
                    $value = $field['default_value'];

                    if ($edit_id !== false) {
                        $return = get_online_custom_field_value($edit_id, $fieldID, $formTo);
                        if (!empty($return)) {
                            $value = $return;
                        }
                    }

                    if (isset($_POST['custom_fields'][$formTo][$fieldID])) {
                        $value = $_POST['custom_fields'][$formTo][$fieldID];
                    }

                    $html .= '<div class="col-md-' . $bsColumn . ' mb-sm"><div class="form-group">';
                    $html .= '<label class="control-label">' . $fieldLabel . ($required == 1 ? ' <span class="required">*</span>' : '') . '</label>';

                    switch ($fieldType) {
                        case 'text':
                        case 'number':
                        case 'email':
                            $html .= '<input type="' . $fieldType . '" class="form-control" autocomplete="off" name="custom_fields[' . $formTo . '][' . $fieldID . ']" value="' . $value . '" />';
                            break;
                        case 'textarea':
                            $html .= '<textarea class="form-control" name="custom_fields[' . $formTo . '][' . $fieldID . ']">' . $value . '</textarea>';
                            break;
                        case 'dropdown':
                            $html .= '<select class="form-control" data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity" name="custom_fields[' . $formTo . '][' . $fieldID . ']">';
                            $html .= dropdownField($field['default_value'], $value);
                            $html .= '</select>';
                            break;
                        case 'date':
                            $html .= '<input type="text" class="form-control" data-plugin-datepicker autocomplete="off" name="custom_fields[' . $formTo . '][' . $fieldID . ']" value="' . $value . '" />';
                            break;
                        case 'checkbox':
                            $html .= '<div class="checkbox-replace"><label class="i-checks"><input type="checkbox" name="custom_fields[' . $formTo . '][' . $fieldID . ']" value="1" ' . ($value == 1 ? 'checked' : '') . '><i></i>' . $fieldLabel . '</label></div>';
                            break;
                    }

                    $html .= '<span class="error">' . \Config\Services::validation()->getError('custom_fields[' . $formTo . '][' . $fieldID . ']') . '</span>';
                    $html .= '</div></div>';
                }
            }
            return $html;
        }
        return '';
    }
}

if (!function_exists('saveCustomFieldsOnline')) {
    function saveCustomFieldsOnline($post, $userID)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_fields_online_values');

        foreach ($post as $key => $value) {
            $insertData = [
                'field_id' => $key,
                'relid' => $userID,
                'value' => $value,
            ];

            $builder->where('relid', $userID);
            $builder->where('field_id', $key);
            $query = $builder->get();

            if ($query->getNumRows() > 0) {
                $results = $query->getRow();
                $builder->where('id', $results->id);
                $builder->update($insertData);
            } else {
                $builder->insert($insertData);
            }
        }
    }
}

if (!function_exists('get_online_custom_field_value')) {
    function get_online_custom_field_value($rel_id, $field_id, $belongs_to)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_field');
        $builder->select('custom_fields_online_values.value');
        $builder->join('custom_fields_online_values', 'custom_fields_online_values.field_id = custom_field.id AND custom_fields_online_values.relid = ' . $rel_id, 'inner');
        $builder->where('custom_field.form_to', $belongs_to);
        $builder->where('custom_fields_online_values.field_id', $field_id);
        $row = $builder->get()->getRowArray();

        return $row['value'] ?? null;
    }
}

if (!function_exists('get_online_custom_table_custom_field_value')) {
    function get_online_custom_table_custom_field_value($field_id, $rel_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('custom_fields_online_values');
        $builder->where('relid', $rel_id);
        $builder->where('field_id', $field_id);
        $row = $builder->get()->getRowArray();

        return $row['value'] ?? null;
    }
}

if (!function_exists('getOnlineCustomFields')) {
    function getOnlineCustomFields($belong_to, $branchID = '')
    {
        $db = \Config\Database::connect();
        $applicationModel = new \App\Models\ApplicationModel();

        if (empty($branchID)) {
            $branchID = $applicationModel->get_branch_id();
        }

        $builder = $db->table('custom_field');
        $builder->select('custom_field.*, IF(oaf.status IS NULL, custom_field.status, oaf.status) AS fstatus, IF(oaf.required IS NULL, custom_field.required, oaf.required) AS required');
        $builder->join('online_admission_fields AS oaf', 'oaf.fields_id = custom_field.id AND oaf.system = 0 AND oaf.branch_id = ' . $branchID, 'left');
        $builder->where('custom_field.status', 1);
        $builder->where('custom_field.form_to', $belong_to);
        $builder->where('custom_field.branch_id', $branchID);
        $builder->orderBy('custom_field.field_order', 'asc');
        return $builder->get()->getResultArray();
    }
}
