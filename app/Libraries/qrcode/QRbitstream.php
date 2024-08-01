<?php

namespace App\Libraries\qrcode;

/*
 * PHP QR Code encoder
 *
 * Bitstream class
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
     
    class QRbitstream {
    
        public $data = array();
        
        //----------------------------------------------------------------------
        public function size(): int
        {
            return count($this->data);
        }
        
        //----------------------------------------------------------------------
        public function allocate($setLength): int
        {
            $this->data = array_fill(0, $setLength, 0);
            return 0;
        }
    
        //----------------------------------------------------------------------
        public static function newFromNum($bits, $num): \QRbitstream
        {
            $qRbitstream = new QRbitstream();
            $qRbitstream->allocate($bits);
            
            $mask = 1 << ($bits - 1);
            for($i=0; $i<$bits; $i++) {
                $qRbitstream->data[$i] = ($num & $mask) !== 0 ? 1 : 0;

                $mask >>= 1;
            }

            return $qRbitstream;
        }
        
        //----------------------------------------------------------------------
        public static function newFromBytes($size, $data): \QRbitstream
        {
            $qRbitstream = new QRbitstream();
            $qRbitstream->allocate($size * 8);

            $p=0;

            for($i=0; $i<$size; $i++) {
                $mask = 0x80;
                for($j=0; $j<8; $j++) {
                    $qRbitstream->data[$p] = ($data[$i] & $mask) !== 0 ? 1 : 0;

                    $p++;
                    $mask >>= 1;
                }
            }

            return $qRbitstream;
        }
        
        //----------------------------------------------------------------------
        public function append(QRbitstream $qRbitstream): int
        {
            if (is_null($qRbitstream)) {
                return -1;
            }
            
            if($qRbitstream->size() == 0) {
                return 0;
            }
            
            if($this->size() == 0) {
                $this->data = $qRbitstream->data;
                return 0;
            }
            
            $this->data = array_values(array_merge($this->data, $qRbitstream->data));

            return 0;
        }
        
        //----------------------------------------------------------------------
        public function appendNum($bits, $num): int
        {
            if ($bits == 0) {
                return 0;
            }

            $qRbitstream = QRbitstream::newFromNum($bits, $num);
            
            if (is_null($qRbitstream)) {
                return -1;
            }

            $ret = $this->append($qRbitstream);
            unset($qRbitstream);

            return $ret;
        }

        //----------------------------------------------------------------------
        public function appendBytes($size, $data): int
        {
            if ($size == 0) {
                return 0;
            }

            $qRbitstream = QRbitstream::newFromBytes($size, $data);
            
            if (is_null($qRbitstream)) {
                return -1;
            }

            $ret = $this->append($qRbitstream);
            unset($qRbitstream);

            return $ret;
        }
        
        //----------------------------------------------------------------------
        /**
         * @return mixed[]
         */
        public function toByte(): array
        {
        
            $size = $this->size();

            if($size == 0) {
                return array();
            }
            
            $data = array_fill(0, (int)(($size + 7) / 8), 0);
            $bytes = (int)($size / 8);

            $p = 0;
            
            for($i=0; $i<$bytes; $i++) {
                $v = 0;
                for($j=0; $j<8; $j++) {
                    $v <<= 1;
                    $v |= $this->data[$p];
                    $p++;
                }

                $data[$i] = $v;
            }
            
            if(($size & 7) !== 0) {
                $v = 0;
                for($j=0; $j<($size & 7); $j++) {
                    $v <<= 1;
                    $v |= $this->data[$p];
                    $p++;
                }

                $data[$bytes] = $v;
            }

            return $data;
        }

    }
