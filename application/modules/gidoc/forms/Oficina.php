<?php

class Gidoc_Form_Oficina extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

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

        $siglasdoc = $this->createElement('text', 'siglasdoc');
        $siglasdoc->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Siglas Documentos')
                    ->addFilter('StringToUpper');

        $selectRepresentante = $this->createElement('select','representante_id');
        $selectRepresentante->setLabel('Representante')
                      ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT id,nombres || ' ' || apellidos AS usuario 
                                                          FROM usuarios
                                                          ORDER BY nombres"));

        $cargo = $this->createElement('text', 'cargo');
        $cargo->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Cargo')
                    ->addFilter('StringToUpper');
        
        
        // Agregar los elementos al form:
        $this->addElement($nombre)
             ->addElement($abreviado)
             ->addElement($siglasdoc)
             ->addElement($selectRepresentante)
             ->addElement($cargo)   
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}