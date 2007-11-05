<?php

class Method extends Model {

    public function setTableDefinition()
    {
        $this->hasColumn(
            'name',
            'string',
            50,
            array(
                'notblank' => true,
                'unique' => true
            )
        );


        $this->hasColumn('abbr', 'string', 6, array('notblank', 'unique'));
    }

}