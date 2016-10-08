<?php

class Usuarios_Form_Usuario extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $nombres = $this->createElement('text', 'nombres');
        $nombres->setAttribs(array('size' => 40,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombres');
        
        $apellidos = $this->createElement('text', 'apellidos');
        $apellidos->setAttribs(array('size' => 40,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Apellidos');

        $iniciales = $this->createElement('text', 'iniciales');
        $iniciales->setAttribs(array('size' => 20,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Iniciales');
        
        $email = $this->createElement('text','email');
        $email->setAttribs(array('size' => 40,
                                 'class' => 'email'
                                     ))
                    ->setLabel('Email');

        $selectDependencia = $this->createElement('select','dependencia_id');
        $selectDependencia->setLabel('Oficina')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,nombre 
                                                          FROM dependencias
                                                          WHERE tipo = 1
                                                          ORDER BY nombre'));

        $cargo = $this->createElement('text', 'cargo');
        $cargo->setAttribs(array('size' => 40,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Cargo');
        
        $usuario = $this->createElement('text','usuario');
        $usuario->setAttribs(array('size' => 30,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Usuario');

        $clave = $this->createElement('password','clave');
        $clave->setRenderPassword(true); // Para que al editar se pemrmita que se llene el valor en el campo
        $clave->setAttribs(array('size' => 30,
                                   'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Clave');

        $selectRol = $this->createElement('select','rol_id');
        $selectRol->setLabel('Rol')
                  ->setAttribs(array('class' => 'required'))                
                  ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                  ->fetchPairs('SELECT id,nombre 
                  FROM roles
                  ORDER BY nombre'));
        
       $selectRol->addMultiOption('', '--- Seleccione Rol ---');
       $selectRol->setValue('');
        
        $this->addElement($nombres)
             ->addElement($apellidos)
             ->addElement($iniciales)   
             ->addElement($email)
             ->addElement($selectDependencia)   
             ->addElement($cargo)   
             ->addElement($usuario)
             ->addElement($clave)
             ->addElement($selectRol)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));
        
        return $this;
    }
 
}

