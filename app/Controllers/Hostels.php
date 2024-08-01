<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\HostelModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Hostels.php
 * @copyright : Reserved RamomCoder Team
 */
class Hostels extends AdminController

{
    public $appLib;

    protected $db;



    /**
     * @var App\Models\HostelModel
     */
    public $hostel;

    public $validation;

    public $input;

    public $hostelModel;

    public $load;

    public $applicationModel;

    public function __construct()
    {



        parent::__construct();

        $this->appLib = service('appLib'); 
$this->hostel = new \App\Models\HostelModel();
    }

    /* hostel form validation rules */
    protected function hostel_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('hostel_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['category_id' => ["label" => translate('category'), "rules" => 'required']]);
        $this->validation->setRules(['watchman_name' => ["label" => translate('watchman_name'), "rules" => 'trim|required']]);
    }

    public function index()
    {
        if (!get_permission('hostel', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('hostel', 'is_add')) {
                ajax_access_denied();
            }

            $this->hostel_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all hostel information in the database file
                $this->hostelModel->hostel_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('hostels');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['hostellist'] = $this->appLib->getTable('hostel');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('hostel_master');
        $this->data['sub_page'] = 'hostels/index';
        $this->data['main_menu'] = 'hostels';
        echo view('layout/index', $this->data);
    }

    // the hostel information is updated here
    public function edit($id = '')
    {
        if (!get_permission('hostel', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->hostel_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all hostel information in the database file
                $this->hostelModel->hostel_save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('hostels');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['hostel'] = $this->appLib->getTable('hostel', ['t.id' => $id], true);
        $this->data['title'] = translate('hostel_master');
        $this->data['sub_page'] = 'hostels/edit';
        $this->data['main_menu'] = 'hostels';
        echo view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('hostel', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('hostel')->delete();
        }
    }

    /* category form validation rules */
    protected function category_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['category_name' => ["label" => translate('category'), "rules" => 'trim|required|callback_unique_category']]);
        $this->validation->setRules(['type' => ["label" => translate('category_for'), "rules" => 'required']]);
    }

    // category information are prepared and stored in the database here
    public function category()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('hostel_category', 'is_add')) {
                access_denied();
            }

            $this->category_validation();
            if ($this->validation->run() !== false) {
                //save hostel type information in the database file
                $this->hostelModel->category_save($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('hostels/category'));
            }
        }

        $this->data['categorylist'] = $this->appLib->getTable('hostel_category');
        $this->data['title'] = translate('category');
        $this->data['sub_page'] = 'hostels/category';
        $this->data['main_menu'] = 'hostels';
        echo view('layout/index', $this->data);
        return null;
    }

    public function category_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('hostel_category', 'is_edit')) {
                ajax_access_denied();
            }

            $this->category_validation();
            if ($this->validation->run() !== false) {
                //update exam term information in the database file
                $this->hostelModel->category_save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('hostels/category');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function category_delete($id)
    {
        if (get_permission('hostel_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('hostel_category')->delete();
        }
    }

    // validate here, if the check type name
    public function unique_category($name)
    {
        $categoryID = $this->request->getPost('category_id');
        $this->request->getPost('type');
        $this->applicationModel->get_branch_id();
        if (!empty($categoryID)) {
            $this->db->where_not_in('id', $categoryID);
        }

        $this->db->table('name')->where();
        $this->db->table('type')->where();
        $this->db->table('branch_id')->where();
        $query = $builder->get('hostel_category');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_category", translate('already_taken'));
            return false;
        }

        return true;
    }

    // room information are prepared and stored in the database here
    public function room()
    {
        if (!get_permission('hostel_room', 'is_view')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('hostel_room', 'is_add')) {
                ajax_access_denied();
            }

            $this->room_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all hostel information in the database file
                $this->hostelModel->room_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['roomlist'] = $this->appLib->getTable('hostel_room');
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('hostel_room');
        $this->data['sub_page'] = 'hostels/room';
        $this->data['main_menu'] = 'hostels';
        echo view('layout/index', $this->data);
    }

    // the room information is updated here
    public function edit_room($id = '')
    {
        if (!get_permission('hostel_room', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->room_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all hostel information in the database file
                $this->hostelModel->room_save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('hostels/room');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['room'] = $this->appLib->getTable('hostel_room', ['t.id' => $id], true);
        $this->data['title'] = translate('hostels_room_edit');
        $this->data['sub_page'] = 'hostels/room_edit';
        $this->data['main_menu'] = 'hostels';
        echo view('layout/index', $this->data);
    }

    public function delete_room($id = '')
    {
        if (get_permission('hostel_room', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('hostel_room')->delete();
        }
    }

    // validate here, if the check room name
    public function unique_room_name($name)
    {
        $roomId = $this->request->getPost('room_id');
        $this->applicationModel->get_branch_id();
        if (!empty($roomId)) {
            $this->db->where_not_in('id', $roomId);
        }

        $this->db->table('name')->where();
        $this->db->table('branch_id')->where();
        $query = $builder->get('hostel_room');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_room_name", translate('already_taken'));
            return false;
        }

        return true;
    }

    // student allocation report is generated here
    public function allocation_report()
    {
        if (!get_permission('hostel_allocation', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['allocationlist'] = $this->hostelModel->allocation_report($classID, $sectionID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('allocation_list');
        $this->data['sub_page'] = 'hostels/allocation';
        $this->data['main_menu'] = 'hostels';
        echo view('layout/index', $this->data);
    }

    public function allocation_delete($id)
    {
        if (get_permission('hostel_allocation', 'is_delete')) {
            $builder->select('student_id');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $studentId = $builder->get('enroll')->row()->student_id;
            if (!empty($studentId)) {
                $arrayData = ['hostel_id' => 0, 'room_id' => 0];
                $this->db->table('id')->where();
                $this->db->table('student')->update();
            }
        }
    }

    // get a list of branch based information
    public function getCategoryByBranch()
    {
        $this->request->getPost('type');
        $branchID = $this->applicationModel->get_branch_id();
        $html = '';
        if (!empty($branchID)) {
            $result = $db->table('hostel_category')->get('hostel_category')->result_array();
            if (count($result) > 0) {
                echo '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    /* get a list of branch based information */
    public function getRoomByHostel()
    {
        $html = '';
        $hostelID = $this->request->getPost('hostel_id');
        if (!empty($hostelID)) {
            $rooms = $db->table('hostel_room')->get('hostel_room')->result_array();
            if (count($rooms) > 0) {
                echo '<option value="">' . translate('select') . '</option>';
                foreach ($rooms as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . get_type_name_by_id('hostel_category', $row['category_id']) . ')' . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_hostel_first') . '</option>';
        }

        echo $html;
    }

    public function getCategoryDetails()
    {
        $this->request->getPost('id');
        $this->db->table('id')->where();
        $query = $builder->get('hostel_category');
        $result = $query->row_array();
        echo json_encode($result);
    }

    protected function room_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('hostel_name'), "rules" => 'trim|required|callback_unique_room_name']]);
        $this->validation->setRules(['hostel_id' => ["label" => translate('hostel_name'), "rules" => 'required']]);
        $this->validation->setRules(['category_id' => ["label" => translate('category'), "rules" => 'trim|required']]);
        $this->validation->setRules(['number_of_beds' => ["label" => translate('no_of_beds'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['bed_fee' => ["label" => translate('cost_per_bed'), "rules" => 'trim|required|numeric']]);
    }
}
