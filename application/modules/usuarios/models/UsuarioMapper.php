<?php

class Usuarios_Model_UsuarioMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Usuarios_Model_DbTable_Usuario';
    protected $_modelClass = 'Usuarios_Model_Usuario';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'usuarios'),'a.*')
               ->joinLeft(array('b' => 'dependencias'),'b.id = a.dependencia_id',array('oficina' => "b.nombre",'oficina_siglasdoc' => 'b.siglasdoc'))
               ->joinLeft(array('c' => 'roles'),'c.id = a.rol_id',array('rol' => "c.nombre"));

        $this->aplicaParamSelect($paramSelect, $select);
        
        return $select;
    }
 
    public function getUsuario($id){
        
        
        $where = "a.id = $id";
        $paramSelect = array('where' => $where);
        $select = $this->getSelect($paramSelect);
        /* Aplico LIMIT para que siempre pueda obtener un solo registro, ya que a veces un usuario puede estar como jefe en 2 oficinas y entonces devuelve 2 registros */
        $select->joinLeft(array('d' => 'dependencias'),'d.representante_id = a.id',array('jefe_de' => "d.nombre"))
               ->limit(1);

        $objRs = $this->getDbTable()->fetchRow($select)->toArray();
        return $objRs;

    }
    
}