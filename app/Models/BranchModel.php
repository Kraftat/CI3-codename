<?php

namespace App\Models;

use CodeIgniter\Model;
class BranchModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function save($data)
    {
        $arrayBranch = array('name' => $data['branch_name'], 'school_name' => $data['school_name'], 'email' => $data['email'], 'mobileno' => $data['mobileno'], 'currency' => $data['currency'], 'symbol' => $data['currency_symbol'], 'city' => $data['city'], 'state' => $data['state'], 'address' => $data['address']);
        if (!isset($data['branch_id'])) {
            $builder->insert('branch', $arrayBranch);
            $id = $builder->insert_id();
        } else {
            $id = $data['branch_id'];
            $builder->where('id', $data['branch_id']);
            $builder->update('branch', $arrayBranch);
        }
        $file_upload = false;
        if (isset($_FILES["logo_file"]) && !empty($_FILES['logo_file']['name'])) {
            $fileInfo = pathinfo($_FILES["logo_file"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["logo_file"]["tmp_name"], "uploads/app_image/logo-" . $img_name);
            $file_upload = true;
        }
        if (isset($_FILES["text_logo"]) && !empty($_FILES['text_logo']['name'])) {
            $fileInfo = pathinfo($_FILES["text_logo"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["text_logo"]["tmp_name"], "uploads/app_image/logo-small-" . $img_name);
            $file_upload = true;
        }
        if (isset($_FILES["print_file"]) && !empty($_FILES['print_file']['name'])) {
            $fileInfo = pathinfo($_FILES["print_file"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["print_file"]["tmp_name"], "uploads/app_image/printing-logo-" . $img_name);
            $file_upload = true;
        }
        if (isset($_FILES["report_card"]) && !empty($_FILES['report_card']['name'])) {
            $fileInfo = pathinfo($_FILES["report_card"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["report_card"]["tmp_name"], "uploads/app_image/report-card-logo-" . $img_name);
            $file_upload = true;
        }
        if ($db->affectedRows() > 0 || $file_upload == true) {
            return true;
        } else {
            return false;
        }
    }
}



