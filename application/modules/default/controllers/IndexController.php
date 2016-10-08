<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Default_IndexController extends MyZend_Generic_Controller
{

    public function indexAction()
    {
    }

    /* Action que es llamada desde cualquier otro módulo para mostrar la view 'mylistar' diseñada en jqgrid */
    public function mylistarAction()
    {
    }

    /* Action que es llamada desde cualquier otro módulo para mostrar el formulario principal de edición */
    public function myeditarAction()
    {
        /* Desactivo el Layout puesto que cargo el formulario en un vantana dialog.*/
        $this->_helper->layout->disableLayout();
    }

    public function practicaAction()
    {
        $this->_helper->layout->setLayout('practicazf');
    }


    public function navigationiniAction()
    {
        /* Ya he cargado el helper navigation en el bootstrap */
    }

}