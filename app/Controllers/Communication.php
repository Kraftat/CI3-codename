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
 * @filename : Communication.php
 * @copyright : Reserved RamomCoder Team
 */
class Communication extends AdminController

{
    public $appLib;

    protected $db;

    /**
     * @var App\Models\CommunicationModel
     */
    public $communication;

    public $input;

    public $communicationModel;

    public $load;

    public $validation;

    public $upload;

    public $applicationModel;

    public function __construct()
    {

        parent::__construct();

        $this->appLib = service('appLib'); 
$this->communication = new \App\Models\CommunicationModel();
    }

    public function index()
    {
        if (is_loggedin()) {
            return redirect()->to(base_url('communication/mailbox/inbox'));
        }

        redirect(base_url(), 'refresh');
        return null;
    }

    public function mailbox($action = 'inbox')
    {
        if ($action == 'compose') {
            $this->data['inside_subview'] = 'message_compose';
        } elseif ($action == 'inbox') {
            $this->data['inside_subview'] = 'message_inbox';
        } elseif ($action == 'sent') {
            $this->data['inside_subview'] = 'message_sent';
        } elseif ($action == 'important') {
            $this->data['inside_subview'] = 'message_important';
        } elseif ($action == 'trash') {
            $this->data['inside_subview'] = 'message_trash';
        } elseif ($action == 'read') {
            $id = urldecode((string) $this->request->getGet('id'));
            if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $id) || is_numeric($id) == false) {
                return redirect()->to(base_url('dashboard'));
            }

            $response = $this->communicationModel->mark_messages_read($id);
            $this->data['message_id'] = $id;
            $this->data['inside_subview'] = 'message_read';
        }

        $this->data['active_user'] = loggedin_role_id() . '-' . get_loggedin_user_id();
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('mailbox');
        $this->data['sub_page'] = 'communication/message';
        $this->data['main_menu'] = 'message';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    public function message_send()
    {
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'trim|required']]);
            $this->validation->setRules(['receiver_id' => ["label" => translate('receiver'), "rules" => 'trim|required']]);
            $this->validation->setRules(['subject' => ["label" => translate('subject'), "rules" => 'trim|required']]);
            $this->validation->setRules(['message_body' => ["label" => translate('message'), "rules" => 'trim|required']]);
            $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_handle_upload']]);
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $messageId = $this->communicationModel->mailbox_compose($post);
                set_alert('success', translate('message_sent_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function message_reply()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_handle_upload']]);
            $this->validation->setRules(['message' => ["label" => 'Message', "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $messageId = $this->request->getPost('message_id');
                if ($this->request->getPost('user_identity') == 'sender') {
                    $arrayMsg['identity'] = 1;
                    $this->db->table('id')->where();
                    $this->db->table('message')->update();
                } else {
                    $arrayMsg['identity'] = 0;
                    $this->db->table('id')->where();
                    $this->db->table('message')->update();
                }

                $arrayMsg['created_at'] = date('Y-m-d H:i:s');
                $arrayMsg['message_id'] = $messageId;
                $arrayMsg['body'] = $this->request->getPost('message');
                if ($_FILES["attachment_file"]['name'] != "") {
                    // uploading file using codeigniter upload library
                    $config['upload_path'] = 'uploads/attachments/';
                    $config['encrypt_name'] = true;
                    $config['allowed_types'] = '*';
                    $file = $this->request->getFile('attachment_file'); $file->initialize($config);
                    if ($this->upload->do_upload("attachment_file")) {
                        $arrayMsg['file_name'] = $file = $this->request->getFile('attachment_file'); $file->data('orig_name');
                        $arrayMsg['enc_name'] = $file = $this->request->getFile('attachment_file'); $file->data('file_name');
                    }
                }

                $this->db->table('message_reply')->insert();
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // file downloader
    public function download()
    {
        $encryptName = urldecode((string) $this->request->getGet('file'));
        $type = urldecode((string) $this->request->getGet('type'));
        if (!preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $type)) {
            return redirect()->to(base_url('dashboard'));
        }

        $table = $type === 'reply' ? 'message_reply' : 'message';
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            $fileName = $db->table($table)->get($table)->row()->file_name;
            if (!empty($fileName)) {
                helper('download');
                return $this->response->download($fileName, file_get_contents('uploads/attachments/' . $encryptName));
            }
        }

        return null;
    }

    // upload file form validation
    public function handle_upload()
    {
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            $allowedExts = array_map('trim', array_map('strtolower', explode(',', (string) $this->data['global_config']['file_extension'])));
            $allowedSizeKB = $this->data['global_config']['file_size'];
            $allowedSize = floatval(1024 * $allowedSizeKB);
            $fileSize = $_FILES["attachment_file"]["size"];
            $fileName = $_FILES["attachment_file"]["name"];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES["attachment_file"]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setRule('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > $allowedSize) {
                    $this->validation->setRule('handle_upload', translate('file_size_shoud_be_less_than') . sprintf(' %s KB.', $allowedSizeKB));
                    return false;
                }
            } else {
                $this->validation->setRule('handle_upload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }

    /* message delete */
    public function delete_mail()
    {
        $arrayID = $this->request->getPost('arrayID');
        $this->request->getPost('mode');
        if (count($arrayID) > 0) {
            foreach ($arrayID as $value) {
                $this->db->table('id')->where();
                $this->db->table('message')->update();
            }

            set_alert('success', translate('message_has_been_deleted'));
        } else {
            set_alert('error', 'Please Select a Message to Delete');
        }
    }

    public function set_fvourite_status()
    {
        $this->request->getPost('messageID');
        $status = $this->request->getPost('status');
        $activeUser = loggedin_role_id() . '-' . get_loggedin_user_id();
        $query = $db->table('message')->get('message')->row();
        if ($activeUser == $query->sender) {
            $data['fav_sent'] = $status == 'false' ? 0 : 1;
        } elseif ($activeUser == $query->reciever) {
            $data['fav_inbox'] = $status == 'false' ? 0 : 1;
        }

        $this->db->table('id')->where();
        $this->db->table('message')->update();
        $return = ['msg' => translate('information_has_been_updated_successfully'), 'status' => true];
        echo json_encode($return);
    }

    /* mailbox trash observe */
    public function trash_observe()
    {
        $activeUser = loggedin_role_id() . '-' . get_loggedin_user_id();
        $arrayID = $this->request->getPost('array_id');
        $mode = $this->request->getPost('mode');
        if ($mode == 'restore') {
            $status = 0;
        } elseif ($mode == 'delete') {
            $status = 1;
        } elseif ($mode == 'forever') {
            $status = 2;
        }

        if (count($arrayID) > 0) {
            $array = [];
            foreach ($arrayID as $id) {
                $getUser = $db->table('message')->get('message')->row();
                if ($getUser->sender == $activeUser) {
                    $array['trash_sent'] = $status;
                } elseif ($getUser->reciever == $activeUser) {
                    $array['trash_inbox'] = $status;
                }

                $this->db->table('id')->where();
                $this->db->table('message')->update();
            }

            if ($option == 'restore') {
                set_alert('success', translate('message_has_been_restored'));
            } elseif ($option == 'delete') {
                set_alert('success', translate('message_has_been_deleted'));
            }
        } else {
            set_alert('error', 'Please Select a Message to Delete');
        }
    }

    public function getStafflistRole()
    {
        $html = "";
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $roleId = $this->request->getPost('role_id');
            $selectedId = $_POST['staff_id'] ?? 0;
            $builder->select('staff.id,staff.name,staff.staff_id,lc.role');
            $this->db->from('staff');
            $builder->join('login_credential as lc', 'lc.user_id = staff.id AND lc.role != 6 AND lc.role != 7', 'inner');
            $this->db->table('lc.role')->where();
            $this->db->table('staff.branch_id')->where();
            $this->db->order_by('staff.id', 'asc');
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $staff) {
                    if ($staff['id'] == get_loggedin_user_id()) {
                        continue;
                    }

                    $selected = $staff['id'] == $selectedId ? 'selected' : '';
                    $html .= "<option value='" . $staff['id'] . "' " . $selected . ">" . $staff['name'] . " (" . $staff['staff_id'] . ")</option>";
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getStudentByClass()
    {
        $html = "";
        $classId = $this->request->getPost('class_id');
        $this->applicationModel->get_branch_id();
        if (!empty($classId)) {
            $builder->select('e.student_id,s.register_no,CONCAT(s.first_name, " ", s.last_name) as fullname');
            $this->db->from('enroll as e');
            $builder->join('student as s', 's.id = e.student_id', 'inner');
            $builder->join('login_credential as l', 'l.user_id = e.student_id and l.role = 7', 'left');
            $this->db->table('l.active')->where();
            $this->db->table('e.session_id')->where();
            $this->db->table('e.class_id')->where();
            $this->db->table('e.branch_id')->where();
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    if ($row['student_id'] == get_loggedin_user_id()) {
                        continue;
                    }

                    $html .= '<option value="' . $row['student_id'] . '">' . $row['fullname'] . ' (Register No : ' . $row['register_no'] . ')</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_class_first') . '</option>';
        }

        echo $html;
    }

    public function getParentListBranch()
    {
        $html = "";
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $roleId = $this->request->getPost('role_id');
            $selectedId = $_POST['parent_id'] ?? 0;
            $builder->select('parent.id,parent.name');
            $this->db->from('parent');
            $this->db->table('parent.branch_id')->where();
            $this->db->order_by('parent.id', 'asc');
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $staff) {
                    if ($staff['id'] == get_loggedin_user_id()) {
                        continue;
                    }

                    $selected = $staff['id'] == $selectedId ? 'selected' : '';
                    $html .= "<option value='" . $staff['id'] . "' " . $selected . ">" . $staff['name'] . "</option>";
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function messageImportant()
    {
        $activeUser = get_loggedin_user_id();

        $sql = "SELECT * FROM message WHERE (sender = " . $db->escape($activeUser) . " AND fav_sent = 1 AND trash_sent = 0) OR (receiver = " .
               $db->escape($activeUser) . " AND fav_inbox = 1 AND trash_inbox = 0) ORDER BY id DESC";
        $messages = $db->query($sql)->getResult();

        $applicationModel = new \App\Models\ApplicationModel(); // Ensure you have an ApplicationModel for getting user details

        return view('communication/message_important', [
            'messages' => $messages,
            'active_user' => $activeUser,
            'applicationModel' => $applicationModel
        ]);
    }

    public function messageTrash()
    {
        $activeUser = get_loggedin_user_id();

        $sql = "SELECT * FROM message WHERE (sender = " . $db->escape($activeUser) . " AND trash_sent = 1) OR (receiver = " .
               $db->escape($activeUser) . " AND trash_inbox = 1) ORDER BY id DESC";
        $messages = $db->query($sql)->getResult();

        $applicationModel = new \App\Models\ApplicationModel(); // Ensure you have an ApplicationModel for getting user details

        return view('communication/message_trash', [
            'messages' => $messages,
            'active_user' => $activeUser,
            'applicationModel' => $applicationModel
        ]);
    }
}
