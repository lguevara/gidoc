<?php

class Gidoc_Model_DbTable_Movimiento extends Zend_Db_Table_Abstract
{

    protected $_name = 'movimientos';

    public function __toString()
    {
        return __CLASS__;
    }

}