<?php

namespace App\Models;

use CodeIgniter\Model;
class HostelModel extends Model
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    public function hostel_save($data)
    {
        $arrayData = array('name' => $data['name'], 'category_id' => $data['category_id'], 'address' => $data['hostel_address'], 'watchman' => $data['watchman_name'], 'remarks' => $data['remarks'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($data['hostel_id'])) {
            $builder->insert('hostel', $arrayData);
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $data['hostel_id']);
            $builder->update('hostel', $arrayData);
        }
    }
    public function category_save($data)
    {
        $arrayData = array('name' => $data['category_name'], 'type' => $data['type'], 'description' => $data['description'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($data['category_id'])) {
            $builder->insert('hostel_category', $arrayData);
        } else {
            $builder->where('id', $data['category_id']);
            $builder->update('hostel_category', $arrayData);
        }
    }
    public function room_save($data)
    {
        $arrayData = array('name' => $data['name'], 'hostel_id' => $data['hostel_id'], 'category_id' => $data['category_id'], 'no_beds' => $data['number_of_beds'], 'bed_fee' => $data['bed_fee'], 'remarks' => $data['remarks'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($data['room_id'])) {
            $builder->insert('hostel_room', $arrayData);
        } else {
            $builder->where('id', $data['room_id']);
            $builder->update('hostel_room', $arrayData);
        }
    }
    // allocation report with student name
    public function allocation_report($classID, $sectionID, $branchID)
    {
        $sql = "SELECT s.first_name, s.last_name, s.register_no, e.branch_id, e.id as enroll_id, h.name as hostel_name, r.name as room_name, r.bed_fee, rc.name as room_category\r\n        FROM student as s INNER JOIN enroll as e ON e.student_id = s.id INNER JOIN hostel as h ON h.id = s.hostel_id INNER JOIN hostel_room as r ON r.id = s.room_id LEFT JOIN\r\n        hostel_category as rc ON rc.id = r.category_id WHERE s.hostel_id != 0 AND s.room_id != 0 AND e.branch_id = " . $db->escape($branchID) . " AND e.class_id = " . $db->escape($classID) . " AND e.section_id = " . $db->escape($sectionID);
        $query = $db->query($sql)->result_array();
        return $query;
    }
    // get hostel information by hostel id and room id
    public function get_student_hostel($hostel_id, $room_id)
    {
        $builder->select('h.name as hostel_name,h.watchman,h.category_id,h.address,hc.name as hcategory_name,rc.name as rcategory_name,hr.name as room_name,hr.no_beds,hr.bed_fee');
        $builder->from('hostel as h');
        $builder->join('hostel_category as hc', 'hc.id = h.category_id', 'left');
        $builder->join('hostel_room as hr', 'hr.hostel_id = h.id', 'left');
        $builder->join('hostel_category as rc', 'rc.id = hr.category_id', 'left');
        $builder->where('hr.id', $room_id);
        $builder->where('h.id', $hostel_id);
        return $builder->get()->row();
    }
}



