<?php

class Default_Form_Empresa extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');
        $this->setAttrib('enctype', 'multipart/form-data');

        $nombre = $this->createElement('text', 'nombre');
        $nombre->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombre');

        $direccion = $this->createElement('text', 'direccion');
        $direccion->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Dirección');
        
        $telefonos = $this->createElement('text', 'telefonos');
        $telefonos->setAttribs(array('size' => 60))
                    ->setLabel('Teléfonos');

        $email = $this->createElement('text', 'email');
        $email->setAttribs(array('size' => 60))
                    ->setLabel('Email');


        $divImagen = $this->createElement('hidden', 'foo', 
                array('description' => '<div>
                    <img class=verimg id=imgLogo width=60 height=60 />
                    </div>',
                      'ignore' => true,
                      'decorators' => array(
                          array('Description', 
                              array('escape'=>false, 'tag'=>'')),
            ),
        ));        
        $imagen = $this->createElement('file', 'imagen'); 
        $imagen->setDestination(APPLICATION_PATH . '/../public/uploads');
        $imagen->setLabel('Imágen (jpg,png)');
        $imagen->addValidator('Size', false, 307200); // limit to 300K
        $imagen->setAttribs(array('accept' => 'jpg,png'));
        $imagenHidden = $this->createElement('hidden', 'imagenHidden'); /* Campo oculto para guardar dato que ya existe en el campo.  En el Controller asigno el valor */
        
//        $imagen->setMaxFileSize(102400); // limits the filesize on the client side
        
        // Agregar los elementos al form:
        $this->addElement($nombre)
             ->addElement($direccion)
             ->addElement($telefonos)
             ->addElement($email)   
             ->addElement($divImagen)
             ->addElement($imagen)
             ->addElement($imagenHidden)                   
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

}