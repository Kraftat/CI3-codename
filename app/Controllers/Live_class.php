<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\LiveClassModel;
use App\Models\SmsModel;
/**
 * @package : Ramom school management system
 * @version : 6.2
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Live_class.php
 * @copyright : Reserved RamomCoder Team
 */
class Live_class extends AdminController
{

    /**
     * @var App\Models\LiveClassModel
     */
    public $liveClass;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;
    public $validation;
    public $input;
    public $applicationModel;
    public $live_classModel;
    public $load;
    public $session;
    public $smsModel;
    public $db;
    public $appLib;

    public function __construct()
    {
        parent::__construct();



        $this->zoomLib = service('zoomLib');
        $this->bigbluebuttonLib = service('bigbluebuttonLib');
        $this->appLib = service('appLib'); 
        $this->liveClass = new \App\Models\LiveClassModel();
        $this->sms = new \App\Models\SmsModel();
    }

    /* live class form validation rules */
    protected function zoom_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['live_class_method' => ["label" => translate('live_class_method'), "rules" => 'trim|required']]);
        $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
        $this->validation->setRules(['section[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['time_start' => ["label" => translate('time_start'), "rules" => 'trim|required|callback_timeslot_validation']]);
        $this->validation->setRules(['time_end' => ["label" => translate('time_end'), "rules" => 'trim|required']]);
        $this->validation->setRules(['duration' => ["label" => translate('duration'), "rules" => 'trim|required']]);
    }

    public function index()
    {
        if (!get_permission('live_class', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('live_class', 'is_add')) {
            $method = $this->request->getPost('live_class_method');
            $post = $this->request->getPost();
            $this->zoom_validation();
            if ($method == 2) {
                $this->validation->setRules(['meeting_id' => ["label" => translate('meeting_id'), "rules" => 'trim|required']]);
            }

            if ($method == 3) {
                $this->validation->setRules(['gmeet_url' => ["label" => "Gmeet URL", "rules" => 'trim|required']]);
            }

            if ($this->validation->run() !== false) {
                // save all route information in the database file
                $branchID = $this->applicationModel->get_branch_id();
                if ($method == 1) {
                    $getConfig = $this->live_classModel->get('live_class_config', ['branch_id' => $branchID], true);
                    $apiType = 0;
                    if (is_superadmin_loggedin()) {
                        $apiKeys = ['zoom_api_key' => $getConfig['zoom_api_key'], 'zoom_api_secret' => $getConfig['zoom_api_secret']];
                    } else {
                        $getSelfAPI = $this->live_classModel->get('zoom_own_api', ['user_type' => 1, 'user_id' => get_loggedin_user_id()], true);
                        if ($getSelfAPI['zoom_api_key'] == '' || $getSelfAPI['zoom_api_secret'] == '' || $getConfig['staff_api_credential'] == 0) {
                            $apiKeys = ['zoom_api_key' => $getConfig['zoom_api_key'], 'zoom_api_secret' => $getConfig['zoom_api_secret']];
                        } else {
                            $apiType = 1;
                            $apiKeys = ['zoom_api_key' => $getSelfAPI['zoom_api_key'], 'zoom_api_secret' => $getSelfAPI['zoom_api_secret']];
                        }
                    }

                    $this->Zoom_lib = service('zoomLib', $apiKeys);
                    $arrayZoom = ['live_class_method' => $method, 'title' => $post['title'], 'meeting_id' => "", 'meeting_password' => "", 'own_api_key' => $apiType, 'duration' => $post['duration'], 'bbb' => "", 'class_id' => $post['class_id'], 'section_id' => json_encode($this->request->getPost('section')), 'remarks' => $post['remarks'], 'date' => date("Y-m-d", strtotime((string) $post['date'])), 'start_time' => date("H:i", strtotime((string) $post['time_start'])), 'end_time' => date("H:i", strtotime((string) $post['time_end'])), 'created_by' => get_loggedin_user_id(), 'branch_id' => $branchID, 'setting' => ['timezone' => $this->data['global_config']['timezone'], 'password' => $post["zoom_password"], 'join_before_host' => $this->request->getPost("join_before_host"), 'host_video' => $this->request->getPost("host_video"), 'participant_video' => $this->request->getPost("participant_video"), 'option_mute_participants' => $this->request->getPost("option_mute_participants")]];
                    $accessToken = session()->get("zoom_access_token");
                    if (empty($accessToken)) {
                        set_alert('error', "Access Token not generated");
                        $array = ['status' => 'success'];
                        echo json_encode($array);
                        exit;
                    }

                    $response = $this->zoom_lib->createMeeting($arrayZoom, $accessToken);
                    session()->set("zoom_access_token", "");
                    if (!empty($response->code)) {
                        set_alert('error', "The Token Signature resulted invalid when verified using the algorithm");
                        $array = ['status' => 'success'];
                        echo json_encode($array);
                        exit;
                    }

                    $arrayZoom['meeting_id'] = $response->id;
                    $arrayZoom['meeting_password'] = $response->encrypted_password;
                    $arrayZoom['bbb'] = json_encode(['join_url' => $response->join_url, 'start_url' => $response->start_url, 'password' => $response->password]);
                    unset($arrayZoom['setting']);
                    $this->live_classModel->save($arrayZoom);
                } elseif ($method == 2) {
                    $this->live_classModel->bbb_class_save($post);
                } elseif ($method == 3) {
                    $this->live_classModel->gmeet_save($post);
                }

                //send live class sms notification
                if (isset($post['send_notification_sms'])) {
                    foreach ($post['section'] as $value) {
                        $stuList = $this->applicationModel->getStudentListByClassSection($post['class_id'], $value, $branchID);
                        foreach ($stuList as $row) {
                            $row['date_of_live_class'] = $post['date'];
                            $row['start_time'] = date("h:i A", strtotime((string) $post['time_start']));
                            $row['end_time'] = date("h:i A", strtotime((string) $post['time_end']));
                            $row['host_by'] = session()->get('name');
                            $this->smsModel->sendLiveClass($row);
                        }
                    }
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['liveClass'] = $this->live_classModel->getList();
        $this->data['title'] = translate('live_class_rooms');
        $this->data['sub_page'] = 'live_class/index';
        $this->data['main_menu'] = 'live_class';
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('live_class', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->award_validation();
            if ($this->validation->run() !== false) {
                // SAVE ALL ROUTE INFORMATION IN THE DATABASE FILE
                $this->live_classModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('live_class');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['live'] = $this->appLib->getTable('live_class', ['t.id' => $id], true);
        $this->data['title'] = translate('live_class');
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['sub_page'] = 'live_class/edit';
        $this->data['main_menu'] = 'live_class_rooms';
        echo view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('live_class', 'is_delete')) {
            $get = $this->live_classModel->get('live_class', ['id' => $id], true, true);
            if ($get['live_class_method'] == 1) {
                if ($get['own_api_key'] == 1) {
                    $getSelfAPI = $this->live_classModel->get('zoom_own_api', ['user_type' => 1, 'user_id' => $get['created_by']], true);
                    if ($getSelfAPI['zoom_api_key'] == '' || $getSelfAPI['zoom_api_secret'] == '') {
                        set_alert('error', "You created by your own zoom account, API Credential is missing.");
                        exit;
                    }

                    $apiKeys = ['zoom_api_key' => $getSelfAPI['zoom_api_key'], 'zoom_api_secret' => $getSelfAPI['zoom_api_secret']];
                } else {
                    $getConfig = $this->live_classModel->get('live_class_config', ['branch_id' => $get['branch_id']], true);
                    $apiKeys = ['zoom_api_key' => $getConfig['zoom_api_key'], 'zoom_api_secret' => $getConfig['zoom_api_secret']];
                }

                $this->Zoom_lib = service('zoomLib', $apiKeys);
                $accessToken = session()->get("zoom_access_token");
                $response = $this->zoom_lib->deleteMeeting($get['meeting_id'], $accessToken);
                if (!is_superadmin_loggedin()) {
                    $this->db->table('branch_id')->where();
                }

                $this->db->table('id')->where();
                $this->db->table('live_class')->delete();
            } else {
                $this->db->table('id')->where();
                $this->db->table('live_class')->delete();
            }
        }
    }

    public function zoom_own_api()
    {
        if ($_POST !== []) {
            if (!get_permission('live_class', 'is_add')) {
                ajax_access_denied();
            }

            $this->validation->setRules(['zoom_api_key' => ["label" => 'Zoom Api Key', "rules" => 'trim|required']]);
            $this->validation->setRules(['zoom_api_secret' => ["label" => 'Zoom Api Secret', "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $arrayData = ['user_type' => loggedin_role_id() !== 7 ? 1 : 2, 'user_id' => get_loggedin_user_id(), 'zoom_api_key' => $this->request->getPost('zoom_api_key'), 'zoom_api_secret' => $this->request->getPost('zoom_api_secret')];
                $apiId = $this->request->getPost('api_id');
                if (empty($apiId)) {
                    $this->db->table('zoom_own_api')->insert();
                } else {
                    $this->db->table('id')->where();
                    $this->db->table('zoom_own_api')->update();
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    public function hostModal()
    {
        if (get_permission('live_class', 'is_add')) {
            $this->data['meetingID'] = $this->request->getPost('meeting_id');
            echo view('live_class/hostModal', $this->data, true);
        }
    }

    public function zoom_meeting_start()
    {
        if (!get_permission('live_class', 'is_add')) {
            access_denied();
        }

        echo view('live_class/host', $this->data);
    }

    public function bbb_meeting_start()
    {
        if (!get_permission('live_class', 'is_add')) {
            access_denied();
        }

        $meetingID = $this->request->getGet('meeting_id', true);
        $liveID = $this->request->getGet('live_id', true);
        $getMeeting = $this->live_classModel->get('live_class', ['id' => $liveID, 'meeting_id' => $meetingID], true);
        $getStaff = $this->appLib->get_table('staff', get_loggedin_user_id(), true);
        if (empty($getMeeting)) {
            set_alert('error', translate('Meeting Not Found.'));
            return redirect()->to(base_url('live_class'));
        }

        $bbbConfig = json_decode((string) $getMeeting['bbb'], true);
        // get BBB api config
        $getConfig = $this->live_classModel->get('live_class_config', ['branch_id' => $getMeeting['branch_id']], true);
        $apiKeys = ['bbb_security_salt' => $getConfig['bbb_salt_key'], 'bbb_server_base_url' => $getConfig['bbb_server_base_url']];
        $this->bigbluebuttonLib = service('bigbluebuttonLib', $apiKeys);
        $arrayBBB = ['meeting_id' => $getMeeting['meeting_id'], 'title' => $getMeeting['title'], 'duration' => $getMeeting['duration'], 'moderator_password' => $bbbConfig['moderator_password'], 'attendee_password' => $bbbConfig['attendee_password'], 'max_participants' => $bbbConfig['max_participants'], 'mute_on_start' => $bbbConfig['mute_on_start'], 'set_record' => $bbbConfig['mute_on_start'], 'presen_name' => $getStaff['name']];
        $response = $this->bigbluebuttonLib->createMeeting($arrayBBB);
        if ($response == false) {
            set_alert('error', "Can\\'t create room! please contact our administrator.");
            return redirect()->to(base_url('live_class'));
        }

        redirect($response);
        return null;
    }

    public function bbb_callback()
    {
        if (is_student_loggedin()) {
            return redirect()->to(base_url('userrole/live_class'));
        }

        return redirect()->to(base_url('live_class'));
    }

    /* showing student list by class and section */
    public function reports()
    {
        // check access permission
        if (!get_permission('live_class_reports', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->request->getPost('class_id');
            $sectionID = $this->request->getPost('section_id');
            $method = $this->request->getPost('live_class_method');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['livelist'] = $this->live_classModel->getReports($classID, $sectionID, $method, $start, $end, $branchID);
        }

        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('live_class_reports');
        $this->data['main_menu'] = 'live_class';
        $this->data['sub_page'] = 'live_class/reports';
        echo view('layout/index', $this->data);
    }

    public function participation_list()
    {
        if (get_permission('live_class_reports', 'is_view') && $_POST) {
            $liveID = $this->request->getPost('live_id');
            $this->data['list'] = $this->live_classModel->get('live_class_reports', ['live_class_id' => $liveID]);
            echo view('live_class/participation_list', $this->data, true);
        }
    }

    public function timeslot_validation($timeStart)
    {
        $timeEnd = $this->request->getPost('time_end');
        if (strtotime((string) $timeStart) >= strtotime((string) $timeEnd)) {
            $this->validation->setRule("timeslot_validation", "The End time must be longer than the Start time.");
            return false;
        }

        return true;
    }

    public function getTokenURL()
    {
        if (get_permission('live_class', 'is_add') && $_POST) {
            $branchID = $this->applicationModel->get_branch_id();
            if (empty($branchID)) {
                echo json_encode(['status' => false, 'message' => translate('select_branch_first')]);
                exit;
            }

            $getConfig = $this->live_classModel->get('live_class_config', ['branch_id' => $branchID], true);
            if (is_superadmin_loggedin()) {
                $apiKeys = ['zoom_api_key' => $getConfig['zoom_api_key'], 'zoom_api_secret' => $getConfig['zoom_api_secret']];
            } else {
                $getSelfAPI = $this->live_classModel->get('zoom_own_api', ['user_type' => 1, 'user_id' => get_loggedin_user_id()], true);
                if ($getSelfAPI['zoom_api_key'] == '' || $getSelfAPI['zoom_api_secret'] == '' || $getConfig['staff_api_credential'] == 0) {
                    $apiKeys = ['zoom_api_key' => $getConfig['zoom_api_key'], 'zoom_api_secret' => $getConfig['zoom_api_secret']];
                } else {
                    $apiKeys = ['zoom_api_key' => $getSelfAPI['zoom_api_key'], 'zoom_api_secret' => $getSelfAPI['zoom_api_secret']];
                }
            }

            if (empty($apiKeys['zoom_api_key'])) {
                echo json_encode(['status' => false, 'message' => translate('zoom_configuration_not_found')]);
            } else {
                $url = "https://zoom.us/oauth/authorize?response_type=code&client_id=" . $apiKeys['zoom_api_key'] . "&redirect_uri=" . base_url('live_class/zoom_OAuth');
                session()->set("zoomAPI", $apiKeys);
                echo json_encode(['status' => true, 'url' => $url]);
            }
        }
    }

    public function zoom_OAuth()
    {
        if (!isset($_GET['code'])) {
            echo "Invalid Access token";
        } else {
            $zoomAPI = session()->get("zoomAPI");
            session()->set("zoomAPI", "");
            if (!empty($zoomAPI)) {
                $this->Zoom_lib = service('zoomLib', $zoomAPI);
                $response = $this->zoom_lib->get_access_token($_GET['code']);
                if (!empty($response)) {
                    session()->set("zoom_access_token", $response['access_token']);
                    set_alert('success', translate('access_token_generated_successfully'));
                    return redirect()->to(base_url('live_class'));
                }
            } else {
                echo "Redirection was successful.";
            }
        }

        return null;
    }
}
