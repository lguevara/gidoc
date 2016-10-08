<?php

class Usuarios_Model_PermisoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Usuarios_Model_DbTable_Permiso';

    function delPermisosByRol($rol_id) {
        $this->getDbTable()->delete("rol_id = $rol_id");                
    }

    function getPermisosByRol($rol_id) {
        $select = $this->getDbTable()->select()
                                     ->from($this->getDbTable(), array('recurso'))
                                     ->where("rol_id = $rol_id");
        
        return $this->getDbTable()->fetchAll($select)->toArray();
        
    }
    
}