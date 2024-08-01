<?php

namespace App\Models;

use CodeIgniter\Model;
class AjaxModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getPayslip($id = '')
    {
        $builder->select('payout_commission.*,staff.name as staff_name,staff.staff_id,ifnull(staff_designation.name,"N/A") as designation_name,ifnull(staff_department.name,"N/A") as department_name,payment_type.name as pay_via_name');
        $builder->from('payout_commission');
        $builder->join('staff', 'staff.id = payout_commission.staff_id', 'left');
        $builder->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $builder->join('staff_department', 'staff_department.id = staff.department', 'left');
        $builder->join('payment_type', 'payment_type.id = payout_commission.pay_via', 'left');
        $builder->where('payout_commission.id', $id);
        return $builder->get()->row_array();
    }
}



