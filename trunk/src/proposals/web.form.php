<?php
class form extends ArrayObject {
    private $filters;
    private $value;
    function __construct($args = null) {
        if ($args == null) return;
        foreach ($args as $name => $value) {
            if (is_array($value) && !isset($value[0][0])) {
                $this[$name] = new self($value);
            } else {
                $this[$name] = new self;
                $this[$name]->value = $value;
            }
        }
    }
    function __set($name, $value) {
        if ($name == 'filters') {
            $this->filters = is_string($value) ? explode(',', $value) : (array) $value;
        } else {
            if (!isset($this[$name])) $this[$name] = new self;
            $this[$name]['value'] = $value;
        }
    }
    function __get($name) {
        if (!isset($this[$name])) $this[$name] = new self;
        $field = $this[$name];
        return $field;
    }
    function __invoke($default = null) {
        echo $this->value ?: $default ?: '';
    }
    function __toString() {
        return $this->value ?: '';
    }
    function validate() {
        if ($this->filters == null) return true;
        $validates = true;
        foreach ($this->filters as $filter) {
            switch (trim($filter)) {
                // filters
                case 'trim':  $this->value = trim($this->value); break;
                case 'ltrim': $this->value = ltrim($this->value); break;
                case 'rtrim':
                case 'chop':  $this->value = rtrim($this->value); break;
                // validators
                case 'required':
                case 'req':   $validates = strlen($this->value) > 0; break;
                case 'boolean':
                case 'bool':  $validates = false !== filter_var($this->value, FILTER_VALIDATE_BOOLEAN); break;
                case 'integer':
                case 'int':   $validates = false !== filter_var($this->value, FILTER_VALIDATE_INT); break;
                case 'float': $validates = false !== filter_var($this->value, FILTER_VALIDATE_FLOAT); break;
                case 'ip':    $validates = false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6); break;
                case 'ipv4':  $validates = false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); break;
                case 'ipv6':  $validates = false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6); break;
                case 'email': $validates = false !== filter_var($this->value, FILTER_VALIDATE_EMAIL); break;
                case 'url':   $validates = false !== filter_var($this->value, FILTER_VALIDATE_URL); break;
                default:
                    if ((strpos($filter, '/')) === 0) {
                        $validates = preg_match($filter, $this->value);
                    } elseif (is_callable($filter)) {
                        $validates = $filter($this->value);
                    }
                    break;
            }
            if (!$validates) return false;
        }
        return true;
    }
}