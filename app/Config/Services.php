<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function appLib($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('appLib');
        }

        return new \App\Libraries\AppLib();
    }

    public static function bigbluebuttonLib($config = [], $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('bigbluebuttonLib', $config);
        }

        return new \App\Libraries\BigbluebuttonLib($config);
    }

    public static function bulk($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('bulk');
        }

        return new \App\Libraries\Bulk();
    }

    public static function bulksmsbd($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('bulksmsbd');
        }

        return new \App\Libraries\Bulksmsbd();
    }

    public static function ciqrcode($config = [], $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('ciqrcode', $config);
        }

        return new \App\Libraries\Ciqrcode($config);
    }

    public static function clickatell($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('clickatell');
        }

        return new \App\Libraries\Clickatell();
    }

    public static function csvimport($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('csvimport');
        }

        return new \App\Libraries\Csvimport();
    }

    public static function customSms($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('customSms');
        }

        return new \App\Libraries\CustomSms();
    }

    public static function html2pdf($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('html2pdf');
        }

        return new \App\Libraries\Html2pdf();
    }

    public static function mailer($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('mailer');
        }

        return new \App\Libraries\Mailer();
    }

    public static function midtransPayment($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('midtransPayment');
        }

        return new \App\Libraries\MidtransPayment();
    }

    public static function msg91($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('msg91');
        }

        return new \App\Libraries\Msg91();
    }

    public static function paypalPayment($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('paypalPayment');
        }

        return new \App\Libraries\PaypalPayment();
    }

    public static function paytmKitLib($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('paytmKitLib');
        }

        return new \App\Libraries\PaytmKitLib();
    }

    public static function razorpayPayment($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('razorpayPayment');
        }

        return new \App\Libraries\RazorpayPayment();
    }

    public static function recaptcha($config = [], $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('recaptcha', $config);
        }

        return new \App\Libraries\Recaptcha($config);
    }

    public static function smscountry($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('smscountry');
        }

        return new \App\Libraries\Smscountry();
    }

    public static function sslcommerz($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('sslcommerz');
        }

        return new \App\Libraries\Sslcommerz();
    }

    public static function stripePayment($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('stripePayment');
        }

        return new \App\Libraries\StripePayment();
    }

    public static function tapPayments($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('tapPayments');
        }

        return new \App\Libraries\TapPayments();
    }

    public static function textlocal($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('textlocal');
        }

        return new \App\Libraries\Textlocal();
    }

    public static function twilio($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('twilio');
        }

        return new \App\Libraries\Twilio();
    }

    public static function zoomLib($config = [], $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('zoomLib', $config);
        }

        return new \App\Libraries\ZoomLib($config);
    }
}
