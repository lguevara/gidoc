<?php

class Usuarios_Form_Cargo extends MyZend_Generic_Form
{

    public function getFrmEdita($usua_id, $forma = 'N', $id = 0)
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $padre_id = $this->createElement('hidden', 'padre_id'); /* En el Controller asigno el valor */

        if($forma == 'N'){
            /* En el combo muestro solo las oficinas a las cuales el usuario no está asignado */
          $sql = "
                SELECT id,nombre 
                FROM dependencias
                WHERE tipo = 1 AND id NOT IN (
                    SELECT dependencia_id 
                    FROM cargos 
                    WHERE usua_id = $usua_id 
                    UNION ALL  
                    SELECT dependencia_id FROM usuarios WHERE id = $usua_id 
                )    
                ORDER BY nombre";
        } else {
            /* En el combo muestro solo las oficinas a las cuales el usuario no está asignado y la oficina del cargo actual que se intenta editar */
          $sql = "
                SELECT id,nombre 
                FROM dependencias
                WHERE tipo = 1 AND id NOT IN (
                    SELECT dependencia_id 
                    FROM cargos 
                    WHERE usua_id = $usua_id AND dependencia_id <> (SELECT dependencia_id FROM cargos WHERE id = $id) 
                    UNION ALL  
                    SELECT dependencia_id FROM usuarios WHERE id = $usua_id 
                )    
                ORDER BY nombre";
        }
        
        
        $selectDependencia = $this->createElement('select','dependencia_id');
        $selectDependencia->setLabel('Oficina')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs($sql));

        $cargo = $this->createElement('text', 'cargo');
        $cargo->setAttribs(array('size' => 40,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Cargo');
        
        // Agregar los elementos al form:
        $this->addElement($padre_id)
             ->addElement($selectDependencia)
             ->addElement($cargo)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}