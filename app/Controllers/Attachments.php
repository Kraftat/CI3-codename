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
 * @filename : Attachments.php
 * @copyright : Reserved RamomCoder Team
 */
class Attachments extends AdminController

{
    public $appLib;

    protected $db;




    public $load;

    /**
     * @var App\Models\AttachmentsModel
     */
    public $attachments;

    public $validation;

    public $input;

    public $attachmentsModel;

    public $applicationModel;

    public function __construct()
    {




        parent::__construct();

        $this->appLib = service('appLib'); 
$this->load->helpers('download');
        $this->attachments = new \App\Models\AttachmentsModel();
        if (!moduleIsEnabled('attachments_book')) {
            access_denied();
        }
    }

    public function index()
    {
        // check access permission
        if (!get_permission('attachments', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['attachmentss'] = $this->attachmentsModel->getAttachmentsList();
        $this->data['title'] = translate('upload_content');
        $this->data['sub_page'] = 'attachments/index';
        $this->data['main_menu'] = 'attachments';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    // attachments information edit here
    public function edit($id = '')
    {
        // check access permission
        if (!get_permission('attachments', 'is_edit')) {
            access_denied();
        }

        $attachmentsDb = $this->db->table('attachments')->where('id', $id)->get()->getRowArray();
        if ($attachmentsDb['uploader_id'] != get_loggedin_user_id()) {
            set_alert('error', 'You do not have permission to edit');
            return redirect()->to(base_url('attachments'));
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['data'] = $this->db->table('attachments')->where('id', $id)->get()->getRowArray();
        $this->data['title'] = translate('upload_content');
        $this->data['sub_page'] = 'attachments/edit';
        $this->data['main_menu'] = 'attachments';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
        return null;
    }

    public function save()
    {
        if ($_POST !== []) {
            if (isset($_POST['attachment_id'])) {
                if (!get_permission('attachments', 'is_edit')) {
                    ajax_access_denied();
                }
            } elseif (!get_permission('attachments', 'is_add')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
            $this->validation->setRules(['type_id' => ["label" => translate('type'), "rules" => 'trim|required']]);
            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
            if (!isset($_POST['all_class_set'])) {
                $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
            }

            if (!isset($_POST['subject_wise']) && !isset($_POST['all_class_set'])) {
                $this->validation->setRules(['subject_id' => ["label" => translate('subject'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['attachment_file' => ["label" => translate('attachment'), "rules" => 'callback_handle_upload']]);
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                $response = $this->attachmentsModel->save($post);
                if (is_array($response)) {
                    set_alert('error', $response['error']);
                } elseif ($response) {
                    set_alert('success', translate('information_has_been_saved_successfully'));
                }

                $url = base_url('attachments');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function delete($id)
    {
        if (get_permission('attachments', 'is_delete')) {
            $encName = $db->table('attachments')->get('attachments')->row()->enc_name;
            $fileName = 'uploads/attachments/' . $encName;
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
                $this->db->table('uploader_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('attachments')->delete();
            if ($db->affectedRows() > 0 && file_exists($fileName)) {
                unlink($fileName);
            }
        }
    }

    /* type form validation rules */
    protected function type_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['type_name' => ["label" => translate('type_name'), "rules" => 'trim|required|callback_unique_type']]);
    }

    // view and save attachment type from database
    public function type()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('attachment_type', 'is_add')) {
                access_denied();
            }

            $this->type_validation();
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('type_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('attachments_type')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(current_url());
            }
        }

        $this->data['typelist'] = $this->appLib->getTable('attachments_type');
        $this->data['title'] = translate('attachment_type');
        $this->data['sub_page'] = 'attachments/type';
        $this->data['main_menu'] = 'attachments';
        echo view('layout/index', $this->data);
    }

    public function type_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('attachment_type', 'is_edit')) {
                ajax_access_denied();
            }

            $this->type_validation();
            if ($this->validation->run() !== false) {
                $arrayData = ['name' => $this->request->getPost('type_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $typeId = $this->request->getPost('type_id');
                $this->db->table('id')->where();
                $this->db->table('attachments_type')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('attachments/type');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // delete attachment type from database
    public function type_delete($id)
    {
        if (get_permission('attachment_type', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('attachments_type')->delete();
        }
    }

    // unique valid attachment type name verification is done here
    public function unique_type($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $typeId = $this->request->getPost('type_id');
        if (!empty($typeId)) {
            $this->db->where_not_in('id', $typeId);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('attachments_type')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_type", translate('already_taken'));
        return false;
    }

    // file downloader
    public function download()
    {
        $encryptName = urldecode((string) $this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encryptName)) {
            $fileName = $db->table('attachments')->get('attachments')->row()->file_name;
            if (!empty($fileName)) {
                return $this->response->download($fileName, file_get_contents('uploads/attachments/' . $encryptName));
            }
        }

        return null;
    }

    public function playVideo()
    {
        // check access permission
        if (!get_permission('attachments', 'is_view')) {
            access_denied();
        }

        $id = $this->request->getPost('id');
        $file = get_type_name_by_id('attachments', $id, 'enc_name');
        echo '<video width="560" controls id="attachment_video">';
        echo '<source src="' . base_url('uploads/attachments/' . $file) . '" type="video/mp4">';
        echo 'Your browser does not support HTML video.';
        echo '</video>';
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

        if (isset($_POST['attachment_id'])) {
            return true;
        }

        $this->validation->setRule('handle_upload', "The Attachment field is required.");
        return false;
    }
}
