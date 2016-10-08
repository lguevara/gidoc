<?php

class Gidoc_Model_DerivotemporalMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Derivostemporales';
    
    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'derivostemporales'),'a.*')
               ->joinLeft(array('b' => 'dependencias'),'b.id = a.dependenciadestino_id',array('oficina_destino' => "b.nombre"))
               ->joinLeft(array('j' => 'usuarios'),'j.id = a.usuariodestino_id',array('usuario_destino' => new Zend_Db_Expr("j.nombres || ' ' || j.apellidos")))
               ;
        
        $this->aplicaParamSelect($paramSelect, $select);
        
        return $select;
    }
    
}