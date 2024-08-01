<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function send(array $data = [], $err = false)
    {
        $emailConfigModel = new \App\Models\EmailConfigModel();
        $getConfig = $emailConfigModel->where('branch_id', $data['branch_id'])->first();
        
        $school_name = getenv('INSTITUTE_NAME'); // Use environment variables or config

        $phpMailer = new PHPMailer();

        if ($getConfig['protocol'] === 'smtp') {
            $smtp_encryption = $getConfig['smtp_encryption'];
            $phpMailer->isSMTP();
            $phpMailer->SMTPDebug = SMTP::DEBUG_OFF;
            $phpMailer->Host = trim($getConfig['smtp_host']);
            $phpMailer->Port = trim($getConfig['smtp_port']);
            if (!empty($smtp_encryption)) {
                $phpMailer->SMTPSecure = $smtp_encryption;
            }
            $phpMailer->SMTPAuth = $getConfig['smtp_auth'];
            $phpMailer->Username = trim($getConfig['smtp_user']);
            $phpMailer->Password = trim($getConfig['smtp_pass']);
        } else {
            $phpMailer->isSendmail();
        }

        if (!empty($data['file'])) {
            $phpMailer->addStringAttachment($data['file'], $data['file_name']);
        }

        $phpMailer->setFrom($getConfig['email'], $school_name);
        $phpMailer->addReplyTo($getConfig['email'], $school_name);
        $phpMailer->addAddress($data['recipient']);

        $phpMailer->Subject = $data['subject'];
        $phpMailer->AltBody = $data['message'];
        $phpMailer->Body = $data['message'];

        if ($phpMailer->send()) {
            return true;
        }

        if ($err) {
            return $phpMailer->ErrorInfo;
        }

        return false;
    }
}
