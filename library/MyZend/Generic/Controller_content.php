<?php
/*
 * Controlador Genérico 
 */

abstract class MyZend_Generic_Controller extends Zend_Controller_Action
{
    protected $_translator;
    protected $_config;
    
    public function init()
    {
        parent::init();

        /** Cargo el traductor para los mensajes */
        $this->_translator = Zend_Registry::get('translatorEs');
        /** Seteo el traductor de los mensajes*/
        Zend_Form::setDefaultTranslator($this->_translator);

        /* Cargo los datos del archivo ini */
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->_config = $bootstrap->getResource('AppConfig');

        /* Asigno variables al Layout */
        $this->_helper->layout->assign("nombresistema", $this->_config->general->nombresistema);

	/* Asigno la url base de la aplicación */
        $this->view->baseUrl = $this->_request->getBaseUrl();
        
        /* Asigno la urlDominio de la aplicación */
        $this->view->urlDominio = $this->_config->general->urlDominio;
    }

    /* Permite deshabilitar el auto-renderizado para que la aplicación no busque el archivo de la acción.
     * No se ejecutar el Layout y no se ejecuta la Vista
     *  */
    public function disableAutoRender() {
        $args = $this->getInvokeArgs();
        $args['noViewRenderer'] = true;
        $this->_setInvokeArgs($args);
        $this->_helper->resetHelpers();

        /* También puede hacerse así */
        //$this->_helper->getHelper('layout')->disableLayout(); /* Para que no se ejecute el Layout */
        //$this->_helper->viewRenderer->setNoRender(); /* Para que no se ejecute la vista */

    }

}