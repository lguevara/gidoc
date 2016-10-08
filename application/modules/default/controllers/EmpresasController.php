<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Default_EmpresasController extends MyZend_Generic_ControllerAdmin
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
        
        $this->view->titulo = 'Empresas';
        $this->_modelo = new Default_Model_EmpresaMapper();
        $this->_form = new Default_Form_Empresa();

        /* Configuración del jqgrid */
        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        $this->view->colNames = "'Título','Contenido','Imagen'";
        $this->view->colModel = "{name:'titulo', index:'titulo', width:30},
                                 {name:'contenido', index:'contenido', width:60},   
                                 {name:'imagen', index:'imagen', width:30}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 700;
        $this->view->fieldFocus = 'titulo';
        $this->view->editarSinDialog = true;
    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
        $this->_forward('mylistar','index','default');
    }

    /**
     * Lista los Empresas existentes
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

        $where = "titulo||contenido ILIKE '%$txtBuscar%'";

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

        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;

        $i = 0;

        foreach ($rsArray as $fila) {
            $id = $fila['id'];
            $titulo = "<a href='#' onClick=editaDesdeLink($id) >" . $fila['titulo']."</a>";

            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($titulo, $fila['contenido'], $fila['imagen']);
            
            $i++;
        }

        $this->_helper->json($response); /* */
    }

    /**
     * Alta de Menús
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
            $obj = new Default_Model_Empresa(array('titulo'  => $form->titulo->getValue(),
                                                  'contenido' => $form->contenido->getValue(),
                                                  'imagen' => $form->imagen->getValue(),
                                                'usuario_id' => 1)
                                        );

            $this->disableAutoRender();
            try {
                $this->_modelo->save($obj);
            } catch(Exception $ex) {
                echo 'error|'.$ex->getMessage();
            }
        } else {
            $this->view->form = $form;
//            $this->_forward('myeditar','index','default');
//            $this->_helper->layout->disableLayout();
            $this->render('editar');
        }
    }

    /**
     * Editar Motivo
     * se toma el parametro id para Editar un motivo
     */
    public function editarAction()
    {

        $this->view->titulo = 'Datos Institucionales';        
        
        $id = 1; /* Siempre se editara la empresa 1*/

        /* Traigo el formulario*/
        $form = $this->_form->getFrmEdita();
        $form->setAction($this->view->baseUrlController . '/editar/id/'. $id .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            /* Recibo los datos del formulario por _POST */
            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData); 

            /* Para el elemento imágen */
            $imagen = $form->imagen->getValue();
            if(!$imagen){ /* Si no se ha seleccionado otra imágen */
                $imagen = $form->imagenHidden->getValue(); /* obtengo el valor del campo oculto */
            }
            
            /* Creo mi objeto */
            $obj = new Default_Model_Empresa(array('id'     => $id,
                                               'nombre'     => $form->nombre->getValue(),
                                               'direccion'  => $form->direccion->getValue(),
                                               'telefonos'  => $form->telefonos->getValue(),
                                               'email'      => $form->email->getValue(),
                                               'imagen'     => $imagen,
                                               'usuario_id' => 1)
                                        );

            $this->disableAutoRender();
            try {
                /* Actualizo mi objeto */
                $this->_modelo->save($obj);
                
                /* Muestro mensaje de éxito */
                $this->_helper->FlashMessenger(array('notice' => 'Actualización efectuada correctamente'));                
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
            
            /* En el campo oculto, asigno como valor lo mismo que tengo en el campo imagen */
            $arrayData['imagenHidden'] = $arrayData['imagen'];
            
            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];

            /* LLeno los campos del formulario con los datos del array */
            $form->populate($arrayData);
            $this->view->form = $form;
            $this->render('editar');
        }
    }

    public function eliminarAction()
    {
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        /* Recibo la lista de Ids separado por comas */
        $idList = $this->_getParam('id');

        if ($idList) {
            /* Convierto la lista a un Array */
            $idArray = explode (",", $idList);

            /* Recorro el array eliminando cada Id */
            foreach ($idArray as $id) {
                try {
                    $this->_modelo->eliminar($id);
                } catch(Exception $ex) {
                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }
            }
        }
    }

}