<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\FeesModel;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Student_promotion.php
 * @copyright : Reserved RamomCoder Team
 */
class Student_promotion extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    public $applicationModel;

    public $input;

    public $load;

    public $validation;

    public $feesModel;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->fees = new \App\Models\FeesModel();
    }

    public function index()
    {
        // check access permission
        if (!get_permission('student_promotion', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if ($this->request->getPost()) {
            $this->data['class_id'] = $this->request->getPost('class_id');
            $this->data['section_id'] = $this->request->getPost('section_id');
            $this->data['students'] = $this->applicationModel->getStudentListByClassSection($this->data['class_id'], $this->data['section_id'], $branchID, false, true, false);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_promotion');
        $this->data['sub_page'] = 'student_promotion/index';
        $this->data['main_menu'] = 'transfer';
        echo view('layout/index', $this->data);
    }

    public function transfersave()
    {
        // check access permission
        if (!get_permission('student_promotion', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $dueForward = isset($_POST['due_forward']) ? 1 : 0;
            $this->validation->setRules(['promote_session_id' => ["label" => translate('promote_to_session'), "rules" => 'required']]);
            $this->validation->setRules(['promote_class_id' => ["label" => translate('promote_to_class'), "rules" => 'required|callback_validClass']]);
            $this->validation->setRules(['promote_section_id' => ["label" => translate('promote_section_id'), "rules" => 'required|callback_validSection']]);
            $items = $this->request->getPost('promote');
            foreach ($items as $key => $value) {
                if (isset($value['enroll_id'])) {
                    $this->validation->setRules(['promote[' . $key . '][roll]' => ["label" => translate('roll'), "rules" => 'callback_unique_prom_roll']]);
                }
            }

            if ($this->validation->run() !== false) {
                $promotionHistorys = [];
                $preClassId = $this->request->getPost('class_id');
                $preSectionId = $this->request->getPost('section_id');
                $preSessionId = get_session_id();
                $promoteSessionId = $this->request->getPost('promote_session_id');
                $promoteClassID = $this->request->getPost('promote_class_id');
                $promoteSectionID = $this->request->getPost('promote_section_id');
                $branchID = $this->applicationModel->get_branch_id();
                $promote = $this->request->getPost('promote');
                $school = $this->feesModel->get('branch', ['id' => $branchID], true);
                $dueDays = empty($school['due_days']) ? 1 : $school['due_days'];
                foreach ($promote as $value) {
                    if (isset($value['enroll_id'])) {
                        $leaveStatus = isset($value['leave']) ? 1 : 0;
                        if ($leaveStatus == 1) {
                            $promoteClassId = $preClassId;
                            $promoteSectionId = $preSectionId;
                        } elseif ($value['class_status'] == 'running') {
                            $promoteClassId = $preClassId;
                            $promoteSectionId = $preSectionId;
                        } else {
                            $promoteClassId = $promoteClassID;
                            $promoteSectionId = $promoteSectionID;
                        }

                        $promotionHistory = [];
                        $promotionHistory['student_id'] = $value['student_id'];
                        $promotionHistory['pre_class'] = $preClassId;
                        $promotionHistory['pre_section'] = $preSectionId;
                        $promotionHistory['pre_session'] = $preSessionId;
                        $promotionHistory['pro_class'] = $promoteClassId;
                        $promotionHistory['pro_section'] = $promoteSectionId;
                        $promotionHistory['pro_session'] = $leaveStatus == 1 ? $preSessionId : $promoteSessionId;
                        $promotionHistory['date'] = date('Y-m-d H:i:s');
                        $promotionHistory['prev_due'] = 0;
                        $promotionHistory['is_leave'] = 0;
                        $enrollId = $value['enroll_id'];
                        $studentId = $value['student_id'];
                        if ($leaveStatus == 1) {
                            $this->db->table('id')->where();
                            $this->db->table('enroll')->update();
                            $promotionHistory['is_leave'] = 1;
                        } else {
                            $roll = empty($value['roll']) ? 0 : $value['roll'];
                            // check existing data
                            $this->db->table('student_id')->where();
                            $this->db->table('session_id')->where();
                            $query = $builder->get('enroll');
                            // insert promotion data
                            $arrayData = ['student_id' => $studentId, 'class_id' => $promoteClassId, 'roll' => $roll, 'section_id' => $promoteSectionId, 'session_id' => $promoteSessionId, 'branch_id' => $branchID];
                            if ($query->num_rows() > 0) {
                                $this->db->table('id')->where();
                                $this->db->table('enroll')->update();
                                $enrollId = $query->row()->id;
                            } else {
                                $this->db->table('enroll')->insert();
                                $enrollId = $this->db->insert_id();
                            }

                            // insert carry forward due data
                            if ($dueForward == 1 && (!empty($value['due_amount']) && $value['due_amount'] != 0)) {
                                $promotionHistory['prev_due'] = $value['due_amount'];
                                $arrayForwardDue = ['branch_id' => $branchID, 'session_id' => $promoteSessionId, 'student_id' => $enrollId, 'prev_due' => $value['due_amount'], 'due_date' => date('Y-m-d', strtotime(sprintf('+%s Days', $dueDays)))];
                                $this->feesModel->carryForwardDue($arrayForwardDue);
                            }
                        }

                        $promotionHistorys[] = $promotionHistory;
                    }
                }

                $this->db->insert_batch('promotion_history', $promotionHistorys);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('student_promotion');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function getPromotionStatus()
    {
        if ($_POST !== []) {
            // check access permission
            if (!get_permission('student_promotion', 'is_add')) {
                ajax_access_denied();
            }

            $classId = $this->request->getPost('class_id');
            $sectionId = $this->request->getPost('section_id');
            $sessionId = $this->request->getPost('session_id');
            if (empty($classId) || empty($sectionId) || empty($sessionId)) {
                $array = ['status' => 2];
                echo json_encode($array);
                exit;
            }

            $r = $db->table('enroll')->get('enroll')->result_array();
            if (empty($r)) {
                $array = ['status' => 0];
            } else {
                $r = array_column($r, 'student_id');
                $array = ['status' => 1, 'msg' => '<i class="far fa-check-circle"></i> Mark students have already been promoted, you can only update now.', 'stu_arr' => $r];
            }

            echo json_encode($array);
        }
    }

    public function unique_prom_roll($roll)
    {
        if (!empty($roll)) {
            $promoteSessionId = $this->request->getPost('promote_session_id');
            $promoteClassId = $this->request->getPost('promote_class_id');
            $promoteSectionId = $this->request->getPost('promote_section_id');
            $branchID = $this->applicationModel->get_branch_id();
            $schoolSettings = $this->feesModel->get('branch', ['id' => $branchID], true, false, 'unique_roll');
            $uniqueRoll = $schoolSettings['unique_roll'];
            if (!empty($uniqueRoll) && $uniqueRoll != 0) {
                $builder->select('id');
                if ($uniqueRoll == 2) {
                    $this->db->table('section_id')->where();
                }

                $this->db->table(['roll' => $roll, 'class_id' => $promoteClassId, 'session_id' => $promoteSessionId, 'branch_id' => $branchID])->where();
                $r = $builder->get('enroll');
                if ($r->num_rows() == 0) {
                    return true;
                }
                $this->validation->setRule('unique_prom_roll', "The %s is already exists.");
                return false;
            }
        }

        return true;
    }

    public function validClass($classID)
    {
        if (!empty($classID)) {
            $preClassId = $this->request->getPost('class_id');
            $promoteSessionId = $this->request->getPost('promote_session_id');
            if ($preClassId == $classID && $promoteSessionId == get_session_id()) {
                $this->validation->setRule('validClass', translate("wrong_command"));
                return false;
            }
        }

        return true;
    }

    public function validSection($sectionID)
    {
        if (!empty($sectionID)) {
            $preClassId = $this->request->getPost('class_id');
            $preSectionId = $this->request->getPost('section_id');
            $promoteSessionId = $this->request->getPost('promote_session_id');
            $promoteClassId = $this->request->getPost('promote_class_id');
            if ($promoteSessionId == get_session_id() && $preClassId == $promoteClassId && $preSectionId == $sectionID) {
                $this->validation->setRule('validSection', translate("wrong_command"));
                return false;
            }
        }

        return true;
    }
}
