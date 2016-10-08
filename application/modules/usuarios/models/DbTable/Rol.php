<?php

class Usuarios_Model_DbTable_Rol extends Zend_Db_Table_Abstract
{

    protected $_name = 'roles';

    public function __toString()
    {
        return __CLASS__;
    }

}