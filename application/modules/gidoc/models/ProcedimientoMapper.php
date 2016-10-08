<?php

class Gidoc_Model_ProcedimientoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Procedimiento';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'procedimientos'),'a.*')
               ->joinLeft(array('b' => 'tablas'),'b.id = a.calificacion_id',array('calificacion' => "b.descripcion"));
        
        $this->aplicaParamSelect($paramSelect, $select);
        
        return $select;
    }
    
}