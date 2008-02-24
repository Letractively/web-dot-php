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

    private static $encodingChars = array(
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        '0','1','2','3','4','5','6','7','8','9','-','.'); 

    protected $size;
    protected $data;
    protected $colors;
    protected $type;
    protected $encoding;
    protected $maxValue;
    protected $maxValueAuto;
    protected $maxValueAutoEnabled;

    public function __construct($size, $type, $encoding = 'simple') {

        $xy = split('x', $size, 2);

        if (count($xy) == 2) {

            if (!ctype_digit($xy[0])) {
                throw new Exception("Invalid Google Chart size (width): '" . $xy[0] . "' (specify size as WxH, for example '300x120').");
            }

            if (!ctype_digit($xy[1])) {
                throw new Exception("Invalid Google Chart size (height): '" . $xy[1] . "' (specify size as WxH, for example '300x120').");
            }

            if (intval($xy[0]) * intval($xy[1]) > 300000) {
                throw new Exception("Invalid Google Chart size (too big): '" . $xy[1] . "' (maximum size is 300,000 pixels).");
            }

        } else {
            throw new Exception("Invalid Google Chart size: '" . $size . "' (specify size as WxH, for example ''300x120'').");
        }

        if ($type != 'lc'  &&
            $type != 'lxy' &&
            $type != 'bhs' &&
            $type != 'bhs' &&
            $type != 'bvs' &&
            $type != 'bvg' &&
            $type != 'p'   &&
            $type != 'p3'  &&
            $type != 'v'   &&
            $type != 't') {

            throw new Exception("Invalid Google Chart type: '" . $type . "' (possible values are 'lc', 'lxy', 'bhs', 'bhg', 'bvs', 'bvg', 'p', 'p3', 'v', and 't').");
        }

        if ($encoding == 'simple') {
            $this->maxValue = 61;
        } else if ($encoding == 'text') {
            $this->maxValue = 100;
        } else if ($encoding == 'extended') {
            $this->maxValue = 4095;
        } else {
            throw new Exception("Invalid Google Chart encoding: '" . $encoding . "' (possible values are 'simple', 'text', and 'extended').");
        }
        $this->maxValueAutoEnabled = false;
        $this->size = $size;
        $this->type = $type;
        $this->encoding = $encoding;
        $this->data = array();
        $this->colors = array();
    }

    public function addData($data) {
        $this->data[] = $data;

        $maxValue = max($data);

        if ($maxValue > $this->maxValueAuto) {
            $this->maxValueAuto = $maxValue;            
        }

        return $this;
    }

    public function addColor($color) {
        if (ctype_xdigit($color) && (strlen($color) == 6 || strlen($color) == 8)) {

            $this->colors[] = $color;
        } else {
            throw new Exception("Invalid Google Chart data color: '" . $color . "' (specify a color with at a 6-letter string of hexadecimal values in the format RRGGBB. You can optionally specify transparency by appending a value between 00 and FF).");
        }

        return $this;
    }

    public function setMaxValue($value) {
        if ($value == 'automatic') {
            $this->maxValueAutoEnabled = true;
        } else {
            $this->maxValueAutoEnabled = false;
            if ($encoding == 'simple') {
                if (is_int($value) && $value > -1 && $value < 62) {
                    $this->maxValue = $value;
                } else {
                    throw new Exception("Invalid Google Chart maximum value (simple encoding): '" . $value . "' (values between 0 and 61, and 'automatic' are allowed for simple encoded chart maximum value).");
                }
            } else if ($encoding == 'text') {
                if (is_numeric($value) && floatval($value) >= 0 && floatval($value) <= 100) {
                    $this->maxValue = $value;
                } else {
                    throw new Exception("Invalid Google Chart maximum value (text encoding): '" . $value . "' (values between 0.0 and 100.00, and 'automatic' are allowed for text encoded chart maximum value).");
                }
            } else if ($encoding == 'extended') {
                if (is_int($value) && $value > -1 && $value < 4096) {
                    $this->maxValue = $value;
                } else {
                    throw new Exception("Invalid Google Chart maximum value (extended encoding): '" . $value . "' (values between 0 and 4095, and 'automatic' are allowed for extended encoded chart maximum value).");
                }
            }
        }

        return $this;
    }

    public function encode() {

        if (count($this->data) === 0) {
            throw new Exception('Google Chart data was not supplied (use addData-method to add data).');
        }

        $encodedData = '';
        
        switch ($this->encoding) {
            case 'simple':
                $encodedData = 's:';
                foreach ($this->data as $data) {
                    $encodedData .= self::encodeSimple($data, ($this->maxValueAutoEnabled) ? $this->maxValueAuto : $this->maxValue);
                    $encodedData .= ',';
                }
                break;
            case 'text':
                $encodedData = 't:';
                foreach ($this->data as $data) {
                    $encodedData .= self::encodeText($data, ($this->maxValueAutoEnabled) ? $this->maxValueAuto : $this->maxValue);
                    $encodedData .= '|';
                }
                break;
            case 'extended':
                $encodedData = 'e:';
                foreach ($this->data as $data) {
                    $encodedData .= self::encodeExtended($data, ($this->maxValueAutoEnabled) ? $this->maxValueAuto : $this->maxValue);
                    $encodedData .= ',';
                }
                break;
        }

        $encodedData = rtrim($encodedData, ',|');

        $optionalParameters = '';

        if (count($this->colors) > 0) {

            $optionalParameters .= '&chco=';

            foreach ($this->colors as $color) {
                $optionalParameters .= $color;
                $optionalParameters .= ',';
            }

            $optionalParameters = rtrim($optionalParameters, ',');
        }

        return sprintf('http://chart.apis.google.com/chart?chs=%s&chd=%s&cht=%s%s', $this->size, $encodedData, $this->type, $optionalParameters);
    }

    public static function encodeSimple($data, $maxValue = 61) {

        if (is_array($data)) {

            $size = count($data);
            $encodedData = array();

            for ($i=0; $i < $size; $i++) {

                if (is_int($data[$i]) && $data[$i] > -1 && $data[$i] < 62) {

                    $value = ($data[$i] > $maxValue) ? $maxValue : $data[$i];

                    $encodedData[$i] = self::$encodingChars[round($value * 61 / $maxValue)];
                } else {
                    $encodedData[$i] = '_';
                }
            }

            return implode('', $encodedData);
            
        } else if (is_int($data) && $data > -1 && $data < 62) {

            $value = ($data > $maxValue) ? $maxValue : $data;

            return self::$encodingChars[round($value * 61 / $maxValue)];
        } else {
            return '_';
        }
    }

    public static function encodeText($data, $maxValue = 100) {

        if (is_array($data)) {

            $size = count($data);
            $encodedData = array();

            for ($i=0; $i < $size; $i++) {

                if (is_numeric($data[$i]) && floatval($data[$i]) >= 0 && floatval($data[$i]) <= 100) {

                    $value = ($data[$i] > $maxValue) ? $maxValue : $data[$i];

                    $encodedData[$i] = floatval($value * 100 / $maxValue);
                } else {
                    $encodedData[$i] = -1;
                }
            }

            return implode(',', $encodedData);

        } else if (is_numeric($data) && floatval($data) >= 0 && floatval($data) <= 100) {

            $value = ($data > $maxValue) ? $maxValue : $data;

            return floatval($value * 100 / $maxValue);
        } else {
            return -1;
        }
    }

    public static function encodeExtended($data, $maxValue = 4095) {

        if (is_array($data)) {

            $size = count($data);
            $encodedData = array();

            for ($i=0; $i < $size; $i++) {

                if (is_int($data[$i]) && $data[$i] > -1 && $data[$i] < 4096) {

                    $value = ($data[$i] > $maxValue) ? $maxValue : $data[$i];

                    $a = intval(round($value * 4095 / $maxValue) / 64);
                    $b = round($value * 4095 / $maxValue) % 64;

                    $encodedData[$i] = self::$encodingChars[$a] . self::$encodingChars[$b];
                } else {
                    $encodedData[$i] = '__';
                }
            }

            return implode('', $encodedData);
            
        } else if (is_int($data) && $data > -1 && $data < 4096) {

            $value = ($data > $maxValue) ? $maxValue : $data;

            $a = intval(round($value * 4095 / $maxValue) / 64);
            $b = round($value * 4095 / $maxValue) % 64;

            return self::$encodingChars[$a] . self::$encodingChars[$b];

        } else {
            return '__';
        }
    }
}