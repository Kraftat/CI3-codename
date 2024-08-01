<?php

namespace App\Models;

use CodeIgniter\Model;
class ExamModel extends Model
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function getExamByID($id = null)
    {
        $sql = "SELECT `e`.*, `exam_term`.`name` as `term_name`, `b`.`name` as `branch_name` FROM `exam` as `e` INNER JOIN `branch` as `b` ON `b`.`id` = `e`.`branch_id` LEFT JOIN `exam_term` ON `exam_term`.`id` = `e`.`term_id` WHERE `e`.`id` = {$db->escape($id)}";
        return $db->query($sql)->row();
    }
    public function searchExamStudentsByRank($class_ID = '', $section_ID = '', $session_ID = '', $exam_ID = '', $branch_id = '')
    {
        $builder->select('e.*,CONCAT_WS(" ",first_name, last_name) as fullname,register_no,c.name as class_name,se.name as section_name,exam_rank.rank,exam_rank.principal_comments,exam_rank.teacher_comments');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('login_credential as l', 'l.user_id = s.id and l.role = 7', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id=se.id', 'left');
        $builder->join('exam_rank', 'exam_rank.enroll_id=e.id and exam_rank.exam_id = ' . $db->escape($exam_ID), 'left');
        $builder->where('e.class_id', $class_ID);
        if (!empty($section_ID)) {
            $builder->where('e.section_id', $section_ID);
        }
        $builder->where('e.branch_id', $branch_id);
        $builder->where('e.session_id', $session_ID);
        $builder->order_by('exam_rank.rank', 'ASC');
        $builder->where('l.active', 1);
        return $builder->get()->getResult();
    }
    public function getExamList()
    {
        $builder->select('e.*,b.name as branch_name');
        $builder->from('exam as e');
        $builder->join('branch as b', 'b.id = e.branch_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('e.branch_id', get_loggedin_branch_id())->where();
        }
        $this->db->table('e.session_id', get_session_id())->where();
        $builder->order_by('e.id', 'asc');
        return $builder->get()->result_array();
    }
    public function exam_save($data)
    {
        $arrayExam = array('name' => $data['name'], 'branch_id' => $this->applicationModel->get_branch_id(), 'term_id' => $data['term_id'], 'type_id' => $data['type_id'], 'mark_distribution' => json_encode($data['mark_distribution']), 'remark' => $data['remark'], 'session_id' => get_session_id(), 'status' => isset($_POST['exam_publish']) ? 1 : 0, 'publish_result' => 0);
        if (!isset($data['exam_id'])) {
            $builder->insert('exam', $arrayExam);
        } else {
            $builder->where('id', $data['exam_id']);
            $builder->update('exam', $arrayExam);
        }
    }
    public function termSave($post)
    {
        $arrayTerm = array('name' => $post['term_name'], 'branch_id' => $this->applicationModel->get_branch_id(), 'session_id' => get_session_id());
        if (!isset($post['term_id'])) {
            $builder->insert('exam_term', $arrayTerm);
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $post['term_id']);
            $builder->update('exam_term', $arrayTerm);
        }
    }
    public function hallSave($post)
    {
        $arrayHall = array('hall_no' => $post['hall_no'], 'seats' => $post['no_of_seats'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($post['hall_id'])) {
            $builder->insert('exam_hall', $arrayHall);
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $post['hall_id']);
            $builder->update('exam_hall', $arrayHall);
        }
    }
    public function gradeSave($data)
    {
        $arrayData = array('branch_id' => $this->applicationModel->get_branch_id(), 'name' => $data['name'], 'grade_point' => $data['grade_point'], 'lower_mark' => $data['lower_mark'], 'upper_mark' => $data['upper_mark'], 'remark' => $data['remark']);
        // posted all data XSS filtering
        if (!isset($data['grade_id'])) {
            $builder->insert('grade', $arrayData);
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $data['grade_id']);
            $builder->update('grade', $arrayData);
        }
    }
    public function get_grade($mark, $branch_id)
    {
        $builder->where('branch_id', $branch_id);
        $query = $builder->get('grade');
        $grades = $query->getResultArray();
        foreach ($grades as $row) {
            if ($mark >= $row['lower_mark'] && $mark <= $row['upper_mark']) {
                return $row;
            }
        }
    }
    public function getSubjectList($examID, $classID, $sectionID, $sessionID)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $builder->select('t.*,s.name as subject_name');
        $builder->from('timetable_exam as t');
        $builder->join('subject as s', 's.id = t.subject_id', 'inner');
        $builder->where('t.exam_id', $examID);
        $builder->where('t.class_id', $classID);
        $builder->where('t.section_id', $sectionID);
        $builder->where('t.session_id', $sessionID);
        $builder->where('t.branch_id', $branchID);
        $builder->group_by('t.subject_id');
        return $builder->get()->result_array();
    }
    public function getTimetableDetail($classID, $sectionID, $examID, $subjectID)
    {
        $builder->select('timetable_exam.mark_distribution');
        $builder->where('class_id', $classID);
        $builder->where('section_id', $sectionID);
        $builder->where('exam_id', $examID);
        $builder->where('subject_id', $subjectID);
        $this->db->table('session_id', get_session_id())->where();
        return $builder->get('timetable_exam')->row_array();
    }
    public function getMarkAndStudent($branchID, $classID, $sectionID, $examID, $subjectID)
    {
        $builder->select('en.*,st.first_name,st.last_name,st.register_no,st.category_id,m.mark as get_mark,IFNULL(m.absent, 0) as get_abs,subject.name as subject_name');
        $builder->from('enroll as en');
        $builder->join('student as st', 'st.id = en.student_id', 'inner');
        $builder->join('mark as m', 'm.student_id = en.student_id and m.class_id = en.class_id and m.section_id = en.section_id and m.exam_id = ' . $db->escape($examID) . ' and m.subject_id = ' . $db->escape($subjectID), 'left');
        $builder->join('subject', 'subject.id = m.subject_id', 'left');
        $builder->where('en.class_id', $classID);
        $builder->where('en.section_id', $sectionID);
        $builder->where('en.branch_id', $branchID);
        $this->db->table('en.session_id', get_session_id())->where();
        $builder->order_by('en.roll', 'ASC');
        return $builder->get()->result_array();
    }
    public function getStudentReportCard($studentID, $examID, $sessionID, $classID = '', $sectionID = '')
    {
        $result = array();
        $builder->select('s.*,CONCAT_WS(" ",s.first_name, s.last_name) as name,e.id as enrollID,e.roll,e.branch_id,e.session_id,e.class_id,e.section_id,c.name as class,se.name as section,sc.name as category,IFNULL(p.father_name,"N/A") as father_name,IFNULL(p.mother_name,"N/A") as mother_name,br.name as institute_name,br.email as institute_email,br.address as institute_address,br.mobileno as institute_mobile_no');
        $builder->from('enroll as e');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('class as c', 'e.class_id = c.id', 'left');
        $builder->join('section as se', 'e.section_id = se.id', 'left');
        $builder->join('student_category as sc', 's.category_id=sc.id', 'left');
        $builder->join('parent as p', 'p.id=s.parent_id', 'left');
        $builder->join('branch as br', 'br.id = e.branch_id', 'left');
        $builder->where('e.student_id', $studentID);
        $builder->where('e.session_id', $sessionID);
        if (!empty($classID)) {
            $builder->where('e.class_id', $classID);
        }
        if (!empty($sectionID)) {
            $builder->where('e.section_id', $sectionID);
        }
        $result['student'] = $builder->get()->row_array();
        $builder->select('m.mark as get_mark,IFNULL(m.absent, 0) as get_abs,subject.name as subject_name, te.mark_distribution, m.subject_id');
        $builder->from('mark as m');
        $builder->join('subject', 'subject.id = m.subject_id', 'left');
        $builder->join('timetable_exam as te', 'te.exam_id = m.exam_id and te.class_id = m.class_id and te.section_id = m.section_id and te.subject_id = m.subject_id', 'left');
        $builder->where('m.exam_id', $examID);
        $builder->where('m.student_id', $studentID);
        $builder->where('m.session_id', $sessionID);
        if (!empty($classID)) {
            $builder->where('m.class_id', $classID);
        }
        if (!empty($sectionID)) {
            $builder->where('m.section_id', $sectionID);
        }
        $builder->group_by('m.subject_id');
        $builder->order_by('subject.id', 'ASC');
        $result['exam'] = $builder->get()->result_array();
        return $result;
    }
}



