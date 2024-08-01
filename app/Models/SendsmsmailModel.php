<?php

namespace App\Models;

use CodeIgniter\Model;
class SendsmsmailModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getStaff($branch_id, $role_id = '', $staff_id = '')
    {
        $builder->select('staff.id,staff.name,staff.mobileno,staff.email');
        $builder->from('staff');
        $builder->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "6" and login_credential.role != "7"', 'inner');
        $builder->where('staff.branch_id', $branch_id);
        if (!empty($role_id)) {
            $method = 'result_array';
            $builder->where('login_credential.role', $role_id);
            $builder->order_by('staff.id', 'ASC');
        }
        if (!empty($staff_id)) {
            $builder->where('staff.id', $staff_id);
            $method = 'row_array';
        }
        return $builder->get()->{$method}();
    }
    public function getParent($branch_id, $parent_id = '')
    {
        $builder->select('id,name,email,mobileno');
        $builder->where('branch_id', $branch_id);
        if (empty($parent_id)) {
            $method = 'result_array';
        } else {
            $builder->where('id', $parent_id);
            $method = 'row_array';
        }
        return $builder->get('parent')->{$method}();
    }
    public function getStudent($branch_id, $student_id = '')
    {
        $builder->select('e.student_id,CONCAT_WS(" ",s.first_name, s.last_name) as name,s.mobileno,s.email');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->where('e.branch_id', $branch_id);
        if (empty($student_id)) {
            $method = 'result_array';
            $this->db->table('e.session_id', get_session_id())->where();
            $builder->order_by('s.id', 'ASC');
        } else {
            $builder->where('s.id', $student_id);
            $method = 'row_array';
        }
        return $builder->get()->{$method}();
    }
    public function getStudentBySection($class_id, $section_id, $branch_id)
    {
        $builder->select('e.student_id,CONCAT_WS(" ",s.first_name, s.last_name) as name,s.mobileno,s.email');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->where('e.class_id', $class_id);
        $builder->where('e.section_id', $section_id);
        $builder->where('e.branch_id', $branch_id);
        $this->db->table('e.session_id', get_session_id())->where();
        $builder->order_by('s.id', 'ASC');
        return $builder->get()->result_array();
    }
    public function saveTemplate($data)
    {
        $insertData = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['template_name'], 'body' => $this->request->getPost('message', false), 'type' => $data['type']);
        if (!isset($data['template_id'])) {
            $builder->insert('bulk_msg_category', $insertData);
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $data['template_id']);
            $builder->update('bulk_msg_category', $insertData);
        }
    }
    public function sendEmail($sendTo, $message, $name, $mobileNo, $emailSubject)
    {
        $message = str_replace('{name}', $name, $message);
        $message = str_replace('{email}', $sendTo, $message);
        $message = str_replace('{mobile_no}', $mobileNo, $message);
        $branchID = $this->applicationModel->get_branch_id();
        $data = array('branch_id' => $branchID, 'recipient' => $sendTo, 'subject' => $emailSubject, 'message' => $message);
        if ($this->mailer->send($data)) {
            return true;
        } else {
            return false;
        }
    }
    public function sendSMS($sendTo, $message, $name, $eMail, $smsGateway, $dlt_templateID)
    {
        $message = str_replace('{name}', $name, $message);
        $message = str_replace('{email}', $eMail, $message);
        $message = str_replace('{mobile_no}', $sendTo, $message);
        if ($smsGateway == 'twilio') {
            $this->twilio = service('twilio');
            $response = $this->twilio->sms($sendTo, $message);
            return true;
        }
        if ($smsGateway == 'clickatell') {
            $this->clickatell = service('clickatell');
            return $this->clickatell->send_message($sendTo, $message);
        }
        if ($smsGateway == 'msg91') {
            $this->msg91 = service('msg91');
            return $this->msg91->send($sendTo, $message, $dlt_templateID);
        }
        if ($smsGateway == 'bulksms') {
            $this->bulk = service('bulk');
            return $this->bulk->send($sendTo, $message);
        }
        if ($smsGateway == 'textlocal') {
            $this->textlocal = service('textlocal');
            return $this->textlocal->sendSms($sendTo, $message);
        }
        if ($smsGateway == 'smscountry') {
            $this->smscountry = service('smscountry');
            return $this->smscountry->send($sendTo, $message);
        }
        if ($smsGateway == 'bulksmsbd') {
            $this->bulksmsbd = service('bulksmsbd');
            return $this->bulksmsbd->send($sendTo, $message);
        }
        if ($smsGateway == 'customSms') {
            $this->custom_sms = service('customSms');
            $res = $this->custom_sms->send($sendTo, $message, $dlt_templateID);
        }
    }
}



