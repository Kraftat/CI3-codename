<?php

namespace App\Services;

use Config\Database;

class DomainService
{
    public function checkDomain($domain)
    {
        $db = Database::connect();
        if ($db->tableExists('custom_domain')) {
            $builder = $db->table('custom_domain');
            $builder->select('count(id) as cid');
            $builder->where('status', 1);
            $builder->where('url', $domain);
            $query = $builder->get();
            $result = $query->getRow();

            return $result && $result->cid > 0;
        }
        return false;
    }
}
