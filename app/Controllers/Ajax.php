<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AjaxModel;
use CodeIgniter\Controller;

class Ajax extends Controller
{
    protected $db;
    protected $ajax;
    protected $applicationModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->ajax = new AjaxModel();
        $this->applicationModel = new \App\Models\ApplicationModel(); // Ensure this is correctly instantiated
    }

    // get exam list based on the branch
    public function getExamByBranch()
    {
        $html = "";
        $branchID = $this->applicationModel->get_branch_id();
        $sessionID = get_session_id();
        
        if (!empty($branchID)) {
            $builder = $this->db->table('exam');
            $builder->select('exam.id, exam.name, exam.term_id')
                    ->where('exam.branch_id', $branchID)
                    ->where('exam.session_id', $sessionID);

            $result = $builder->get()->getResultArray();

            if (count($result) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    if ($row['term_id'] != 0) {
                        // Fetch term name based on term_id
                        $termBuilder = $this->db->table('exam_term');
                        $termBuilder->select('name');
                        $termBuilder->where('id', $row['term_id']);
                        $term = $termBuilder->get()->getRow()->name;
                        $name = $row['name'] . ' (' . $term . ')';
                    } else {
                        $name = $row['name'];
                    }

                    $html .= '<option value="' . $row['id'] . '">' . $name . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }



    // get class assign modal
    public function getClassAssignM()
    {
        $classID = $this->request->getPost('class_id');
        $sectionID = $this->request->getPost('section_id');
        $branchID = get_type_name_by_id('class', $classID, 'branch_id');
        $html = "";
        $subjects = $builder->getWhere('subject', ['branch_id' => $branchID])->result_array();
        if (count($subjects) > 0) {
            foreach ($subjects as $row) {
                $queryAssign = $builder->getWhere("subject_assign", ['class_id' => $classID, 'section_id' => $sectionID, 'session_id' => get_session_id(), 'subject_id' => $row['id']]);
                $html .= '<option value="' . $row['id'] . '"' . ($queryAssign->num_rows() != 0 ? 'selected' : '') . '>' . $row['name'] . '</option>';
            }
        }

        $data['branch_id'] = $branchID;
        $data['class_id'] = $classID;
        $data['section_id'] = $sectionID;
        $data['subject'] = $html;
        echo json_encode($data);
    }

    public function getAdvanceSalaryDetails()
    {
        if (get_permission('advance_salary', 'is_add')) {
            $this->data['salary_id'] = $this->request->getPost('id');
            echo view('advance_salary/approvel_modalView', $this->data);
        }
    }

    public function getLeaveCategoryDetails()
    {
        if (get_permission('leave_category', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('leave_category');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function getDataByBranch()
    {
        $html = "";
        $table = $this->request->getPost('table');
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $result = $db->table($table)->get($table)->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getClassByBranch()
    {
        $html = "";
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $classes = $db->table('class')->get('class')->result_array();
            if (count($classes) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($classes as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getStudentByClass($enroll = 0)
    {
        $html = "";
        $classId = $this->request->getPost('class_id');
        $sectionId = $this->request->getPost('section_id');
        $this->applicationModel->get_branch_id();
        $studentId = $_POST['student_id'] ?? 0;
        if (!empty($classId)) {
            $builder->select('e.student_id,e.id,e.roll,CONCAT(s.first_name, " ", s.last_name) as fullname');
            $this->db->from('enroll as e');
            $builder->join('student as s', 's.id = e.student_id', 'inner');
            $builder->join('login_credential as l', 'l.user_id = e.student_id and l.role = 7', 'left');
            $this->db->table('l.active')->where();
            $this->db->table('e.session_id')->where();
            if (!empty($sectionId)) {
                $this->db->table('e.section_id')->where();
            }

            $this->db->table('e.class_id')->where();
            $this->db->table('e.branch_id')->where();
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    if ($enroll == 0) {
                        $sel = $row['student_id'] == $studentId ? 'selected' : '';
                        $html .= '<option value="' . $row['student_id'] . '"' . $sel . '>' . $row['fullname'] . ' ( Roll : ' . $row['roll'] . ')</option>';
                    } else {
                        $sel = $row['id'] == $studentId ? 'selected' : '';
                        $html .= '<option value="' . $row['id'] . '"' . $sel . '>' . $row['fullname'] . ' (' . translate('roll') . " : " . $row['roll'] . ')</option>';
                    }
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_class_first') . '</option>';
        }

        echo $html;
    }

    // get section list based on the class
    public function getSectionByClass()
    {
        $html = "";
        $classID = $this->request->getPost("class_id");
        $mode = $this->request->getPost("all");
        $multi = $this->request->getPost("multi");
        if (!empty($classID)) {
            $getClassTeacher = $this->appLib->getClassTeacher($classID);
            if (is_array($getClassTeacher)) {
                $result = $getClassTeacher;
                if (count($result) == 0) {
                    $builder->select('timetable_class.section_id,section.name as section_name');
                    $this->db->from('timetable_class');
                    $builder->join('section', 'section.id = timetable_class.section_id', 'left');
                    $this->db->table(['timetable_class.teacher_id' => get_loggedin_user_id(), 'timetable_class.session_id' => get_session_id(), 'timetable_class.class_id' => $classID])->where();
                    $this->db->group_by('timetable_class.section_id');
                    $result = $builder->get()->result_array();
                }
            } else {
                $result = $builder->select('sections_allocation.section_id,section.name as section_name')->from('sections_allocation')->join('section', 'section.id = sections_allocation.section_id', 'left')->where('sections_allocation.class_id', $classID)->get()->result_array();
            }

            if (count($result) > 0) {
                if ($multi == false) {
                    $html .= '<option value="">' . translate('select') . '</option>';
                }

                if ($mode == true && !is_array($getClassTeacher)) {
                    $html .= '<option value="all">' . translate('all_sections') . '</option>';
                }

                foreach ($result as $row) {
                    $html .= '<option value="' . $row['section_id'] . '">' . $row['section_name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_selection_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_class_first') . '</option>';
        }

        echo $html;
    }

    public function getStafflistRole()
    {
        $html = "";
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $roleId = $this->request->getPost('role_id');
            $selectedId = $_POST['staff_id'] ?? 0;
            $builder->select('staff.id,staff.name,staff.staff_id,lc.role');
            $this->db->from('staff');
            $builder->join('login_credential as lc', 'lc.user_id = staff.id AND lc.role != 6 AND lc.role != 7', 'inner');
            if (!empty($roleId)) {
                $this->db->table('lc.role')->where();
            }

            $this->db->table('staff.branch_id')->where();
            $this->db->order_by('staff.id', 'asc');
            $result = $builder->get()->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $staff) {
                    $selected = $staff['id'] == $selectedId ? 'selected' : '';
                    $html .= "<option value='" . $staff['id'] . "' " . $selected . ">" . $staff['name'] . " (" . $staff['staff_id'] . ")</option>";
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    // get staff all details
    public function getEmployeeList()
    {
        $html = "";
        $this->request->getPost('role');
        $designation = $this->request->getPost('designation');
        $department = $this->request->getPost('department');
        $selectedId = $_POST['staff_id'] ?? 0;
        $builder->select('staff.*,staff_designation.name as des_name,staff_department.name as dep_name,login_credential.role as role_id, roles.name as role');
        $this->db->from('staff');
        $builder->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
        $builder->join('roles', 'roles.id = login_credential.role', 'left');
        $builder->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $builder->join('staff_department', 'staff_department.id = staff.department', 'left');
        $this->db->table('login_credential.role')->where();
        $this->db->table('login_credential.active')->where();
        if ($designation != '') {
            $this->db->table('staff.designation')->where();
        }

        if ($department != '') {
            $this->db->table('staff.department')->where();
        }

        $result = $builder->get()->result_array();
        if (count($result) > 0) {
            $html .= "<option value=''>" . translate('select') . "</option>";
            foreach ($result as $row) {
                $selected = $row['id'] == $selectedId ? 'selected' : '';
                $html .= "<option value='" . $row['id'] . "' " . $selected . ">" . $row['name'] . " (" . $row['staff_id'] . ")</option>";
            }
        } else {
            $html .= '<option value="">' . translate('no_information_available') . '</option>';
        }

        echo $html;
    }

    // get subject list based on the class
    public function getSubjectByClass()
    {
        $html = "";
        $classID = $this->request->getPost('classID');
        if (!empty($classID)) {
            $builder->select('subject_assign.subject_id,subject.name,subject.subject_code');
            $this->db->from('subject_assign');
            $builder->join('subject', 'subject.id = subject_assign.subject_id', 'left');
            $this->db->table('subject_assign.class_id')->where();
            if (!is_superadmin_loggedin()) {
                $this->db->table('subject_assign.branch_id')->where();
            }

            $subjects = $builder->get()->result_array();
            if (count($subjects) > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($subjects as $row) {
                    $html .= '<option value="' . $row['subject_id'] . '">' . $row['name'] . ' (' . $row['subject_code'] . ')</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_class_first') . '</option>';
        }

        echo $html;
    }

    public function get_salary_template_details()
    {
        if (get_permission('salary_template', 'is_view')) {
            $templateId = $this->request->getPost('id');
            $this->data['allowances'] = $this->ajaxModel->get('salary_template_details', ['type' => 1, 'salary_template_id' => $templateId]);
            $this->data['deductions'] = $this->ajaxModel->get('salary_template_details', ['type' => 2, 'salary_template_id' => $templateId]);
            $this->data['template'] = $this->ajaxModel->get('salary_template', ['id' => $templateId], true);
            echo view('payroll/qview_salary_templete', $this->data);
        }
    }

    public function department_details()
    {
        if (get_permission('department', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('staff_department');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function designation_details()
    {
        if (get_permission('designation', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('staff_designation');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function getLoginAuto()
    {
        if (is_superadmin_loggedin()) {
            $getBranch = $this->getBranchDetails();
            $data = [];
            $data['student'] = $getBranch['stu_generate'] == 1 ? 1 : 0;
            $data['guardian'] = $getBranch['grd_generate'] == 1 ? 1 : 0;
            echo json_encode($data);
        }
    }

    public function getProductCategoryDetails()
    {
        if (get_permission('product_category', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('product_category');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }
}
