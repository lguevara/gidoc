<?php

class Usuarios_Model_DbTable_Cargo extends Zend_Db_Table_Abstract
{

    protected $_name = 'cargos';

    public function __toString()
    {
        return __CLASS__;
    }

}