<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mapper
 *
 * @author lguevara
 */

abstract class MyZend_Generic_Mapper {

    protected $_dbTableClass;
    protected $_dbTable;

    public function __construct()
    {

        $dbTable = new $this->_dbTableClass();

        if (!$dbTable instanceof Zend_Db_Table_Abstract) {

            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        
        /*Seteo DateStyle para asegurar que la base de datos reciba las fechas en el formato corrsepondiente, aunque en el servidor no esté configurado correctamente */
        $db = $this->getDbTable()->getAdapter();  
        $db->query("SET datestyle = 'ISO, DMY';");
        
    }

    public function setDbTable($dbTable)
    {

        if (is_string($dbTable)) {

            $dbTable = new $dbTable();
        }

        if (!$dbTable instanceof Zend_Db_Table_Abstract) {

            throw new Exception('Invalid table data gateway provided');
        }

        $this->_dbTable = $dbTable;

        return $this;
    }

    public function getDbTable()
    {

        if (null === $this->_dbTable) {

            $this->setDbTable($this->_dbTableClass);
        }

        return $this->_dbTable;
    }

    public function save(array $data)
    {
        if (isset($data['id'])){
            $id = $data['id'];
        }else{
            $id = null;
        }
            
        if ($id === null) {
            $valRetorno = $this->getDbTable()->insert($data); /* retorna el id insertado */
        } else {
            $valRetorno = $this->getDbTable()->update($data, array('id = ?' => $id)); /* Retorna el número de filas actualizadas */
        }
        
        return $valRetorno;
        
    }
    
    
    public function saveEjecuta($obj, array $data)
    {
        if (null === ($id = $obj->getId())) {

            unset($data['id']);

            $valRetorno = $this->getDbTable()->insert($data); /* retorna el id insertado */            

        } else {
            $valRetorno = $this->getDbTable()->update($data, array('id = ?' => $id)); /* Retorna el número de filas actualizadas */            
        }
        
        return $valRetorno;
        
    }

    public function findToArray($id)
    {
        $result = $this->getDbTable()->find($id)->current()->toArray();

        return $result;
    }

    public function getSelect($paramSelect)
    {
        /* Obtengo el nombre de la tabla */
        $info = $this->getDbTable()->info();
        $table = $info['name'];

        /* Armo el select */
        $select = $this->getDbTable()->select();
        $select->from($table);

        $this->aplicaParamSelect($paramSelect, $select);
        return $select;
    }

    public function aplicaParamSelect($paramSelect, $select) {

        /* Recibo los parámetros */
        if(isset($paramSelect['where'])){
            $where = $paramSelect['where'];
            if($where) {
                $select->where($where);
            }
        }

        if(isset($paramSelect['ord'])){
            $ord = $paramSelect['ord'];
            $idx = $paramSelect['idx'];
            if ($idx!='') {
                $select->order($idx.' '.$ord);
            }
        }

        if(isset($paramSelect['limit'])){
            $limit = $paramSelect['limit'];
            $start = $paramSelect['start'];

            $select->limit($limit,$start);
        }

        return $select;

    }

    public function getList($idx = '', $ord = '', $start, $limit, $where = '') {
        
        $paramSelect = array('idx' => $idx,
                            'ord' => $ord,
                            'start' => $start,
                            'limit' => $limit,
                            'where' => $where);
        $select = $this->getSelect($paramSelect);
        $rs = $this->getDbTable()->fetchAll($select);
        return $rs;
    }

    public function eliminarObjeto($obj)
    {
        $id = $obj->getId();
        $objTmp = $this->getDbTable()->find($id)->current();
        if($objTmp) {
            $objTmp->delete();
        }else {
            throw new Exception("No existe Registro");
        }
    }

    public function eliminarXXX($id)
    {
        $objTmp = $this->getDbTable()->find($id)->current();
        if($objTmp) {
            $objTmp->delete();
        }else {
            throw new Exception("No existe Registro");
        }
    }

    public function eliminar($id, $usuario) {

//        $this->tableGateway->delete(array('id' => $id));

        if($usuario->id == 1){ /* Si es un Administrador */ 

            return $this->getDbTable()->delete(array('id = ?' => $id));
        
        } else { /* Si es un operador */

            /* Solo elimina el registro que el usuario_id ha creado */
            return $this->getDbTable()->delete(array('id = ?' => $id, 'usuario_id = ?' => $usuario->id));
            
        }
        
    }

    public function Count($where = '') {
        $select = $this->getSelect(array('where' => $where));
        return $select->query()->rowCount();
    }

    public function getById($id) {
        return $this->getDbTable()->find($id)->current();
    }

}
?>
