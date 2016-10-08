<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Gidoc_ReportesController extends MyZend_Generic_ControllerAdmin
{
    /**
     *
     * @var Formulario
     */
    private $_form;
    /**
     * Inicialización
     */
    public function init()
    {
        parent::init();

        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        $this->view->titulo = 'Reportes';        


    }

    public function indexAction()
    {
    }
    
    public function imprimirAction()
    {
        /* Obtengo el parámetro */
        $idReporte = $this->_request->getParam('idreporte');
        
        /* Traigo el formulario */
        $form = new Gidoc_Form_Reporte();
        $this->_helper->getHelper('layout')->disableLayout(); /* Para que no se ejecute el Layout */            
        $this->view->idReporte = $idReporte;                
        $this->view->form = $form->getFrm($idReporte);
    }
    

}