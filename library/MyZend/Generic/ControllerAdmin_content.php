<?php

abstract class MyZend_Generic_ControllerAdmin extends MyZend_Generic_Controller
{
    public function preDispatch()
    {
        if (!@$_SESSION['sis_userid']) {
                $this->_forward("index", "login", "default");
        }
    }

}