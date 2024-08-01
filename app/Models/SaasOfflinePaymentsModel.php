<?php

namespace App\Models;

use CodeIgniter\Model;
class SaasOfflinePaymentsModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function typeSave($data = array())
    {
        $arrayData = array('name' => $data['type_name'], 'note' => $data['note']);
        if (!isset($data['type_id'])) {
            $builder->insert('saas_offline_payment_types', $arrayData);
        } else {
            $builder->where('id', $data['type_id']);
            $builder->update('saas_offline_payment_types', $arrayData);
        }
    }
    public function getOfflinePaymentsList($where = array(), $single = false)
    {
        $builder->select('op.*,sr.reference_no,sr.school_name,address,sr.admin_name,sr.contact_number,sr.address');
        $builder->from('saas_offline_payments as op');
        $builder->join('saas_school_register as sr', 'sr.id = op.school_register_id', 'inner');
        if (!empty($where)) {
            $builder->where($where);
        }
        if ($single == true) {
            $result = $builder->get()->row_array();
        } else {
            $builder->order_by('op.id', 'ASC');
            $result = $builder->get()->getResult();
        }
        return $result;
    }
    public function update($id = '', $status = '')
    {
        $status = $status - 1;
        $arrayFees = array('status' => $status, 'payment_status' => $status);
        // update in DB
        $builder->where('id', $id);
        $builder->update('saas_school_register', $arrayFees);
    }
}



