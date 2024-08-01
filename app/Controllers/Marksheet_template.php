<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\MarksheetTemplateModel;
/**
 * @package : Ramom school management system
 * @version : 6.6
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Marksheet_template.php
 * @copyright : Reserved RamomCoder Team
 */
class Marksheet_template extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\MarksheetTemplateModel
     */
    public $marksheetTemplate;

    public $validation;

    public $marksheet_templateModel;

    public $input;

    public $load;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->marksheetTemplate = new \App\Models\MarksheetTemplateModel();
    }

    /* form validation rules */
    protected function _validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['marksheet_template_name' => ["label" => translate('template') . " " . translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['page_layout' => ["label" => translate('page_layout'), "rules" => 'trim|required']]);
        $this->validation->setRules(['top_space' => ["label" => "Top Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['bottom_space' => ["label" => "Bottom Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['right_space' => ["label" => "Right Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['left_space' => ["label" => "Left Space", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['photo_size' => ["label" => "Photo Size", "rules" => 'trim|numeric']]);
        $this->validation->setRules(['header_content' => ["label" => translate('header') . " " . translate('content'), "rules" => 'trim|required']]);
        $this->validation->setRules(['footer_content' => ["label" => translate('footer') . " " . translate('content'), "rules" => 'trim|required']]);
        $this->validation->setRules(['background_file' => ["label" => translate('background_file'), "rules" => 'trim|callback_photoHandleUpload[background_file]']]);
        $this->validation->setRules(['left_signature_file' => ["label" => translate('left') . " " . translate('signature'), "rules" => 'trim|callback_photoHandleUpload[left_signature_file]']]);
        $this->validation->setRules(['middle_signature_file' => ["label" => translate('middle') . " " . translate('signature'), "rules" => 'trim|callback_photoHandleUpload[middle_signature_file]']]);
        $this->validation->setRules(['right_signature_file' => ["label" => translate('right') . " " . translate('signature'), "rules" => 'trim|callback_photoHandleUpload[right_signature_file]']]);
        $this->validation->setRules(['logo_file' => ["label" => translate('logo') . " " . translate('image'), "rules" => 'trim|callback_photoHandleUpload[logo_file]']]);
    }

    public function index()
    {
        if (!get_permission('marksheet_template', 'is_view')) {
            access_denied();
        }

        if ($_POST !== [] && get_permission('marksheet_template', 'is_add')) {
            $this->_validation();
            if ($this->validation->run() !== false) {
                // SAVE INFORMATION IN THE DATABASE FILE
                $this->marksheet_templateModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['certificatelist'] = $this->marksheet_templateModel->getList();
        $this->data['title'] = translate('marksheet') . " " . translate('template');
        $this->data['sub_page'] = 'marksheet_template/index';
        $this->data['main_menu'] = 'marksheet_template';
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('marksheet_template', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->_validation();
            if ($this->validation->run() !== false) {
                // save all information in the database file
                $this->marksheet_templateModel->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('marksheet_template/index');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['certificate'] = $this->appLib->getTable('marksheet_template', ['t.id' => $id], true);
        if (empty($this->data['certificate'])) {
            return redirect()->to(base_url('marksheet_template/index'));
        }

        $this->data['title'] = translate('marksheet') . " " . translate('template');
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css'], 'js' => ['vendor/summernote/summernote.js', 'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js']];
        $this->data['sub_page'] = 'marksheet_template/edit';
        $this->data['main_menu'] = 'marksheet_template';
        echo view('layout/index', $this->data);
        return null;
    }

    public function delete($id = '')
    {
        if (get_permission('marksheet_template', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $getRow = $builder->get('marksheet_template')->row_array();
            if (!empty($getRow)) {
                $path = 'uploads/marksheet/';
                if (file_exists($path . $getRow['background'])) {
                    unlink($path . $getRow['background']);
                }

                if (file_exists($path . $getRow['logo'])) {
                    unlink($path . $getRow['logo']);
                }

                if (file_exists($path . $getRow['left_signature'])) {
                    unlink($path . $getRow['left_signature']);
                }

                if (file_exists($path . $getRow['middle_signature'])) {
                    unlink($path . $getRow['middle_signature']);
                }

                if (file_exists($path . $getRow['right_signature'])) {
                    unlink($path . $getRow['right_signature']);
                }

                $this->db->table('id')->where();
                $this->db->table('marksheet_template')->delete();
            }
        }
    }

    public function getCertificate()
    {
        if (get_permission('marksheet_template', 'is_view')) {
            $templateID = $this->request->getPost('id');
            $this->data['marksheet_template'] = $this->marksheet_templateModel->get('marksheet_template', ['id' => $templateID], true);
            echo view('marksheet_template/viewTemplete', $this->data);
        }
    }
}
