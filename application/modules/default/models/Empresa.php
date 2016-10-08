<?php

class Default_Model_Empresa extends MyZend_Generic_Model
{
    protected $_id;
    protected $_nombre;
    protected $_direccion;
    protected $_telefonos;    
    protected $_email;
    protected $_imagen;
    
    public function getId() {
        return $this->_id;
    }

    public function setId($_id) {
        $this->_id = $_id;
    }

    public function getNombre() {
        return $this->_nombre;
    }

    public function setNombre($nombre) {
        $this->_nombre = $nombre;
    }

    public function getDireccion() {
        return $this->_direccion;
    }

    public function setDireccion($direccion) {
        $this->_direccion = $direccion;
    }

    public function getTelefonos() {
        return $this->_telefonos;
    }

    public function setTelefonos($telefonos) {
        $this->_telefonos = $telefonos;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function setEmail($email) {
        $this->_email = $email;
    }

    public function getImagen() {
        return $this->_imagen;
    }

    public function setImagen($_imagen) {
        $this->_imagen = $_imagen;
    }
 
}