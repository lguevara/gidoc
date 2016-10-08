<?php

class Gidoc_Form_Entidad extends MyZend_Generic_Form
{

    public function getFrmEdita($idForm)
    {
        $this->setMethod('post');
        $this->setAttrib('id',$idForm);

        $nombre = $this->createElement('text', 'nombre');
        $nombre->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombre')
                    ->addFilter('StringToUpper');

        $abreviado = $this->createElement('text', 'abreviado');
        $abreviado->setAttribs(array('size' => 40,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Abreviado')
                    ->addFilter('StringToUpper');

        // Agregar los elementos al form:
        $this->addElement($nombre)
             ->addElement($abreviado)
             ->addElement('submit', 'guardarEntidad', array('label' => 'Guardar'));

        return $this;
    }

}