<?php
class Form extends ArrayIterator {

    private $_data;

    public function __construct($data) {
        $this->_data = $data;
    }

    public function __set($name, $filters) {
        $this[$name] = new FormField($name, isset($this->_data[$name]) ? $this->_data[$name] : null, $filters);
    }

    public function __call($name, $args) {
        //if (isset($this[$name])){
        //
        //}
    }

    public function validate() {
        foreach ($this as $name => $field) {
            if (!$field->validate()) return false;
        }
        return true;
    }
}

class FormField extends ArrayIterator {

    public $name;
    public $value;
    public $filters;

    public function __construct($name, $value, $filters) {
        $this->name = $name;
        $this->value = $value;
        $this->filters = $filters;
    }

    public function validate() {

        foreach ($this->filters as $filter)
        {
            switch ($filter) {
                case 'required':
                    if (!Validate::presence($this->value)) return false;
                    break;
                case 'bool':
                    if (!Validate::bool($this->value)) return false;
                    break;
                case 'int':
                    if (!Validate::int($this->value)) return false;
                    break;
                case 'float':
                    if (!Validate::float($this->value)) return false;
                    break;
                case 'ip':
                    if (!Validate::ip($this->value)) return false;
                    break;
                case 'ipv6':
                    if (!Validate::ipv6($this->value)) return false;
                    break;
                case 'email':
                    if (!Validate::email($this->value)) return false;
                    break;
                case 'url':
                    break;
                default:
                    if ((strpos($filter, '/')) === 0 || (strpos($filter, '#') === 0)) {
                        if (!Validate::format($this->value, $filter)) return false;
                    }
                    break;
            }
        }
        return true;
    }
}