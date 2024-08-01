<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Event.php
 * @copyright : Reserved RamomCoder Team
 */
class Event extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\EventModel
     */
    public $event;

    public $applicationModel;

    public $validation;

    public $input;

    public $eventModel;

    public $load;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->event = new \App\Models\EventModel();
    }

    public function index()
    {
        // check access permission
        if (!get_permission('event', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('event', 'is_add')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
            if (!isset($_POST['holiday'])) {
                $this->validation->setRules(['type_id' => ["label" => translate('type'), "rules" => 'trim|required']]);
                $this->validation->setRules(['audition' => ["label" => translate('audition'), "rules" => 'trim|required']]);
                $audition = $this->request->getPost('audition');
            } else {
                $audition = 1;
            }

            $this->validation->setRules(['daterange' => ["label" => translate('date'), "rules" => 'trim|required']]);
            if ($audition == 2) {
                $this->validation->setRules(['selected_audience[]' => ["label" => translate('class'), "rules" => 'trim|required']]);
            } elseif ($audition == 3) {
                $this->validation->setRules(['selected_audience[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['user_photo' => ["label" => 'profile_picture', "rules" => 'callback_photoHandleUpload[user_photo]']]);
            if ($this->validation->run() !== false) {
                if ($audition != 1) {
                    $selectedList = [];
                    foreach ($this->request->getPost('selected_audience') as $user) {
                        $selectedList[] = $user;
                    }
                } else {
                    $selectedList = null;
                }

                $holiday = $this->request->getPost('holiday');
                $type = empty($holiday) ? $this->request->getPost('type_id') : 'holiday';
                $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
                $startDate = date("Y-m-d", strtotime($daterange[0]));
                $endDate = date("Y-m-d", strtotime($daterange[1]));
                $eventImage = 'defualt.png';
                if (isset($_FILES["user_photo"]) && $_FILES['user_photo']['name'] != '' && !empty($_FILES['user_photo']['name'])) {
                    $eventImage = $this->eventModel->fileupload("user_photo", "./uploads/frontend/events/", '', false);
                }

                $arrayEvent = ['branch_id' => $branchID, 'type' => $type, 'audition' => $audition, 'image' => $eventImage, 'selected_list' => json_encode($selectedList), 'start_date' => $startDate, 'end_date' => $endDate];
                $this->eventModel->save($arrayEvent);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('event');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('events');
        $this->data['sub_page'] = 'event/index';
        $this->data['main_menu'] = 'event';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/daterangepicker/daterangepicker.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        // check access permission
        if (!get_permission('event', 'is_edit')) {
            access_denied();
        }

        $this->data['event'] = $this->appLib->getTable('event', ['t.id' => $id], true);
        if (empty($this->data['event'])) {
            redirect('dashboard');
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
            if (!isset($_POST['holiday'])) {
                $this->validation->setRules(['type_id' => ["label" => translate('type'), "rules" => 'trim|required']]);
                $this->validation->setRules(['audition' => ["label" => translate('audition'), "rules" => 'trim|required']]);
                $audition = $this->request->getPost('audition');
            } else {
                $audition = 1;
            }

            $this->validation->setRules(['daterange' => ["label" => translate('date'), "rules" => 'trim|required']]);
            if ($audition == 2) {
                $this->validation->setRules(['selected_audience[]' => ["label" => translate('class'), "rules" => 'trim|required']]);
            } elseif ($audition == 3) {
                $this->validation->setRules(['selected_audience[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['user_photo' => ["label" => 'profile_picture', "rules" => 'callback_photoHandleUpload[user_photo]']]);
            if ($this->validation->run() !== false) {
                if ($audition != 1) {
                    $selectedList = [];
                    foreach ($this->request->getPost('selected_audience') as $user) {
                        $selectedList[] = $user;
                    }
                } else {
                    $selectedList = null;
                }

                $holiday = $this->request->getPost('holiday');
                $type = empty($holiday) ? $this->request->getPost('type_id') : 'holiday';
                $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
                $startDate = date("Y-m-d", strtotime($daterange[0]));
                $endDate = date("Y-m-d", strtotime($daterange[1]));
                $eventImage = $this->request->getPost('old_event_image');
                if (isset($_FILES["user_photo"]) && $_FILES['user_photo']['name'] != '' && !empty($_FILES['user_photo']['name'])) {
                    $eventimage = $eventImage == 'defualt.png' ? '' : $eventImage;
                    $eventImage = $this->eventModel->fileupload("user_photo", "./uploads/frontend/events/", $eventimage, false);
                }

                $arrayEvent = ['id' => $this->request->getPost('id'), 'branch_id' => $branchID, 'type' => $type, 'audition' => $audition, 'image' => $eventImage, 'selected_list' => json_encode($selectedList), 'start_date' => $startDate, 'end_date' => $endDate];
                $this->eventModel->save($arrayEvent);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('event');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('events');
        $this->data['sub_page'] = 'event/edit';
        $this->data['main_menu'] = 'event';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/daterangepicker/daterangepicker.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        echo view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        // check access permission
        if (get_permission('event', 'is_delete')) {
            $eventDb = $this->db->table('event')->where('id', $id)->get()->getRowArray();
            $fileName = $eventDb['image'];
            if ($eventDb['created_by'] == get_loggedin_user_id() || is_superadmin_loggedin()) {
                $this->db->table('id')->where();
                $this->db->table('event')->delete();
                if ($fileName !== 'defualt.png') {
                    $fileName = 'uploads/frontend/events/' . $fileName;
                    if (file_exists($fileName)) {
                        unlink($fileName);
                    }
                }
            } else {
                set_alert('error', 'You do not have permission to delete');
            }
        } else {
            set_alert('error', translate('access_denied'));
        }
    }

    /* types form validation rules */
    protected function types_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['type_name' => ["label" => translate('name'), "rules" => 'trim|required|callback_unique_type']]);
    }

    // exam term information are prepared and stored in the database here
    public function types()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('event_type', 'is_add')) {
                access_denied();
            }

            $this->types_validation();
            if ($this->validation->run() !== false) {
                //save information in the database file
                $data['name'] = $this->request->getPost('type_name');
                $data['icon'] = $this->request->getPost('event_icon');
                $data['branch_id'] = $this->applicationModel->get_branch_id();
                $this->db->table('event_types')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }

        $this->data['typelist'] = $this->appLib->getTable('event_types');
        $this->data['sub_page'] = 'event/types';
        $this->data['main_menu'] = 'event';
        $this->data['title'] = translate('event_type');
        echo view('layout/index', $this->data);
    }

    public function types_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('event_type', 'is_edit')) {
                ajax_access_denied();
            }

            $this->types_validation();
            if ($this->validation->run() !== false) {
                //save information in the database file
                $data['name'] = $this->request->getPost('type_name');
                $data['icon'] = $this->request->getPost('event_icon');
                $data['branch_id'] = $this->applicationModel->get_branch_id();
                $this->db->table('id')->where();
                $this->db->table('event_types')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('event/types');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function type_delete($id)
    {
        if (!get_permission('event_type', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('event_types')->delete();
    }

    /* unique valid type name verification is done here */
    public function unique_type($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $typeId = $this->request->getPost('type_id');
        if (!empty($typeId)) {
            $this->db->where_not_in('id', $typeId);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('event_types')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_type", translate('already_taken'));
        return false;
    }

    // publish on show website
    public function show_website()
    {
        $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $arrayData['show_web'] = $status == 'true' ? 1 : 0;
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('event')->update();
        $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
        echo json_encode($return);
    }

    // publish status
    public function status()
    {
        $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $arrayData['status'] = $status == 'true' ? 1 : 0;
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('event')->update();
        $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
        echo json_encode($return);
    }

    public function getDetails()
    {
        $id = $this->request->getPost('event_id');
        if (empty($id)) {
            redirect(base_url(), 'refresh');
        }

        $auditions = ["1" => "everybody", "2" => "class", "3" => "section"];
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $ev = $builder->get('event')->row_array();
        $type = $ev['type'] == 'holiday' ? translate('holiday') : get_type_name_by_id('event_types', $ev['type']);
        $remark = empty($ev['remark']) ? 'N/A' : $ev['remark'];
        $html = "<tbody><tr>";
        $html .= "<td>" . translate('title') . "</td>";
        $html .= "<td>" . $ev['title'] . "</td>";
        $html .= "</tr><tr>";
        $html .= "<td>" . translate('type') . "</td>";
        $html .= "<td>" . $type . "</td>";
        $html .= "</tr><tr>";
        $html .= "<td>" . translate('date_of_start') . "</td>";
        $html .= "<td>" . _d($ev['start_date']) . "</td>";
        $html .= "</tr><tr>";
        $html .= "<td>" . translate('date_of_end') . "</td>";
        $html .= "<td>" . _d($ev['end_date']) . "</td>";
        $html .= "</tr><tr>";
        $html .= "<td>" . translate('audience') . "</td>";
        $audition = $auditions[$ev['audition']];
        $html .= "<td>" . translate($audition);
        if ($ev['audition'] != 1) {
            $selecteds = json_decode((string) $ev['selected_list']);
            if ($ev['audition'] == 2) {
                foreach ($selecteds as $selected) {
                    $html .= "<br> <small> - " . get_type_name_by_id('class', $selected) . '</small>';
                }
            }

            if ($ev['audition'] == 3) {
                foreach ($selecteds as $selected) {
                    $selected = explode('-', (string) $selected);
                    $html .= "<br> <small> - " . get_type_name_by_id('class', $selected[0]) . " (" . get_type_name_by_id('section', $selected[1]) . ')</small>';
                }
            }
        }

        $html .= "</td>";
        $html .= "</tr><tr>";
        $html .= "<td>" . translate('description') . "</td>";
        $html .= "<td>" . $remark . "</td>";
        $html .= "</tr></tbody>";
        echo $html;
    }

    /* generate section with class group */
    public function getSectionByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $result = $builder->getWhere('class', ['branch_id' => $branchID])->result_array();
            if (count($result) > 0) {
                foreach ($result as $class) {
                    $html .= '<optgroup label="' . $class['name'] . '">';
                    $allocations = $builder->getWhere('sections_allocation', ['class_id' => $class['id']])->result_array();
                    if (count($allocations) > 0) {
                        foreach ($allocations as $allocation) {
                            $section = $builder->getWhere('section', ['id' => $allocation['section_id']])->row_array();
                            $html .= '<option value="' . $class['id'] . "-" . $allocation['section_id'] . '">' . $section['name'] . '</option>';
                        }
                    } else {
                        $html .= '<option value="">' . translate('no_selection_available') . '</option>';
                    }

                    $html .= '</optgroup>';
                }
            }
        }

        echo $html;
    }

    public function get_events_list($branchID = '')
    {
        if (is_loggedin()) {
            $this->db->table('branch_id')->where();

            $this->db->table('status')->where();
            $events = $builder->get('event')->getResult();
            if (!empty($events)) {
                foreach ($events as $row) {
                    $arrayData = ['id' => $row->id, 'title' => $row->title, 'start' => $row->start_date, 'end' => date('Y-m-d', strtotime($row->end_date . "+1 days"))];
                    if ($row->type == 'holiday') {
                        $arrayData['className'] = 'fc-event-danger';
                        $arrayData['icon'] = 'umbrella-beach';
                    } else {
                        $icon = get_type_name_by_id('event_types', $row->type, 'icon');
                        $arrayData['icon'] = $icon;
                    }

                    $eventdata[] = $arrayData;
                }

                echo json_encode($eventdata);
            }
        }
    }
}
