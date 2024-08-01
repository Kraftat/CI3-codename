<?php

namespace App\Models;

use CodeIgniter\Model;
class TransportModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function route_save($data)
    {
        $arraRoute = array('name' => $data['route_name'], 'start_place' => $data['start_place'], 'stop_place' => $data['stop_place'], 'remarks' => $data['remarks'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($data['route_id'])) {
            $builder->insert('transport_route', $arraRoute);
        } else {
            $builder->where('id', $data['route_id']);
            $builder->update('transport_route', $arraRoute);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function vehicle_save($data)
    {
        $arraVehicle = array('vehicle_no' => $data['vehicle_no'], 'capacity' => $data['capacity'], 'insurance_renewal' => $data['insurance_renewal'], 'driver_name' => $data['driver_name'], 'driver_phone' => $data['driver_phone'], 'driver_license' => $data['driver_license'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($data['vehicle_id'])) {
            $builder->insert('transport_vehicle', $arraVehicle);
        } else {
            $builder->where('id', $data['vehicle_id']);
            $builder->update('transport_vehicle', $arraVehicle);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function stoppage_save($data)
    {
        $arraStoppage = array('stop_position' => $data['stop_position'], 'stop_time' => date("H:i", strtotime($data['stop_time'])), 'route_fare' => $data['route_fare'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($data['stoppage_id'])) {
            $builder->insert('transport_stoppage', $arraStoppage);
        } else {
            $builder->where('id', $data['stoppage_id']);
            $builder->update('transport_stoppage', $arraStoppage);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    // allocation report with student name
    public function allocation_report($classID, $sectionID, $branchID)
    {
        $builder->select('ta.*,r.name as route_name,v.vehicle_no,sp.stop_position,sp.stop_time,sp.route_fare,s.first_name,s.last_name,s.register_no,e.id as enroll_id');
        $builder->from('transport_assign as ta');
        $builder->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $builder->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $builder->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $builder->join('student as s', 's.route_id = ta.route_id AND s.vehicle_id = ta.vehicle_id', 'left');
        $builder->join('enroll as e', 'e.student_id = s.id', 'left');
        $builder->where('ta.branch_id', $branchID);
        $builder->where('e.class_id', $classID);
        $builder->where('e.section_id', $sectionID);
        return $builder->get()->result_array();
    }
    // get route,vehicle,stoppage assign list
    public function getAssignList($branch_id = '')
    {
        $builder->select('ta.route_id,ta.stoppage_id,ta.branch_id,r.name,r.start_place,r.stop_place,sp.stop_position,sp.stop_time,sp.route_fare');
        $builder->from('transport_assign as ta');
        $builder->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $builder->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $this->db->group_by(array('ta.route_id', 'ta.stoppage_id', 'ta.branch_id'));
        if (!empty($branch_id)) {
            $builder->where('ta.branch_id', $branch_id);
        }
        return $builder->get()->result_array();
    }
    public function getAssignEdit($id = '')
    {
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id', get_loggedin_branch_id())->where();
        }
        $builder->where('route_id', $id);
        $builder->limit(1);
        return $builder->get('transport_assign')->row_array();
    }
    // get vehicle list by route_id
    public function get_vehicle_list($route_id)
    {
        $builder->select('ta.vehicle_id,v.vehicle_no');
        $builder->from('transport_assign as ta');
        $builder->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $builder->where('ta.route_id', $route_id);
        $vehicles = $builder->get()->getResult();
        $name_list = '';
        foreach ($vehicles as $row) {
            $name_list .= '- ' . $row->vehicle_no . '<br>';
        }
        return $name_list;
    }
    // get route information by route id and vehicle id
    public function get_student_route($route_id, $vehicle_id)
    {
        $builder->select('ta.route_id,ta.stoppage_id,ta.vehicle_id,r.name as route_name,r.start_place,r.stop_place,sp.stop_position,sp.stop_time,sp.route_fare,v.vehicle_no,v.driver_name,v.driver_phone');
        $builder->from('transport_assign as ta');
        $builder->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $builder->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $builder->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $builder->where('ta.route_id', $route_id);
        $builder->where('ta.vehicle_id', $vehicle_id);
        return $builder->get()->row();
    }
}



