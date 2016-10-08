<?php

class Default_Model_DbTable_Empresa extends Zend_Db_Table_Abstract
{

    protected $_name = 'empresa';

    public function __toString()
    {
        return __CLASS__;
    }

}