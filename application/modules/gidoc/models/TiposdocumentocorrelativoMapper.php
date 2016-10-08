<?php

class Gidoc_Model_TiposdocumentocorrelativoMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Tiposdocumentocorrelativo';


    /*
     * Obtengo el número correlativo según el tipo de documento.
     * 
     */
    public function getNumeroCorrelativo($operacion, $documento_tipo, $tipo_documento_id, 
                                        $dependencia_id, $usuario_id, $periodo, $numero = 0){

        
        $db = $this->getDbTable()->getAdapter();  
        $query = "
            select obtener_correlativo($operacion, $documento_tipo, $tipo_documento_id, $dependencia_id, $usuario_id, $periodo, $numero)
        ";
        
        $numero = $db->fetchOne($query);
        
        return $numero;
        
    }
    
}