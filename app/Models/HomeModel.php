<?php

namespace App\Models;

use CodeIgniter\Model;

class HomeModel extends Model
{
    protected $appLib;

    public function __construct()
    {
        parent::__construct();
        $this->db = db_connect(); // Initialize the database connection
        $this->appLib = \Config\Services::appLib(); // Initialize the appLib service
    }

    public function getDefaultBranch()
    {
        $saasExisting = $this->appLib->isExistingAddon('saas');
        $uri = service('uri'); // Use the service to get URI segment

        if ($saasExisting && $this->db->tableExists("custom_domain")) {
            $getDomain = $this->getCurrentDomain();
            if (!empty($getDomain)) {
                return $getDomain->school_id;
            } else {
                $school = $uri->getSegment(1);
                $builder = $this->db->table('front_cms_setting');
                $row = $builder->select('branch_id')->getWhere(['url_alias' => $school])->getRowArray();
                if (empty($row) || $row['branch_id'] == 0) {
                    return $this->getCMSdefault();
                } else {
                    return $row['branch_id'];
                }
            }
        } else {
            $school = $uri->getSegment(1);
            $builder = $this->db->table('front_cms_setting');
            $row = $builder->select('branch_id')->getWhere(['url_alias' => $school])->getRowArray();
            if (empty($row) || $row['branch_id'] == 0) {
                return $this->getCMSdefault();
            } else {
                return $row['branch_id'];
            }
        }
    }

    public function getCmsHome($item_type, $branch_id, $active = 1, $single = true)
    {
        $builder = $this->db->table('front_cms_home');
        $builder->select('*');
        $builder->where('active', $active);
        $builder->where('branch_id', $branch_id);
        $builder->where('item_type', $item_type);
        $query = $builder->get();
        if ($single == true) {
            $method = "getRowArray";
        } else {
            $builder->orderBy("id", "asc");
            $method = "getResultArray";
        }
        return $query->{$method}();
    }

    public function whatsappChat()
    {
        $branchID = $this->getDefaultBranch();
        $builder = $this->db->table('whatsapp_chat');
        $builder->select("*");
        $builder->where('branch_id', $branchID);
        $builder->limit(1);
        $r = $builder->get()->getRowArray();
        return $r;
    }

    public function whatsappAgent()
    {
        $branchID = $this->getDefaultBranch();
        $builder = $this->db->table('whatsapp_agent');
        $builder->select("*");
        $builder->where("enable", 1);
        $builder->where('branch_id', $branchID);
        $r = $builder->get()->getResult();
        return $r;
    }

    public function get_teacher_list($start = '', $branch_id = '')
    {
        $builder = $this->db->table('staff');
        $builder->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name');
        $builder->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != 7', 'inner');
        $builder->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $builder->join('staff_department', 'staff_department.id = staff.department', 'left');
        $builder->where('login_credential.role', 3);
        $builder->where('login_credential.active', 1);
        $builder->where('staff.branch_id', $branch_id);
        $builder->orderBy('staff.id', 'asc');
        if ($start != '') {
            $builder->limit(4, $start);
        }
        $result = $builder->get()->getResultArray();
        return $result;
    }

    public function get_teacher_departments($branch_id)
    {
        $builder = $this->db->table('staff_department');
        $builder->select('staff_department.id as department_id,staff_department.name as department_name');
        $builder->join('staff', 'staff.department = staff_department.id', 'left');
        $builder->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != 7', 'inner');
        $builder->where('login_credential.role', 3);
        $builder->where('login_credential.active', 1);
        $builder->where('staff_department.branch_id', $branch_id);
        $builder->groupBy('staff_department.id');
        $builder->orderBy('staff.id', 'asc');
        $result = $builder->get()->getResultArray();
        return $result;
    }

    public function branch_list()
    {
        $builder = $this->db->table('branch as b');
        $builder->select('b.school_name,b.id');
        $builder->join('front_cms_setting as f', 'f.branch_id = b.id', 'inner');
        $builder->where('f.cms_active', 1);
        $result = $builder->get()->getResult();
        $arrayData = array();
        foreach ($result as $row) {
            $arrayData[$row->id] = $row->school_name;
        }
        return $arrayData;
    }

    public function menuList($school = '', $branchID = '')
    {
        $mainMenu = array();
        $subMenu = array();
        $mergeMenu = array();
        if (empty($branchID)) {
            $branchID = $this->getDefaultBranch();
        }
        if (empty($school)) {
            $builder = $this->db->table('front_cms_setting');
            $cms_setting = $builder->select('url_alias')->getWhere(['branch_id' => $branchID])->getRow();
            $school = $cms_setting->url_alias;
        }
        $builder = $this->db->table('front_cms_menu');
        $builder->select('front_cms_menu.*,if(mv.name is null, front_cms_menu.title, mv.name) as title,if(mv.parent_id is null, front_cms_menu.parent_id, mv.parent_id) as parent_id,if(mv.ordering is null, front_cms_menu.ordering, mv.ordering) as ordering,mv.invisible');
        $builder->join('front_cms_menu_visible as mv', 'mv.menu_id = front_cms_menu.id and mv.branch_id = ' . $branchID, 'left');
        $builder->where('front_cms_menu.publish', 1);
        $builder->whereIn('front_cms_menu.branch_id', [0, $branchID]);
        $result = $builder->get()->getResultArray();
        //php array sort
        array_multisort(array_column($result, 'ordering'), SORT_ASC, SORT_NUMERIC, $result);
        foreach ($result as $key => $value) {
            if ($value['invisible'] == 0) {
                if ($value['parent_id'] == 0) {
                    $mainMenu[$key] = $value;
                } else {
                    $subMenu[$key] = $value;
                }
            }
        }
        foreach ($mainMenu as $key => $value) {
            $mergeMenu[$key] = $value;
            $mergeMenu[$key]['url'] = $this->genURL($value, $school);
            foreach ($subMenu as $key2 => $value2) {
                if ($value['id'] == $value2['parent_id']) {
                    $mergeMenu[$key]['submenu'][$key2] = array('title' => $value2['title'], 'open_new_tab' => $value2['open_new_tab'], 'url' => $this->genURL($value2, $school));
                }
            }
        }
        return $mergeMenu;
    }

    public function genURL($array = array(), $school = '')
    {
        $url = "#";
        if (!empty($school)) {
            $school = '/' . $school;
        }
        $saasExisting = $this->appLib->isExistingAddon('saas');
        if ($saasExisting && $this->db->tableExists("custom_domain")) {
            $getDomain = $this->getCurrentDomain();
            if (!empty($getDomain)) {
                $school = "";
            }
        }
        if ($array['system'] && $array['alias'] !== 'pages') {
            $url = base_url($school . '/' . $array['alias']);
        } else if ($array['ext_url']) {
            $url = $array['ext_url_address'];
        } else {
            $url = base_url($school . '/page/' . $array['alias']);
        }
        return $url;
    }

    public function getExamList($branchID = '', $classID = '', $sectionID = '')
    {
        $sessionID = get_session_id();
        $builder = $this->db->table('timetable_exam');
        $builder->select('exam.id,exam.name,exam.term_id');
        $builder->join('exam', 'exam.id = timetable_exam.exam_id', 'left');
        if (!empty($classID)) {
            $builder->where('timetable_exam.class_id', $classID);
        }
        if (!empty($sectionID)) {
            $builder->where('timetable_exam.section_id', $sectionID);
        }
        $builder->where('exam.status', 1);
        $builder->where('exam.publish_result', 1);
        $builder->where('timetable_exam.branch_id', $branchID);
        $builder->where('timetable_exam.session_id', $sessionID);
        $builder->groupBy('timetable_exam.exam_id');
        $result = $builder->get()->getResultArray();
        return $result;
    }

    public function getGalleryCategory($branch_id)
    {
        $builder = $this->db->table('front_cms_gallery_category');
        $builder->select('front_cms_gallery_category.id as category_id,front_cms_gallery_category.name as category_name');
        $builder->join('front_cms_gallery_content', 'front_cms_gallery_content.category_id = front_cms_gallery_category.id', 'inner');
        $builder->where('front_cms_gallery_category.branch_id', $branch_id);
        $builder->groupBy('front_cms_gallery_category.id');
        $builder->where('front_cms_gallery_content.show_web', 1);
        $builder->orderBy('front_cms_gallery_category.id', 'asc');
        $result = $builder->get()->getResultArray();
        return $result;
    }

    public function getGalleryList($branch_id)
    {
        $builder = $this->db->table('front_cms_gallery_content');
        $builder->select('front_cms_gallery_content.*,staff.name as staff_name');
        $builder->join('staff', 'staff.id = front_cms_gallery_content.added_by', 'left');
        $builder->where('front_cms_gallery_content.branch_id', $branch_id);
        $builder->where('front_cms_gallery_content.show_web', 1);
        $builder->orderBy('front_cms_gallery_content.id', 'asc');
        $result = $builder->get()->getResultArray();
        return $result;
    }

    public function getStatisticsCounter($type, $branch_id)
    {
        $result = 0;
        if (in_array($type, ['class', 'section', 'live_class', 'subject', 'exam', 'book', 'branch'])) {
            $builder = $this->db->table($type);
            $builder->select('id');
            if ($type != 'branch') {
                $builder->where("branch_id", $branch_id);
            }
            $result = $builder->countAllResults();
        }
        if (in_array($type, ['employees', 'teacher'])) {
            $builder = $this->db->table('staff');
            $builder->select('count(staff.id) as snumber');
            $builder->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
            $builder->whereNotIn('login_credential.role', [1]);
            if ($type == 'teacher') {
                $builder->where('login_credential.role', 3);
            } else {
                $builder->whereNotIn('login_credential.role', [1, 6, 7]);
            }
            $q = $builder->get()->getRowArray();
            $result = $q['snumber'];
        }
        if ($type == 'student') {
            $builder = $this->db->table('enroll');
            $builder->select('student.id');
            $builder->join('student', 'student.id = enroll.student_id', 'inner');
            $builder->where('enroll.branch_id', $branch_id);
            $result = $builder->countAllResults();
        }
        if ($type == 'parent') {
            $builder = $this->db->table('parent');
            $builder->select('count(parent.id) as snumber');
            $builder->where('parent.branch_id', $branch_id);
            $q = $builder->get()->getRowArray();
            $result = $q['snumber'];
        }
        return $result;
    }

    public function getPaymentConfig($branchID)
    {
        $builder = $this->db->table('payment_config');
        $builder->select('*');
        $builder->where('branch_id', $branchID);
        return $builder->get()->getRowArray();
    }

    public function getCurrentDomain()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $url = rtrim($url, '/');
        $domain = parse_url($url, PHP_URL_HOST);
        $builder = $this->db->table('custom_domain');
        $getDomain = $builder->select('school_id')->getWhere(['status' => 1, 'url' => $domain])->getRow();
        return $getDomain;
    }

    public function getCMSdefault()
    {
        $builder = $this->db->table('global_settings');
        $builder->select('cms_default_branch');
        $builder->where('id', 1);
        $row = $builder->get()->getRowArray();
        return $row['cms_default_branch'];
    }

    public function checkAdmissionReferenceNo($ref_no)
    {
        $builder = $this->db->table('online_admission');
        $builder->select("id");
        $builder->where("reference_no", $ref_no);
        $query = $builder->get();
        $result = $query->getRowArray();
        return !empty($result);
    }

    public function getLatestNews($branchID = '')
    {
        $builder = $this->db->table('front_cms_news_list');
        $builder->limit(10);
        $builder->where('show_web', 1);
        $builder->where('branch_id', $branchID);
        $builder->orderBy("id", "desc");
        $news_list = $builder->get()->getResult();
        return $news_list;
    }

    public function getLatestNewsList($branchID = '', $params = [])
    {
        $builder = $this->db->table('front_cms_news_list');
        $builder->where('branch_id', $branchID);
        $builder->where('show_web', 1);
        if (!empty($params['start']) && !empty($params['limit'])) {
            $builder->limit($params['limit'], $params['start']);
        } elseif (empty($params['start']) && !empty($params['limit'])) {
            $builder->limit($params['limit']);
        }
        $q = $builder->get()->getResultArray();
        return $q;
    }

    //Fahad fix bug (removed 7 day limit)
    public function getLatestEventList($branchID = '', $params = [])
    {
        $today_date = date('Y-m-d'); // Today's date for comparison
        $end_date = date('Y-m-d');
        $builder = $this->db->table('event');
        $builder->where('start_date >=', $today_date); // Fetch events starting today or in the future
        $builder->where('end_date >=', $end_date);
        $builder->where('branch_id', $branchID);
        $builder->where('status', 1);
        $builder->where('show_web', 1);
        if (!empty($params['start']) && !empty($params['limit'])) {
            $builder->limit($params['limit'], $params['start']);
        } elseif (empty($params['start']) && !empty($params['limit'])) {
            $builder->limit($params['limit']);
        }
        $q = $builder->get()->getResultArray();
        return $q;
    }
}
