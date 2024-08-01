<?php

namespace App\Models;

use CodeIgniter\Model;
class FeespaymentModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->model('sms_model');
    }
    public function get_student_invoice($student_id = '')
    {
        $builder->select('fi.*,e.student_id,e.roll,s.first_name,s.last_name,s.register_no');
        $builder->from('fee_invoice as fi');
        $builder->join('enroll as e', 'e.student_id = fi.student_id', 'left');
        $builder->join('student as s', 's.id = fi.student_id', 'left');
        $builder->where('fi.student_id', $student_id);
        $builder->order_by('fi.id', 'desc');
        return $builder->get();
    }
    public function get_invoice_single($id = '')
    {
        $builder->select('fi.*,e.student_id,e.roll,e.class_id,s.first_name,s.last_name,s.email,s.current_address,c.name as class_name');
        $builder->from('fee_invoice as fi');
        $builder->join('enroll as e', 'e.student_id = fi.student_id', 'left');
        $builder->join('student as s', 's.id = fi.student_id', 'left');
        $builder->join('class as c', 'c.id = e.class_id', 'left');
        $builder->where('fi.id', $id);
        return $builder->get()->row();
    }
    public function save_online_pay($data = array())
    {
        $arrayHistory = array('fee_invoice_id' => $data['invoice_id'], 'collect_by' => 'online', 'remarks' => $data['remarks'], 'method' => $data['method'], 'amount' => $data['payment_amount'], 'date' => date("Y-m-d"), 'session_id' => get_session_id());
        $builder->insert('payment_history', $arrayHistory);
        if ($data['total_due'] <= $data['payment_amount']) {
            $builder->where('id', $data['invoice_id']);
            $this->db->table('fee_invoice', array('status' => 2))->update();
        } else {
            $builder->where('id', $data['invoice_id']);
            $this->db->table('fee_invoice', array('status' => 1))->update();
        }
        $builder->where('id', $data['invoice_id']);
        $builder->set('total_paid', 'total_paid + ' . $data['payment_amount'], false);
        $builder->set('total_due', 'total_due - ' . $data['payment_amount'], false);
        $builder->update('fee_invoice');
        // send payment confirmation sms
        $arrayHistory['student_id'] = $data['student_id'];
        $this->smsModel->send_sms($arrayHistory, 2);
    }
}



