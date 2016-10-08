<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Gidoc_OficinasController extends MyZend_Generic_ControllerAdmin
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
        
        /* Asigno el título según el controller del navigation */
        $page = $this->view->navigation()->findOneBy('controller',$this->_request->getControllerName());        
        $this->view->titulo = $page->_title;        

        $this->_modelo = new Gidoc_Model_DependenciaMapper();
        $this->_form = new Gidoc_Form_Oficina();

        /* Configuración del jqgrid */
        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        $this->view->colNames = "'Nombre','Abreviado','Siglas Doc.','Representante','Cargo'";
        $this->view->colModel = "{name:'nombre', index:'nombre', width:50},
                                 {name:'abreviado', index:'abreviado', width:40},
                                 {name:'siglasdoc', index:'siglasdoc', width:40},
                                 {name:'representante', index:'representante', width:40},
                                 {name:'cargo', index:'cargo', width:40}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 600;
        $this->view->fieldFocus = 'nombre';

    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
        $this->_forward('mylistar','index','default');
    }

    /**
     * Lista los Oficinaes existentes
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
        $where = "tipo = 1 AND ";  /* Para ver solo las Oficinas */        

        $where .= "nombre||abreviado ILIKE '%$txtBuscar%'";        
        
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
            $linkEdita = "<a href='#' onClick=editaDesdeLink($id) >" . $fila['nombre']."</a>";

            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($linkEdita, $fila['abreviado'], $fila['siglasdoc'], $fila['representante'], $fila['cargo']);
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

            /* Creo mi objeto */
            $data = array('nombre'     => $form->nombre->getValue(),
                          'abreviado'  => $form->abreviado->getValue(),
                          'siglasdoc'  => $form->siglasdoc->getValue(),
                          'representante_id'    => $form->representante_id->getValue(),                
                          'cargo'      => $form->cargo->getValue(),                
                          'usuario_id' => $this->_usuario->id );

            $this->disableAutoRender();
            try {
                $this->_modelo->save($data);
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

            /* Creo mi objeto */
            $data = array('id'         => $id,
                          'nombre'     => $form->nombre->getValue(),
                          'abreviado'  => $form->abreviado->getValue(),
                          'siglasdoc'  => $form->siglasdoc->getValue(),
                          'representante_id' => $form->representante_id->getValue(),
                          'cargo'      => $form->cargo->getValue(),
                          'usuario_id' => $this->_usuario->id
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

            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];
            
            /* LLeno los campos del formulario con los datos del array */
            $form->populate($arrayData);
            $this->view->form = $form;
            $this->_forward('myeditar','index','default');
        }
    }

}