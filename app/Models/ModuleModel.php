<?php

namespace App\Models;

use CodeIgniter\Model;
class ModuleModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getStatusArr($branchID)
    {
        $builder->select('permission_modules.id,permission_modules.prefix,if(oaf.isEnabled is null, 1, oaf.isEnabled) as status');
        $builder->from('permission_modules');
        $builder->join('modules_manage as oaf', 'oaf.modules_id = permission_modules.id and oaf.branch_id = ' . $branchID, 'left');
        $builder->where('permission_modules.in_module', 1);
        $builder->group_by('permission_modules.id');
        $builder->order_by('permission_modules.prefix', 'asc');
        $result = $builder->get()->getResult();
        return $result;
    }
}



