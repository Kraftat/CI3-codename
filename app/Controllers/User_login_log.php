<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\UserLoginLogModel;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : User_login_log.php
 * @copyright : Reserved RamomCoder Team
 */
class User_login_log extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\UserLoginLogModel
     */
    public $userLoginLog;

    public $load;

    public $input;

    public $user_login_logModel;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->userLoginLog = new \App\Models\UserLoginLogModel();
    }

    public function index($role = 'staff')
    {
        if (!get_permission('user_login_log', 'is_view')) {
            access_denied();
        }

        $roleArr = ["staff", "student", "parent"];
        if (!in_array($role, $roleArr, true)) {
            $role = 'staff';
        }

        $this->data['role'] = $role;
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['title'] = translate('user_login_log');
        $this->data['sub_page'] = 'user_login_log/index';
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
    }

    public function getLogListDT($role = 'staff')
    {
        if ($_POST !== []) {
            $postData = $this->request->getPost();
            echo $this->user_login_logModel->getLogListDT($postData, $role);
        }
    }

    public function clear()
    {
        if (get_permission('user_login_log', 'is_delete')) {
            if (is_superadmin_loggedin()) {
                $this->db->truncate('login_log');
            } else {
                $this->db->table('branch_id')->where();
                $this->db->table('login_log')->delete();
            }
        }
    }
}
