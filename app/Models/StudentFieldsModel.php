<?php

namespace App\Models;

use CodeIgniter\Model;
class StudentFieldsModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    // test save and update function
    public function getOnlineStatus($prefix, $branchID)
    {
        $builder->select('if(oaf.status is null, student_fields.default_status, oaf.status) as status,if(oaf.required is null, student_fields.default_required, oaf.required) as required');
        $builder->from('student_fields');
        $builder->join('online_admission_fields as oaf', 'oaf.fields_id = student_fields.id and oaf.system = 1 and oaf.branch_id = ' . $branchID, 'left');
        $builder->where('student_fields.prefix', $prefix);
        $result = $builder->get()->row_array();
        return $result;
    }
    public function getOnlineStatusArr($branchID)
    {
        $builder->select('student_fields.id,student_fields.prefix,if(oaf.status is null, student_fields.default_status, oaf.status) as status,if(oaf.required is null, student_fields.default_required, oaf.required) as required');
        $builder->from('student_fields');
        $builder->join('online_admission_fields as oaf', 'oaf.fields_id = student_fields.id and oaf.system = 1 and oaf.branch_id = ' . $branchID, 'left');
        $builder->order_by('student_fields.id', 'asc');
        $result = $builder->get()->getResult();
        return $result;
    }
    public function getStatus($prefix, $branchID)
    {
        $builder->select('if(oaf.status is null, student_fields.default_status, oaf.status) as status,if(oaf.required is null, student_fields.default_required, oaf.required) as required');
        $builder->from('student_fields');
        $builder->join('student_admission_fields as oaf', 'oaf.fields_id = student_fields.id and oaf.branch_id = ' . $branchID, 'left');
        $builder->where('student_fields.prefix', $prefix);
        $result = $builder->get()->row_array();
        return $result;
    }
    public function getStatusArr($branchID)
    {
        $builder->select('student_fields.id,student_fields.prefix,if(oaf.status is null, student_fields.default_status, oaf.status) as status,if(oaf.required is null, student_fields.default_required, oaf.required) as required');
        $builder->from('student_fields');
        $builder->join('student_admission_fields as oaf', 'oaf.fields_id = student_fields.id and oaf.branch_id = ' . $branchID, 'left');
        $builder->order_by('student_fields.id', 'asc');
        $result = $builder->get()->getResult();
        return $result;
    }
    public function getOnlineCustomFields($branchID)
    {
        $builder->select('custom_field.*,if(oaf.status is null, custom_field.status, oaf.status) as fstatus,if(oaf.required is null, custom_field.required, oaf.required) as required');
        $builder->from('custom_field');
        $builder->join('online_admission_fields as oaf', 'oaf.fields_id = custom_field.id and oaf.system = 0 and oaf.branch_id = ' . $branchID, 'left');
        $builder->where('custom_field.form_to', 'student');
        $builder->where('custom_field.branch_id', $branchID);
        $builder->order_by('custom_field.field_order', 'asc');
        $fields = $builder->get()->getResult();
        return $fields;
    }
    public function getStatusProfile($prefix, $branchID)
    {
        $builder->select('if(oaf.status is null, student_fields.default_status, oaf.status) as status,if(oaf.required is null, student_fields.default_required, oaf.required) as required');
        $builder->from('student_fields');
        $builder->join('student_profile_fields as oaf', 'oaf.fields_id = student_fields.id and oaf.branch_id = ' . $branchID, 'left');
        $builder->where('student_fields.prefix', $prefix);
        $result = $builder->get()->row_array();
        return $result;
    }
    public function getStatusProfileArr($branchID)
    {
        $builder->select('student_fields.id,student_fields.prefix,if(oaf.status is null, student_fields.default_status, oaf.status) as status,if(oaf.required is null, student_fields.default_required, oaf.required) as required');
        $builder->from('student_fields');
        $builder->join('student_profile_fields as oaf', 'oaf.fields_id = student_fields.id and oaf.branch_id = ' . $branchID, 'left');
        $builder->order_by('student_fields.id', 'asc');
        $result = $builder->get()->getResult();
        return $result;
    }
}



