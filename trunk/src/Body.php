<?php
/*
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

    /* =======================================================================
     * Properties
     * ======================================================================= */

    protected $content;
    protected $attributes;

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    public function __construct() {
        $this->content = '';
        $this->attributes = array();
    }

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public function __toString() {
        return $this->content;
    }

    /* =======================================================================
     * Array Access Implementation
     * ======================================================================= */

    public function offsetExists($offset) {
        return (isset($this->attributes[$offset]));
    }

    public function offsetGet($offset) {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value) {
        if($offset){
            $this->attributes[$offset] = $value;
        }
        else{
            $this->content = $value;
        }
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->attributes[$offset]);
        }
    }
}
