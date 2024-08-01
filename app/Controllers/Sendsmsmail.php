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
 * @filename : Sendsmsmail.php
 * @copyright : Reserved RamomCoder Team
 */
class Sendsmsmail extends AdminController

{
    /**
     * @var mixed
     */
    public $Mailer;
    public $mailer;

    public $bulk;

    public $appLib;

    protected $db;

    public $load;

    /**
     * @var App\Models\SendsmsmailModel
     */
    public $sendsmsmail;

    public $applicationModel;

    public $input;

    public $validation;

    public $sendsmsmailModel;

    public $uri;

    public function __construct()
    {

        parent::__construct();



        $this->mailer = service('mailer');$this->bulk = service('bulk');$this->appLib = service('appLib'); 
$this->Mailer = service('mailer');
        $this->sendsmsmail = new \App\Models\SendsmsmailModel();
        if (!moduleIsEnabled('bulk_sms_and_email')) {
            access_denied();
        }
    }

    public function sms()
    {
        if (!get_permission('sendsmsmail', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['headerelements'] = ['css' => ['vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('bulk_sms_and_email');
        $this->data['sub_page'] = 'sendsmsmail/sms';
        $this->data['main_menu'] = 'sendsmsmail';
        echo view('layout/index', $this->data);
    }

    public function email()
    {
        if (!get_permission('sendsmsmail', 'is_add')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-timepicker/css/bootstrap-timepicker.css'], 'js' => ['vendor/bootstrap-timepicker/bootstrap-timepicker.js', 'vendor/summernote/summernote.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('bulk_sms_and_email');
        $this->data['sub_page'] = 'sendsmsmail/email';
        $this->data['main_menu'] = 'sendsmsmail';
        echo view('layout/index', $this->data);
    }

    public function delete($id)
    {
        if (get_permission('sendsmsmail', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('bulk_sms_email')->delete();
        }
    }

    public function campaign_reports()
    {
        if (!get_permission('sendsmsmail_reports', 'is_view')) {
            access_denied();
        }

        $this->applicationModel->get_branch_id();
        if ($_POST !== []) {
            $sendType = $this->request->getPost('send_type');
            $campaignType = $this->request->getPost('campaign_type');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->db->table('DATE(created_at) >=')->where();
            $this->db->table('DATE(created_at) <=')->where();
            $this->db->table('message_type')->where();
            $this->db->table('branch_id')->where();
            if ($sendType != 'both') {
                $this->db->table('posting_status')->where();
            }

            $this->data['campaignlist'] = $builder->get('bulk_sms_email')->result_array();
            $this->data['startdate'] = $start;
            $this->data['enddate'] = $end;
        }

        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        $this->data['title'] = translate('bulk_sms_and_email');
        $this->data['sub_page'] = 'sendsmsmail/campaign_reports';
        $this->data['main_menu'] = 'sendsmsmail';
        echo view('layout/index', $this->data);
    }

    public function save()
    {
        if (!get_permission('sendsmsmail', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $messageType = $this->request->getPost('message_type') == 'sms' ? 1 : 2;
            $branchID = $this->applicationModel->get_branch_id();
            $recipientType = $this->request->getPost('recipient_type');
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['campaign_name' => ["label" => translate('campaign_name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['message' => ["label" => translate('message'), "rules" => 'trim|required']]);
            if ($messageType == 1) {
                $this->validation->setRules(['sms_gateway' => ["label" => translate('sms_gateway'), "rules" => 'trim|required']]);
            } else {
                $this->validation->setRules(['email_subject' => ["label" => translate('email_subject'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['recipient_type' => ["label" => translate('type'), "rules" => 'trim|required']]);
            if ($recipientType == 1) {
                $this->validation->setRules(['role_group[]' => ["label" => translate('role'), "rules" => 'trim|required']]);
            }

            if ($recipientType == 2) {
                $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'trim|required']]);
                $this->validation->setRules(['recipients[]' => ["label" => translate('name'), "rules" => 'trim|required']]);
            }

            if ($recipientType == 3) {
                $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
                $this->validation->setRules(['section[]' => ["label" => translate('section'), "rules" => 'trim|required']]);
            }

            if (isset($_POST['send_later'])) {
                $this->validation->setRules(['schedule_date' => ["label" => translate('schedule_date'), "rules" => 'trim|required']]);
                $this->validation->setRules(['schedule_time' => ["label" => translate('schedule_time'), "rules" => 'trim|required']]);
            }

            if ($this->validation->run() !== false) {
                $userArray = [];
                $receivedDetails = [];
                $campaignName = $this->request->getPost('campaign_name');
                $message = $this->request->getPost('message', false);
                $scheduleDate = $this->request->getPost('schedule_date');
                $scheduleTime = $this->request->getPost('schedule_time');
                $sendLater = isset($_POST['send_later']) ? 1 : 2;
                $emailSubject = $this->request->getPost('email_subject');
                $smsGateway = $this->request->getPost('sms_gateway');
                $dltTemplateID = $this->request->getPost('dlt_template_id');
                if ($recipientType == 1) {
                    $roleGroup = $this->request->getPost('role_group[]');
                    $receivedDetails['role'] = $roleGroup;
                    foreach ($roleGroup as $usersValue) {
                        if ($usersValue != 6 && $usersValue != 7) {
                            $staff = $this->sendsmsmailModel->getStaff($branchID, $usersValue);
                            if (count($staff) > 0) {
                                foreach ($staff as $value) {
                                    $userArray[] = ['name' => $value['name'], 'email' => $value['email'], 'mobileno' => $value['mobileno']];
                                }
                            }
                        }

                        if ($usersValue == 6) {
                            $parents = $this->sendsmsmailModel->getParent($branchID);
                            if (count($parents) > 0) {
                                foreach ($parents as $value) {
                                    $userArray[] = ['name' => $value['name'], 'email' => $value['email'], 'mobileno' => $value['mobileno']];
                                }
                            }
                        }

                        if ($usersValue == 7) {
                            $students = $this->sendsmsmailModel->getStudent($branchID);
                            if (count($students) > 0) {
                                foreach ($students as $value) {
                                    $userArray[] = ['name' => $value['name'], 'email' => $value['email'], 'mobileno' => $value['mobileno']];
                                }
                            }
                        }
                    }
                }

                if ($recipientType == 2) {
                    $roleID = $this->request->getPost('role_id');
                    $recipients = $this->request->getPost('recipients[]');
                    foreach ($recipients as $value) {
                        if ($roleID != 6 && $roleID != 7) {
                            $staff = $this->sendsmsmailModel->getStaff($branchID, '', $value);
                            if (!empty($staff)) {
                                $userArray[] = ['name' => $staff['name'], 'email' => $staff['email'], 'mobileno' => $staff['mobileno']];
                            }
                        }

                        if ($roleID == 6) {
                            $parent = $this->sendsmsmailModel->getParent($branchID, $value);
                            if (!empty($parent)) {
                                $userArray[] = ['name' => $parent['name'], 'email' => $parent['email'], 'mobileno' => $parent['mobileno']];
                            }
                        }

                        if ($roleID == 7) {
                            $student = $this->sendsmsmailModel->getStudent($branchID, $value);
                            if (!empty($student)) {
                                $userArray[] = ['name' => $student['name'], 'email' => $student['email'], 'mobileno' => $student['mobileno']];
                            }
                        }
                    }
                }

                if ($recipientType == 3) {
                    $classID = $this->request->getPost('class_id');
                    $sections = $this->request->getPost('section[]');
                    $receivedDetails['class'] = $classID;
                    $receivedDetails['sections'] = $sections;
                    foreach ($sections as $value) {
                        $students = $this->sendsmsmailModel->getStudentBySection($classID, $value, $branchID);
                        if (count($students) > 0) {
                            foreach ($students as $value) {
                                $userArray[] = ['name' => $value['name'], 'email' => $value['email'], 'mobileno' => $value['mobileno']];
                            }
                        }
                    }
                }

                $sCount = 0;
                if ($sendLater == 1) {
                    $additional = json_encode($userArray);
                } else {
                    foreach ($userArray as $value) {
                        if ($messageType == 1) {
                            $response = $this->sendsmsmailModel->sendSMS($value['mobileno'], $message, $value['name'], $value['email'], $smsGateway, $dltTemplateID);
                        } else {
                            $response = $this->sendsmsmailModel->sendEmail($value['email'], $message, $value['name'], $value['mobileno'], $emailSubject);
                        }

                        if ($response == true) {
                            $sCount++;
                        }
                    }

                    $additional = '';
                }

                $receivedDetails = $receivedDetails === [] ? '' : json_encode($receivedDetails);
                $arrayData = ['campaign_name' => $campaignName, 'message' => $message, 'message_type' => $messageType, 'recipient_type' => $recipientType, 'recipients_details' => $receivedDetails, 'additional' => $additional, 'schedule_time' => date('Y-m-d H:i:s', strtotime($scheduleDate . ' ' . $scheduleTime)), 'posting_status' => $sendLater, 'total_thread' => count($userArray), 'successfully_sent' => $sCount, 'branch_id' => $branchID];
                if ($messageType == 1) {
                    $arrayData['sms_gateway'] = $smsGateway;
                } else {
                    $arrayData['email_subject'] = $emailSubject;
                }

                $this->db->table('bulk_sms_email')->insert();
                set_alert('success', translate('message_sent_successfully'));
                $url = $messageType == 1 ? base_url('sendsmsmail/sms') : base_url('sendsmsmail/email');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // add send sms mail template
    public function template()
    {
        $type = html_escape($this->uri->segment(3));
        $typeA = ['email', 'sms'];
        $result = in_array($type, $typeA, true);
        $typeN = $type == 'sms' ? 1 : 2;
        if (!get_permission('sendsmsmail_template', 'is_view') || !$result) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('sendsmsmail_template', 'is_add')) {
            // validate inputs
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['template_name' => ["label" => translate('name'), "rules" => 'required']]);
            $this->validation->setRules(['message' => ["label" => translate('message'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $post['type'] = $typeN;
                $this->sendsmsmailModel->saveTemplate($post);
                $url = current_url();
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['type'] = $type;
        $this->data['templetelist'] = $this->appLib->getTable('bulk_msg_category', ['type' => $typeN]);
        $this->data['title'] = translate('bulk_sms_and_email');
        $this->data['sub_page'] = 'sendsmsmail/template_' . $type;
        $this->data['main_menu'] = 'sendsmsmail';
        echo view('layout/index', $this->data);
    }

    // edit send sms mail template
    public function template_edit($id, $type = '')
    {
        $typeA = ['email', 'sms'];
        $result = in_array($type, $typeA, true);
        $typeN = $type == 'sms' ? 1 : 2;
        if (!get_permission('sendsmsmail_template', 'is_edit') || !$result) {
            access_denied();
        }

        if ($_POST !== []) {
            // validate inputs
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['template_name' => ["label" => translate('name'), "rules" => 'required']]);
            $this->validation->setRules(['message' => ["label" => translate('message'), "rules" => 'required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $post['type'] = $typeN;
                $this->sendsmsmailModel->saveTemplate($post);
                $url = base_url('sendsmsmail/template/' . $type);
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        $this->data['type'] = $type;
        $this->data['templete'] = $this->appLib->getTable('bulk_msg_category', ['t.id' => $id, 't.type' => $typeN], true);
        $this->data['title'] = translate('bulk_sms_and_email');
        $this->data['sub_page'] = 'sendsmsmail/template_edit_' . $type;
        $this->data['main_menu'] = 'sendsmsmail';
        echo view('layout/index', $this->data);
    }

    public function template_delete($id)
    {
        if (!get_permission('sendsmsmail_template', 'is_delete')) {
            access_denied();
        }

        $this->db->table('id')->where();
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('bulk_msg_category')->delete();
    }

    public function getRecipientsByRole()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        $roleID = $this->request->getPost('role_id');
        if (!empty($branchID)) {
            if ($roleID != 6 && $roleID != 7) {
                $builder->select('staff.id,staff.name,staff.staff_id,lc.role');
                $this->db->from('staff');
                $builder->join('login_credential as lc', 'lc.user_id = staff.id AND lc.role != 6 AND lc.role != 7', 'inner');
                $this->db->table('lc.role')->where();
                $this->db->table('staff.branch_id')->where();
                $this->db->order_by('staff.id', 'asc');
                $result = $builder->get()->result_array();
                foreach ($result as $staff) {
                    $html .= "<option value='" . $staff['id'] . "'>" . $staff['name'] . " (" . $staff['staff_id'] . ")</option>";
                }
            }

            if ($roleID == 6) {
                $this->db->table('branch_id')->where();
                $result = $builder->get('parent')->result_array();
                foreach ($result as $row) {
                    $html .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }

            if ($roleID == 7) {
                $builder->select('e.student_id,e.roll,CONCAT(s.first_name, " ", s.last_name) as name');
                $this->db->from('enroll as e');
                $builder->join('student as s', 's.id = e.student_id', 'inner');
                $this->db->table('e.branch_id')->where();
                $this->db->table('e.session_id')->where();
                $students = $builder->get()->result_array();
                foreach ($students as $row) {
                    $html .= "<option value='" . $row['student_id'] . "'>" . $row['name'] . " (Roll" . $row['roll'] . ")</option>";
                }
            }
        }

        echo $html;
    }

    public function getSectionByClass()
    {
        $html = "";
        $classID = $this->request->getPost("class_id");
        if (!empty($classID)) {
            $result = $builder->select('sections_allocation.section_id,section.name')->from('sections_allocation')->join('section', 'section.id = sections_allocation.section_id', 'left')->where('sections_allocation.class_id', $classID)->get()->result_array();
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
                }
            }
        }

        echo $html;
    }

    public function getSmsGateway()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        if (!empty($branchID)) {
            $builder->select('sms_api.name');
            $this->db->from('sms_api');
            $builder->join('sms_credential', 'sms_credential.sms_api_id = sms_api.id', 'inner');
            $this->db->table('sms_credential.branch_id')->where();
            $this->db->table('sms_credential.is_active')->where();
            $this->db->order_by('sms_api.id', 'asc');
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['name'] . '">' . ucfirst((string) $row['name']) . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_sms_gateway_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getTemplateByBranch()
    {
        $html = "";
        $type = $this->request->getPost('type');

        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $result = $db->table('bulk_msg_category')->get('bulk_msg_category')->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
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

    public function getSmsTemplateText()
    {
        $id = $this->request->getPost('id');
        $row = $this->db->table('bulk_msg_category')->where(['id' => $id])->get()->getRowArray();
        echo $row['body'];
    }

    public function getDetails()
    {
        if (get_permission('sendsmsmail', 'is_view')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->data['bulkdata'] = $builder->get('bulk_sms_email')->row_array();
            echo view('sendsmsmail/messageModal', $this->data);
        }
    }
}
