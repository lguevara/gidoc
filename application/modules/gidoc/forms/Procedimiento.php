<?php

class Gidoc_Form_Procedimiento extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $denominacion = $this->createElement('textarea', 'denominacion');
        $denominacion->setAttribs(array('rows' => '2',
                                   'cols' => '60',
                                   'class' => 'required'
                                 ))
                    ->setLabel('Denominación');
        
        $calificacion_id = $this->createElement('select','calificacion_id');
        $calificacion_id->setLabel('Calificación')
                      ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT id,descripcion 
                                                          FROM tablas 
                                                          WHERE tipo = 3 
                                                          ORDER BY descripcion"));

        $plazo_atencion = $this->createElement('text', 'plazo_atencion');
        $plazo_atencion->setAttribs(array('size' => 10,
                                 'class' => 'required number'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Plazo de atención');
        
        
        $pasos = $this->createElement('textarea', 'pasos');
        $pasos->setAttribs(array('rows' => '3',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Pasos para tramitación');

        $pago_derecho = $this->createElement('text', 'pago_derecho');
        $pago_derecho->setAttribs(array('size' => 10,
                                 'class' => 'required number'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Derecho de tramitación');
        
        // Agregar los elementos al form:
        $this->addElement($denominacion)
             ->addElement($calificacion_id)
             ->addElement($plazo_atencion)
             ->addElement($pasos)
             ->addElement($pago_derecho)   
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}