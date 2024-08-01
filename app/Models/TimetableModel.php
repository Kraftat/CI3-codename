<?php

namespace App\Models;

use CodeIgniter\Model;
class TimetableModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    // class wise information save
    public function classwise_save($data)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $sectionID = $data['section_id'];
        $classID = $data['class_id'];
        $sessionID = get_session_id();
        $day = $data['day'];
        $arrayItems = $this->request->getPost('timetable');
        if (!empty($arrayItems)) {
            foreach ($arrayItems as $key => $value) {
                if (!isset($value['break'])) {
                    $subjectID = $value['subject'];
                    $teacherID = $value['teacher'];
                    $break = false;
                } else {
                    $subjectID = 0;
                    $teacherID = 0;
                    $break = true;
                }
                $timeStart = date("H:i:s", strtotime($value['time_start']));
                $timeEnd = date("H:i:s", strtotime($value['time_end']));
                $roomNumber = $value['class_room'];
                if (!empty($timeStart) && !empty($timeEnd)) {
                    $arrayRoutine = array('class_id' => $classID, 'section_id' => $sectionID, 'subject_id' => $subjectID, 'teacher_id' => $teacherID, 'time_start' => $timeStart, 'time_end' => $timeEnd, 'class_room' => $roomNumber, 'session_id' => $sessionID, 'branch_id' => $branchID, 'break' => $break, 'day' => $day);
                    if ($data['old_id'][$key] == 0) {
                        $builder->insert('timetable_class', $arrayRoutine);
                    } else {
                        $builder->where('id', $data['old_id'][$key]);
                        $builder->update('timetable_class', $arrayRoutine);
                    }
                }
            }
        }
        $arrayI = isset($data['i']) ? $data['i'] : array();
        $preserve_array = isset($data['old_id']) ? $data['old_id'] : array();
        $deleteArray = array_diff($arrayI, $preserve_array);
        if (!empty($deleteArray)) {
            $builder->where_in('id', $deleteArray);
            $builder->delete('timetable_class');
        }
    }
    public function getExamTimetableList($classID, $sectionID, $branchID)
    {
        $sessionID = get_session_id();
        $builder->select('t.*,b.name as branch_name');
        $builder->from('timetable_exam as t');
        $builder->join('branch as b', 'b.id = t.branch_id', 'left');
        $builder->where('t.branch_id', $branchID);
        $builder->where('t.class_id', $classID);
        $builder->where('t.section_id', $sectionID);
        $builder->where('t.session_id', $sessionID);
        $builder->order_by('t.id', 'asc');
        $builder->group_by('t.exam_id');
        return $builder->get()->result_array();
    }
    public function getSubjectExam($classID, $sectionID, $examID, $branchID)
    {
        $sessionID = get_session_id();
        $sql = "SELECT sa.*, s.name as subject_name, te.time_start, te.time_end, te.hall_id, te.exam_date, te.mark_distribution FROM subject_assign as sa\r\n        LEFT JOIN subject as s ON s.id = sa.subject_id LEFT JOIN timetable_exam as te ON te.class_id = sa.class_id and te.section_id = sa.section_id and\r\n        te.subject_id = sa.subject_id and te.session_id = sa.session_id and te.exam_id = " . $db->escape($examID) . " WHERE sa.class_id = " . $db->escape($classID) . " AND sa.section_id = " . $db->escape($sectionID) . " AND sa.branch_id = " . $db->escape($branchID) . " AND sa.session_id = " . $db->escape($sessionID);
        $query = $db->query($sql);
        return $query->getResultArray();
    }
    public function getExamTimetableByModal($examID, $classID, $sectionID, $branchID = '')
    {
        $sessionID = get_session_id();
        $builder->select('t.*,s.name as subject_name,eh.hall_no');
        $builder->from('timetable_exam as t');
        $builder->join('subject as s', 's.id = t.subject_id', 'left');
        $builder->join('exam_hall as eh', 'eh.id = t.hall_id', 'left');
        if (!empty($branchID)) {
            $builder->where('t.branch_id', $branchID);
        } else if (!is_superadmin_loggedin()) {
            $this->db->table('t.branch_id', get_loggedin_branch_id())->where();
        }
        $builder->where('t.exam_id', $examID);
        $builder->where('t.class_id', $classID);
        $builder->where('t.section_id', $sectionID);
        $builder->where('t.session_id', $sessionID);
        return $builder->get();
    }
}



