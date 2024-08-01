<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SmsModel;
use App\Models\DashboardModel;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Alumni.php
 * @copyright : Reserved RamomCoder Team
 */
class Alumni extends AdminController

{
    public $appLib;

    protected $db;



    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    /**
     * @var App\Models\AlumniModel
     */
    public $alumni;

    /**
     * @var App\Models\DashboardModel
     */
    public $dashboard;

    public $applicationModel;

    public $input;

    public $load;

    public $validation;

    public $alumniModel;

    public $session;

    public $dashboardModel;

    public $smsModel;

    public function __construct()
    {



        parent::__construct();

        $this->appLib = service('appLib'); 
$this->sms = new \App\Models\SmsModel();
        $this->alumni = new \App\Models\AlumniModel();
        $this->dashboard = new \App\Models\DashboardModel();
    }

    public function index()
    {
        if (!get_permission('manage_alumni', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost()) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['passing_session'] = $this->request->getPost('passing_session');
            $this->data['students'] = $this->alumniModel->getStudentListByClassSection($this->data['class_id'], $this->data['section_id'], $branchID, $this->data['passing_session']);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['title'] = translate('alumni');
        $this->data['sub_page'] = 'alumni/index';
        $this->data['main_menu'] = 'alumni';
        echo view('layout/index', $this->data);
    }

    // student alumni details send by ajax
    public function alumniDetails()
    {
        if (get_permission('manage_alumni', 'is_view')) {
            $id = $this->request->getPost('id');
            $this->db->table('enroll_id')->where();
            $query = $builder->get('alumni_students');
            $result = $query->row_array();
            if (empty($result)) {
                $result = ['id' => '', 'enroll_id' => '', 'email' => '', 'mobile_no' => '', 'profession' => '', 'address' => '', 'photo' => '', 'image_url' => base_url('uploads/app_image/defualt.png')];
            } else {
                $result['image_url'] = get_image_url('alumni', $result['photo']);
            }

            echo json_encode($result);
        }
    }

    public function save()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['mobile_no' => ["label" => translate('mobile_no'), "rules" => 'trim|required']]);
            $this->validation->setRules(['email' => ["label" => translate('email'), "rules" => 'trim|valid_email']]);
            // checking profile photo format
            $this->validation->setRules(['user_photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[user_photo]']]);
            if ($this->validation->run() == true) {
                $insertData = ['enroll_id' => $this->request->getPost('enroll_id'), 'email' => $this->request->getPost('email'), 'mobile_no' => $this->request->getPost('mobile_no'), 'profession' => $this->request->getPost('profession'), 'address' => $this->request->getPost('address')];
                $id = $this->request->getPost('id');
                if (!empty($id) && $id != '') {
                    if (!get_permission('manage_alumni', 'is_edit')) {
                        ajax_access_denied();
                    }

                    $alumniImage = $this->request->getPost('old_image');
                    if (isset($_FILES["user_photo"]) && $_FILES['user_photo']['name'] != '' && !empty($_FILES['user_photo']['name'])) {
                        $alumniImage = $alumniImage == 'defualt.png' ? '' : $alumniImage;
                        $alumniImage = $this->alumniModel->fileupload("user_photo", "./uploads/images/alumni/", $alumniImage, true);
                    }

                    $insertData['photo'] = $alumniImage;
                    $this->db->table('id')->where();
                    $this->db->table('alumni_students')->update();
                } else {
                    if (!get_permission('manage_alumni', 'is_add')) {
                        ajax_access_denied();
                    }

                    $alumniImage = 'defualt.png';
                    if (isset($_FILES["user_photo"]) && $_FILES['user_photo']['name'] != '' && !empty($_FILES['user_photo']['name'])) {
                        $alumniImage = $this->alumniModel->fileupload("user_photo", "./uploads/images/alumni/", '', true);
                    }

                    $insertData['photo'] = $alumniImage;
                    $this->db->table('alumni_students')->insert();
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function delete($id)
    {
        if (get_permission('manage_alumni', 'is_delete')) {
            $photo = $db->table('alumni_students')->get('alumni_students')->row()->photo;
            $fileName = FCPATH . '/uploads/images/alumni/' . $photo;
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $this->db->table('id')->where();
            $this->db->table('alumni_students')->delete();
        }
    }

    public function event()
    {
        if (!get_permission('alumni_events', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $language = 'en';
        $jsArray = ['vendor/moment/moment.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js', 'vendor/fullcalendar/fullcalendar.js'];
        if (session()->get('set_lang') != 'english') {
            $language = $this->dashboardModel->languageShortCodes(session()->get('set_lang'));
            $jsArray[] = sprintf('vendor/fullcalendar/locale/%s.js', $language);
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-fileupload/bootstrap-fileupload.min.css', 'vendor/fullcalendar/fullcalendar.css'], 'js' => $jsArray];
        $this->data['language'] = $language;
        $this->data['title'] = translate('alumni');
        $this->data['sub_page'] = 'alumni/events';
        $this->data['main_menu'] = 'alumni';
        echo view('layout/index', $this->data);
    }

    public function saveEvents()
    {
        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['audience' => ["label" => translate('audience'), "rules" => 'trim|required']]);
            $audience = $this->request->getPost('audience');
            if ($audience == 2) {
                $this->validation->setRules(['selected_audience[]' => ["label" => translate('class'), "rules" => 'trim|required']]);
            } elseif ($audience == 3) {
                $this->validation->setRules(['selected_audience[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
            }

            if ($audience != 1) {
                $this->validation->setRules(['passing_session' => ["label" => translate('passing_session') . " " . translate('title'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['event_title' => ["label" => translate('events') . " " . translate('title'), "rules" => 'trim|required']]);
            $this->validation->setRules(['from_date' => ["label" => translate('date_of_start'), "rules" => 'trim|required']]);
            $this->validation->setRules(['to_date' => ["label" => translate('date_of_end'), "rules" => 'trim|required']]);
            $this->validation->setRules(['note' => ["label" => translate('note'), "rules" => 'trim|required']]);
            // checking profile photo format
            $this->validation->setRules(['user_photo' => ["label" => translate('photo'), "rules" => 'callback_photoHandleUpload[user_photo]']]);
            if ($this->validation->run() == true) {
                $passingSession = "";
                if ($audience != 1) {
                    $selectedList = [];
                    $passingSession = $this->request->getPost('passing_session');
                    foreach ($this->request->getPost('selected_audience') as $user) {
                        $selectedList[] = $user;
                    }
                } else {
                    $selectedList = null;
                }

                $insertData = ['title' => $this->request->getPost('event_title'), 'audience' => $this->request->getPost('audience'), 'session_id' => $passingSession, 'selected_list' => json_encode($selectedList), 'from_date' => $this->request->getPost('from_date'), 'to_date' => $this->request->getPost('to_date'), 'note' => $this->request->getPost('note'), 'branch_id' => $branchID];
                $id = $this->request->getPost('id');
                if (!empty($id) && $id != '') {
                    if (!get_permission('alumni_events', 'is_edit')) {
                        ajax_access_denied();
                    }

                    $alumniImage = $this->request->getPost('old_image');
                    if (isset($_FILES["user_photo"]) && $_FILES['user_photo']['name'] != '' && !empty($_FILES['user_photo']['name'])) {
                        $alumniImage = $alumniImage == 'defualt.png' ? '' : $alumniImage;
                        $alumniImage = $this->alumniModel->fileupload("user_photo", "./uploads/images/alumni_events/", $alumniImage, true);
                    }

                    $insertData['photo'] = $alumniImage;
                    $this->db->table('id')->where();
                    $this->db->table('alumni_events')->update();
                } else {
                    if (!get_permission('alumni_events', 'is_add')) {
                        ajax_access_denied();
                    }

                    $alumniImage = 'defualt.png';
                    if (isset($_FILES["user_photo"]) && $_FILES['user_photo']['name'] != '' && !empty($_FILES['user_photo']['name'])) {
                        $alumniImage = $this->alumniModel->fileupload("user_photo", "./uploads/images/alumni_events/", '', true);
                    }

                    $insertData['photo'] = $alumniImage;
                    $this->db->table('alumni_events')->insert();
                }

                // send sms to student
                if (isset($_POST['send_sms'])) {
                    $studentsArray = [];
                    if ($audience == 1) {
                        $students = $this->alumniModel->getlist($branchID);
                        foreach ($students as $student) {
                            $arraySMS = ['name' => $student['fullname'], 'mobile_no' => $student['mobile_no'], 'from_date' => _d($insertData['from_date']), 'to_date' => _d($insertData['to_date']), 'branch_id' => $branchID];
                            $studentsArray[] = $arraySMS;
                        }
                    } elseif ($audience == 2) {
                        foreach ($this->request->getPost('selected_audience') as $user) {
                            $classID = $user;
                            $students = $this->alumniModel->getList($branchID, $classID, "", $passingSession);
                            foreach ($students as $student) {
                                $arraySMS = ['name' => $student['fullname'], 'mobile_no' => $student['mobile_no'], 'from_date' => _d($insertData['from_date']), 'to_date' => _d($insertData['to_date']), 'branch_id' => $branchID];
                                $studentsArray[] = $arraySMS;
                            }
                        }
                    } elseif ($audience == 3) {
                        foreach ($this->request->getPost('selected_audience') as $user) {
                            $array = explode('-', (string) $user);
                            $students = $this->alumniModel->getList($branchID, $array[0], $array[1], $passingSession);
                            foreach ($students as $student) {
                                $arraySMS = ['name' => $student['fullname'], 'event_title' => $insertData['title'], 'mobile_no' => $student['mobile_no'], 'from_date' => _d($insertData['from_date']), 'to_date' => _d($insertData['to_date']), 'branch_id' => $branchID];
                                $studentsArray[] = $arraySMS;
                            }
                        }
                    }

                    foreach ($studentsArray as $value) {
                        $this->smsModel->alumniEvent($value);
                    }
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function getEventsList()
    {
        if (get_permission('alumni_events', 'is_view')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('status')->where();
            $events = $builder->get('alumni_events')->getResult();
            if (!empty($events)) {
                foreach ($events as $row) {
                    $arrayData = ['id' => $row->id, 'title' => $row->title, 'start' => $row->from_date, 'end' => date('Y-m-d', strtotime($row->to_date . "+1 days"))];
                    $eventdata[] = $arrayData;
                }

                echo json_encode($eventdata);
            }
        }
    }

    public function event_delete($id)
    {
        if (get_permission('alumni_events', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $photo = $db->table('alumni_events')->get('alumni_events')->row()->photo;
            $fileName = FCPATH . '/uploads/images/alumni_events/' . $photo;
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $this->db->table('id')->where();
            $this->db->table('alumni_events')->delete();
        }
    }

    public function eventDetails()
    {
        if (get_permission('alumni_events', 'is_view')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('alumni_events');
            $result = $query->row_array();
            if (empty($result)) {
                $result = ['id' => '', 'title' => '', 'audience' => '', 'session_id' => '', 'selected_list' => '', 'from_date' => '', 'to_date' => '', 'note' => '', 'photo' => '', 'show_web' => '', 'branch_id' => ''];
            }

            echo json_encode($result);
        }
    }

    public function getEventDetails()
    {
        if (get_permission('alumni_events', 'is_view')) {
            $id = $this->request->getPost('event_id');
            if (empty($id)) {
                redirect(base_url(), 'refresh');
            }

            $auditions = ["1" => "everybody", "2" => "class", "3" => "section"];
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $ev = $builder->get('alumni_events')->row_array();
            $type = '<img alt="" class="user-img-circle" src="' . get_image_url('alumni_events', $ev['photo']) . '" width="110" height="110">';
            $remark = empty($ev['note']) ? 'N/A' : $ev['note'];
            $html = "<tbody><tr>";
            $html .= "<td>" . translate('title') . "</td>";
            $html .= "<td>" . $ev['title'] . "</td>";
            $html .= "</tr><tr>";
            $html .= "<td>" . translate('photo') . "</td>";
            $html .= "<td>" . $type . "</td>";
            $html .= "</tr><tr>";
            $html .= "<td>" . translate('date_of_start') . "</td>";
            $html .= "<td>" . _d($ev['from_date']) . "</td>";
            $html .= "</tr><tr>";
            $html .= "<td>" . translate('date_of_end') . "</td>";
            $html .= "<td>" . _d($ev['to_date']) . "</td>";
            $html .= "</tr><tr>";
            $html .= "<td>" . translate('audience') . "</td>";
            $audience = $auditions[$ev['audience']];
            $html .= "<td>" . translate($audience);
            if ($ev['audience'] != 1) {
                $selecteds = json_decode((string) $ev['selected_list']);
                if ($ev['audience'] == 2) {
                    foreach ($selecteds as $selected) {
                        $html .= "<br> <small> - " . get_type_name_by_id('class', $selected) . '</small>';
                    }
                }

                if ($ev['audience'] == 3) {
                    foreach ($selecteds as $selected) {
                        $selected = explode('-', (string) $selected);
                        $html .= "<br> <small> - " . get_type_name_by_id('class', $selected[0]) . " (" . get_type_name_by_id('section', $selected[1]) . ')</small>';
                    }
                }
            }

            $html .= "</td>";
            $html .= "</tr><tr>";
            $html .= "<td>" . translate('note') . "</td>";
            $html .= "<td>" . $remark . "</td>";
            $html .= "</tr></tbody>";
            echo $html;
        }
    }
}
