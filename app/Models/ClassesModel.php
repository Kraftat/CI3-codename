<?php

namespace App\Models;

use CodeIgniter\Model;
class ClassesModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getTeacherAllocation($branch_id = '')
    {
        $builder->select('ta.*,st.name as teacher_name,st.staff_id as teacher_id,c.name as class_name,c.branch_id,s.name as section_name');
        $builder->from('teacher_allocation as ta');
        $builder->join('staff as st', 'st.id = ta.teacher_id', 'left');
        $builder->join('class as c', 'c.id = ta.class_id', 'left');
        $builder->join('section as s', 's.id = ta.section_id', 'left');
        $builder->order_by('ta.id', 'ASC');
        $this->db->table('ta.session_id', get_session_id())->where();
        if (!empty($branch_id)) {
            $builder->where('c.branch_id', $branch_id);
        }
        return $builder->get();
    }
    public function teacherAllocationSave($data)
    {
        $arrayData = array('branch_id' => $this->applicationModel->get_branch_id(), 'session_id' => get_session_id(), 'class_id' => $data['class_id'], 'section_id' => $data['section_id'], 'teacher_id' => $data['staff_id']);
        if (!isset($data['allocation_id'])) {
            if (get_permission('assign_class_teacher', 'is_add')) {
                $builder->insert('teacher_allocation', $arrayData);
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
        } else {
            if (get_permission('assign_class_teacher', 'is_edit')) {
                $builder->where('id', $data['allocation_id']);
                $builder->update('teacher_allocation', $arrayData);
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
        }
    }
}



