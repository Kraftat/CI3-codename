<?php

namespace App\Models;

use CodeIgniter\Model;
class CustomDomainModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getCustomDomain()
    {
        $builder->select('cd.*,branch.name as branch_name,front_cms_setting.url_alias');
        $builder->from('custom_domain as cd');
        $builder->join('branch', 'branch.id = cd.school_id', 'inner');
        $builder->join('front_cms_setting', 'front_cms_setting.branch_id = cd.school_id', 'left');
        $builder->order_by('cd.id', 'ASC');
        if (!is_superadmin_loggedin()) {
            $this->db->table('cd.school_id', get_loggedin_branch_id())->where();
        }
        return $builder->get()->getResult();
    }
    public function getCustomDomainDetails($id = '')
    {
        $builder->select('cd.*,branch.name as branch_name,branch.email,branch.mobileno');
        $builder->from('custom_domain as cd');
        $builder->join('branch', 'branch.id = cd.school_id', 'inner');
        $builder->where('cd.id', $id);
        if (!is_superadmin_loggedin()) {
            $this->db->table('cd.school_id', get_loggedin_branch_id())->where();
        }
        return $builder->get()->row();
    }
    public function getDNSinstruction()
    {
        $builder->select('*');
        $builder->from('custom_domain_instruction');
        $builder->where('id', 1);
        return $builder->get()->row();
    }
    public function getDomain_name($url)
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $url;
        $parsed_url = parse_url($url);
        if (isset($parsed_url['host'])) {
            $host = $parsed_url['host'];
            $host_parts = explode('.', $host);
            $domain = @$host_parts[count($host_parts) - 2] . '.' . @$host_parts[count($host_parts) - 1];
            if (substr($domain, 0, 1) != '.') {
                $domain = "." . $domain;
            }
            return $domain;
        } else {
            return "";
        }
    }
}



