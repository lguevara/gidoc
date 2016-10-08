<?php
/**
 * Description of ProyectoModelo
 *
 * @author Administrador
 */

class Usuarios_Model_UsuarioModelo extends MyZend_Generic_Modelo {
    /**
     *
     * @var String
     */
    protected $_name = 'usuarios';
    protected $_primary = 'id';


    public function getSelect($paramSelect){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('a'=> 'usuarios'),'a.*')
               ->joinLeft(array('b' => 'persona'),'a.pers_id = b.pers_id',array('empleado' => new Zend_Db_Expr("b.pers_nombres || ' ' || b.pers_apellpaterno")));

        $where = $paramSelect['where'];
        $select->where($where);

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

    public function getListUsuariosADelegar($depe_id)
    {
        /* Obtengo los delegados de la $depe_id que estén activos */
        $usua_id = $_SESSION['sis_userid'];
        $where = "a.usua_id <> $usua_id AND a.depe_id = $depe_id
                  AND a.usua_id NOT IN (SELECT usua_id_delegado
                                        FROM delegaciones
                                        WHERE estado = 1 AND depe_id = $depe_id)";
        $paramSelect = array('where' => $where);

        $select = $this->getSelect($paramSelect);
        $resultSet = $this->fetchAll($select);

        return $resultSet;
    }


}