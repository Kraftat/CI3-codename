<?php

namespace App\Models;

use CodeIgniter\Model;
class CommunicationModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    // mailbox compose
    public function mailbox_compose($data)
    {
        $id = '';
        $branchID = $this->applicationModel->get_branch_id();
        $sender = loggedin_role_id() . '-' . get_loggedin_user_id();
        $reciever = $data['role_id'] . '-' . $data['receiver_id'];
        $arrayMsg = array('body' => $data['message_body'], 'subject' => $data['subject'], 'sender' => $sender, 'reciever' => $reciever, 'created_at' => date('Y-m-d H:i:s'));
        if ($_FILES["attachment_file"]['name'] != "") {
            // uploading file using codeigniter upload library
            $config['upload_path'] = 'uploads/attachments/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attachment_file")) {
                $arrayMsg['file_name'] = $this->upload->data('orig_name');
                $arrayMsg['enc_name'] = $this->upload->data('file_name');
            }
        }
        $builder->insert('message', $arrayMsg);
        $id = $builder->insert_id();
        // send new message received email
        $this->db->table(array('branch_id' => $branchID, 'template_id' => 4))->where();
        $getTemplate = $builder->get('email_templates_details')->row_array();
        if ($getTemplate['notified'] == 1) {
            $user = $this->applicationModel->getUserNameByRoleID($data['role_id'], $data['receiver_id']);
            $message = $getTemplate['template_body'];
            $message = str_replace("{institute_name}", get_global_setting('institute_name'), $message);
            $message = str_replace("{recipient}", $user['name'], $message);
            $message = str_replace("{message}", $data['message_body'], $message);
            $message = str_replace("{message_url}", base_url('communication/mailbox/read?type=inbox&id=' . $id), $message);
            $msg_data['recipient'] = $user['email'];
            $msg_data['subject'] = $getTemplate['subject'];
            $msg_data['message'] = $message;
            $this->model("email_model");
            $this->emailModel->sendEmail($msg_data);
        }
        return $id;
    }
    public function mark_messages_read($message_id)
    {
        $activeUser = loggedin_role_id() . '-' . get_loggedin_user_id();
        $builder->where('reciever', $activeUser);
        $builder->where('id', $message_id);
        $this->db->table('message', array('read_status' => 1, 'updated_at' => date('Y-m-d H:i:s')))->update();
        $builder->where('sender', $activeUser);
        $builder->where('id', $message_id);
        $this->db->table('message', array('reply_status' => 0, 'updated_at' => date('Y-m-d H:i:s')))->update();
    }
}



