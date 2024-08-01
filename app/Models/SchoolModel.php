<?php

namespace App\Models;

use CodeIgniter\Model;
class SchoolModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function getBranchID()
    {
        if (is_superadmin_loggedin()) {
            return $this->request->getGet('branch_id', true);
        } else {
            return get_loggedin_branch_id();
        }
    }
    public function branchUpdate($data)
    {
        $calWithFine = isset($data['cal_with_fine']) ? 1 : 0;
        $arrayBranch = array('name' => $data['branch_name'], 'school_name' => $data['school_name'], 'email' => $data['email'], 'mobileno' => $data['mobileno'], 'currency' => $data['currency'], 'symbol' => $data['currency_symbol'], 'city' => $data['city'], 'state' => $data['state'], 'address' => $data['address'], 'teacher_restricted' => isset($data['teacher_restricted']) ? 1 : 0, 'stu_generate' => isset($data['generate_student']) ? 1 : 0, 'stu_username_prefix' => $data['stu_username_prefix'], 'stu_default_password' => $data['stu_default_password'], 'grd_generate' => isset($data['generate_guardian']) ? 1 : 0, 'grd_username_prefix' => $data['grd_username_prefix'], 'grd_default_password' => $data['grd_default_password'], 'due_days' => $data['due_days'], 'translation' => $data['translation'], 'timezone' => $data['timezone'], 'weekends' => isset($data['weekends']) ? implode(',', $data['weekends']) : "", 'reg_prefix_enable' => isset($data['reg_prefix_enable']) ? 1 : 0, 'reg_start_from' => $this->request->getPost('reg_start_from'), 'institution_code' => $this->request->getPost('institution_code'), 'reg_prefix_digit' => $this->request->getPost('reg_prefix_digit'), 'due_with_fine' => $calWithFine, 'offline_payments' => $data['offline_payments'], 'unique_roll' => $data['unique_roll'], 'currency_formats' => $data['currency_formats'], 'symbol_position' => $data['symbol_position'], 'show_own_question' => $data['show_own_question']);
        $builder->where('id', $data['brance_id']);
        $builder->update('branch', $arrayBranch);
        if (!empty($data['translation'])) {
            if (!is_superadmin_loggedin()) {
                $isRTL = $this->appLib->getRTLStatus($data['translation']);
                $this->session->set_userdata(['set_lang' => $data['translation']]);
                $this->session->set_userdata(['is_rtl' => $isRTL]);
            }
        }
    }
    function getSmsConfig()
    {
        if (is_superadmin_loggedin()) {
            $branch_id = $this->request->getGet('branch_id');
        } else {
            $branch_id = get_loggedin_branch_id();
        }
        $api = array();
        $result = $builder->get('sms_api')->getResult();
        foreach ($result as $key => $value) {
            $api[$value->name] = $db->table('sms_credential')->get('sms_credential')->row_array();
        }
        return $api;
    }
}



