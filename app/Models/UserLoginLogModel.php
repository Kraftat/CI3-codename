<?php

namespace App\Models;

use CodeIgniter\Model;
class UserLoginLogModel extends MYModel
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function getLogListDT($postData, $role)
    {
        if (empty($role) || $role == 'staff') {
            $role_con = "`login_log`.`role` != 7 AND `login_log`.`role` != 6";
            $search = "`staff`.`name`";
        } elseif ($role == 'parent') {
            $role_con = "`login_log`.`role` = 6";
            $search = "`parent`.`name`";
        } elseif ($role == 'student') {
            $role_con = "`login_log`.`role` = 7";
            $search = "`student`.`first_name`";
        }
        $response = array();
        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length'];
        // Rows display per page
        $searchValue = $postData['search']['value'];
        // Search value
        // order
        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column'];
        // Column index
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'desc' : $postData['order'][0]['dir'];
        // asc or desc
        $column_order = array('`login_log`.`id`');
        $search_arr = array();
        $searchQuery = "";
        if ($searchValue != '') {
            $search_arr[] = " ({$search} like '%" . $searchValue . "%' OR `login_log`.`browser` like '%" . $searchValue . "%' OR `login_log`.`timestamp` like '%" . $searchValue . "%') ";
        }
        if (!is_superadmin_loggedin()) {
            $branch_id = $db->escape(get_loggedin_branch_id());
            $search_arr[] = " `login_log`.`branch_id` = {$branch_id} ";
        } else {
            $column_order[] = '`login_log`.`branch_id`';
        }
        // order
        $column_order[] = '`login_log`.`user_id`';
        $column_order[] = '`login_log`.`role`';
        $column_order[] = '`login_log`.`ip`';
        $column_order[] = '`login_log`.`browser`';
        $column_order[] = '`login_log`.`timestamp`';
        $column_order[] = '`login_log`.`platform`';
        if (count($search_arr) > 0) {
            $searchQuery = implode("AND", $search_arr);
        }
        // Total number of records without filtering
        if (is_superadmin_loggedin()) {
            $sql = "SELECT `login_log`.`id` FROM `login_log` WHERE {$role_con}";
        } else {
            $branchID = $db->escape(get_loggedin_branch_id());
            $sql = "SELECT `login_log`.`id` FROM `login_log` WHERE `login_log`.`branch_id` = {$branchID} AND {$role_con}";
        }
        $records = $db->query($sql)->result();
        $totalRecords = count($records);
        // Total number of record with filtering
        if (empty($role) || $role == 'staff') {
            $sql = "SELECT `login_log`.`id`, `staff`.`name` FROM `login_log` INNER JOIN `staff` ON `staff`.`id` = `login_log`.`user_id` WHERE {$role_con}";
        } elseif ($role == 'parent') {
            $sql = "SELECT `login_log`.`id`, `parent`.`name` FROM `login_log` INNER JOIN `parent` ON `parent`.`id` = `login_log`.`user_id` WHERE {$role_con}";
        } elseif ($role == 'student') {
            $sql = "SELECT `login_log`.`id`, CONCAT_WS(' ',`student`.`first_name`, `student`.`last_name`) as `name` FROM `login_log` INNER JOIN `student` ON `student`.`id` = `login_log`.`user_id` WHERE {$role_con}";
        }
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $db->query($sql)->result();
        $totalRecordwithFilter = count($records);
        // Fetch records
        if (empty($role) || $role == 'staff') {
            $sql = "SELECT `login_log`.*, `staff`.`name`, IFNULL(`branch`.`name`, '-') as `branch_name` FROM `login_log` LEFT JOIN `branch` ON `branch`.`id` = `login_log`.`branch_id` INNER JOIN `staff` ON `staff`.`id` = `login_log`.`user_id` WHERE {$role_con}";
        } elseif ($role == 'parent') {
            $sql = "SELECT `login_log`.*, `parent`.`name`, IFNULL(`branch`.`name`, '-') as `branch_name` FROM `login_log` LEFT JOIN `branch` ON `branch`.`id` = `login_log`.`branch_id` INNER JOIN `parent` ON `parent`.`id` = `login_log`.`user_id` WHERE {$role_con}";
        } elseif ($role == 'student') {
            $sql = "SELECT `login_log`.*, CONCAT_WS(' ',`student`.`first_name`, `student`.`last_name`) as `name`, IFNULL(`branch`.`name`, '-') as `branch_name` FROM `login_log` LEFT JOIN `branch` ON `branch`.`id` = `login_log`.`branch_id` INNER JOIN `student` ON `student`.`id` = `login_log`.`user_id` WHERE {$role_con}";
        }
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY " . $column_order[$columnIndex] . " {$columnSortOrder} LIMIT {$start}, {$rowperpage}";
        $records = $db->query($sql)->result();
        $data = array();
        $count = $start + 1;
        foreach ($records as $record) {
            $row = array();
            $row[] = $count++;
            if (is_superadmin_loggedin()) {
                $row[] = $record->branch_name;
            }
            $row[] = $record->name;
            $row[] = get_type_name_by_id('roles', $record->role);
            $row[] = $record->ip;
            $row[] = $record->browser;
            $row[] = $record->timestamp;
            $row[] = $record->platform;
            $data[] = $row;
        }
        // Response
        $response = array("draw" => intval($draw), "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecordwithFilter, "data" => $data);
        return json_encode($response);
    }
}



