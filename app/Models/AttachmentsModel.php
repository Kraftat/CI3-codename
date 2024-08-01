<?php

namespace App\Models;

use CodeIgniter\Model;
class AttachmentsModel extends MYModel
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function save($data)
    {
        $classID = !isset($data['all_class_set']) ? $data['class_id'] : 'unfiltered';
        $arrayData = array('branch_id' => $this->applicationModel->get_branch_id(), 'title' => $data['title'], 'type_id' => $data['type_id'], 'remarks' => $data['remarks'], 'date' => date("Y-m-d", strtotime($data['date'])), 'session_id' => get_session_id(), 'uploader_id' => get_loggedin_user_id(), 'class_id' => $classID, 'subject_id' => get_loggedin_user_id(), 'updated_at' => date("Y-m-d H:i:s"));
        if (!isset($data['all_class_set']) && !isset($data['subject_wise'])) {
            $arrayData['subject_id'] = $data['subject_id'];
        } else {
            $arrayData['subject_id'] = 'unfiltered';
        }
        if (!isset($data['attachment_id'])) {
            // uploading file using codeigniter upload library
            $config['upload_path'] = 'uploads/attachments/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attachment_file")) {
                $arrayData['file_name'] = $this->upload->data('orig_name');
                $arrayData['enc_name'] = $this->upload->data('file_name');
                $builder->insert('attachments', $arrayData);
            } else {
                return ['error' => $this->upload->display_errors()];
            }
        } else if ($_FILES['attachment_file']['name'] != "") {
            $config['upload_path'] = 'uploads/attachments/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attachment_file")) {
                $encrypt_name = $db->table('attachments')->get('attachments')->row()->enc_name;
                $file_name = 'uploads/attachments/' . $encrypt_name;
                if (file_exists($file_name)) {
                    unlink($file_name);
                }
                $arrayData['file_name'] = $this->upload->data('orig_name');
                $arrayData['enc_name'] = $this->upload->data('file_name');
                $builder->where('id', $data['attachment_id']);
                $builder->update('attachments', $arrayData);
            } else {
                return ['error' => $this->upload->display_errors()];
            }
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $data['attachment_id']);
            $builder->update('attachments', $arrayData);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function type_save($data, $id = null)
    {
        $arrayType = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['type_name']);
        if ($id == null) {
            $builder->insert('attachments_type', $arrayType);
        } else {
            $builder->where('id', $id);
            $builder->update('attachments_type', $arrayType);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    // get attachments list
    public function getAttachmentsList()
    {
        $builder->select('a.*,b.name as branch_name,at.name as type_name,c.name as class_name,s.name as subject_name');
        $builder->from('attachments as a');
        $builder->join('attachments_type as at', 'at.id = a.type_id', 'left');
        $builder->join('class as c', 'c.id = a.class_id', 'left');
        $builder->join('branch as b', 'b.id = a.branch_id', 'left');
        $builder->join('subject as s', 's.id = a.subject_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('a.branch_id', get_loggedin_branch_id())->where();
        }
        if (loggedin_role_id() == 6) {
            $classID = $db->table('enroll')->get('enroll')->row()->class_id;
            $this->db->table('class_id', $classID)->or_where('class_id', 'unfiltered')->where();
        }
        if (loggedin_role_id() == 7) {
            $classID = $db->table('enroll')->get('enroll')->row()->class_id;
            $this->db->table('class_id', $classID)->or_where('class_id', 'unfiltered')->where();
        }
        $builder->order_by('a.id', 'desc');
        $result = $builder->get()->result_array();
        return $result;
    }
}



