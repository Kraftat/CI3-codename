<?php

namespace App\Libraries\qrcode;

/*
 * PHP QR Code encoder
 *
 * Masking
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
	define('N1', 3);
	define('N2', 3);
	define('N3', 40);
	define('N4', 10);

	class QRmask {

		public $runLength = array();

		//----------------------------------------------------------------------
		public function __construct() 
        {
            $this->runLength = array_fill(0, QRSPEC_WIDTH_MAX + 1, 0);
        }

        //----------------------------------------------------------------------
        public function writeFormatInformation($width, array &$frame, $mask, $level): int
        {
            $blacks = 0;
            $format =  QRspec::getFormatInfo($mask, $level);

            for($i=0; $i<8; $i++) {
                if(($format & 1) !== 0) {
                    $blacks += 2;
                    $v = 0x85;
                } else {
                    $v = 0x84;
                }

                $frame[8][$width - 1 - $i] = chr($v);
                if($i < 6) {
                    $frame[$i][8] = chr($v);
                } else {
                    $frame[$i + 1][8] = chr($v);
                }

                $format >>= 1;
            }

            for($i=0; $i<7; $i++) {
                if(($format & 1) !== 0) {
                    $blacks += 2;
                    $v = 0x85;
                } else {
                    $v = 0x84;
                }

                $frame[$width - 7 + $i][8] = chr($v);
                if($i == 0) {
                    $frame[8][7] = chr($v);
                } else {
                    $frame[8][6 - $i] = chr($v);
                }

                $format >>= 1;
            }

            return $blacks;
        }

        //----------------------------------------------------------------------
        public function mask0($x, $y): int { return ($x+$y)&1;                       }

        public function mask1($x, $y): int { return ($y&1);                          }

        public function mask2($x, $y): int { return ($x%3);                          }

        public function mask3($x, $y): int { return ($x+$y)%3;                       }

        public function mask4($x, $y): int { return (((int)($y/2))+((int)($x/3)))&1; }

        public function mask5($x, $y): int { return (($x*$y)&1)+($x*$y)%3;           }

        public function mask6($x, $y): int { return ((($x*$y)&1)+($x*$y)%3)&1;       }

        public function mask7($x, $y): int { return ((($x*$y)%3)+(($x+$y)&1))&1;     }

        //----------------------------------------------------------------------
        /**
         * @return mixed[]
         */
        private function generateMaskNo(string $maskNo, string $width, $frame): array
        {
            $bitMask = array_fill(0, $width, array_fill(0, $width, 0));

            for($y=0; $y<$width; $y++) {
                for($x=0; $x<$width; $x++) {
                    if((ord($frame[$y][$x]) & 0x80) !== 0) {
                        $bitMask[$y][$x] = 0;
                    } else {
                        $maskFunc = call_user_func(array($this, 'mask'.$maskNo), $x, $y);
                        $bitMask[$y][$x] = ($maskFunc == 0)?1:0;
                    }

                }
            }

            return $bitMask;
        }

        //----------------------------------------------------------------------
        public static function serial($bitFrame): string|false
        {
            $codeArr = array();

            foreach ($bitFrame as $line)
                $codeArr[] = implode('', $line);

            return gzcompress(implode("\n", $codeArr), 9);
        }

        //----------------------------------------------------------------------
        public static function unserial($code): array
        {
            $codeArr = array();

            $codeLines = explode("\n", gzuncompress($code));
            foreach ($codeLines as $codeLine)
                $codeArr[] = str_split($codeLine);

            return $codeArr;
        }

        //----------------------------------------------------------------------
        public function makeMaskNo(string $maskNo, string $width, $s, &$d, $maskGenOnly = false): ?int 
        {
            $b = 0;
            $bitMask = array();

            $fileName = QR_CACHE_DIR.'mask_'.$maskNo.DIRECTORY_SEPARATOR.'mask_'.$width.'_'.$maskNo.'.dat';

            if (QR_CACHEABLE) {
                if (file_exists($fileName)) {
                    $bitMask = self::unserial(file_get_contents($fileName));
                } else {
                    $bitMask = $this->generateMaskNo($maskNo, $width, $s);
                    if (!file_exists(QR_CACHE_DIR.'mask_'.$maskNo)) {
                        mkdir(QR_CACHE_DIR.'mask_'.$maskNo);
                    }

                    file_put_contents($fileName, self::serial($bitMask));
                }
            } else {
                $bitMask = $this->generateMaskNo($maskNo, $width, $s);
            }

            if ($maskGenOnly) {
                return null;
            }

            $d = $s;

            for($y=0; $y<$width; $y++) {
                for($x=0; $x<$width; $x++) {
                    if($bitMask[$y][$x] == 1) {
                        $d[$y][$x] = chr(ord($s[$y][$x]) ^ (int)$bitMask[$y][$x]);
                    }

                    $b += ord($d[$y][$x]) & 1;
                }
            }

            return $b;
        }

        //----------------------------------------------------------------------
        public function makeMask($width, $frame, $maskNo, $level)
        {
            $masked = array_fill(0, $width, str_repeat("\0", $width));
            $this->makeMaskNo($maskNo, $width, $frame, $masked);
            $this->writeFormatInformation($width, $masked, $maskNo, $level);

            return $masked;
        }

        //----------------------------------------------------------------------
        public function calcN1N3($length): int|float
        {
            $demerit = 0;

            for($i=0; $i<$length; $i++) {

                if($this->runLength[$i] >= 5) {
                    $demerit += (N1 + ($this->runLength[$i] - 5));
                }

                if (($i & 1) !== 0 && (($i >= 3) && ($i < ($length-2)) && ($this->runLength[$i] % 3 == 0))) {
                    $fact = (int)($this->runLength[$i] / 3);
                    if(($this->runLength[$i-2] == $fact) &&
                       ($this->runLength[$i-1] == $fact) &&
                       ($this->runLength[$i+1] == $fact) &&
                       ($this->runLength[$i+2] == $fact)) {
                        if (($this->runLength[$i-3] < 0) || ($this->runLength[$i-3] >= (4 * $fact))) {
                            $demerit += N3;
                        } elseif ((($i+3) >= $length) || ($this->runLength[$i+3] >= (4 * $fact))) {
                            $demerit += N3;
                        }
                    }
                }
            }

            return $demerit;
        }

        //----------------------------------------------------------------------
        public function evaluateSymbol($width, $frame): int|float
        {
            $head = 0;
            $demerit = 0;

            for($y=0; $y<$width; $y++) {
                $head = 0;
                $this->runLength[0] = 1;

                $frameY = $frame[$y];

                if ($y>0) {
                    $frameYM = $frame[$y-1];
                }

                for($x=0; $x<$width; $x++) {
                    if(($x > 0) && ($y > 0)) {
                        $b22 = ord($frameY[$x]) & ord($frameY[$x-1]) & ord($frameYM[$x]) & ord($frameYM[$x-1]);
                        $w22 = ord($frameY[$x]) | ord($frameY[$x-1]) | ord($frameYM[$x]) | ord($frameYM[$x-1]);

                        if((($b22 | ($w22 ^ 1))&1) !== 0) {                                                                     
                            $demerit += N2;
                        }
                    }

                    if (($x == 0) && (ord($frameY[$x]) & 1)) {
                        $this->runLength[0] = -1;
                        $head = 1;
                        $this->runLength[$head] = 1;
                    } elseif ($x > 0) {
                        if(((ord($frameY[$x]) ^ ord($frameY[$x-1])) & 1) !== 0) {
                            $head++;
                            $this->runLength[$head] = 1;
                        } else {
                            $this->runLength[$head]++;
                        }
                    }
                }

                $demerit += $this->calcN1N3($head+1);
            }

            for($x=0; $x<$width; $x++) {
                $head = 0;
                $this->runLength[0] = 1;

                for($y=0; $y<$width; $y++) {
                    if ($y == 0 && (ord($frame[$y][$x]) & 1)) {
                        $this->runLength[0] = -1;
                        $head = 1;
                        $this->runLength[$head] = 1;
                    } elseif ($y > 0) {
                        if(((ord($frame[$y][$x]) ^ ord($frame[$y-1][$x])) & 1) !== 0) {
                            $head++;
                            $this->runLength[$head] = 1;
                        } else {
                            $this->runLength[$head]++;
                        }
                    }
                }

                $demerit += $this->calcN1N3($head+1);
            }

            return $demerit;
        }


        //----------------------------------------------------------------------
        public function mask($width, $frame, $level)
        {
            $minDemerit = PHP_INT_MAX;
            $bestMaskNum = 0;
            $bestMask = array();

            $checked_masks = array(0,1,2,3,4,5,6,7);

            if (QR_FIND_FROM_RANDOM !== false) {

                $howManuOut = 8-(QR_FIND_FROM_RANDOM % 9);
                for ($i = 0; $i <  $howManuOut; $i++) {
                    $remPos = rand (0, count($checked_masks)-1);
                    unset($checked_masks[$remPos]);
                    $checked_masks = array_values($checked_masks);
                }

            }

            $bestMask = $frame;

            foreach($checked_masks as $checked_mask) {
                $mask = array_fill(0, $width, str_repeat("\0", $width));

                $demerit = 0;
                $blacks  = $this->makeMaskNo($checked_mask, $width, $frame, $mask);
                $blacks += $this->writeFormatInformation($width, $mask, $checked_mask, $level);
                $blacks  = (int)(100 * $blacks / ($width * $width));
                $demerit = (int)(abs($blacks - 50) / 5) * N4;
                $demerit += $this->evaluateSymbol($width, $mask);

                if($demerit < $minDemerit) {
                    $minDemerit = $demerit;
                    $bestMask = $mask;
                    $bestMaskNum = $checked_mask;
                }
            }

            return $bestMask;
        }

        //----------------------------------------------------------------------
    }
