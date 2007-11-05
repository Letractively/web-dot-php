<?php

class Method extends Model {

    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 50, array('notblank', 'unique'));
        $this->hasColumn('abbr', 'string', 6, array('notblank', 'unique'));
    }

}