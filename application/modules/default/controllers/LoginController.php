<?php
/**
 * Description of IndexController
 *
 */
//require_once 'default/models/LoginModelo.php';

class Default_LoginController extends MyZend_Generic_Controller
{
    private $_loginModelo;

    public function init()
    {
        parent::init();

        $this->_loginModelo = new Default_Model_LoginModelo();
        $this->view->titulo = 'Login de Acceso';
        $this->view->baseUrl = $this->getRequest()->getBaseUrl();
    }

    public function indexAction()
    {
        $this->view->form = $this->_getLoginForm();
        $this->render();
    }

    public function autenticarAction()
    {
        if ($this->getRequest()->isPost()) {
            // Obtenemos los parámetros del formulario
            $postParams = $this->_request->getPost();
            $form = $this->_getLoginForm();

            if(!$form->isValid($postParams)) {
                    // Falla la validación; volvemos a generar el formulario
                    // Poblamos al formulario con los datos
                    $form->populate($postParams);
                    $this->view->form = $form;
                    $this->render('index');
                    return;
            }

            $usuario = $form->usuario->getValue();
            $password = $form->pass->getValue();
            $responseLogin = null;

            try {
                    $this->_loginModelo->login($usuario, $password);
                    $this->_helper->redirector('index', 'index','default'); 
            } catch(Exception $e){
                //$this->view->mensaje = $e->getMessage();
                $this->_helper->FlashMessenger(array('error' => $e->getMessage()));
                $this->_forward('index');
            }
        } else {
            return $this->_forward('index');
        }
    }

    public function logoutAction()
    {
            $this->_loginModelo->logout();
            $this->_redirect("/");
    }

    private function _getLoginForm()
    {
        $form = new Zend_Form();
        $form->setAttrib('id','frmEditar');        
        
        $form->setAction($this->view->baseUrl. '/login/autenticar');
        $form->setMethod('post');

        $usuario = $form->createElement('text', 'usuario');
        $usuario->setLabel('Usuario');
        $usuario->addValidator(new Zend_Validate_Alnum())
               ->setRequired(true);


        $password = $form->createElement('password', 'pass');
        $password->setLabel('Contraseña');
        $password->addValidator('alnum')
                 ->addValidator('StringLength', false, array(4, 15))
                 ->setRequired(true);

        $form->addElement($usuario)
             ->addElement($password)
             ->addElement('submit', 'login', array('label' => 'Ingresar'));

        return $form;
     }
}