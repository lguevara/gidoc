<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Default_VerempresaController extends MyZend_Generic_Controller
{
    public function init()
    {
        parent::init();   
        
        /* Desactivo el Layout */
        $this->_helper->layout->disableLayout();
    }

    /**
     * El index 
     */
    public function indexAction()
    {
        $id = 1; /* Id de la empresa que voy a obtener */

        $obj = new Default_Model_EmpresaMapper();
        $objEmpresa = $obj->getEmpresa($id);
        $this->view->objEmpresa = $objEmpresa;
    }

}