<?php

class Usuarios_Model_Usuario extends MyZend_Generic_Model
{
    protected $_id;
    protected $_nombres;
    protected $_apellidos;
    protected $_iniciales;
    protected $_email;
    protected $_dependencia_id;
    protected $_cargo;    
    protected $_usuario;    
    protected $_clave;    
    protected $_rol_id;    
    protected $_fecha_registro;
    protected $_usuario_id;

    public function getId() {
        return $this->_id;
    }

    public function setId($_id) {
        $this->_id = $_id;
    }

    public function getNombres() {
        return $this->_nombres;
    }

    public function setNombres($_nombres) {
        $this->_nombres = $_nombres;
    }

    public function getApellidos() {
        return $this->_apellidos;
    }

    public function setApellidos($_apellidos) {
        $this->_apellidos = $_apellidos;
    }

    public function getIniciales() {
        return $this->_iniciales;
    }

    public function setIniciales($iniciales) {
        $this->_iniciales = $iniciales;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function setEmail($_email) {
        $this->_email = $_email;
    }

    public function getDependencia_id() {
        return $this->_dependencia_id;
    }

    public function setDependencia_id($dependencia_id) {
        $this->_dependencia_id = $dependencia_id;
    }

    public function getCargo() {
        return $this->_cargo;
    }

    public function setCargo($cargo) {
        $this->_cargo = $cargo;
    }
    
    public function getUsuario() {
        return $this->_usuario;
    }

    public function setUsuario($_usuario) {
        $this->_usuario = $_usuario;
    }
    
    public function getClave() {
        return $this->_clave;
    }

    public function setClave($_clave) {
        $this->_clave = $_clave;
    }

    public function getRol_id() {
        return $this->_rol_id;
    }

    public function setRol_id($rol_id) {
        $this->_rol_id = $rol_id;
    }

    public function getFecha_registro() {
        return $this->_fecha_registro;
    }

    public function setFecha_registro($_fecha_registro) {
        $this->_fecha_registro = $_fecha_registro;
    }

    public function getUsuario_id() {
        return $this->_usuario_id;
    }

    public function setUsuario_id($_usuario_id) {
        $this->_usuario_id = $_usuario_id;
    }

}