<?php

class Usuarios_Model_CargoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Usuarios_Model_DbTable_Cargo';
    
    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'cargos'),'a.*')
               ->joinLeft(array('b' => 'dependencias'),'b.id = a.dependencia_id',array('oficina' => "b.nombre"));

        $this->aplicaParamSelect($paramSelect, $select);
        
        return $select;
    }
    

}