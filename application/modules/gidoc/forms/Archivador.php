<?php

class Gidoc_Form_Archivador extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $nombre = $this->createElement('text', 'nombre');
        $nombre->setAttribs(array('size' => 50,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombre')
                    ->addFilter('StringToUpper');

        
        $periodo = $this->createElement('text', 'periodo');
        $periodo->setAttribs(array('size' => 10,
                                  'class' => 'required number'))
                    ->setLabel('Periodo')
                    ->setValue(date("Y"));

        // Agregar los elementos al form:
        $this->addElement($nombre)
             ->addElement($periodo)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}