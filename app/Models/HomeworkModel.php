<?php

namespace App\Models;

use CodeIgniter\Model;
class HomeworkModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    public function getList($classID, $sectionID, $subjectID, $branchID)
    {
        $builder->select('homework.*,subject.name as subject_name,class.name as class_name,section.name as section_name,staff.name as creator_name');
        $builder->from('homework');
        $builder->join('subject', 'subject.id = homework.subject_id', 'left');
        $builder->join('class', 'class.id = homework.class_id', 'left');
        $builder->join('section', 'section.id = homework.section_id', 'left');
        $builder->join('staff', 'staff.id = homework.created_by', 'left');
        $builder->where('homework.class_id', $classID);
        $builder->where('homework.section_id', $sectionID);
        $builder->where('homework.subject_id', $subjectID);
        $builder->where('homework.branch_id', $branchID);
        $this->db->table('homework.session_id', get_session_id())->where();
        $builder->order_by('homework.id', 'desc');
        return $builder->get()->result_array();
    }
    public function evaluationCounter($classID, $sectionID, $homeworkID)
    {
        $countStu = $db->table('enroll')->get('enroll')->num_rows();
        $countEva = $db->table('homework_evaluation')->get('homework_evaluation')->num_rows();
        $incomplete = $countStu - $countEva;
        return array('total' => $countStu, 'complete' => $countEva, 'incomplete' => $incomplete);
    }
    public function getEvaluate($homeworkID)
    {
        $builder->select('homework.*,CONCAT_WS(" ",s.first_name, s.last_name) as fullname,s.register_no,e.student_id, e.roll,subject.name as subject_name,class.name as class_name,section.name as section_name,he.id as ev_id,he.status as ev_status,he.remark as ev_remarks,he.rank,hs.message,hs.enc_name');
        $builder->from('homework');
        $builder->join('enroll as e', 'e.class_id=homework.class_id and e.section_id = homework.section_id and e.session_id = homework.session_id', 'inner');
        $builder->join('student as s', 'e.student_id = s.id', 'inner');
        $builder->join('homework_evaluation as he', 'he.homework_id = homework.id and he.student_id = e.student_id', 'left');
        $builder->join('homework_submit as hs', 'hs.homework_id = homework.id and hs.student_id = e.student_id', 'left');
        $builder->join('subject', 'subject.id = homework.subject_id', 'left');
        $builder->join('class', 'class.id = homework.class_id', 'left');
        $builder->join('section', 'section.id = homework.section_id', 'left');
        $builder->where('homework.id', $homeworkID);
        if (!is_superadmin_loggedin()) {
            $this->db->table('homework.branch_id', get_loggedin_branch_id())->where();
        }
        $this->db->table('homework.session_id', get_session_id())->where();
        $builder->order_by('homework.id', 'desc');
        return $builder->get()->result_array();
    }
    // save student homework in DB
    public function save($data)
    {
        $status = isset($data['published_later']) ? TRUE : FALSE;
        $sms_notification = isset($data['notification_sms']) ? TRUE : FALSE;
        $arrayHomework = array('branch_id' => $this->applicationModel->get_branch_id(), 'class_id' => $data['class_id'], 'section_id' => $data['section_id'], 'session_id' => get_session_id(), 'subject_id' => $data['subject_id'], 'date_of_homework' => date("Y-m-d", strtotime($data['date_of_homework'])), 'date_of_submission' => date("Y-m-d", strtotime($data['date_of_submission'])), 'description' => $data['homework'], 'created_by' => get_loggedin_user_id(), 'create_date' => date("Y-m-d"), 'status' => $status, 'sms_notification' => $sms_notification);
        if ($status == TRUE) {
            $arrayHomework['schedule_date'] = date("Y-m-d", strtotime($data['schedule_date']));
        } else {
            $arrayHomework['schedule_date'] = null;
        }
        if (isset($data['homework_id'])) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id', get_loggedin_branch_id())->where();
            }
            $builder->where('id', $data['homework_id']);
            $builder->update('homework', $arrayHomework);
            $insert_id = $data['homework_id'];
        } else {
            $builder->insert('homework', $arrayHomework);
            $insert_id = $builder->insert_id();
        }
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            $uploaddir = './uploads/attachments/homework/';
            if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                die("Error creating folder {$uploaddir}");
            }
            $fileInfo = pathinfo($_FILES["attachment_file"]["name"]);
            $document = basename($_FILES['attachment_file']['name']);
            $file_name = $insert_id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["attachment_file"]["tmp_name"], $uploaddir . $file_name);
        } else if (isset($data['old_document'])) {
            $document = $data['old_document'];
        } else {
            $document = "";
        }
        $builder->where('id', $insert_id);
        $this->db->table('homework', array('document' => $document))->update();
        //send homework sms notification
        if (isset($data['notification_sms'])) {
            $stuList = $this->applicationModel->getStudentListByClassSection($arrayHomework['class_id'], $arrayHomework['section_id'], $arrayHomework['branch_id']);
            foreach ($stuList as $row) {
                $row['date_of_homework'] = $arrayHomework['date_of_homework'];
                $row['date_of_submission'] = $arrayHomework['date_of_submission'];
                $row['subject_id'] = $arrayHomework['subject_id'];
                $this->smsModel->sendHomework($row);
            }
        }
    }
}



