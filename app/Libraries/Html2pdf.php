<?php

namespace App\Libraries;

use Mpdf\Mpdf;

class Html2pdf
{
    /**
     * @var Mpdf
     */
    public Mpdf $mpdf;

    public function __construct()
    {
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'format' => 'A4',
        ]);
    }
}
