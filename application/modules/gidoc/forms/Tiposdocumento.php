<?php

class Gidoc_Form_Tiposdocumento extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $descripcion = $this->createElement('text', 'descripcion');
        $descripcion->setAttribs(array('size' => 50,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Descripción')
                    ->addFilter('StringToUpper');

        $descripcion_corta = $this->createElement('text', 'descripcion_corta');
        $descripcion_corta->setAttribs(array('size' => 30))
                    ->setLabel('Descripción corta')
                    ->addFilter('StringToUpper');

        // Agregar los elementos al form:
        $this->addElement($descripcion)
             ->addElement($descripcion_corta)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}