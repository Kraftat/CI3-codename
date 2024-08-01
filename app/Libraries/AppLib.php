<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\Services;

class AppLib
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = Services::session();

        if (!$this->db) {
            log_message('error', 'Database connection failed.');
            throw new \RuntimeException('Database connection failed.');
        }

        if (!$this->session) {
            log_message('error', 'Session service could not be loaded.');
            throw new \RuntimeException('Session service could not be loaded.');
        }

        log_message('info', 'AppLib initialized successfully.');
    }

    public function getCredentialId($userId, $staff = 'staff')
    {
        $builder = $this->db->table('login_credential');
        $builder->select('id');

        if ($staff == 'staff') {
            $builder->whereNotIn('role', [6, 7]);
        } elseif ($staff == 'parent') {
            $builder->where('role', 6);
        } elseif ($staff == 'student') {
            $builder->where('role', 7);
        }

        $builder->where('user_id', $userId);
        $result = $builder->get()->getRowArray();
        return $result['id'] ?? null;
    }

    public function isExistingAddon($prefix = ''): bool
    {
        if ($prefix != "") {
            $builder = $this->db->table('addon');
            $builder->select('id')->where('prefix', $prefix);
            $row = $builder->get()->getRow();
            return !empty($row);
        }
        return false;
    }

    public function studentLastRegID($branchId = '')
    {
        $builder = $this->db->table('student');
        $builder->select('register_no');
        $builder->join('enroll', 'enroll.student_id = student.id', 'inner');
        $builder->where('branch_id', $branchId);
        $builder->orderBy('student.id', 'desc');
        $builder->limit(1);
        return $builder->get()->getRow();
    }

    public function getBillNo($table): string
    {
        $builder = $this->db->table($table);
        if (!is_superadmin_loggedin()) {
            $builder->where('branch_id', get_loggedin_branch_id());
        }

        $result = $builder->selectMax('bill_no', 'id')->get()->getRowArray();
        $id = $result['id'];
        $bill = empty($id) ? 1 : $id + 1;
        return str_pad($bill, 4, '0', STR_PAD_LEFT);
    }

    public function getTable(string $table, $id = null, $single = false)
    {
        $builder = $this->db->table($table);

        if ($id !== null) {
            $builder->where('id', $id);
        }

        if ($single) {
            return $builder->get()->getRowArray();
        } else {
            $builder->orderBy('id', 'ASC');
            return $builder->get()->getResultArray();
        }
    }

    public function checkBranchRestrictions($table, $id = ''): void
    {
        if (empty($id)) {
            throw new \RuntimeException('Access denied');
        }

        if (!is_superadmin_loggedin()) {
            $builder = $this->db->table($table);
            $builder->select('id, branch_id')->where('id', $id)->limit(1);
            $query = $builder->get();

            if ($query->getNumRows() !== 0) {
                $branchId = $query->getRow()->branch_id;
                if ($branchId != $this->session->get('loggedin_branch')) {
                    throw new \RuntimeException('Access denied');
                }
            }
        }
    }

    public function passHashed($password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isRTLenabled(): bool
    {
        $rtl = $this->session->get('is_rtl');
        return !empty($rtl) && $rtl === true;
    }

    public function getRTLStatus($lang): bool
    {
        $builder = $this->db->table('language_list');
        $row = $builder->select('rtl')->where('lang_field', $lang)->get()->getRow();
        return $row->rtl != 0;
    }

    public function verifyPassword($password, $encryptPassword): bool
    {
        return password_verify($password, $encryptPassword);
    }

    public function getStaffList($branchId = '', $role = '')
    {
        if (empty($branchId)) {
            return ['' => translate('select_branch_first')];
        } else {
            $builder = $this->db->table('staff as s');
            $builder->select('s.id, s.name, s.staff_id');
            $builder->join('login_credential as l', 'l.user_id = s.id and l.role != 6 and l.role != 7', 'inner');
            if (!empty($branchId)) {
                $builder->where('s.branch_id', $branchId);
            }

            if (!empty($role)) {
                $builder->whereIn('l.role', [$role]);
            }

            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row->id] = $row->name . ' (' . $row->staff_id . ')';
            }
            return $array;
        }
    }

    public function getClass($branchId = '')
    {
        if (empty($branchId)) {
            return ['' => translate('select_branch_first')];
        } else {
            $getClassTeacher = $this->getClassTeacher();
            if (is_array($getClassTeacher)) {
                $builder = $this->db->table('timetable_class');
                $builder->select('class.id, class.name');
                $builder->join('class', 'class.id = timetable_class.class_id', 'left');
                $builder->where('timetable_class.teacher_id', get_loggedin_user_id());
                $builder->where('timetable_class.session_id', get_session_id());
                $builder->groupBy('timetable_class.class_id');
                $result = $builder->get()->getResultArray();
                if ($getClassTeacher !== []) {
                    $result = array_merge($result, $getClassTeacher);
                }
            } else {
                $builder = $this->db->table('class');
                $builder->where('branch_id', $branchId);
                $result = $builder->get()->getResultArray();
            }

            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row['id']] = $row['name'];
            }
            return $array;
        }
    }

    public function getStudentCategory($branchId = '')
    {
        if (empty($branchId)) {
            return ['' => translate('select_branch_first')];
        } else {
            $builder = $this->db->table('student_category');
            $builder->where('branch_id', $branchId);
            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
            return $array;
        }
    }

    public function getSections($classId = '', $all = false, $multi = false)
    {
        if (empty($classId)) {
            return ['' => translate('select_class_first')];
        } else {
            $getClassTeacher = $this->getClassTeacher($classId);
            if (is_array($getClassTeacher)) {
                $result = $getClassTeacher;
                if (count($result) == 0) {
                    $builder = $this->db->table('timetable_class');
                    $builder->select('timetable_class.section_id, section.name');
                    $builder->join('section', 'section.id = timetable_class.section_id', 'left');
                    $builder->where([
                        'timetable_class.teacher_id' => get_loggedin_user_id(),
                        'timetable_class.session_id' => get_session_id(),
                        'timetable_class.class_id' => $classId,
                    ]);
                    $builder->groupBy('timetable_class.section_id');
                    $result = $builder->get()->getResultArray();
                }
            } else {
                $builder = $this->db->table('sections_allocation');
                $builder->where('class_id', $classId);
                $result = $builder->get()->getResultArray();
            }

            if ($multi == false) {
                $array = ['' => translate('select')];
            }

            if ($all == true && loggedin_role_id() != 3) {
                $array['all'] = translate('all_sections');
            }

            foreach ($result as $row) {
                $array[$row['section_id']] = get_type_name_by_id('section', $row['section_id']);
            }
            return $array;
        }
    }

    public function getDepartment($branchId = '')
    {
        if (empty($branchId)) {
            return ['' => translate('select_branch_first')];
        } else {
            $builder = $this->db->table('staff_department');
            $builder->where('branch_id', $branchId);
            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
            return $array;
        }
    }

    public function getDesignation($branchId = '')
    {
        if ($branchId == '') {
            return ['' => translate('select_branch_first')];
        } else {
            $builder = $this->db->table('staff_designation');
            $builder->where('branch_id', $branchId);
            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
            return $array;
        }
    }

    public function getVehicleByRoute($routeId = '')
    {
        if ($routeId == '') {
            return ['' => translate('first_select_the_route')];
        } else {
            $builder = $this->db->table('transport_assign');
            $builder->where('route_id', $routeId);
            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row->vehicle_id] = get_type_name_by_id('transport_vehicle', $row->vehicle_id, 'vehicle_no');
            }
            return $array;
        }
    }

    public function getRoomByHostel($hostelId = '')
    {
        if ($hostelId == '') {
            return ['' => translate('first_select_the_hostel')];
        } else {
            $builder = $this->db->table('hostel_room');
            $builder->where('hostel_id', $hostelId);
            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            foreach ($result as $row) {
                $array[$row->id] = $row->name . ' (' . get_type_name_by_id('hostel_category', $row->category_id) . ')';
            }
            return $array;
        }
    }

    public function getSelectByBranch($table, $branchId = '', $all = false, $where = '')
    {
        if (empty($branchId)) {
            return ['' => translate('select_branch_first')];
        } else {
            $builder = $this->db->table($table);
            if (is_array($where)) {
                $builder->where($where);
            }

            $builder->where('branch_id', $branchId);
            $result = $builder->get()->getResult();
            $array = ['' => translate('select')];
            if ($all == true) {
                $array['all'] = translate('all_select');
            }

            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
            return $array;
        }
    }

    public function getSelectList($table, $all = ''): array
    {
        $arrayData = ['' => translate('select')];
        if ($all == 'all') {
            $arrayData['all'] = translate('all_select');
        }

        $builder = $this->db->table($table);
        $result = $builder->get()->getResult();
        foreach ($result as $row) {
            $arrayData[$row->id] = $row->name;
        }

        return $arrayData;
    }

    public function getRoles($arraId = [1, 6, 7]): array
    {
        $builder = $this->db->table('roles');
        // Exclude specific role IDs if not set to 'all'
        if ($arraId != 'all') {
            $builder->whereNotIn('id', $arraId);
        }
    
        // Fetch the branch ID for the logged-in user (except for superadmins)
        $branchId = get_loggedin_branch_id();
    
        // If the user is not a superadmin, filter roles based on branch or system-wide roles
        if (!is_superadmin_loggedin()) {
            $builder->groupStart();
            $builder->where('branch_id', $branchId);
            $builder->orWhere('branch_id', NULL); // Include global roles not tied to any specific branch
            $builder->orWhere('is_system', 1); // Include system roles applicable to all branches
            $builder->groupEnd();
        }
    
        // Retrieve roles from the database
        $rolelist = $builder->get()->getResult();
        $roleArray = ['' => translate('select')];
        foreach ($rolelist as $role) {
            $roleArray[$role->id] = $role->name;
        }
    
        return $roleArray;
    }

    public function generateCSRF(): string
    {
        $security = Services::security();
        return '<input type="hidden" name="' . $security->getCSRFTokenName() . '" value="' . $security->getCSRFHash() . '" />';
    }

    public function getDocumentCategory(): array
    {
        return [
            '' => translate('select'),
            '1' => "Resume File",
            '2' => "Offer Letter",
            '3' => "Joining Letter",
            '4' => "Experience Certificate",
            '5' => "Resignation Letter",
            '6' => "Other Documents",
        ];
    }

    public function getDocumentCategoryV2(): array
    {
        return [
            '' => translate('select'),
            'Resume File' => "Resume File",
            'Offer Letter' => "Offer Letter",
            'Joining Letter' => "Joining Letter",
            'Experience Certificate' => "Experience Certificate",
            'Resignation Letter' => "Resignation Letter",
            'Other Documents' => "Other Documents",
        ];
    }

    public function getAnimationsList(): array
    {
        return [
            'fadeIn' => "fadeIn",
            'fadeInUp' => "fadeInUp",
            'fadeInDown' => "fadeInDown",
            'fadeInLeft' => "fadeInLeft",
            'fadeInRight' => "fadeInRight",
            'bounceIn' => "bounceIn",
            'rotateInUpLeft' => "rotateInUpLeft",
            'rotateInDownLeft' => "rotateInDownLeft",
            'rotateInUpRight' => "rotateInUpRight",
            'rotateInDownRight' => "rotateInDownRight",
        ];
    }

    public function getMonthsList($m): string
    {
        $months = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July ',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
        return $months[$m];
    }

    public function getMonthsDropdown($startMonth = ''): array
    {
        $array = ['' => translate('select')];
        $startMonth = empty($startMonth) ? 1 : $startMonth;
        for ($i = $startMonth; $i < $startMonth + 12; $i++) {
            $month = date('m', mktime(0, 0, 0, $i, 10));
            $array[$month] = translate(strtolower(date('F', mktime(0, 0, 0, $i, 10))));
        }

        return $array;
    }

    public function getDateFormat(): array
    {
        return [
            "Y-m-d" => "yyyy-mm-dd",
            "Y/m/d" => "yyyy/mm/dd",
            "Y.m.d" => "yyyy.mm.dd",
            "d-M-Y" => "dd-mmm-yyyy",
            "d/M/Y" => "dd/mmm/yyyy",
            "d.M.Y" => "dd.mmm.yyyy",
            "d-m-Y" => "dd-mm-yyyy",
            "d/m/Y" => "dd/mm/yyyy",
            "d.m.Y" => "dd.mm.yyyy",
            "m-d-Y" => "mm-dd-yyyy",
            "m/d/Y" => "mm/dd/yyyy",
            "m.d.Y" => "mm.dd.yyyy",
        ];
    }

    public function getBloodGroup(): array
    {
        return [
            '' => translate('select'),
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'O+' => 'O+',
            'O-' => 'O-',
            'AB+' => 'AB+',
            'AB-' => 'AB-',
        ];
    }

    public function timezoneList()
    {
        static $timezones = null;
        if ($timezones === null) {
            $timezones = [];
            $offsets = [];
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $now->setTimezone(new \DateTimeZone($timezone));
                $offsets[] = $offset = $now->getOffset();
                $timezones[$timezone] = '(' . $this->formatGMTOffset($offset) . ') ' . $this->formatTimezoneName($timezone);
            }

            array_multisort($offsets, $timezones);
        }

        return $timezones;
    }

    public function formatGMTOffset($offset): string
    {
        $hours = intval($offset / 3600);
        $minutes = abs(intval($offset % 3600 / 60));
        return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
    }

    public function formatTimezoneName($name): array|string
    {
        $name = str_replace('/', ', ', $name);
        $name = str_replace('_', ' ', $name);
        return str_replace('St ', 'St. ', $name);
    }

    public function getClassTeacher($classID = '')
    {
        if (loggedin_role_id() == 3) {
            $builder = $this->db->table('branch');
            $getMode = $builder->select('teacher_restricted')
                ->where('id', get_loggedin_branch_id())
                ->get()
                ->getRow()
                ->teacher_restricted;

            if ($getMode == 0) {
                return false;
            } else {
                $builder = $this->db->table('teacher_allocation');
                $builder->select('class.id, class.name, teacher_allocation.section_id, section.name as section_name');
                $builder->join('class', 'class.id = teacher_allocation.class_id', 'left');
                $builder->join('section', 'section.id = teacher_allocation.section_id', 'left');
                $builder->where('teacher_allocation.teacher_id', get_loggedin_user_id());
                $builder->where('teacher_allocation.session_id', get_session_id());

                if (!empty($classID)) {
                    $builder->where('teacher_allocation.class_id', $classID);
                }

                return $builder->get()->getResultArray();
            }
        } else {
            return false;
        }
    }

    public function licenceVerify(): bool
    {
        $file = WRITEPATH . 'config/purchase_key.php';
        @chmod($file, FILE_WRITE_MODE);
        $purchase = file_get_contents($file);
        if ($purchase === '' || $purchase === '0' || $purchase === false) {
            return false;
        }

        $purchase = json_decode($purchase);
        if (!is_array($purchase)) {
            return false;
        } elseif (empty($purchase[0]) || empty($purchase[1])) {
            return false;
        } else {
            return true;
        }
    }

    public function getAttendanceType()
    {
        $role_id = $this->session->get('loggedin_role_id');
        $branchID = $this->session->get('loggedin_branch');
        if ($role_id == 1) {
            return 2;
        }

        $builder = $this->db->table('branch');
        $builder->select('attendance_type')->where('id', $branchID);
        $result = $builder->get()->getRow();
        return $result->attendance_type;
    }

    public function getSchoolConfig($branchID = '', $select = '*')
    {
        $branch_id = empty($branchID) ? $this->session->get('loggedin_branch') : $branchID;
        $builder = $this->db->table('branch');
        $builder->select($select)->where('id', $branch_id);
        return $builder->get()->getRow();
    }
}
