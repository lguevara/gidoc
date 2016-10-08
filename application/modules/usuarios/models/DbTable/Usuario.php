<?php

class Usuarios_Model_DbTable_Usuario extends Zend_Db_Table_Abstract
{

    protected $_name = 'usuarios';

    public function __toString()
    {
        return __CLASS__;
    }

}