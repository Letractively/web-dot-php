<?php
/*
$Id$

Class: GoogleChart

    PHP wrapper for Google Chart API

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
class GoogleChart {

    protected $size;
    protected $data;
    protected $type;
    protected $encoding;
    protected $maxValue;

    public function __construct($size, $data, $type, $encoding = 'simple', $maxValue = null) {
        $this->size = $size;
        $this->data = $data;
        $this->type = $type;
        $this->encoding = $encoding;
        $this->maxValue = $maxValue;
    }

    public function buildUrl() {

        $encodedData = self::encode($this->data, $this->encoding, $this->maxValue);

        $url = 'http://chart.apis.google.com/chart?chs=%s&chd=%s&cht=%s';

        return sprintf($url, $this->size, $encodedData, $this->type);
    }

    public static function encode($data, $encoding = 'simple', $maxValue = null) {

        switch ($encoding) {
            case 'simple':
                return self::encodeSimple($data, $maxValue);
                break;
            case 'text':
                return self::encodeText($data, $maxValue);
                break;
            case 'extended':
                return self::encodeExtended($data, $maxValue);
                break;
        }
    }

    public static function encodeSimple($data, $maxValue = null) {

        $chars = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');

        $size = count($data);

        $encodedData = 's:';

        if ($size > 0) {
            if (is_array($data[0])) {

                if (is_int($maxValue) !== true) {

                    $mx = array();

                    for($i=0; $i < $size; $i++) {
                        $mx[$i] = max($data[$i]);                                                
                    }

                    $maxValue = max($mx); 
                }

                for($i=0; $i < $size; $i++) {

                    if ($i !== 0) {
                        $encodedData .= ',';
                    }
                
                    $dt = $data[$i];
                    $sz = count($dt);

                    for ($j=0; $j < $sz; $j++) {

                        if (is_int($dt[$j])) {
                            $encodedData .= $chars[round(61 * $dt[$j] / $maxValue)];
                        } else {
                            $encodedData .= '_';
                        }
                    }
                }

            } else {

                $maxValue = (is_int($maxValue) === true) ? $maxValue : max($data);

                for($i=0; $i < $size; $i++) {

                    if (is_int($data[$i])) {
                        $encodedData .=  $chars[round(61 * $data[$i] / $maxValue)];
                    } else {
                        $encodedData .= '_';
                    }
                }

            }
        } else {
            $encodedData = '';
        }

        return $encodedData;
    }

    public static function encodeText($data) {
    }

    public static function encodeExtended($data) {
    }

    

}