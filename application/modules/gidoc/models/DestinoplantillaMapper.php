<?php

class Gidoc_Model_DestinoplantillaMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Destinoplantilla';

    public function getByDocumentoId($id) {
        return $this->getDbTable()->fetchAll("documento_id = $id")->current();        
    }
    
    
}