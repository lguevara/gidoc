<?php

class Gidoc_Model_DestinoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Destino';

    public function getByDocumentoId($id) {
        return $this->getDbTable()->fetchAll("documento_id = $id")->current();        
    }
    
    
}