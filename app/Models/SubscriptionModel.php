<?php

namespace App\Models;

use CodeIgniter\Model;
class SubscriptionModel extends MYModel
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    // get plan details
    public function getPlanDetails($id = '', $trial = false)
    {
        $builder->select('*');
        $builder->where('id', $id);
        $builder->where('status', 1);
        if ($trial == true) {
            $builder->where('free_trial', 0);
        }
        $get = $builder->get('saas_package')->row_array();
        return $get;
    }
    public function savePaymentData($data)
    {
        // insert in DB
        $insertArray = array('subscriptions_id' => $data['current_subscriptions_id'], 'package_id' => $data['package_id'], 'payment_id' => $data['payment_id'], 'amount' => $data['amount'], 'discount' => $data['discount'], 'payment_method' => $data['payment_method'], 'renew' => 1, 'purchase_date' => date("Y-m-d"), 'expire_date' => $data['expire_date'], 'currency' => $currency);
        $builder->insert('saas_subscriptions_transactions', $insertArray);
        // update subscriptions in DB
        $updateArray = array('package_id' => $data['package_id'], 'expire_date' => $data['expire_date'], 'upgrade_lasttime' => date("Y-m-d"));
        $this->db->table('school_id', get_loggedin_branch_id())->where();
        $builder->update('saas_subscriptions', $updateArray);
        // reorganize permissions
        if ($data['package_id'] != $data['current_package_id']) {
            $schooolID = get_loggedin_branch_id();
            $saasPackage = $this->getPlanDetails($data['package_id']);
            //manage modules permission
            $permission = json_decode($saasPackage['permission'], true);
            $modules_manage_insert = array();
            $modules_manage_update = array();
            $getPermissions = $db->table('permission_modules')->get('permission_modules')->getResult();
            foreach ($getPermissions as $key => $value) {
                $get_existPermissions = $db->table('modules_manage')->get('modules_manage');
                if (in_array($value->id, $permission)) {
                    if ($get_existPermissions->num_rows() > 0) {
                        $modules_manage_update[] = ['id' => $get_existPermissions->row()->id, 'modules_id' => $value->id, 'isEnabled' => 1, 'branch_id' => $schooolID];
                    } else {
                        $modules_manage_insert[] = ['modules_id' => $value->id, 'isEnabled' => 1, 'branch_id' => $schooolID];
                    }
                } else if ($get_existPermissions->num_rows() > 0) {
                    $modules_manage_update[] = ['id' => $get_existPermissions->row()->id, 'modules_id' => $value->id, 'isEnabled' => 0, 'branch_id' => $schooolID];
                } else {
                    $modules_manage_insert[] = ['modules_id' => $value->id, 'isEnabled' => 0, 'branch_id' => $schooolID];
                }
            }
            if (!empty($modules_manage_update)) {
                $builder->update_batch('modules_manage', $modules_manage_update, 'id');
            }
            if (!empty($modules_manage_insert)) {
                $builder->insert_batch('modules_manage', $modules_manage_insert);
            }
        }
    }
    // get subscriptions details
    public function getSubscriptions()
    {
        $builder->select('*');
        $this->db->table('school_id', get_loggedin_branch_id())->where();
        $get = $builder->get('saas_subscriptions')->row_array();
        return $get;
    }
    public function getCurrency()
    {
        $builder->select('currency,currency_symbol,currency_formats,symbol_position');
        $builder->where('id', 1);
        $get = $builder->get('global_settings')->row();
        return $get;
    }
    public function getAdminDetails()
    {
        $sql = "SELECT `name`,`email`,`photo`,`mobileno` FROM `staff` WHERE `id` = " . $db->escape(get_loggedin_user_id());
        $getUser = $db->query($sql)->row_array();
        return $getUser;
    }
}



