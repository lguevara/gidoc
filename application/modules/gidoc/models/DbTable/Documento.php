<?php

class Gidoc_Model_DbTable_Documento extends Zend_Db_Table_Abstract
{

    protected $_name = 'documentos';

    public function __toString()
    {
        return __CLASS__;
    }

}