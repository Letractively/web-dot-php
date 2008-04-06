<?php
/**
$Id$

Class: Body

    View body implementation, wraps content and attributes of a view to
    a layout variable.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class Body implements ArrayAccess {
    
    protected $content;
    protected $attributes;

    /**
    Function __construct
     */
    public function __construct() {
        $this->content = '';
        $this->attributes = array();
    }

    /**
    Function __toString
     */
    public function __toString() {
        return $this->content;
    }

    /**
    function offsetExists
     */
    public function offsetExists($offset) {
        return (isset($this->attributes[$offset]));
    }


    /**
    Function offsetGet
     */
    public function offsetGet($offset) {
        return $this->attributes[$offset];
    }

    /**
    Function offsetSet
     */
    public function offsetSet($offset, $value) {
        if ($offset) {
            $this->attributes[$offset] = $value;
        } else {
            $this->content = $value;
        }
    }

    /**
    Function offsetUnset
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->attributes[$offset]);
        }
    }
}
