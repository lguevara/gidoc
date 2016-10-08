<?php

class Usuarios_Model_DbTable_Permiso extends Zend_Db_Table_Abstract
{

    protected $_name = 'permisos';

    public function __toString()
    {
        return __CLASS__;
    }

}