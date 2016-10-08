<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Gidoc_PlantillasController extends MyZend_Generic_ControllerAdmin
{
    /**
     *
     * @var Formulario
     */
    private $_form;
    
    private $_modeloMovimiento;
    
    /**
     * Inicialización
     */
    public function init()
    {
        parent::init();
        
        $this->_modelo = new Gidoc_Model_PlantillaMapper();
        $this->_modeloMovimiento = new Gidoc_Model_MovimientoplantillaMapper();
        $this->_form = new Gidoc_Form_Plantilla();

        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        
        
    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
    }

    public function poratenderAction()
    {
        /* Asigno el título según el controller del navigation */
        //$page = $this->view->navigation()->findOneBy('action',$this->_request->getActionName());        
        $this->view->titulo = 'Plantillas';   

        /* Configuración del jqgrid */
        $this->view->colNames = "'Nombre','Documento','Asunto','Archivo','tipomovimiento_id','Origen'";
        $this->view->colModel = "{name:'nombre', index:'nombre', width:40},
                                 {name:'documento', index:'documento', width:50},
                                 {name:'asunto', index:'asunto', width:60},
                                 {name:'', index:'', width:10},
                                 {name:'tipomovimiento_id', index:'tipomovimiento_id', width:1},
                                 {name:'origen', index:'origen', width:1}";
        $this->view->hideCol = "'tipomovimiento_id','origen'";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 600;
        $this->view->fieldFocus = 'firma';
        $this->view->editarSinDialog = true;
        
    }
    
    /**
     * Lista los Plantillaes existentes
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

        $where = "a.tipomovimiento_id IN (1,2,3) AND a.procesado = FALSE AND ";
        
        $where .= "b.nombre||d.descripcion||b.asunto ILIKE '%$txtBuscar%'";
        
        
        /* Paso Array a formato Json */
        $count = $this->_modeloMovimiento->Count($where);

        if( $count > 0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        if ($start<0) $start=0;

        $rsArray = $this->_modeloMovimiento->getList($sidx,$sord,$start,$limit,$where);

        $response = new stdClass();          
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;

        $i = 0;

        //$urlReportes = 'http://localhost:10088/misreportes/gidoc/';
        //$urlrptDocumento = $urlReportes.'rptDocumento2.php';
        
        //$urlReportes = 'http://localhost:10088/misreportes/gidoc/';
        
        foreach ($rsArray as $fila) {
            $id = $fila['id'];
            $documento_id = $fila['documento_id'];
            $nombre = $fila['nombre'];
            $linkEdita = "<a href='#' onClick=editaDesdeLink($id) >$nombre</a>";
            
            $urlrptDocumento = "/gidoc/rptdocumento/index/documento_id/$documento_id/tipo/1";
            
            $linkVerArchivo = '';
            if($fila['archivo']){
                    $linkVerArchivo = "<a target='_blank' href='/uploads/".$fila['archivo']."'><span class='ui-icon ui-icon-document'></span></a>";
            } else {
                /* Si es Documento interno */
                if($fila['origen'] == 1){
                    $linkVerArchivo = "<a target='_blank' href='$urlrptDocumento' ><span class='ui-icon ui-icon-document'></span></a>";
                }
            }

//            if($fila['usuario_destino']){
//                $derivadoA = $fila['oficina_destino'].'/'.$fila['usuario_destino'];                
//            } else {
//                $derivadoA = $fila['oficina_destino'];  
//            }

            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($linkEdita, $fila['documento'],$fila['asunto'],$linkVerArchivo, $fila['tipomovimiento_id'], $fila['origen']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }

    
    public function agregarinternoAction()
    {
        /* Traigo el formulario */
        $form = $this->_form->getFrmEditaInterno();
        $form->setAction($this->view->baseUrlController . '/agregarinterno/');

        if ($this->getRequest()->isPost()) {

            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData);

            /* Verifico valores que llegan desde el formulario */
            $referencia = $form->referencia->getValue();
            if ($referencia == ''){
                $referencia = null;
            }

            $tipo_documento_id = $form->tipo_documento_id->getValue();

            /* Recibo datos para la tabla destinos */
            $dependencia_iddestino = $form->dependencia_iddestino->getValue();
            
            if($dependencia_iddestino) {
                
                $listNameOptions = $form->getElement('dependencia_iddestino')->getMultiOptions();
                $paraDestino = $listNameOptions[$form->getValue('dependencia_iddestino')];            
                
            } else {
                
                $dependencia_iddestino = null;
                $paraDestino = $form->para_destino->getValue();                
            }
            
            
            $cargoDestino = $form->cargo_destino->getValue();
            $dependenciaDestino = $form->dependencia_destino->getValue();

            /* Fin de: Recibo datos para la tabla destinos*/
            
            /* Creo mi objeto */
            $data = array('origen'                  => 1, /* Interno */
                          'nombre'                  => $form->nombre->getValue(),
                          'dependencia_id'          => $this->_usuario->dependencia_id,
                          'firma'                   => $this->_usuario->nombres . ' ' . $this->_usuario->apellidos,
                          'cargo'                   => $this->_usuario->cargo,
                          'tipo_documento_id'       => $tipo_documento_id,
                          'siglas'                  => $this->_usuario->oficina_siglasdoc,
                          'asunto'                  => $form->asunto->getValue(),
                          'referencia'              => $referencia,
                          'cuerpo'                  => $form->cuerpo->getValue(),
                          'dependencia_registra_id' => $this->_usuario->dependencia_id,
                          'usuario_id'              => $this->_usuario->id
                    );

            try {
                /* Actualizo mi objeto */
                $documento_id = $this->_modelo->save($data);
                
                /* Guardo los datos correspondientes para la tabla destinos */
                $modelDestino = new Gidoc_Model_DestinoplantillaMapper();

                /* Grabo el destino */
                $dataDestino = array('dependencia_iddestino' => $dependencia_iddestino,
                                     'para'          => $paraDestino,
                                     'cargo'         => $cargoDestino,
                                     'dependencia'   => $dependenciaDestino,
                                     'documento_id'  => $documento_id,
                                     'usuario_id'    => $this->_usuario->id                    
                                );
                
                $modelDestino->save($dataDestino);
                
                /**/

            } catch(Exception $ex) {
                //throw new Exception("Error al Editar el registro". $ex->getMessage());
                
                /* Para mostrar el error en el Flash Messenger y mantener los datos del formulario */
                $this->_helper->FlashMessenger(array('error' => $ex->getMessage()));                                
                
                $form->populate($formData);
                $this->view->form = $form;
                return $this->render('editarinterno');
                /* */
                
            }
            
            $this->_helper->redirector('poratender', 'plantillas', 'gidoc');            
            
        } else {
            $this->view->form = $form;
            $this->render('editarinterno');
            
        }
    }

    /**
     * Editar 
     * se toma el parametro id para Editar
     */
    public function editarinternoAction()
    {
        $id = (int)$this->_request->getParam('id', 0);

        /* Traigo el formulario*/
        $form = $this->_form->getFrmEditaInterno();
        $form->setAction($this->view->baseUrlController . '/editarinterno/id/'. $id .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $movimiento = $this->_modeloMovimiento->getById($id);
            $id = $movimiento->documento_id;
            
            /* Recibo los datos del formulario por _POST */
            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData); 

            /* Verifico valores que llegan desde el formulario */
            $referencia = $form->referencia->getValue();
            if ($referencia == ''){
                $referencia = null;
            }
            
            /* Recibo datos para la tabla destinos */
            $dependencia_iddestino = $form->dependencia_iddestino->getValue();
            
            if($dependencia_iddestino) {
                
                $listNameOptions = $form->getElement('dependencia_iddestino')->getMultiOptions();
                $paraDestino = $listNameOptions[$form->getValue('dependencia_iddestino')];            
                
            } else {
                
                $dependencia_iddestino = null;
                $paraDestino = $form->para_destino->getValue();                
            }
            
            $idDestino = $form->id_destino->getValue();            
            $cargoDestino = $form->cargo_destino->getValue();
            $dependenciaDestino = $form->dependencia_destino->getValue();
            
            /* Fin: Recibo datos para la tabla destinos*/
            
            $data = array('id'                      => $id,
                          'nombre'                  => $form->nombre->getValue(),                
                          'tipo_documento_id'       => $form->tipo_documento_id->getValue(),
                          'asunto'                  => $form->asunto->getValue(),
                          'referencia'              => $referencia,
                          'cuerpo'                  => $form->cuerpo->getValue()
                    );
            
            try {
                /* Actualizo mi objeto */
                $this->_modelo->save($data);
                
                /* Guardo los datos correspondientes para la tabla destinos */
                $modelDestino = new Gidoc_Model_DestinoplantillaMapper();

                /* Grabo el destino */
                $dataDestino = array('id'            => $idDestino,
                                     'dependencia_iddestino' => $dependencia_iddestino,                                        
                                     'para'          => $paraDestino,
                                     'cargo'         => $cargoDestino,
                                     'dependencia'   => $dependenciaDestino
                                );
                
                $modelDestino->save($dataDestino);
                
                /**/

            } catch(Exception $ex) {
                // throw new Exception("Error al Editar el registro". $ex->getMessage());                
                
                /* Para mostrar el error en el Flash Messenger y mantener los datos del formulario */
                $this->_helper->FlashMessenger(array('error' => $ex->getMessage()));                                
                
                $form->populate($formData);
                $this->view->form = $form;
                return $this->render();
                /* */
            }
            
            $this->_helper->redirector('poratender', 'plantillas', 'gidoc');            
            
        } else { /* Se llama al formulario   */
    
            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $movimiento = $this->_modeloMovimiento->getById($id);
            $id = $movimiento->documento_id;
            
            /* Obtengo el objeto de la tabla Documento */
            $obj = $this->_modelo->getById($id);

            /* Paso los campos del objeto a un array  */
            $arrayData = $obj->toArray();

            /* Obtengo el objeto de la tabla Destinos */
            $modelDestino = new Gidoc_Model_DestinoplantillaMapper();
            $objDestino = $modelDestino->getByDocumentoId($id);
            
            /* Paso los campos al arrayData */
            $arrayData['id_destino'] = $objDestino->id;
            $arrayData['dependencia_iddestino'] = "$objDestino->dependencia_iddestino";            
            $arrayData['para_destino'] = $objDestino->para;
            $arrayData['cargo_destino'] = $objDestino->cargo;
            $arrayData['dependencia_destino'] = $objDestino->dependencia;
            
            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];
            
            /* LLeno los campos del formulario con los datos del array */
            $form->populate($arrayData);
            $this->view->form = $form;
            $this->render('editarinterno');
        }
    }
    
}