<?php

namespace App\Models;

use CodeIgniter\Model;
class AdvancesalaryModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    // employee basic salary validation by salary template
    public function getBasicSalary($staff_id = '', $amount = '')
    {
        $q = $builder->getWhere('staff', ['id' => $staff_id])->row_array();
        if (empty($q['salary_template_id']) || $q['salary_template_id'] == 0) {
            return 1;
        } else {
            $basic_salary = $builder->getWhere("salary_template", ['id' => $q['salary_template_id']])->row()->basic_salary;
            if ($amount > $basic_salary) {
                return 2;
            }
        }
        return 3;
    }
    // employee advance salary validation by month
    public function getAdvanceValidMonth($staff_id, $month)
    {
        $get_advance_month = $builder->getWhere("advance_salary", ["staff_id" => $staff_id, "deduct_month" => date("m", strtotime((string) $month)), "year" => date("Y", strtotime((string) $month)), "status" => 2])->num_rows();
        $get_salary_month = $builder->getWhere("payslip", ["staff_id" => $staff_id, "month" => date("m", strtotime((string) $month)), "year" => date("Y", strtotime((string) $month))])->num_rows();
        if ($get_advance_month == 0 && $get_salary_month == 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getAdvanceSalaryList($month = '', $year = '', $branch_id = '', $staff_id = '')
    {
        $builder->select('advance_salary.*,staff.name,staff.staff_id as uniqid,staff.photo,lc.role as role_id,roles.name as role');
        $builder->from('advance_salary');
        $builder->join('staff', 'staff.id = advance_salary.staff_id', 'inner');
        $builder->join('login_credential as lc', 'lc.user_id = staff.id and lc.role != 6 and lc.role != 7', 'left');
        $builder->join('roles', 'roles.id = lc.role', 'left');
        if (!empty($month)) {
            $builder->where('advance_salary.deduct_month', $month);
            $builder->where('advance_salary.year', $year);
        }
        if (!empty($branch_id)) {
            $builder->where('advance_salary.branch_id', $branch_id);
        }
        if (!empty($staff_id)) {
            $builder->where('advance_salary.staff_id', $staff_id);
        }
        return $builder->get()->result_array();
    }
}



