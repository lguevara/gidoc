<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Usuarios_UsuariosController extends MyZend_Generic_ControllerAdmin
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
        
        $this->view->titulo = 'Usuarios';
        $this->_modelo = new  Usuarios_Model_UsuarioMapper();
        $this->_form = new Usuarios_Form_Usuario();

        /* Configuración del jqgrid */
        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        $this->view->colNames = "'Nombres','Apellidos','Usuario','Oficina','Cargo','Email',''";
        $this->view->colModel = "{name:'nombres', index:'nombres', width:30},
                                 {name:'apellidos', index:'apellidos', width:30},
                                 {name:'usuario', index:'usuario', width:20},
                                 {name:'oficina', index:'oficina', width:30},
                                 {name:'cargo', index:'cargo', width:30},
                                 {name:'email', index:'email', width:30},
                                 {name:'cargos', index:'cargos', width:20}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 500;
        $this->view->fieldFocus = 'nombres';

    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
        //$this->_forward('mylistar','index','default');
    }

    /**
     * Lista los Usuarios existentes
     */
    public function listarAction()
    {
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Recibo otros parámetros que envío */
        $txtBuscar = $this->getRequest()->getParam('txtBuscar'); /* Texto a buscar */

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        if($txtBuscar){
            $where = "a.nombres||a.apellidos||a.usuario||a.cargo||b.nombre ILIKE '%$txtBuscar%'";            
        } else {
            $where = "";            
        }
        


        /* Paso Array a formato Json */
        $count = $this->_modelo->Count($where);

        if( $count > 0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        if ($start<0) $start=0;

        $rsArray = $this->_modelo->getList($sidx,$sord,$start,$limit,$where);

        $response = new stdClass();  
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;

        $i = 0;

        foreach ($rsArray as $fila) {
            $id = $fila['id'];
            $nombres = "<a href='#' onClick=editaDesdeLink($id) >" . $fila['nombres']."</a>";

            $boton = "<button type='button' onclick='otrosCargos($id)' >Otros Cargos</button>";            
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($nombres, $fila['apellidos'], $fila['usuario'], $fila['oficina'], $fila['cargo'], $fila['email'], $boton);
            $i++;
        }

        $this->_helper->json($response); /* */
    }

    /**
     * Alta de Motivos
     */
    public function agregarAction()
    {
        /* Traigo el formulario */
        $form = $this->_form->getFrmEdita();
        $form->setAction($this->view->baseUrlController . '/agregar/');

        if ($this->getRequest()->isPost()) {

            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData);

//            $usua_id = $_SESSION['sis_userid'];

            /* Creo mi objeto */
            $obj = array('nombres'       => $form->nombres->getValue(),
                        'apellidos'      => $form->apellidos->getValue(),
                        'iniciales'      => $form->iniciales->getValue(),
                        'usuario'        => $form->usuario->getValue(),
                        'email'          => $form->email->getValue(),
                        'dependencia_id' => $form->dependencia_id->getValue(),
                        'cargo'          => $form->cargo->getValue(),
                        'clave'          => md5($form->clave->getValue()),
                        'rol_id'         => $form->rol_id->getValue(),
                        'usuario_id'     => 1
                                                );

            $this->disableAutoRender();
            try {
                $this->_modelo->save($obj);
            } catch(Exception $ex) {
                echo 'error|'.$ex->getMessage();
            }
        } else {
            $this->view->form = $form;
            $this->_forward('myeditar','index','default');
        }
    }

    /**
     * Editar Motivo
     * se toma el parametro id para Editar un motivo
     */
    public function editarAction()
    {
        $id = (int)$this->_request->getParam('id', 0);

        /* Traigo el formulario*/
        $form = $this->_form->getFrmEdita();
        $form->setAction($this->view->baseUrlController . '/editar/id/'. $id .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            /* Recibo los datos del formulario por _POST */
            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData); 

            $clave = $form->clave->getValue();
            if($clave <> '*****'){ // Significa que al editar se ha modificado la clave

                $data = array('id' => $id,
                    'nombres' => $form->nombres->getValue(),
                    'apellidos' => $form->apellidos->getValue(),
                    'iniciales' => $form->iniciales->getValue(),
                    'usuario' => $form->usuario->getValue(),
                    'email' => $form->email->getValue(),
                    'dependencia_id' => $form->dependencia_id->getValue(),
                    'cargo' => $form->cargo->getValue(),
                    'clave' => md5($form->clave->getValue()),
                    'rol_id' => $form->rol_id->getValue());
                
                
                
            } else {
                
                $data = array('id' => $id,
                    'nombres' => $form->nombres->getValue(),
                    'apellidos' => $form->apellidos->getValue(),
                    'iniciales' => $form->iniciales->getValue(),
                    'usuario' => $form->usuario->getValue(),
                    'email' => $form->email->getValue(),
                    'dependencia_id' => $form->dependencia_id->getValue(),
                    'cargo' => $form->cargo->getValue(),
                    'rol_id' => $form->rol_id->getValue());
                
            }

            
            $this->disableAutoRender();
            try {
                /* Actualizo mi objeto */
                $this->_modelo->save($data);
            } catch(Exception $ex) {
                echo 'error|'.$ex->getMessage();
            }
        } else { /* Se llama al formulario   */
            $obj = $this->_modelo->getById($id);

            if (null === $obj) {
                    //$this->_redirect('/');
            }
            /* Paso los campos del objeto a un array  */
            $arrayData = $obj->toArray();

            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];

            /* LLeno los campos del formulario con los datos del array */
            $arrayData['clave'] = '*****'; // Agrego esto, para controlar si se modifica la clave.
            $form->populate($arrayData);
            $this->view->form = $form;
            $this->_forward('myeditar','index','default');

        }
    }
 
}