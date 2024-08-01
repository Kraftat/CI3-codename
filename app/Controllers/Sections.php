<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\ApplicationModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Sections.php
 * @copyright : Reserved RamomCoder Team
 */
class Sections extends AdminController
{
    public $appLib;

    public $load;

    public $validation;

    public $input;

    public $applicationModel;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib');}

    public function index()
    {
        if (!get_permission('section', 'is_view')) {
            access_denied();
        }

        $this->data['sectionlist'] = $this->appLib->getTable('section');
        $this->data['title'] = translate('section_control');
        $this->data['sub_page'] = 'sections/index';
        $this->data['main_menu'] = 'sections';
        echo view('layout/index', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('section', 'is_edit')) {
            access_denied();
        }

        $this->data['section'] = $this->appLib->getTable('section', ['t.id' => $id], true);
        $this->data['title'] = translate('section_control');
        $this->data['sub_page'] = 'sections/edit';
        $this->data['main_menu'] = 'sections';
        echo view('layout/index', $this->data);
    }

    public function save()
    {
        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['name' => ["label" => translate('name'), "rules" => 'trim|required|callback_unique_name']]);
            $this->validation->setRules(['capacity' => ["label" => translate('capacity'), "rules" => 'trim|numeric']]);
            if ($this->validation->run() !== false) {
                $arraySection = ['name' => $this->request->getPost('name'), 'capacity' => $this->request->getPost('capacity'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $sectionID = $this->request->getPost('section_id');
                if (empty($sectionID)) {
                    if (get_permission('section', 'is_add')) {
                        $this->db->table('section')->insert();
                    }

                    set_alert('success', translate('information_has_been_saved_successfully'));
                } else {
                    if (get_permission('section', 'is_edit')) {
                        if (!is_superadmin_loggedin()) {
                            $this->db->table('branch_id')->where();
                        }

                        $this->db->table('id')->where();
                        $this->db->table('section')->update();
                    }

                    set_alert('success', translate('information_has_been_updated_successfully'));
                }

                $url = base_url('sections');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // validate here, if the check sectio name
    public function unique_name($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $sectionID = $this->request->getPost('section_id');
        if (!empty($sectionID)) {
            $this->db->where_not_in('id', $sectionID);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('section')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }
        $this->validation->setRule("unique_name", translate('already_taken'));
        return false;
    }

    public function delete($id = '')
    {
        if (get_permission('section', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('section')->delete();
        }
    }
}
