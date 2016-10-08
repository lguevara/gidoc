<?php

class Gidoc_Model_MovimientoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Movimiento';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'movimientos'),array('a.*','archivos' => "(select array_to_string(array_agg('<a href=/uploads/' || nombre_archivo || ' target=_blank>' || descripcion || '</a>' ), '<br>') 
                                                                        FROM archivos
                                                                        WHERE documento_id = b.id)"))
               ->joinLeft(array('b' => 'documentos'),'b.id = a.documento_id',array('expediente_id','secuencia','firma','cargo','fecha_documento','numero','siglas','asunto','archivo','origen','tipo','clasificacion_id','doc_dependencia_id' => 'b.dependencia_id','visador','usuario_id_registro' => 'b.usuario_id'))
               ->joinLeft(array('c' => 'dependencias'),'c.id = b.dependencia_id',array('dependencia_origen' => "c.nombre"))
               ->joinLeft(array('d' => 'tipos_documentos'),'d.id = b.tipo_documento_id',
                          array('documento' => new Zend_Db_Expr("d.descripcion_corta || ' ' || LPAD(b.numero::TEXT,6,'0') || COALESCE(b.siglas,'')")))
               ->joinLeft(array('e' => 'archivadores'),'e.id = a.archivador_id',array('archivador' => "e.nombre"))
               ->joinLeft(array('f' => 'dependencias'),'f.id = a.dependencia_id',array('oficina_registra' => "f.nombre"))
               ->joinLeft(array('g' => 'usuarios'),'g.id = a.usuario_id',array('usuario_registra' => "g.nombres"))
               ->joinLeft(array('h' => 'tablas'),'h.id = a.tipomovimiento_id',array('movimiento' => "h.descripcion")) 
               ->joinLeft(array('i' => 'dependencias'),'i.id = a.dependenciadestino_id',array('oficina_destino' => "i.nombre"))
               ->joinLeft(array('j' => 'usuarios'),'j.id = a.usuariodestino_id',array('usuario_destino' => "j.nombres"))
               ->joinLeft(array('r' => 'documentos'),'r.id = a.documento_idadjuntado',array('expediente_idadjuntado' => new Zend_Db_Expr("r.expediente_id || '-' || r.secuencia"))); 
        
        
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