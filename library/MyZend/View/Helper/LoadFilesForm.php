<?php
class MyZend_View_Helper_LoadFilesForm extends Zend_View_Helper_Abstract
{
    public function LoadFilesForm ($paramArray = array())
    {
        /* Obtengo el objeto vista */
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;

        /* Añado al headScript los archivos necesarios para ui.datepicker */
        if(in_array('ui.datepicker',$paramArray)){
            $view->headScript()->appendFile($this->view->baseUrl.'/js/jquery/jquery.maskedinput-1.2.2.min.js');
            $view->headScript()->appendFile($this->view->baseUrl.'/js/jquery/ui/jquery.ui.datepicker-es.js');
        }

        echo $this->view->headScript()->appendFile($this->view->baseUrl.'/js/jquery/jquery.form.js')
                                      ->appendFile($this->view->baseUrl.'/js/jquery/jqvalidate/jquery.validate.js')
                                      ->appendFile($this->view->baseUrl.'/js/jquery/jqvalidate/messages_es.js');
    }
}