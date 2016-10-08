<?php

class Gidoc_Model_DbTable_Dependencia extends Zend_Db_Table_Abstract
{

    protected $_name = 'dependencias';

    public function __toString()
    {
        return __CLASS__;
    }

}