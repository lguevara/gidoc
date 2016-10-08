<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Usuarios_CargosController extends MyZend_Generic_ControllerAdmin
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

        /* Creo el objeto(padre) que alpasaré a la vista */
        $nameAction = $this->_request->getActionName();

        /* Asigno el título según el controller del navigation */
        $this->view->titulo = 'Otros Cargos';        

        $this->_modelo = new  Usuarios_Model_CargoMapper();
        $this->_form = new Usuarios_Form_Cargo();

        /* Configuración del jqgrid */
        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        $this->view->colNames = "'Oficina','Cargo'";
        $this->view->colModel = "{name:'oficina', index:'oficina', width:30},
                                 {name:'cargo', index:'cargo', width:30}";
        $this->view->sortName = "oficina"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 500;
        $this->view->fieldFocus = 'cargo';
        
    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
        //$this->_forward('mylistar','index','default');

        $this->_helper->layout->setLayout('main_limpio');  /* Se setea un Layout sin el menú. */
        /* Obtengo el objeto Padre*/
        $padre_id = $this->_getParam('padre_id');

        $obj = new Usuarios_Model_UsuarioMapper();
        $objPadre = $obj->getList('','',0,0,"a.id = $padre_id");
        $this->view->objPadre = $objPadre[0];
        
    }

    /**
     * Lista los Movimientos existentes
     */
    public function listarAction()
    {

        /* Obtengo los datos del padre */
        $padre_id = $this->getRequest()->getParam('padre_id');
        
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Recibo otros parámetros que envío */
        $txtBuscar = $this->getRequest()->getParam('txtBuscar'); /* Texto a buscar */

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        $where = "a.usua_id = $padre_id AND ";
        
        $where .= "a.cargo||b.nombre ILIKE '%$txtBuscar%'";

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
            
            $linkEdita = "<a href='#' onClick=editaDesdeLink($id) >" . $fila['oficina']."</a>";

            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($linkEdita, $fila['cargo']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }

    /**
     * Alta 
     */
    public function agregarAction()
    {
        $padre_id = (int)$this->_getParam('padre_id','N');
        
        /* Traigo el formulario */
        $form = $this->_form->getFrmEdita($padre_id);
        $form->setAction($this->view->baseUrlController . '/agregar/');

        if ($this->getRequest()->isPost()) {

            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData);

            /* Creo mi objeto */
            $data = array('usua_id' => $form->padre_id->getValue(),
                          'dependencia_id'       => $form->dependencia_id->getValue(),                
                          'cargo'       => $form->cargo->getValue(),                
                          'usuario_id'   => $this->_usuario->id
                         );

            
            $this->disableAutoRender();
            try {
                $this->_modelo->save($data);
            } catch(Exception $ex) {
                echo 'error|'.$ex->getMessage();
            }
        } else {
            
            /* Seteo valores por defecto */
            $form->populate(array('padre_id' => $padre_id));
            
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

        $obj = $this->_modelo->getById($id);
        $arrayData = $obj->toArray();
        $padre_id = $arrayData['usua_id'];

        /* Traigo el formulario*/
        $form = $this->_form->getFrmEdita($padre_id, 'E', $id);
        $form->setAction($this->view->baseUrlController . '/editar/id/'. $id .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            /* Recibo los datos del formulario por _POST */
            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData); 

            /* Creo mi objeto */
            $data = array('id' => $id,
                          'dependencia_id'       => $form->dependencia_id->getValue(),                
                          'cargo'       => $form->cargo->getValue()
                        );

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

            /* Asgino el valor del Pk padre al Objeto padre_id del formulario */
            $arrayData['padre_id'] = $arrayData['usua_id'];
            
            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];

            /* Creo la variable que contenga el usuario actual */
            $this->view->usuario = $this->_usuario;
            
            /* LLeno los campos del formulario con los datos del array */
            $form->populate($arrayData);
            $this->view->form = $form;
            $this->_forward('myeditar','index','default');

        }
    }

}