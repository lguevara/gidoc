<?php

class Gidoc_Model_DocumentoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Documento';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'documentos'),'a.*')
               ->joinLeft(array('b' => 'dependencias'),'b.id = a.dependencia_id',array('dependencia_origen' => "b.nombre"))
               ->joinLeft(array('c' => 'tipos_documentos'),'c.id = a.tipo_documento_id',
                          array('documento' => new Zend_Db_Expr("c.descripcion_corta || ' ' || 
                                                   LPAD(a.numero::TEXT,6,'0') ||
                                                   '-' || date_part('year'::text, a.fecha_documento) ||
                                                    a.siglas")))
               ->joinLeft(array('d' => 'tablas'),'d.id = a.clasificacion_id',array('clasificacion' => "d.descripcion"));
        
        $this->aplicaParamSelect($paramSelect, $select);

        return $select;
    }
    
    public function getDocumentoById($id) {

        $paramSelect = array('where' => "a.id = $id");
        $select = $this->getSelect($paramSelect);

        $rs = $this->getDbTable()->fetchAll($select)->current();
        return $rs;
    }

    public function getDocumentoByExpdte($expdte_id, $secuencia) {

        $rs = $this->getDbTable()->fetchRow("expediente_id = $expdte_id AND secuencia = $secuencia");

        /*
        $select = $this->getDbTable()->select();
        $rs = $this->getDbTable()->fetchAll(
                $select->where("expediente_id = $expdte_id AND secuencia = $secuencia")                
                );        
*/
        return $rs;
    }
    
    
    /**
     * Obtiene el primer documento o documento inicial del expediente.
     * 
     * @param integer $id del expediente
     * @return recordsource
     */
    public function getDocumentoInicialDeExpdte($id) {

        $paramSelect = array('where' => "a.expediente_id = $id");
        $select = $this->getSelect($paramSelect);

        $rs = $this->getDbTable()->fetchAll($select)->toArray();
        return $rs;
    }
    
    
    public function firmarDocumento($documento_id, $firmante_id) {
        
        $db = $this->getDbTable()->getAdapter();  
        $query = "
            select firmardocumento($documento_id, $firmante_id, 20, 0)
        ";
        
        $db->fetchOne($query);
        
    }
    

    public function deshacerFirma($documento_id, $firmante_id, $tipoFirma = 0, $documento_visto_id = 0) {
        
        $db = $this->getDbTable()->getAdapter();  
        $query = "
            select deshacerfirma($documento_id, $firmante_id, $tipoFirma, $documento_visto_id)
        ";
        
        $db->fetchOne($query);
        
    }
    
    public function permiteVerDocumento($documento_id, $usuario_id) {

        $db = $this->getDbTable()->getAdapter();  
        $query = "
            select permiteverdocumento($documento_id, $usuario_id)
        ";
        $ret = $db->fetchOne($query);
        
        return $ret;
        
        
    }

    /**
     * 
     * @param type $nombre del grupo a guardar 
     * @param type $usuario_id Id del usuario que será dueño del grupo 
     */
    public function guardarGrupoDerivacion($nombre, $usuario_id) {
        
        $db = $this->getDbTable()->getAdapter();  
        $query = "
            select guardar_grupo_derivacion($nombre, $usuario_id)
        ";
        
        $db->fetchOne($query);
        
    }
    
    public function grabaDerivacionesDeGrupo($destino_id, $usuario_id, $acciones = '', $movimientoprocesado_id = 0) {
        
        $db = $this->getDbTable()->getAdapter();  
        $query = "
            select graba_derivaciones_de_grupo($destino_id, $usuario_id, '$acciones', $movimientoprocesado_id)
        ";
        
        $db->fetchOne($query);
        
    }
    
    
}