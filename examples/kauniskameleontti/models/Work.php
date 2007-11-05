<?php

class Work extends Model {

    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 50, array('notblank', 'unique'));
        $this->hasColumn('abbr', 'string', 6, array('notblank', 'unique'));
        $this->hasColumn('tax', 'interger', 2, array('notblank', 'unique', 'min' => 0, 'max' => 100));
    }

}