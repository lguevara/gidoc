<?php

abstract class MyZend_Generic_ControllerAdmin extends MyZend_Generic_Controller
{
    protected $_usuario;
    
    protected $_modelo;    
    
    public function preDispatch()
    {
        if (!Default_Model_LoginModelo::isLoggedIn()) {
                $this->_forward("index", "login", "default");
        }
        /* Obtengo la instancia del Usuario */
        /* Se puede usar cualquiera de estas 2 líneas */
        //$this->_usuario = Zend_Auth::getInstance()->getIdentity();  /* Obtengo el dato directamente del Zend_Auth */
        $this->_usuario = Default_Model_LoginModelo::getIdentity(); /* Obtengo el dato desde mi modelo que a su vez llama al Zend_Auth */

        /* Obtengo el objeto Appconfig para recoger datos de la tabla de autenticación */
        $appConfig = Zend_Registry::get('appConfig');
        $campoIdUsuario = $appConfig->auth->ColumnId;

        /* Asigno la variable de vista y para el Layout del objeto Usuario */
        //$this->view->usua_idsession = $this->_usuario->id;        
        $this->view->usua_idsession = $this->_usuario->$campoIdUsuario;
        $this->_helper->layout->assign("usuario", $this->_usuario);
   }

    public function eliminarAction()
    {
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        /* Recibo la lista de Ids separado por comas */
        $idList = $this->_getParam('id');

        if ($idList) {
            /* Convierto la lista a un Array */
            $idArray = explode (",", $idList);

            /* Recorro el array eliminando cada Id */
            foreach ($idArray as $id) {
                try {
                    $resultado = $this->_modelo->eliminar($id, $this->_usuario);
                    
                    if($resultado == 0) { /* Si no se eliminó */
                        echo 'error|No está autorizado a eliminar este registro';                        
                        exit;
                    }
                    
                    
                } catch(Exception $ex) {
                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }
            }
        }
    }
    
}