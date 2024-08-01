<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\FeesModel;
use App\Models\SmsModel;
use App\Models\SendsmsmailModel;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Cron_api.php
 * @copyright : Reserved RamomCoder Team
 */
class Cron_api extends MyController

{
    protected $db;



    /**
     * @var App\Models\FeesModel
     */
    public $fees;

    /**
     * @var App\Models\SmsModel
     */
    public $sms;

    /**
     * @var App\Models\SendsmsmailModel
     */
    public $sendsmsmail;

    public $api_key;

    public $load;

    public $sendsmsmailModel;

    public $applicationModel;

    public $smsModel;

    public $feesModel;

    public function __construct()
    {



        $this->fees = new \App\Models\FeesModel();
        $this->sms = new \App\Models\SmsModel();
        $this->sendsmsmail = new \App\Models\SendsmsmailModel();
        $this->api_key = $this->data['global_config']['cron_secret_key'];
    }

    public function index()
    {
        if (!is_loggedin() || !get_permission('cron_job', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('cron_job', 'is_edit')) {
                access_denied();
            }

            $this->db->table('id')->where();
            $this->db->table('global_settings')->update();
            set_alert('success', "Successfully Created The New Secret Key.");
            redirect(current_url());
        }

        $this->data['title'] = translate('cron_job');
        $this->data['sub_page'] = 'cron_api/index';
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
    }

    public function send_smsemail_command($apiKey = '')
    {
        if ($apiKey != "" && $this->api_key != $apiKey) {
            echo "API Key is required or API Key does not match.";
            exit;
        }

        $sql = "SELECT * FROM bulk_sms_email WHERE posting_status = 1 AND schedule_time < NOW() ORDER BY schedule_time ASC";
        $bulkArray = $db->query($sql)->result_array();
        foreach ($bulkArray as $row) {
            $this->db->table('id')->where();
            $this->db->table('bulk_sms_email')->update();
            $sCount = 0;
            $usersList = json_decode((string) $row['additional'], true);
            foreach ($usersList as $user) {
                if ($row['message_type'] == 1) {
                    $response = $this->sendsmsmailModel->sendSMS($user['mobileno'], $row['message'], $user['name'], $user['email'], $row['sms_gateway']);
                } else {
                    $response = $this->sendsmsmailModel->sendEmail($user['email'], $row['message'], $user['name'], $user['mobileno'], $row['email_subject']);
                }

                if ($response == true) {
                    $sCount++;
                }
            }

            $this->db->table('id')->where();
            $this->db->table('bulk_sms_email')->update();
        }
    }

    public function homework_command($apiKey = '')
    {
        if ($apiKey != "" && $this->api_key != $apiKey) {
            echo "API Key is required or API Key does not match.";
            exit;
        }

        $sql = "SELECT * FROM homework WHERE status = 1 AND date(schedule_date) = CURDATE() ORDER BY schedule_date ASC";
        $homeworkArray = $db->query($sql)->result_array();
        foreach ($homeworkArray as $row) {
            $this->db->table('id')->where();
            $this->db->table('homework')->update();
            //send homework sms notification
            if ($row['sms_notification'] == 1) {
                $stuList = $this->applicationModel->getStudentListByClassSection($row['class_id'], $row['section_id'], $row['branch_id']);
                foreach ($stuList as $stuRow) {
                    $stuRow['date_of_homework'] = $row['date_of_homework'];
                    $stuRow['date_of_submission'] = $row['date_of_submission'];
                    $stuRow['subject_id'] = $row['subject_id'];
                    $this->smsModel->sendHomework($stuRow);
                }
            }
        }
    }

    public function fees_reminder_command($apiKey = '')
    {
        if ($apiKey != "" && $this->api_key != $apiKey) {
            echo "API Key is required or API Key does not match.";
            exit;
        }

        $feesArray = $builder->get('fees_reminder')->result_array();
        foreach ($feesArray as $row) {
            $studentList = [];
            $days = $row['days'];
            if ($row['frequency'] == 'before') {
                $date = date('Y-m-d', strtotime(sprintf('+ %s days', $days)));
            } elseif ($row['frequency'] == 'after') {
                $date = date('Y-m-d', strtotime(sprintf('- %s days', $days)));
            }

            $getFeeTypes = $this->feesModel->getFeeReminderByDate($date, $row['branch_id']);
            foreach ($getFeeTypes as $typeValue) {
                $getStuDetails = $this->feesModel->getStudentsListReminder($typeValue['fee_groups_id'], $typeValue['fee_type_id']);
                foreach ($getStuDetails as $stuValue) {
                    $stuValue['due_date'] = _d($typeValue['due_date']);
                    $stuValue['type_name'] = $typeValue['name'];
                    $stuValue['total_amount'] = (float) $typeValue['amount'];
                    $stuValue['balance_amount'] = (float) ($typeValue['amount'] - ($stuValue['payment']['total_paid'] + $stuValue['payment']['total_discount']));
                    unset($stuValue['payment']);
                    if ($stuValue['balance_amount'] > 0) {
                        $studentList[] = $stuValue;
                    }
                }
            }

            foreach ($studentList as $stuRow) {
                $this->smsModel->feeReminder($stuRow, $row);
            }
        }
    }
}
