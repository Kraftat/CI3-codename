<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\ModuleModel;
/**
 * @package : Ramom school management system
 * @version : 6.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Modules.php
 * @copyright : Reserved RamomCoder Team
 */
class Modules extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\ModuleModel
     */
    public $module;

    public $load;

    public $applicationModel;

    public $input;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->module = new \App\Models\ModuleModel();
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    public function index()
    {
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['sub_page'] = 'modules/index';
        $this->data['title'] = translate('modules');
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
    }

    public function save()
    {
        if ($_POST !== []) {
            $branchID = $this->applicationModel->get_branch_id();
            $systemFields = $this->request->getPost('system_fields');
            foreach ($systemFields as $key => $value) {
                $isStatus = isset($value['status']) ? 1 : 0;
                $arrayData = ['modules_id' => $key, 'branch_id' => $branchID, 'isEnabled' => $isStatus];
                $existPrivileges = $db->table('modules_manage')->get('modules_manage')->num_rows();
                if ($existPrivileges > 0) {
                    $this->db->table('modules_manage')->update();
                } else {
                    $this->db->table('modules_manage')->insert();
                }
            }

            $message = translate('information_has_been_saved_successfully');
            $array = ['status' => 'success', 'message' => $message];
            echo json_encode($array);
        }
    }
}
