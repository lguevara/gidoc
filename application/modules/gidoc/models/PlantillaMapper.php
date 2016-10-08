<?php

class Gidoc_Model_PlantillaMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Plantilla';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'documentosplantillas'),'a.*')
               ->joinLeft(array('b' => 'dependencias'),'b.id = a.dependencia_id',array('dependencia_origen' => "b.nombre"))
               ->joinLeft(array('c' => 'tipos_documentos'),'c.id = a.tipo_documento_id',array('documento' => "c.descripcion_corta"));
        
        $this->aplicaParamSelect($paramSelect, $select);
        
        return $select;
    }
    
    public function getDocumentoById($id) {

        $paramSelect = array('where' => "a.id = $id");
        $select = $this->getSelect($paramSelect);

        $rs = $this->getDbTable()->fetchAll($select)->current();
        
        return $rs;
    }
    
    
    
}