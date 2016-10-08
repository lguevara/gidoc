<?php

class Usuarios_Form_Rol extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $nombre = $this->createElement('text', 'nombre');
        $nombre->setAttribs(array('size' => 50,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombre');

        // Agregar los elementos al form:
        $this->addElement($nombre)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}