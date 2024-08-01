<?php

namespace App\Models;


use CodeIgniter\Model;
use App\Helpers;

class MYModel extends Model
{
    protected $DBGroup = 'default';
    // Define the database group to use, if needed
    protected $table;
    // Optionally specify the table name if this model is tied to a specific table

    public function __construct()
    {
        helper('general'); // Load the general helper

        parent::__construct();
        // Optionally initialize the database connection manually
        $this->db = \Config\Database::connect();
    }

    public function hash($password)
    {
        return hash("sha512", $password . config('App')->encryptionKey);
    }

    public function uploadImage($role, $fields = "user_photo")
    {
        $return_photo = 'default.png';
        $old_user_photo = $this->request->getPost('old_user_photo');
        if ($this->request->getFile($fields) && !$this->request->getFile($fields)->hasMoved()) {
            $file = $this->request->getFile($fields);
            $newName = $file->getRandomName();
            $uploadPath = WRITEPATH . 'uploads/images/' . $role;
            if ($file->move($uploadPath, $newName)) {
                // Unlink the previous photo if exists
                if (!empty($old_user_photo)) {
                    $oldFilePath = $uploadPath . '/' . $old_user_photo;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $return_photo = $newName;
            }
        } else if (!empty($old_user_photo)) {
            $return_photo = $old_user_photo;
        }
        return $return_photo;
    }

    public function get($table, $where_array = null, $single = false, $branch = false, $columns = '*')
    {
        $builder = $this->db->table($table);
        $builder->select($columns);
        if (is_array($where_array)) {
            $builder->where($where_array);
        }
        if ($branch && !is_superadmin_loggedin()) {
            $builder->where("branch_id", get_loggedin_branch_id());
        }
        if ($single) {
            $result = $builder->get()->getRowArray();
        } else {
            $builder->orderBy('id', 'ASC');
            $result = $builder->get()->getResultArray();
        }
        if (empty($result) && $single) {
            $fields = $this->db->getFieldNames($table);
            return array_fill_keys($fields, "");
        }
        return $result;
    }

    public function getSingle($table, $id = null, $single = false)
    {
        $builder = $this->db->table($table);
        $builder->where('id', $id);
        if ($single) {
            return $builder->get()->getRow();
        } else {
            return $builder->get()->getResult();
        }
    }

    public function fileupload($media_name, $upload_path = "", $old_file = '', $enc = true)
    {
        $file = $this->request->getFile($media_name);
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $config = ['upload_path' => $upload_path, 'allowed_types' => '*', 'encrypt_name' => $enc];
            if ($file->move($upload_path, $enc ? $file->getRandomName() : $file->getName())) {
                if (!empty($old_file)) {
                    $oldFilePath = $upload_path . '/' . $old_file;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                return $file->getName();
            }
        } else {
            return !empty($old_file) ? $old_file : "";
        }
    }
}
