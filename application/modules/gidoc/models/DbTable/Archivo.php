<?php

class Gidoc_Model_DbTable_Archivo extends Zend_Db_Table_Abstract
{

    protected $_name = 'archivos';

    public function __toString()
    {
        return __CLASS__;
    }

}