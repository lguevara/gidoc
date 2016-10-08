<?php

class Gidoc_Model_DependenciaMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Gidoc_Model_DbTable_Dependencia';

    public function getSelect($paramSelect){
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'dependencias'),'a.*')
               ->joinLeft(array('b' => 'usuarios'),'b.id = a.representante_id',array('representante' => new Zend_Db_Expr("b.nombres || ' ' || b.apellidos")));
        
        $this->aplicaParamSelect($paramSelect, $select);
        
        return $select;
    }
    
    /***
     * Devuelve los Id de las dependencias donde es jefe el representante_id que recibe 
     */
    public function getDependenciasDondeEsJefe($representante_id){

        $db = $this->getDbTable()->getAdapter();  
        $query = "
            SELECT id FROM dependencias WHERE representante_id = $representante_id 
        ";
        
        $arrDependenciasIds = $db->fetchCol($query);
        
        return $arrDependenciasIds;
        
    }

    /* Para el Select2 de Entidades al registrar documentos externos. */
    public function getForSelectEntidad($id = null, $textSearch) {

        if($id){
            
            $where = "WHERE a.id = $id ";
            
        }  else {
            
            /* Convierto el texto en un array */
            $arrayTextSearch = explode(" ", $textSearch);

            if(count($arrayTextSearch) > 1 and $arrayTextSearch[1]) {

                /* Hago la conversión para buscar cualquier palabra en cualquier parte del texto */
                foreach ($arrayTextSearch as $key => $value) {
                    $arrayTextSearch[$key] = "'%$value%'";
                }
                
                /*Convierto el array en texto */
                $palabras = implode(",", $arrayTextSearch);                    
                
                $where = "WHERE a.tipo = 2 AND a.nombre ilike ALL(array[$palabras]) ";
                
            } else { /* Hago la búsqueda normal */
                
                $where = "WHERE a.tipo = 2 AND a.nombre ILIKE '%$textSearch%' ";
            }
            
        }

        $db = $this->getDbTable()->getAdapter();  
        $select = "
            SELECT a.id AS id, a.nombre AS text 
            FROM dependencias a
            $where
            ORDER BY nombre            
        ";

        $rs = $db->fetchAll($select);
        
        return $rs;
    }
    
    
}