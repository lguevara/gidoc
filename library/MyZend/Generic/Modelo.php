<?php
/*
 * Modelo Genérico
 */

abstract class MyZend_Generic_Modelo extends Zend_Db_Table
{

    public function getSelect($paramSelect){

        /* Armo el select */
        $select = $this->select();
        $select->from($this->_name);

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
        $rs = $this->fetchAll($select);
        return $rs;
    }

    public function CountXXX($where = '') {
        $select = $this->select();
        $select = $select->from($this->_name, array("count" => "COUNT(*)"));

        if($where) {
            $select->where($where);
        }
        return $this->getAdapter()->fetchOne($select->__toString());
    }

    public function Count($where = '') {
        $select = $this->getSelect(array('where' => $where));
        return $select->query()->rowCount();
    }

}