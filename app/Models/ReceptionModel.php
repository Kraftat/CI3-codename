<?php

namespace App\Models;

use CodeIgniter\Model;
class ReceptionModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function postalSave($data = [])
    {
        $attachment_file = "";
        $oldAttachment_file = $this->request->getPost('old_document_file');
        if (isset($_FILES["document_file"]) && !empty($_FILES['document_file']['name'])) {
            $config['upload_path'] = './uploads/reception/postal/';
            $config['allowed_types'] = '*';
            $config['overwrite'] = false;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("document_file")) {
                // need to unlink previous photo
                if (!empty($oldAttachment_file)) {
                    $unlink_path = 'uploads/reception/postal/' . $oldAttachment_file;
                    if (file_exists($unlink_path)) {
                        @unlink($unlink_path);
                    }
                }
                $attachment_file = $this->upload->data('file_name');
            }
        } else if (!empty($oldAttachment_file)) {
            $attachment_file = $oldAttachment_file;
        }
        $arrayInsert = array('sender_title' => $data['sender_title'], 'receiver_title' => $data['receiver_title'], 'reference_no' => $data['reference_no'], 'address' => $data['address'], 'date' => date("Y-m-d", strtotime($data['date'])), 'note' => $data['note'], 'file' => $attachment_file, 'confidential' => isset($data['confidential']) ? 1 : 0, 'type' => $data['type'], 'branch_id' => $this->applicationModel->get_branch_id(), 'created_by' => get_loggedin_user_id(), 'updated_at' => date('Y-m-d H:i:s'));
        if (!isset($data['id'])) {
            $arrayInsert['created_at'] = date('Y-m-d H:i:s');
            $builder->insert('postal_record', $arrayInsert);
        } else {
            $builder->where('id', $data['id']);
            $builder->update('postal_record', $arrayInsert);
        }
    }
    public function enquirySave($data = [])
    {
        $arrayInsert = array('name' => $data['name'], 'father_name' => $data['father_name'], 'mother_name' => $data['mother_name'], 'mobile_no' => $data['mobile_no'], 'email' => $data['email'], 'date' => date("Y-m-d", strtotime($data['date'])), 'birthday' => empty($data['birthday']) ? NULL : date("Y-m-d", strtotime($data['birthday'])), 'gender' => $data['gender'], 'address' => $data['address'], 'previous_school' => $data['previous_school'], 'reference_id' => $data['reference'], 'response_id' => $data['response_id'], 'response' => $data['response'], 'note' => $data['note'], 'no_of_child' => $data['no_of_child'], 'class_id' => $data['class_id'], 'branch_id' => $this->applicationModel->get_branch_id(), 'assigned_id' => $data['staff_id'], 'updated_at' => date('Y-m-d H:i:s'));
        if (!isset($data['id'])) {
            $arrayInsert['created_by'] = get_loggedin_user_id();
            $arrayInsert['created_at'] = date('Y-m-d H:i:s');
            $builder->insert('enquiry', $arrayInsert);
        } else {
            $builder->where('id', $data['id']);
            $builder->update('enquiry', $arrayInsert);
        }
    }
    public function call_logSave($data = [])
    {
        $arrayInsert = array('name' => $data['name'], 'number' => $data['phone_number'], 'purpose_id' => $data['purpose_id'], 'call_type' => $data['call_type'], 'date' => date("Y-m-d", strtotime($data['date'])), 'follow_up' => empty($data['follow_up_date']) ? NULL : date("Y-m-d", strtotime($data['follow_up_date'])), 'start_time' => date("H:i:s", strtotime($data['start_time'])), 'end_time' => date("H:i:s", strtotime($data['end_time'])), 'note' => $data['note'], 'branch_id' => $this->applicationModel->get_branch_id(), 'created_by' => get_loggedin_user_id(), 'updated_at' => date('Y-m-d H:i:s'));
        if (!isset($data['id'])) {
            $arrayInsert['created_at'] = date('Y-m-d H:i:s');
            $builder->insert('call_log', $arrayInsert);
        } else {
            $builder->where('id', $data['id']);
            $builder->update('call_log', $arrayInsert);
        }
    }
    public function visitor_logSave($data = [])
    {
        $arrayInsert = array('name' => $data['name'], 'number' => $data['phone_number'], 'purpose_id' => $data['purpose_id'], 'date' => date("Y-m-d", strtotime($data['date'])), 'entry_time' => date("H:i:s", strtotime($data['entry_time'])), 'exit_time' => date("H:i:s", strtotime($data['exit_time'])), 'number_of_visitor' => $data['number_of_visitor'], 'id_number' => $data['id_number'], 'token_pass' => $data['token_pass'], 'note' => $data['note'], 'branch_id' => $this->applicationModel->get_branch_id(), 'created_by' => get_loggedin_user_id(), 'updated_at' => date('Y-m-d H:i:s'));
        if (!isset($data['id'])) {
            $arrayInsert['created_at'] = date('Y-m-d H:i:s');
            $builder->insert('visitor_log', $arrayInsert);
        } else {
            $builder->where('id', $data['id']);
            $builder->update('visitor_log', $arrayInsert);
        }
    }
    public function complaintSave($data = [])
    {
        $attachment_file = "";
        $oldAttachment_file = $this->request->getPost('old_document_file');
        if (isset($_FILES["document_file"]) && !empty($_FILES['document_file']['name'])) {
            $config['upload_path'] = './uploads/reception/complaint/';
            $config['allowed_types'] = '*';
            $config['overwrite'] = false;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("document_file")) {
                // need to unlink previous photo
                if (!empty($oldAttachment_file)) {
                    $unlink_path = 'uploads/reception/complaint/' . $oldAttachment_file;
                    if (file_exists($unlink_path)) {
                        @unlink($unlink_path);
                    }
                }
                $attachment_file = $this->upload->data('file_name');
            }
        } else if (!empty($oldAttachment_file)) {
            $attachment_file = $oldAttachment_file;
        }
        $arrayInsert = array('name' => $data['complainant_name'], 'number' => $data['phone_number'], 'type_id' => $data['type_id'], 'date' => date("Y-m-d", strtotime($data['date'])), 'assigned_id' => $data['staff_id'], 'date_of_solution' => isset($data['date_of_solution']) ? date("Y-m-d", strtotime($data['date_of_solution'])) : '', 'action' => isset($data['action']) ? $data['action'] : '', 'file' => $attachment_file, 'note' => $data['note'], 'branch_id' => $this->applicationModel->get_branch_id(), 'created_by' => get_loggedin_user_id(), 'updated_at' => date('Y-m-d H:i:s'));
        if (!isset($data['id'])) {
            $arrayInsert['created_at'] = date('Y-m-d H:i:s');
            $builder->insert('complaint', $arrayInsert);
        } else {
            $builder->where('id', $data['id']);
            $builder->update('complaint', $arrayInsert);
        }
    }
    public function getStatus()
    {
        $date = array("" => "Select", "1" => "Active", "2" => "Partially Closed", "3" => "Missed", "4" => "Closed");
        return $date;
    }
    public function follow_up_details($enquiryID)
    {
        $query = $db->table('enquiry_follow_up')->get('enquiry_follow_up');
        $id = $query->row()->id;
        $builder->select('*');
        $builder->from('enquiry_follow_up');
        $builder->where('id', $id);
        $query = $builder->get();
        return $query->row_array();
    }
}



