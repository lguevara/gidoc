<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initAppConfig()
    {
        /* Retorno los datos del archivo application.ini */

        $fileApplicationIni = 'application_'.MY_DOMINIO.'.ini';    
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/$fileApplicationIni",APPLICATION_ENV);
        Zend_Registry::set('appConfig', $config);
        return $config;
    }

    protected function _initSession()
    {
        Zend_Session::start();
    }    
    
    protected function _initAcl() {

        $acl = new MyZend_Acl($this->_initAppConfig());
        
        /* Obtengo el recurso view */
        $this->bootstrap('view');        
        $view = $this->getResource('view');

        /* Asigno el Acl y Rol al navigation */
        $view->navigation()->setAcl($acl)
                           ->setRole($this->_getRole());

        return $acl;
        
    }
    
    private function _getRole(){

        $auth = Zend_Auth::getInstance();
        
        $role = "Visitante";

        if ($auth->hasIdentity()) {
            if (isset($auth->getIdentity()->rol)) {
                $role = $auth->getIdentity()->rol;
            }
        }
                
        return $role;
    }

    protected function _initView()
    {

        $parametros = $this->getOption('general');
        $siglas = $parametros['siglas'];


        //Inicializa la vista
        $view = new Zend_View();
        $view->doctype('HTML5');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $view->headTitle($siglas);
        
        //Agregamos la vista al ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view);

        /**
         * Retornamos siempre el recurso, en este caso el objeto vista
         * De esta manera el recurso puede ser almacenado en el contenedor
         * del Boostrap
         */
        return $view;
    }

    /* Para añadir helpers */
    protected function _initViewHelpers()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->addHelperPath('Noumenal/View/Helper','Noumenal_View_Helper');
        $view->addHelperPath('MyZend/View/Helper','MyZend_View_Helper');

    }
    
    /**
     *
     * Inicializo el Traductor y lo guardo en el Registro para
     * tenerlo cuando lo necesite
     *
     * @return Translator
     */
    protected function _initTranslatorEs()
    {
        $languagePath = APPLICATION_PATH . "/languages/es.php";
        $translator = new Zend_Translate('array', $languagePath, 'es');
        Zend_Registry::set('translatorEs', $translator);
        return $translator;
    }

    /**
     * Para inicializar el plugin ZFDebug 
     * Hay problemas con Jquery al activar este plugin
     */
    protected function XXX_initZFDebug()
    {
        if ('development' == APPLICATION_ENV) {
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->registerNamespace('ZFDebug');

            $options = array(
                'plugins' => array('Variables', 
                                'File' => array('base_path' => APPLICATION_PATH),
                                'Memory', 
                                'Time', 
                                'Registry', 
                                'Exception')
            );

            if ($this->hasPluginResource('db')) {
                $this->bootstrap('db');
                $db = $this->getPluginResource('db')->getDbAdapter();
                $options['plugins']['Database']['adapter'] = $db;
            }

            if ($this->hasPluginResource('cache')) {
                $this->bootstrap('cache');
                $cache = $this-getPluginResource('cache')->getDbAdapter();
                $options['plugins']['Cache']['backend'] = $cache->getBackend();
            }

            $debug = new ZFDebug_Controller_Plugin_Debug($options);

            $this->bootstrap('frontController');
            $frontController = $this->getResource('frontController');
            $frontController->registerPlugin($debug);
        }
    }    
    
    
}