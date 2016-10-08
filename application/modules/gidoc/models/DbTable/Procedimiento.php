<?php

class Gidoc_Model_DbTable_Procedimiento extends Zend_Db_Table_Abstract
{

    protected $_name = 'procedimientos';

    public function __toString()
    {
        return __CLASS__;
    }

}