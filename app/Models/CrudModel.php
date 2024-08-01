<?php

namespace App\Models;

use CodeIgniter\Model;
class CrudModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    // check employee access permission
    public function block_user($user_role)
    {
        if (empty($user_role)) {
            return false;
        } else {
            if (is_superadmin_loggedin()) {
                $blockuser = array('admin', 'teacher', 'librarian', 'accountant');
            } elseif (is_admin_loggedin()) {
                $blockuser = array('teacher', 'librarian', 'accountant');
            }
            if (in_array($user_role, $blockuser)) {
                return true;
            } else {
                return false;
            }
        }
    }
}



