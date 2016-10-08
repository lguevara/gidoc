<?php

class Gidoc_Form_Plantilla extends MyZend_Generic_Form
{

    public function getFrmEditaInterno()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $idDestino = $this->createElement('hidden', 'id_destino');
        $idDestino->setDecorators(array('ViewHelper'));

        $nombre = $this->createElement('text', 'nombre');
        $nombre->setAttribs(array('size' => 80,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombre de plantilla')
                    ->addFilter('StringToUpper');

        $selectPara = $this->createElement('select','dependencia_iddestino');
        $selectPara->setLabel('Destinatario')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT a.id, 
                                                                 b.nombres || ' ' || b.apellidos 
                                                          FROM dependencias a 
                                                          LEFT JOIN usuarios b ON b.id = a.representante_id 
                                                          WHERE tipo = 1 AND representante_id IS NOT NULL 
                                                          ORDER BY nombre"));
        $selectPara->setAttribs(array('class' => 'required'));
       $selectPara->addMultiOption('', '--- Seleccione Destinatario ---');        
       $selectPara->setValue('');        

        $para = $this->createElement('text', 'para_destino');
        $para->setAttribs(array('size' => 60))
                    ->setLabel('Para')
                    ->addFilter('StringToUpper');

        $cargo = $this->createElement('text', 'cargo_destino');
        $cargo->setAttribs(array('size' => 60))
                    ->setLabel('Cargo')
                    ->addFilter('StringToUpper');

        $dependencia = $this->createElement('text', 'dependencia_destino');
        $dependencia->setAttribs(array('size' => 60))
                    ->setLabel('Dependencia')
                    ->addFilter('StringToUpper');
        
        $selectTipoDocumento = $this->createElement('select','tipo_documento_id');
        $selectTipoDocumento->setLabel('Tipo de documento')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,descripcion
                                                          FROM tipos_documentos
                                                          ORDER BY descripcion'));
        $selectTipoDocumento->setAttribs(array('class' => 'required'));
       $selectTipoDocumento->addMultiOption('', '--- Seleccione Tipo de Documento ---');        
       $selectTipoDocumento->setValue('');
        
        $asunto = $this->createElement('textarea', 'asunto');
        $asunto->setAttribs(array('rows' => '2',
                                   'cols' => '60',
                                   'class' => 'required'
                                 ))
                    ->setLabel('Asunto');

        $referencia = $this->createElement('textarea', 'referencia');
        $referencia->setAttribs(array('rows' => '1',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Referencia');

        $cuerpo = $this->createElement('textarea', 'cuerpo');
        $cuerpo->setAttribs(array('rows' => '20',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Cuerpo');
        
        
        // Agregar los elementos al form:
        $this->addElement($idDestino)
             ->addElement($nombre)   
             ->addElement($selectPara)
             ->addElement($para)
             ->addElement($cargo)
             ->addElement($dependencia)   
             ->addElement($selectTipoDocumento)
             ->addElement($asunto)   
             ->addElement($referencia)   
             ->addElement($cuerpo)      
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

    
}