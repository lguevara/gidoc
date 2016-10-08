<?php

class Default_Model_EmpresaMapper extends MyZend_Generic_Mapper
{
    protected $_dbTableClass = 'Default_Model_DbTable_Empresa';
    protected $_modelClass = 'Default_Model_Empresa';

//    public function save(Default_Model_Empresa $obj)
//    {
//        $data = array(
//            'nombre'     => $obj->getNombre(),
//            'direccion'  => $obj->getDireccion(),
//            'telefonos'  => $obj->getTelefonos(),
//            'email'      => $obj->getEmail(),
//            'imagen'     => $obj->getImagen()
//        );
//
//        $this->saveEjecuta($obj,$data);
//    }

    public function getLastEmpresa($numero = 1)
    {
        $paramSelect = array('ord' => 'desc',
                             'idx' => 'id',
                             'limit' => $numero,
                             'start' => 0);

        $select = $this->getSelect($paramSelect);
        $resultSet = $this->getDbTable()->fetchAll($select);
        
        //Zend_Debug::dump($select);

        /* Creo un array de objetos */
        $entries = array();

        foreach ($resultSet as $row) {
            
            $obj = new Default_Model_Empresa();
            $obj->setId($row->id);
            $obj->setTitulo($row->titulo);
            $obj->setContenido($row->contenido);
            $obj->setImagen($row->imagen);
            
            $entries[] = $obj;
        }

        return $entries;
        
    }

    public function getEmpresa($id)
    {
        $result = $this->findToArray($id);
        
        $obj = new Default_Model_Empresa($result);

        return $obj;

    }
   
    
}