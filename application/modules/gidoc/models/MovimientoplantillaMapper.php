<?php

class Gidoc_Model_MovimientoplantillaMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Movimientoplantilla';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'movimientosplantillas'),'a.*')
               ->joinLeft(array('b' => 'documentosplantillas'),'b.id = a.documento_id',array('nombre','asunto','archivo','origen'))
               ->joinLeft(array('c' => 'dependencias'),'c.id = b.dependencia_id',array('dependencia_origen' => "c.nombre"))
               ->joinLeft(array('d' => 'tipos_documentos'),'d.id = b.tipo_documento_id',array('documento' => "d.descripcion"))
               ->joinLeft(array('f' => 'dependencias'),'f.id = a.dependencia_id',array('oficina_registra' => "f.nombre"))
               ->joinLeft(array('h' => 'tablas'),'h.id = a.tipomovimiento_id',array('movimiento' => "h.descripcion"));

        
        $this->aplicaParamSelect($paramSelect, $select);

        return $select;
    }
    
    /**
     * Función que devuelve el Id del Movimiento donde ha participado un usuario 
     * en el trámite de un documento.
     * 
     * @param type $usuario_id
     * @param type $documento_id 
     */
    public function getMovIdForUsuario($usuario_id,$documento_id) {

        $db = $this->getDbTable()->getAdapter();  
        $query = "
            SELECT id FROM movimientos WHERE documento_id = $documento_id AND usuario_id = $usuario_id LIMIT 1
        ";
        
        $movimiento_id = $db->fetchOne($query);
        
        return $movimiento_id;
        
    }

    
    
}