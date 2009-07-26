<?php
class Form {

    private $fields;

    function __construct() {
        $this->fields = array();
    }

    function validate() {
       foreach ($this->fields as $field) if (!$field->validate()) return false;
       return true;
    }

    function __call($name, $args) {
        echo strlen($this->$name) ? $this->$name : (isset($args[0]) ? $args[0] : '');
    }

    function __set($name, $value) {
        $this->$name->value = $value;
    }

    function __get($name) {
        if (!isset($this->fields[$name])) $this->fields[$name] = new FormField($name);
        return $this->fields[$name];
    }
}

class FormField {
    function __construct($name, $value = '') {
        $this->name = $name;
        $this->value = $value;
        $this->filters = array();
    }

    function validate() {

        $validates = true;

        foreach ($this->filters as $filter) {
            switch ($filter) {
                // filters
                case 'trim':  $this->value = trim($this->value); break;
                case 'ltrim': $this->value = ltrim($this->value); break;
                case 'rtrim':
                case 'chop':  $this->value = rtrim($this->value); break;
                // validators
                case 'req':   $validates = strlen($this->value) > 0; break;
                case 'bool':  $validates = filter_var($this->value, FILTER_VALIDATE_BOOLEAN); break;
                case 'int':   $validates = filter_var($this->value, FILTER_VALIDATE_INT); break;
                case 'float': $validates = filter_var($this->value, FILTER_VALIDATE_FLOAT); break;
                case 'ip':    $validates = filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6); break;
                case 'ipv4':  $validates = filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); break;
                case 'ipv6':  $validates = filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6); break;
                case 'email': $validates = filter_var($this->value, FILTER_VALIDATE_EMAIL); break;
                case 'url':   $validates = filter_var($this->value, FILTER_VALIDATE_URL); break;
                default:
                    // regex validator
                    if ((strpos($filter, '/')) === 0 || (strpos($filter, '#') === 0)) {
                        $validates = preg_match($filter, $this->value);
                    }
                    break;
            }

            if (!$validates) return false;
        }

        return true;
    }

    function __toString() {
        return $this->value;
    }
}