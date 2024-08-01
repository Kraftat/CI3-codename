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
 * @filename : Transport.php
 * @copyright : Reserved RamomCoder Team
 */
class Transport extends AdminController

{
    public $appLib;

    protected $db;




    /**
     * @var App\Models\TransportModel
     */
    public $transport;

    public $validation;

    public $input;

    public $transportModel;

    public $load;

    public $applicationModel;

    public $uri;

    public function __construct()
    {




        parent::__construct();

        $this->appLib = service('appLib'); 
$this->transport = new \App\Models\TransportModel();
    }

    public function index()
    {
        redirect(base_url(), 'refresh');
    }

    // route user interface 
    public function route()
    {
        if (!get_permission('transport_route', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('transport_route', 'is_add')) {
                ajax_access_denied();
            }

            $this->route_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all route information in the database file
                $this->transportModel->route_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('transport/route');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['transportlist'] = $this->appLib->getTable('transport_route');
        $this->data['title'] = translate('route_master');
        $this->data['sub_page'] = 'transport/route';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    // route all information are prepared and user interface
    public function route_edit($id = '')
    {
        if (!get_permission('transport_route', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->route_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all route information in the database file
                $this->transportModel->route_save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('transport/route');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['route'] = $this->appLib->getTable('transport_route', ['t.id' => $id], true);
        $this->data['title'] = translate('route_master');
        $this->data['sub_page'] = 'transport/route_edit';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    public function route_delete($id = '')
    {
        if (get_permission('transport_route', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('transport_route')->delete();
        }
    }

    // vehicle information add and delete
    public function vehicle()
    {
        if (!get_permission('transport_vehicle', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('transport_vehicle', 'is_add')) {
                ajax_access_denied();
            }

            $this->vehicle_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all vehicle information in the database file
                $this->transportModel->vehicle_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('transport/vehicle');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['transportlist'] = $this->appLib->getTable('transport_vehicle');
        $this->data['title'] = translate('vehicle_master');
        $this->data['sub_page'] = 'transport/vehicle';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    // vehicle information edit 
    public function vehicle_edit($id = '')
    {
        if (!get_permission('transport_vehicle', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->vehicle_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all vehicle information in the database file
                $this->transportModel->vehicle_save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('transport/vehicle');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['vehicle'] = $this->appLib->getTable('transport_vehicle', ['t.id' => $id], true);
        $this->data['title'] = translate('vehicle_master');
        $this->data['sub_page'] = 'transport/vehicle_edit';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    public function vehicle_delete($id = '')
    {
        if (get_permission('transport_route', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('transport_vehicle')->delete();
        }
    }

    // stoppage information add and delete
    public function stoppage()
    {
        if (!get_permission('transport_stoppage', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('transport_stoppage', 'is_add')) {
                ajax_access_denied();
            }

            $this->stoppage_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all stoppage information in the database file
                $this->transportModel->stoppage_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('transport/stoppage');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['stoppagelist'] = $this->appLib->getTable('transport_stoppage');
        $this->data['title'] = translate('stoppage');
        $this->data['sub_page'] = 'transport/stoppage';
        $this->data['main_menu'] = 'transport';
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        echo view('layout/index', $this->data);
    }

    // stoppage information edit
    public function stoppage_edit($id = '')
    {
        if (!get_permission('transport_stoppage', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->stoppage_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all stoppage information in the database file
                $this->transportModel->stoppage_save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('transport/stoppage');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['stoppage'] = $this->appLib->getTable('transport_stoppage', ['t.id' => $id], true);
        $this->data['title'] = translate('stoppage');
        $this->data['sub_page'] = 'transport/stoppage_edit';
        $this->data['main_menu'] = 'transport';
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function stoppage_delete($id = '')
    {
        if (get_permission('transport_stoppage', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('transport_stoppage')->delete();
        }
    }

    /* user interface with assign vehicles and stoppage information and delete */
    public function assign()
    {
        if (!get_permission('transport_assign', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (!get_permission('transport_assign', 'is_add')) {
                ajax_access_denied();
            }

            $this->assign_validation();
            if ($this->validation->run() !== false) {
                $vehicles = $this->request->getPost('vehicle');
                foreach ($vehicles as $vehicle) {
                    $arrayData[] = ['branch_id' => $branchID, 'route_id' => $this->request->getPost('route_id'), 'stoppage_id' => $this->request->getPost('stoppage_id'), 'vehicle_id' => $vehicle];
                }

                $this->db->insert_batch('transport_assign', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('transport/assign');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('assign_vehicle');
        $this->data['sub_page'] = 'transport/assign';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    /* user interface with vehicles assign information edit */
    public function assign_edit($id = '')
    {
        if (!get_permission('transport_assign', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->assign_validation();
            if ($this->validation->run() !== false) {
                $branchID = $this->applicationModel->get_branch_id();
                $routeID = $this->request->getPost('route_id');
                $stoppageID = $this->request->getPost('stoppage_id');
                $vehicles = $this->request->getPost('vehicle');
                foreach ($vehicles as $vehicle) {
                    $data = ['branch_id' => $branchID, 'route_id' => $id, 'vehicle_id' => $vehicle];
                    $query = $builder->getWhere("transport_assign", $data);
                    if ($query->num_rows() == 0) {
                        $data['stoppage_id'] = $stoppageID;
                        $this->db->table('transport_assign')->insert();
                    } else {
                        $this->db->table('id')->where();
                        $this->db->table('transport_assign')->update();
                    }
                }

                $this->db->where_not_in('vehicle_id', $vehicles);
                $this->db->table('route_id')->where();
                $this->db->table('branch_id')->where();
                $this->db->table('transport_assign')->delete();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('transport/assign');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['assign'] = $this->transportModel->getAssignEdit($id);
        $this->data['title'] = translate('assign_vehicle');
        $this->data['sub_page'] = 'transport/assign_edit';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    public function assign_delete($id = '')
    {
        if (get_permission('transport_assign', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('route_id')->where();
            $this->db->table('transport_assign')->delete();
        }
    }

    // validate here, if the check route assign
    public function unique_route_assign($id)
    {
        if ($this->uri->segment(3)) {
            $this->db->where_not_in('route_id', $this->uri->segment(3));
        }

        $this->db->table(['route_id' => $id])->where();
        $uniformRow = $builder->get('transport_assign')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }
        $this->validation->setRule("unique_route_assign", "This route is already assigned.");
        return false;
    }

    /* student transport allocation report */
    public function report()
    {
        if (!get_permission('transport_allocation', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $this->data['allocationlist'] = $this->transportModel->allocation_report($classID, $sectionID, $branchID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('allocation_report');
        $this->data['sub_page'] = 'transport/allocation';
        $this->data['main_menu'] = 'transport';
        echo view('layout/index', $this->data);
    }

    public function allocation_delete($id)
    {
        if (get_permission('transport_allocation', 'is_delete')) {
            $builder->select('student_id');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $studentId = $builder->get('enroll')->row()->student_id;
            if (!empty($studentId)) {
                $arrayData = ['vehicle_id' => 0, 'route_id' => 0];
                $this->db->table('id')->where();
                $this->db->table('student')->update();
            }
        }
    }

    /* get vehicle list based on the route */
    public function get_vehicle_by_route()
    {
        $routeID = $this->request->getPost("routeID");
        if (!empty($routeID)) {
            $query = $db->table('transport_assign')->get('transport_assign');
            if ($query->num_rows() != 0) {
                echo '<option value="">' . translate('select') . '</option>';
                $vehicles = $query->getResultArray();
                foreach ($vehicles as $row) {
                    echo '<option value="' . $row['vehicle_id'] . '">' . get_type_name_by_id('transport_vehicle', $row['vehicle_id'], 'vehicle_no') . '</option>';
                }
            } else {
                echo '<option value="">' . translate('no_selection_available') . '</option>';
            }
        } else {
            echo '<option value="">' . translate('first_select_the_route') . '</option>';
        }
    }

    /* get vehicle list based on the branch */
    public function getVehicleByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $result = $db->table('transport_vehicle')->get('transport_vehicle')->result_array();
            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['vehicle_no'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_selection_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('first_select_the_route') . '</option>';
        }

        echo $html;
    }

    /* get stoppage list based on the branch */
    public function getStoppageByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $result = $db->table('transport_stoppage')->get('transport_stoppage')->result_array();
            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['stop_position'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_selection_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('first_select_the_branch') . '</option>';
        }

        echo $html;
    }

    protected function route_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['route_name' => ["label" => translate('route_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['start_place' => ["label" => translate('start_place'), "rules" => 'required']]);
        $this->validation->setRules(['stop_place' => ["label" => translate('stop_place'), "rules" => 'trim|required']]);
    }

    protected function stoppage_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['stop_position' => ["label" => translate('stoppage'), "rules" => 'trim|required']]);
        $this->validation->setRules(['stop_time' => ["label" => translate('stop_time'), "rules" => 'required']]);
        $this->validation->setRules(['route_fare' => ["label" => translate('route_fare'), "rules" => 'trim|required|numeric']]);
    }

    protected function vehicle_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['vehicle_no' => ["label" => translate('vehicle_no'), "rules" => 'trim|required']]);
        $this->validation->setRules(['capacity' => ["label" => translate('capacity'), "rules" => 'required|numeric']]);
        $this->validation->setRules(['driver_name' => ["label" => translate('driver_name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['driver_phone' => ["label" => translate('driver_phone'), "rules" => 'trim|required']]);
        $this->validation->setRules(['driver_license' => ["label" => translate('driver_license'), "rules" => 'trim|required']]);
    }

    protected function assign_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['route_id' => ["label" => translate('transport_route'), "rules" => 'required|callback_unique_route_assign']]);
        $this->validation->setRules(['stoppage_id' => ["label" => translate('stoppage'), "rules" => 'required']]);
        $this->validation->setRules(['vehicle[]' => ["label" => translate('vehicle'), "rules" => 'required']]);
    }
}
