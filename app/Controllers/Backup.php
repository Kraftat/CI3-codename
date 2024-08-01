<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Backup.php
 * @copyright : Reserved RamomCoder Team
 */
class Backup extends AdminController
{
    public $appLib;

    protected $db;

    public $load;

    public $dbutil;

    public $input;

    public $validation;

    public $upload;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib');helper(['download']);
    }

    public function index()
    {
        if (!get_permission('backup', 'is_view')) {
            access_denied();
        }

        $this->data['sub_page'] = 'database_backup/index';
        $this->data['main_menu'] = 'settings';
        $this->data['title'] = translate('database_backup');
        $this->data['headerelements'] = [
            'css' => ['vendor/dropify/css/dropify.min.css'],
            'js' => ['vendor/dropify/js/dropify.min.js']
        ];
        echo view('layout/index', $this->data);
    }

    /* create database backup */
    public function create()
    {
        if (!get_permission('backup', 'is_add')) {
            access_denied();
        }

        $this->load->dbutil();
        $options = [
            'format' => 'zip',
            // gzip, zip, txt
            'add_drop' => true,
            // Whether to add DROP TABLE statements to backup file
            'add_insert' => true,
            // Whether to add INSERT data to backup file
            'filename' => 'DB-backup_' . date('Y-m-d_H-i'),
        ];
        $backup = $this->dbutil->backup($options);
        if (!write_file('./uploads/db_backup/DB-backup_' . date('Y-m-d_H-i') . '.zip', $backup)) {
            set_alert('error', translate('database_backup_failed'));
        } else {
            set_alert('success', translate('database_backup_completed'));
        }

        return redirect()->to(base_url('backup'));
    }

    public function download()
    {
        $file = urldecode((string)$this->request->getGet('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $file)) {
            $data = file_get_contents('./uploads/db_backup/' . $file);
            return $this->response->download($file, $data);
        }

        return redirect()->to(base_url('backup'));
    }

    public function delete_file($file)
    {
        if (!get_permission('backup', 'is_delete')) {
            access_denied();
        }

        unlink('./uploads/db_backup/' . $file);
    }

    public function restore_file()
    {
        if (!get_permission('backup_restore', 'is_add')) {
            ajax_access_denied();
        }

        $this->validation->setRules([
            'uploaded_file' => [
                'label' => translate('file_upload'),
                'rules' => 'uploaded[uploaded_file]|mime_in[uploaded_file,application/zip]|max_size[uploaded_file,10240]'
            ]
        ]);

        if ($this->validation->run() == true) {
            helper('filesystem');
            $file = $this->request->getFile('uploaded_file');
            $newName = $file->getRandomName();
            $file->move('./uploads/db_temp/', $newName);

            $backup = "./uploads/db_temp/" . $newName;

            if (!unzip($backup, "./uploads/db_temp/", true, true)) {
                set_alert('error', "Backup Restore Error");
                return redirect()->to(base_url('backup'));
            }

            $this->load->dbforge();
            $backup = str_replace('.zip', '', $backup);
            $fileContent = file_get_contents($backup . ".sql");
            $this->db->query('USE ' . $this->db->database . ';');
            foreach (explode(";\n", $fileContent) as $sql) {
                $sql = trim($sql);
                if ($sql !== '' && $sql !== '0') {
                    $this->db->query($sql);
                }
            }

            set_alert('success', "Backup Restore Successfully");

            unlink($backup . '.sql');
            unlink($backup . '.zip');
            $array = ['status' => 'success'];
        } else {
            $error = $this->validation->getErrors();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
        return null;
    }
}
?>




