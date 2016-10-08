<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Gidoc_DocumentosController extends MyZend_Generic_ControllerAdmin
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
        
        $this->_modelo = new Gidoc_Model_DocumentoMapper();
        $this->_modeloMovimiento = new Gidoc_Model_MovimientoMapper();
        $this->_modeloArchivo = new Gidoc_Model_ArchivoMapper(); 
        $this->_modeloDerivotemporal = new Gidoc_Model_DerivotemporalMapper();        
        
        $this->_form = new Gidoc_Form_Documento();

        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
       
        /* Para el Grid Derivos Temporales */
        /* Configuración del jqgrid */
        $this->view->derivos_colNames = "'Oficina','Usario','Acciones',''";
        $this->view->derivos_colModel = "{name:'oficina_destino', index:'oficina_destino', width:50},
                                         {name:'usuario_destino', index:'usuario_destino', width:50},
                                         {name:'acciones', index:'acciones', width:100},
                                         {name:'opcion', index:'opcion', width:10}";
        $this->view->derivos_sortName = "id"; 

        /* */
        
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
        $page = $this->view->navigation()->findOneBy('action',$this->_request->getActionName());        
        $this->view->titulo = $page->_title;        

        /* Configuración del jqgrid */
        $this->view->colNames = "'Registro','Orígen','Firma','Cargo','Documento','Fecha Doc.','Asunto','Ver','Derivado a','Acciones','tipomovimiento_id','Origen','Tipo'";
        $this->view->colModel = "{name:'documento_id', index:'documento_id', width:10},
                                 {name:'dependencia_origen', index:'dependencia_origen', width:40},
                                 {name:'firma', index:'firma', width:30},
                                 {name:'cargo', index:'cargo', width:30},
                                 {name:'documento', index:'documento', width:50},
                                 {name:'fecha_documento', index:'fecha_documento', width:20, formatter:'date', formatoptions:{srcformat:'Y-m-d',newformat:'d/m/Y'}},
                                 {name:'asunto', index:'asunto', width:60},
                                 {name:'', index:'', width:30},
                                 {name:'oficina_destino', index:'oficina_destino', width:40},
                                 {name:'acciones', index:'acciones', width:40},
                                 {name:'tipomovimiento_id', index:'tipomovimiento_id', width:1},
                                 {name:'origen', index:'origen', width:1},
                                 {name:'tipo', index:'tipo', width:1}";
        $this->view->hideCol = "'tipomovimiento_id','origen','tipo'";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 600;
        $this->view->fieldFocus = 'firma';
        $this->view->editarSinDialog = true;

        $formOficinas = $this->_form->getFrmOficinas();
        $this->view->formOficinas = $formOficinas;
    }
    
    /**
     * Lista los Documentoes existentes
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
        $todaOficina = $this->getRequest()->getParam('todaOficina'); 
        $dependencia_busca_id = $this->getRequest()->getParam('dependencia_busca_id'); 

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        if(!$dependencia_busca_id){
            $dependencia_busca_id = $this->_usuario->dependencia_id;
        }
        
        $usuario_id = $this->_usuario->id;                

        if($todaOficina){
            
            $where = "a.dependencia_id = $dependencia_busca_id AND a.tipomovimiento_id IN (1,2,3,5,7) AND a.procesado = FALSE AND ";            
            
        } else {
            
            $where = "a.dependencia_id = $dependencia_busca_id AND a.usuario_id = $usuario_id AND a.tipomovimiento_id IN (1,2,3,5,7) AND a.procesado = FALSE AND ";            
            
        }
        
        $where .= "a.documento_id||c.nombre||b.firma||b.cargo||b.asunto ILIKE '%$txtBuscar%'";

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
            $secuencia = $fila['secuencia'];            
            $expediente_id = $fila['expediente_id'];            
            $tipomovimiento_id = $fila['tipomovimiento_id'];

            $linkEdita = "<a href='#' onClick=editaDesdeLink($id) >$expediente_id-$secuencia</a>";
            
            $urlrptDocumento = "/gidoc/rptdocumento/index/documento_id/$documento_id";            
            
            $linkVerArchivo = '';
            if($fila['archivo']){
                    $linkVerArchivo = "<a target='_blank' href='/uploads/".$fila['archivo']."'><span class='ui-icon ui-icon-document'></span></a>";
            } else {
                /* Si es Documento interno */
                if($fila['origen'] == 1){
                    $linkVerArchivo = "<a target='_blank' href='$urlrptDocumento' ><span class='ui-icon ui-icon-document'></span></a>";
                }
            }

            if($fila['usuario_destino']){
                $derivadoA = $fila['oficina_destino'].'/'.$fila['usuario_destino'];                
            } else {
                $derivadoA = $fila['oficina_destino'];  
            }

            /* Preparo botón para firma o deshacer firma */
            $boton = '';
            if($tipomovimiento_id == 1 ){ /* Si está solo registrado */

                if($this->_config->general->opcionFirma){
                    /* Si es Doc. Personal */
                    if($fila['tipo'] == 1) {
                        /* Mostrar botón para firmar */
                        if(!$fila['firma']) {                        
                            $boton = "<div><button type='button' onclick='firmar($documento_id)' >Firmar</button></div>";
                        } else {
                            $boton = "<div><button type='button' onclick='deshacer_firma($documento_id)' >Retirar Firma</button></div>";
                        }
                    } else { /* Si es doc. Interno */
                        /* Muestro el botón firmar o deshacer firma si el Usuario activo es jefe y la oficina orígen es la oficina del usuario activo . */
                        if($this->_usuario->jefe_de !== null and $fila['doc_dependencia_id'] == $this->_usuario->dependencia_id) {
                            if(!$fila['firma']) {                        
                                $boton = "<div><button type='button' onclick='firmar($documento_id)' >Firmar</button></div>";
                            } else {
                                $boton = "<div><button type='button' onclick='deshacer_firma($documento_id)' >Retirar Firma</button></div>";
                            }
                        }

                    }
                }
                
            }

            /* Fin:  Preparo botón para firma o deshacer firma */            
            
            $linkVerArchivo = '';
            if($fila['archivo']){
                    $linkVerArchivo = "<a target='_blank' href='/uploads/".$fila['archivo']."'><span class='ui-icon ui-icon-document'></span></a>";
            } else {
                /* Si es Documento interno */
                if($fila['origen'] == 1){
                    if($fila['clasificacion_id'] <> 22){ /* Si es diferente de "Confidencial", podrá verse el documento */
                        $linkVerArchivo = "<a target='_blank' href='$urlrptDocumento' ><span class='ui-icon ui-icon-document'></span></a>";
                    }

                    $linkVerArchivo .= $boton;
                    
                }
            }

            
            /* Para mostrar tambien los archivos subidos */
            $linkVerArchivo .= $fila['archivos'];
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($linkEdita, $fila['dependencia_origen'], $fila['firma'], 
                                                $fila['cargo'], $fila['documento'], $fila['fecha_documento'],
                                                $fila['asunto'],$linkVerArchivo, $derivadoA,
                                                $fila['acciones'], $fila['tipomovimiento_id'], $fila['origen'], $fila['tipo']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }

    public function archivadosAction()
    {
        /* Asigno el título según el controller del navigation */
        $page = $this->view->navigation()->findOneBy('action',$this->_request->getActionName());        
        $this->view->titulo = $page->_title;        

        /* Configuración del jqgrid */
        $this->view->colNames = "'Registro','Orígen','Firma','Documento','Fecha Doc.','Asunto','Archivador','Acciones finales'";
        $this->view->colModel = "{name:'documento_id', index:'documento_id', width:10},
                                 {name:'dependencia_origen', index:'dependencia_origen', width:40},
                                 {name:'firma', index:'firma', width:30},
                                 {name:'documento', index:'documento', width:40},
                                 {name:'fecha_documento', index:'fecha_documento', width:20, formatter:'date', formatoptions:{srcformat:'Y-m-d',newformat:'d/m/Y'}},
                                 {name:'asunto', index:'asunto', width:40},
                                 {name:'archivador', index:'archivador', width:10},
                                 {name:'acciones', index:'acciones', width:20}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 800;
        $this->view->fieldFocus = 'firma';
        $this->view->editarSinDialog = true;
        
    }

    public function listararchivadosAction()
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

        $usuario_id = $this->_usuario->id;
        $where = "a.usuario_id = $usuario_id AND a.tipomovimiento_id = 4 AND a.procesado = FALSE AND ";
        
        $where .= "a.documento_id||c.nombre||b.firma||e.nombre||b.asunto ILIKE '%$txtBuscar%'";

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

        foreach ($rsArray as $fila) {
            $id = $fila['id'];
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($fila['expediente_id'].'-'.$fila['secuencia'], $fila['dependencia_origen'], $fila['firma'], 
                                                $fila['documento'], $fila['fecha_documento'],
                                                $fila['asunto'],$fila['archivador'],$fila['acciones']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }
    
    /**
     * Alta 
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

            /* Verifico valores que llegan desde el formulario */
            $numero = $form->numero->getValue();
            if ($numero == ''){
                $numero = 0;
            }

            $expediente_id = $form->expediente_id->getValue();
            if ($expediente_id == ''){
                $expediente_id = 0;
            }

            $procedimiento_id = $form->procedimiento_id->getValue();
            if ($procedimiento_id == ''){
                $procedimiento_id = NULL;
            }
            
            /* Creo mi objeto */
            $data = array('origen'                  => 2, /* Externo */
                          'tipo'                    => null,
                          'expediente_id'           => $expediente_id,
                          'dependencia_id'          => $form->dependencia_id->getValue(),
                          'firma'                   => $form->firma->getValue(),
                          'cargo'                   => $form->cargo->getValue(),
                          'tipo_documento_id'       => $form->tipo_documento_id->getValue(),
                          'fecha_documento'         => $form->fecha_documento->getValue(),
                          'numero'                  => $numero,
                          'siglas'                  => $form->siglas->getValue(),
                          'asunto'                  => $form->asunto->getValue(),
                          'anexos'                  => $form->anexos->getValue(),
                          'procedimiento_id'        => $procedimiento_id,
                          'dependencia_registra_id' => $form->dependencia_registra_id->getValue(),
                          'usuario_id'              => $this->_usuario->id
                    );

            
            try {
                
                $registro_id = $this->_modelo->save($data);

                /* Obtengo los datos del nuevo registro */
                $objDocumento = $this->_modelo->getDocumentoById($registro_id);
                
                $documento = $objDocumento->documento;
                $fechaRegistro = new Zend_Date($objDocumento->fecha_registro, Zend_Date:: ISO_8601);
                $fechaHora = $fechaRegistro->toString('dd/MM/yyyy h:m:s');
                $origen = $objDocumento->dependencia_origen;
                $expediente_id = $objDocumento->expediente_id;
                $secuencia = $objDocumento->secuencia;
                
                
                $texto = "
                <spam style='font-size: 16px;'> Registro grabado: </spam>
                <spam style='font-size: 22px;font-weight: bold;'>$expediente_id-$secuencia</spam>'
                <p>
                <spam style='font-size: 12px;font-weight: bold;'> Documento: </spam>
                <spam style='font-size: 12px;'>$documento</spam>'
                <p>
                <spam style='font-size: 12px;font-weight: bold;'> Orígen: </spam>
                <spam style='font-size: 12px;'>$origen</spam>'
                <p>                
                <spam style='font-size: 12px;font-weight: bold;'> Fecha/Hora de registro: </spam>
                <spam style='font-size: 12px;'>$fechaHora</spam>'
                <p>   
                <div style='width:100%; text-align: center;' >
                <a href='#' onclick=$('#msgFlash').fadeOut('slow') >Cerrar</a>
                </div>
                ";
                
                $this->_helper->FlashMessenger(array('notice' => $texto)); 
                
            } catch(Exception $ex) {
                // echo 'error|'.$ex->getMessage();
                        
                /* Para mostrar el error en el Flash Messenger y mantener los datos del formulario */
                $this->_helper->FlashMessenger(array('error' => $ex->getMessage()));                                
                
                $form->populate($formData);
                $this->view->form = $form;
                return $this->render('editar');
                /* */
                
            }
            
            $this->_helper->redirector('poratender', 'documentos', 'gidoc');            

        } else {
            
            /* Para recibir número de expediente */
            $expediente_id = $this->_request->getParam('expdte_id');
            
            if($expediente_id){
                $form->expediente_id->setValue($expediente_id);
            }
            /* Fin: Para recibir número de expediente */
            
            
            $this->view->form = $form;
            //$this->_helper->layout->disableLayout();
            $this->render('editar');            
            
        }
    }

    /**
     * Editar 
     * se toma el parametro id para Editar
     */
    public function editarAction()
    {
        $id = (int)$this->_request->getParam('id', 0);

        /* Traigo el formulario*/
        $form = $this->_form->getFrmEdita();
        $form->setAction($this->view->baseUrlController . '/editar/id/'. $id .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $objMovimiento = new Gidoc_Model_MovimientoMapper();
            $movimiento = $objMovimiento->getById($id);
            $id = $movimiento->documento_id;
            
            /* Recibo los datos del formulario por _POST */
            $formData = $this->_request->getPost();

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData); 

            /* Verifico valores que llegan desde el formulario */
            $numero = $form->numero->getValue();
            if ($numero == ''){
                $numero = 0;
            }
            
            
            $expediente_id = $form->expediente_id->getValue();
            if ($expediente_id == ''){
                $expediente_id = 0;
            }
            
            $procedimiento_id = $form->procedimiento_id->getValue();
            if ($procedimiento_id == ''){
                $procedimiento_id = NULL;
            }
            
            
            /* Creo mi objeto */
            $data = array('id'                      => $id,
                          'expediente_id'           => $expediente_id,                                
                          'dependencia_id'          => $form->dependencia_id->getValue(),
                          'firma'                   => $form->firma->getValue(),
                          'cargo'                   => $form->cargo->getValue(),
                          'tipo_documento_id'       => $form->tipo_documento_id->getValue(),
                          'fecha_documento'         => $form->fecha_documento->getValue(),
                          'numero'                  => $numero,
                          'siglas'                  => $form->siglas->getValue(),
                          'asunto'                  => $form->asunto->getValue(),
                          'anexos'                  => $form->anexos->getValue(),                
                          'procedimiento_id'        => $procedimiento_id,                
                          'dependencia_registra_id' => $form->dependencia_registra_id->getValue(),                                
                          'usuario_id'              => $this->_usuario->id                
                         );

            try {
                /* Actualizo mi objeto */
                $this->_modelo->save($data);
            } catch(Exception $ex) {
                // echo 'error|'.$ex->getMessage();
                
                /* Para mostrar el error en el Flash Messenger y mantener los datos del formulario */
                $this->_helper->FlashMessenger(array('error' => $ex->getMessage()));                                
                
                $form->populate($formData);
                $this->view->form = $form;
                return $this->render('editar');
                /* */
                
            }

            $this->_helper->redirector('poratender', 'documentos', 'gidoc');            
            
        } else { /* Se llama al formulario   */
    
            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $objMovimiento = new Gidoc_Model_MovimientoMapper();
            $movimiento = $objMovimiento->getById($id);
            $id = $movimiento->documento_id;
            
            /* Obtengo el objeto de la tabla Documento */
            $obj = $this->_modelo->getById($id);

            if (null === $obj) {
                    //$this->_redirect('/');
            }
            /* Paso los campos del objeto a un array  */
            $arrayData = $obj->toArray();

            $date = new Zend_Date($arrayData['fecha_documento'], Zend_Date:: ISO_8601);
	    $fecha = $date->toString('dd/MM/yyyy');
	    $arrayData['fecha_documento'] = $fecha;
            
            if($arrayData['procedimiento_id'] == null ){
                $arrayData['procedimiento_id'] = '';            
            }
                
            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];

            /* Variables para poder subir archivos */
            $this->view->id = $id;
            /* Para el Grid archivos */
            /* Configuración del jqgrid */
            $this->view->archivos_colNames = "'Archivo',''";
            $this->view->archivos_colModel = "{name:'descripcion', index:'descripcion', width:50},
                                              {name:'opcion', index:'opcion', width:10}";
            $this->view->archivos_sortName = "descripcion"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        
            /* Fin:  Variables para poder subir archivos */
            
            /* LLeno los campos del formulario con los datos del array */
            $form->populate($arrayData);
            $this->view->form = $form;
            //$this->_helper->layout->disableLayout();
            $this->render('editar');            

        }
    }
    
    public function agregarinternoAction()
    {
        // Recibo el parámetro Tipo 
        $tipo = (int)$this->_request->getParam('tipo', 1);   /* si no llega el parámetro sigifica que es Documento Personal (1)*/
        
        /* Traigo el formulario */
        $form = $this->_form->getFrmEditaInterno($tipo);

        if($tipo == 1){ /* si es documento personal */
            $form->setAction($this->view->baseUrlController . '/agregarinterno/');
        } else { /* Es documento jefatural */
            $form->setAction($this->view->baseUrlController . '/agregarinterno/tipo/0');
        }

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
            
            /* Dependencia donde quedará en Proceso el documento */
            if($tipo == 1){ /* si es documento personal */
                
                $numero = $form->numero->getValue();                
                //$siglas = $this->_usuario->oficina_siglasdoc.'-'.$this->_usuario->iniciales;
                
                if($this->_config->general->opcionFirma){
                    
//                    $dependencia_id = $this->_usuario->dependencia_id;                    
                    $dependencia_id = $form->dependencia_id->getValue();                    
                    $firma = ''; 
                    
                } else {
                    
                    $dependencia_id = $form->dependencia_id->getValue();
                    $firma  = $this->_usuario->nombres . ' ' . $this->_usuario->apellidos;
                    
                }

                $cargo  = $this->_usuario->cargo;
                
                
                
            } else { /* Si es documento jefatural */

                if($this->_config->general->opcionFirma){
                    
                    $dependencia_id = $form->dependencia_id->getValue(); /* queda en proceso en la oficina donde está esperando ser firmado */
                    $numero = 0;
                    $siglas = '';
                    $firma = '';
                    $cargo  = '';
                    
                } else {

                    $dependencia_id = $form->dependencia_id->getValue(); 
                    $numero = $form->numero->getValue();                
                    $firma = '';
                    $cargo  = '';
                    
                }
                

            }
            
            $expediente_id = $form->expediente_id->getValue();
            if ($expediente_id == ''){
                $expediente_id = 0;
            }
            
            /**/

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
            
            /* Fin de Recibo datos... */

            if($tipo == 1){ /* Si es Doc. Personal */

                $dependencia_registra_id = $dependencia_id;
                
            } else { /* Es documento de jefatura */
                
                $dependencia_registra_id = $form->dependencia_registra_id->getValue();
                
            }
            
            /* Creo mi objeto */
            $data = array('origen'                  => 1, /* Interno */
                          'tipo'                    => $tipo, /* 0->Doc. Jefatural, 1->Doc. Personal */
                          'expediente_id'           => $expediente_id,                                                
                          'dependencia_id'          => $dependencia_id,
                          'firma'                   => $firma,
                          'cargo'                   => $cargo,
                          'tipo_documento_id'       => $tipo_documento_id,
                          'numero'                  => $numero,
                          'asunto'                  => $form->asunto->getValue(),
                          'referencia'              => $referencia,
                          'cuerpo'                  => $form->cuerpo->getValue(),
                          'dependencia_registra_id' => $dependencia_registra_id,
                          'usuario_id'              => $this->_usuario->id
                    );
            
            try {
                
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                
                /* Actualizo mi objeto */
                $documento_id = $this->_modelo->save($data);

                /* Si es doc. para Firma Jefatural */
//                if($tipo == 0){
                    
                    /* Si el que registra el doc. es el mismo que debe firmar el documento, 
                     * entonces debe grabarse ya con la firma */
                    
                    /* Obtengo el pers_id del que debe firmar el documento */
//                    $objModelDependencia = new Gidoc_Model_DependenciaMapper();
//                    $objDependencia = $objModelDependencia->getById($dependencia_id);
                    
//                    if($objDependencia->representante_id == $this->_usuario->pers_id){
                        
//                        $this->_modelo->firmarDocumento($documento_id, $this->_usuario->id);
                        
//                    }
                    
//                }
                
                /* FIN:  Si es doc. para Firma Jefatural */
                
                /* Guardo los datos correspondientes para la tabla destinos */
                $modelDestino = new Gidoc_Model_DestinoMapper();

                /* Grabo el destino */
                $dataDestino = array('dependencia_iddestino' => $dependencia_iddestino,
                                     'para'          => $paraDestino,
                                     'cargo'         => $cargoDestino,
                                     'dependencia'   => $dependenciaDestino,
                                     'documento_id'  => $documento_id,
                                     'usuario_id'    => $this->_usuario->id                    
                                );
                
                $modelDestino->save($dataDestino);

                /* Para mostrar mensaje de Registro Grabado */
                $registro_id = $documento_id;

                /* Obtengo los datos del nuevo registro */
                $objDocumento = $this->_modelo->getDocumentoById($registro_id);
                
                $documento = $objDocumento->documento;
                $fechaRegistro = new Zend_Date($objDocumento->fecha_registro, Zend_Date:: ISO_8601);
                $fechaHora = $fechaRegistro->toString('dd/MM/yyyy h:m:s');
                $origen = $objDocumento->dependencia_origen;
                $expediente_id = $objDocumento->expediente_id;
                $secuencia = $objDocumento->secuencia;
                
                $texto = "
                <spam style='font-size: 16px;'> Registro grabado: </spam>
                <spam style='font-size: 22px;font-weight: bold;'>$expediente_id-$secuencia</spam>'
                <p>
                <spam style='font-size: 12px;font-weight: bold;'> Documento: </spam>
                <spam style='font-size: 12px;'>$documento</spam>'
                <p>
                <spam style='font-size: 12px;font-weight: bold;'> Orígen: </spam>
                <spam style='font-size: 12px;'>$origen</spam>'
                <p>                
                <spam style='font-size: 12px;font-weight: bold;'> Fecha/Hora de registro: </spam>
                <spam style='font-size: 12px;'>$fechaHora</spam>'
                <p>   
                <div style='width:100%; text-align: center;' >
                <a href='#' onclick=$('#msgFlash').fadeOut('slow') >Cerrar</a>
                </div>
                ";
                
                $this->_helper->FlashMessenger(array('notice' => $texto)); 
                
                /* Fin Para mostrar mensaje de Registro Grabado */
                
                /**/
                $db->commit();                                

            } catch(Exception $ex) {
                //throw new Exception("Error al Editar el registro". $ex->getMessage());
                
                /* Para mostrar el error en el Flash Messenger y mantener los datos del formulario */
                $texto = $ex->getMessage();
                
                $texto .= "
                <div style='width:100%; text-align: center;' >
                <a href='#' onclick=$('#msgFlash').fadeOut('slow') >Cerrar</a>
                </div>
                ";
                
                $this->_helper->FlashMessenger(array('error' => $texto));                                
                
                $form->populate($formData);
                $this->view->form = $form;
                return $this->render('editarinterno');
                /* */
                
                $db->rollback();                
                
            }
            
            $this->_helper->redirector('poratender', 'documentos', 'gidoc');            
            
        } else {
            
            /* Si recibo una plantilla */
            $plantilla_id = (int)$this->_request->getParam('plantilla_id', 0);
            
            if($plantilla_id){

                $this->view->desdePlantilla = true;
                
                /* Obtengo el objeto de la tabla Documento */
                $modelPlantilla = new Gidoc_Model_PlantillaMapper();
                $obj = $modelPlantilla->getById($plantilla_id);

                /* Paso los campos del objeto a un array  */
                $arrayData = $obj->toArray();

                /* Obtengo el objeto de la tabla Destinos */
                $modelDestino = new Gidoc_Model_DestinoplantillaMapper();
                $objDestino = $modelDestino->getByDocumentoId($plantilla_id);

                /* Paso los campos al arrayData */
                $arrayData['id_destino'] = $objDestino->id;
                $arrayData['dependencia_iddestino'] = "$objDestino->dependencia_iddestino";                                
                $arrayData['para_destino'] = $objDestino->para;
                $arrayData['cargo_destino'] = $objDestino->cargo;
                $arrayData['dependencia_destino'] = $objDestino->dependencia;
                
                /* Relleno el formulario */
                $form->populate($arrayData);
                
            }
           
            /* FIN - Si recibo una plantilla */

            /* Para recibir número de expediente */
            $expediente_id = $this->_request->getParam('expdte_id');
            
            if($expediente_id){
                $form->expediente_id->setValue($expediente_id);
            }
            /* Fin: Para recibir número de expediente */
            
            /* Seteo el elemento dependencia_id con la dependencia del usuario que registra */
            if($tipo == 0){ /* si es documento jefatural */            
                $form->getElement('dependencia_id')->setValue($this->_usuario->dependencia_id);
            }
            
            /* Preparo datos para la vista */
            $this->view->tipo = $tipo;
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
        $id = (int)$this->_request->getParam('id', 0); /* El Id del registro que se va a editar */
        $tipo = (int)$this->_request->getParam('tipo', 1);   /* si no llega el parámetro sigifica que es Documento Personal (1)*/

        /* Traigo el formulario*/
        $form = $this->_form->getFrmEditaInterno($tipo);
        
        if($tipo == 1){ /* si es documento personal */
            $form->setAction($this->view->baseUrlController . '/editarinterno/id/'. $id .'/');
        } else { /* Es documento jefatural */
            $form->setAction($this->view->baseUrlController . '/editarinterno/id/'. $id .'/tipo/0');
        }
        

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $objMovimiento = new Gidoc_Model_MovimientoMapper();
            $movimiento = $objMovimiento->getById($id);
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

            $expediente_id = $form->expediente_id->getValue();
            if ($expediente_id == ''){
                $expediente_id = 0;
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
            
            /* Fin de Recibo datos... */
            
            if($tipo == 1){ /* si es documento personal */
            
                $data = array('id'                      => $id,
                            'dependencia_id'            => $form->dependencia_id->getValue(),                    
                            'tipo_documento_id'       => $form->tipo_documento_id->getValue(),
                            'expediente_id'           => $expediente_id,                                                                                        
                            'numero'                  => $form->numero->getValue(),                                
                            'asunto'                  => $form->asunto->getValue(),
                            'referencia'              => $referencia,
                            'cuerpo'                  => $form->cuerpo->getValue()
                        );

            } else { /* Si es documento jefatural */

                $data = array('id'                      => $id,
                            'dependencia_id'          => $form->dependencia_id->getValue(), /* queda en proceso en la oficina donde está esperando ser firmado */                
                            'tipo_documento_id'       => $form->tipo_documento_id->getValue(),
                            'expediente_id'           => $expediente_id,                                                                                        
                            'asunto'                  => $form->asunto->getValue(),
                            'referencia'              => $referencia,
                            'cuerpo'                  => $form->cuerpo->getValue()
                        );
                
            }
            
            try {
                /* Actualizo mi objeto */
                $this->_modelo->save($data);
                
                /* Guardo los datos correspondientes para la tabla destinos */
                $modelDestino = new Gidoc_Model_DestinoMapper();

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
                $texto = $ex->getMessage();
                
                $texto .= "
                <div style='width:100%; text-align: center;' >
                <a href='#' onclick=$('#msgFlash').fadeOut('slow') >Cerrar</a>
                </div>
                ";
                
                $this->_helper->FlashMessenger(array('error' => $texto));                                
                
                $form->populate($formData);
                $this->view->tipo = $tipo;                
                $this->view->form = $form;
                return $this->render();
                /* */
            }
            
            $this->_helper->redirector('poratender', 'documentos', 'gidoc');            
            
        } else { /* Se llama al formulario   */
    
            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $objMovimiento = new Gidoc_Model_MovimientoMapper();
            $movimiento = $objMovimiento->getById($id);
            $id = $movimiento->documento_id;
            
            /* Obtengo el objeto de la tabla Documento */
            $obj = $this->_modelo->getById($id);

            /* Paso los campos del objeto a un array  */
            $arrayData = $obj->toArray();

            /* Obtengo el objeto de la tabla Destinos */
            $modelDestino = new Gidoc_Model_DestinoMapper();
            $objDestino = $modelDestino->getByDocumentoId($id);
            
            /* Paso los campos al arrayData */
            $arrayData['id_destino'] = $objDestino->id;
            $arrayData['dependencia_iddestino'] = "$objDestino->dependencia_iddestino";            
            $arrayData['para_destino'] = $objDestino->para;
            $arrayData['cargo_destino'] = $objDestino->cargo;
            $arrayData['dependencia_destino'] = $objDestino->dependencia;
            
            /* Creo variable para poder ocultar el botón guardar, si el que edita no es el dueño del registro */
            $this->view->usua_idregistro = $arrayData['usuario_id'];

            /* Variables para poder subir archivos */
            $this->view->id = $id;
            /* Para el Grid archivos */
            /* Configuración del jqgrid */
            $this->view->archivos_colNames = "'Archivo',''";
            $this->view->archivos_colModel = "{name:'descripcion', index:'descripcion', width:50},
                                              {name:'opcion', index:'opcion', width:10}";
            $this->view->archivos_sortName = "descripcion"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        
            /* Fin:  Variables para poder subir archivos */
            
            /* LLeno los campos del formulario con los datos del array */
            $form->populate($arrayData);
            $this->view->tipo = $tipo;
            $this->view->form = $form;
            $this->view->documento = $obj;            
            $this->render('editarinterno');
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
                    $resultado = $this->_modeloMovimiento->eliminar($id, $this->_usuario);
                    
                    if($resultado == 0) { /* Si no se eliminó */
                        echo 'error|No está autorizado a eliminar este registro';                        
                        exit;
                    }
                    
                    
                } catch(Exception $ex) {
                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }
            }
        }
    }
    
    
    public function archivarAction()
    {
        /* Recibo la lista de Ids separado por comas */
        $idList = $this->_getParam('id');
        
        /* Traigo el formulario*/
        $form = $this->_form->getFrmArchiva();
        $form->setAction($this->view->baseUrlController . '/archivar/id/'. $idList .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo la lista de Ids separado por comas */
            //$idList = $this->_getParam('id');

            if ($idList) {
                /* Convierto la lista a un Array */
                $idArray = explode (",", $idList);
                
                /* Recibo datos del formulario */
                $formData = $this->_request->getPost();                

                /* Lleno el formulario con los datos recibidos por _POST */
                $form->populate($formData);
                
                /* Creo el objeto Movimiento */
                $objMovimiento = new Gidoc_Model_MovimientoMapper();
                
                /* Recorro el array eliminando cada Id */
                foreach ($idArray as $id) {

                    /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
                    $movimiento = $objMovimiento->getById($id);
                    $documento_id = $movimiento->documento_id;

                    /* Creo mi array */
                    $data = array('documento_id'    => $documento_id,
                                'dependencia_id'    => $this->_usuario->dependencia_id,
                                'usuario_id'        => $this->_usuario->id,
                                'tipomovimiento_id' => 4,
                                'acciones'          => $form->acciones->getValue(),
                                'archivador_id'     => $form->archivador_id->getValue(),
                                'movimientoprocesado_id' => $id);
                    
                    $movimiento = new Gidoc_Model_MovimientoMapper();
                    try {
                        $movimiento->save($data);
                    } catch(Exception $ex) {
                        $message = $ex->getMessage();
                        echo 'error|'.$message;
                    }
                }
            }

            
        } else { /* Se llama al formulario   */
            
            $this->view->form = $form;
            $this->_forward('myeditar','index','default');

        }
    }


    public function adjuntarAction()
    {
        /* Recibo la lista de Ids separado por comas */
        $idList = $this->_getParam('id');
        
        /* Traigo el formulario*/
        $form = $this->_form->getFrmAdjunta($idList);
        $form->setAction($this->view->baseUrlController . '/adjuntar/id/'. $idList .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo la lista de Ids separado por comas */
            //$idList = $this->_getParam('id');

            if ($idList) {
                /* Convierto la lista a un Array */
                $idArray = explode (",", $idList);
                
                /* Recibo datos del formulario */
                $formData = $this->_request->getPost();                

                /* Lleno el formulario con los datos recibidos por _POST */
                $form->populate($formData);
                
                /* Creo el objeto Movimiento */
                $objMovimiento = new Gidoc_Model_MovimientoMapper();
                
                /* Recorro el array eliminando cada Id */
                foreach ($idArray as $id) {

                    /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
                    $movimiento = $objMovimiento->getById($id);
                    $documento_id = $movimiento->documento_id;

                    /* Creo mi array */
                    $data = array('documento_id'         => $documento_id,
                                'dependencia_id'         => $this->_usuario->dependencia_id,
                                'usuario_id'             => $this->_usuario->id,
                                'tipomovimiento_id'      => 6,
                                'acciones'               => $form->acciones->getValue(),
                                'documento_idadjuntado'  => $form->documento_idadjuntado->getValue(),
                                'movimientoprocesado_id' => $id);
                    
                    $movimiento = new Gidoc_Model_MovimientoMapper();
                    try {
                        $movimiento->save($data);
                    } catch(Exception $ex) {
                        $message = $ex->getMessage();
                        echo 'error|'.$message;
                    }
                }
            }

            
        } else { /* Se llama al formulario   */
            
            $this->view->form = $form;
            $this->_forward('myeditar','index','default');

        }
    }
    
    
        public function devolverdearchivoAction()
    {
        
        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */

            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo la lista de Ids separado por comas */
            $idList = $this->_getParam('id');
            
            if ($idList) {
                /* Convierto la lista a un Array */
                $idArray = explode (",", $idList);
                
                /* Recibo datos del formulario */
                $formData = $this->_request->getPost();                

                /* Creo el objeto Movimiento */
                $objMovimiento = new Gidoc_Model_MovimientoMapper();
                
                /* Recorro el array eliminando cada Id */
                foreach ($idArray as $id) {

                    /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento */
                    $movimiento = $objMovimiento->getById($id);
                    $documento_id = $movimiento->documento_id;

                    /* Creo mi array */
                    $data = array('documento_id'    => $documento_id,
                                'dependencia_id'    => $this->_usuario->dependencia_id,
                                'usuario_id'        => $this->_usuario->id,
                                'tipomovimiento_id' => 5,
                                'movimientoprocesado_id' => $id);
                    
                    $movimiento = new Gidoc_Model_MovimientoMapper();
                    try {
                        $movimiento->save($data);
                    } catch(Exception $ex) {
                        $message = $ex->getMessage();
                        echo 'error|'.$message;
                    }
                }
            }

        } 
    }

        public function desadjuntarAction()
    {
        
        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */

            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo la lista de Ids separado por comas */
            $idList = $this->_getParam('id');
            $documento_idadjuntado = $this->_getParam('idadjuntado');            
            
            if ($idList) {
                /* Convierto la lista a un Array */
                $idArray = explode (",", $idList);
                
                /* Recibo datos del formulario */
                $formData = $this->_request->getPost();                

                /* Creo el objeto Movimiento */
                $objMovimiento = new Gidoc_Model_MovimientoMapper();
                
                /* Recorro el array eliminando cada Id */
                foreach ($idArray as $id) {

                    /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento */
                    $movimiento = $objMovimiento->getById($id);
                    $documento_id = $movimiento->documento_id;

                    /* Creo mi array */
                    $data = array('documento_id'    => $documento_id,
                                'dependencia_id'    => $this->_usuario->dependencia_id,
                                'usuario_id'        => $this->_usuario->id,
                                'tipomovimiento_id' => 7,
                                'documento_idadjuntado'  => $documento_idadjuntado,                        
                                'movimientoprocesado_id' => $id);
                    
                    $movimiento = new Gidoc_Model_MovimientoMapper();
                    try {
                        $movimiento->save($data);
                    } catch(Exception $ex) {
                        $message = $ex->getMessage();
                        echo 'error|'.$message;
                    }
                }
            }

        } 
    }
    
    public function derivarAction()
    {
        /* Recibo la lista de Ids separado por comas */
        $idList = $this->_getParam('id');
        
        /* Traigo el formulario*/
        $form = $this->_form->getFrmDeriva();
        $form->setAction($this->view->baseUrlController . '/derivar/id/'. $idList .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            if ($idList) {
                /* Convierto la lista a un Array */
                $idArray = explode (",", $idList);
                
                /* Recibo datos del formulario */
                $formData = $this->_request->getPost();                

                /* Lleno el formulario con los datos recibidos por _POST */
                $form->populate($formData);
                
                /* Creo el objeto Movimiento */
                $objMovimiento = new Gidoc_Model_MovimientoMapper();

                /* Verifico el valor del usuario destino */
                $usuariodestino_id = @$formData['usuariodestino_id']; /* Como es un elemento que se a agregado con ajax y no se encuentra en el formulario, entonces debo obtener el dato directamente del $formdata */
                if($usuariodestino_id == ''){
                    $usuariodestino_id = null;
                }
                
                /* Para determinar si está derivando a una oficina o a un grupo */
                $dependenciadestino_id = $form->dependenciadestino_id->getValue();
                $destinoArray = explode("-", $dependenciadestino_id);
                $tipoDestino = $destinoArray[0];
                $destino_id = $destinoArray[1];
                
                
                /* Recorro el array */
                foreach ($idArray as $id) {

                    /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento */
                    $movimiento = $objMovimiento->getById($id);
                    $documento_id = $movimiento->documento_id;

                    if($tipoDestino == 'ofi') {  /* Si se está derivando a una oficina */
                    
                        /* Creo mi array */
                        $data = array('documento_id'    => $documento_id,
                                    'dependencia_id'    => $this->_usuario->dependencia_id,
                                    'usuario_id'        => $this->_usuario->id,
                                    'tipomovimiento_id' => 2,
                                    'acciones'          => $form->acciones->getValue(),
                                    'dependenciadestino_id'  => $destino_id,
                                    'usuariodestino_id'      => $usuariodestino_id,
                                    'movimientoprocesado_id' => $id);

                        $movimiento = new Gidoc_Model_MovimientoMapper();
                        try {
                            $movimiento->save($data);
                        } catch(Exception $ex) {
                            $message = $ex->getMessage();
                            echo 'error|'.$message;
                        }
                    
                    } else { /* Se está derivando a un grupo */
                        
                        try {

                            $this->_modelo->grabaDerivacionesDeGrupo($destino_id, $this->_usuario->id, $form->acciones->getValue(), $id);

                        } catch(Exception $ex) {

                            $message = $ex->getMessage();
                            echo 'error|'.$message;
                        }
                        
                    }   
                    
                }
            }

            
        } else { /* Se llama al formulario   */
            
            $this->view->form = $form;
            $this->_helper->layout->disableLayout();
            $this->render('derivar');
            //$this->_forward('myeditar','index','default');

        }
    }
    
    public function derivartemporalAction()
    {
        
        /* Traigo el formulario*/
        $form = $this->_form->getFrmDeriva();
        $form->setAction($this->view->baseUrlController . '/derivartemporal/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            
            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo datos del formulario */
            $formData = $this->_request->getPost();                

            /* Lleno el formulario con los datos recibidos por _POST */
            $form->populate($formData);

            $dependenciadestino_id = $form->dependenciadestino_id->getValue();
            $destinoArray = explode("-", $dependenciadestino_id);
            $tipoDestino = $destinoArray[0];
            $destino_id = $destinoArray[1];
            
            if($tipoDestino == 'ofi') {  /* Si se está derivando a una oficina */
            
                /* Verifico el valor del usuario destino */
                $usuariodestino_id = $formData['usuariodestino_id']; /* Como es un elemento que se a agregado con ajax y no se encuentra en el formulario, entonces debo obtener el dato directamente del $formdata */
                if($usuariodestino_id == ''){
                    $usuariodestino_id = null;
                }

                /* Creo mi array */
                $data = array('usuario_id'        => $this->_usuario->id,
                            'acciones'          => $form->acciones->getValue(),
                            'dependenciadestino_id'  => $destino_id,
                            'usuariodestino_id'      => $usuariodestino_id);

                $movimiento = new Gidoc_Model_DerivotemporalMapper();
                try {
                    $movimiento->save($data);
                } catch(Exception $ex) {
                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }

            } else { /* Se está derivando a un grupo */ 

                try {

                    $this->_modelo->grabaDerivacionesDeGrupo($destino_id, $this->_usuario->id, $form->acciones->getValue());

                } catch(Exception $ex) {

                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }
                
            }
                
        } else { /* Se llama al formulario   */
            
            $this->view->form = $form;
            $this->_helper->layout->disableLayout();
            $this->render('derivar');

        }
    }
    
    public function getForDivusuarioAction()
    {
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();


        /* Recibo la dependencia */
        $dependencia_id = $this->_getParam('dependenciadestino_id');
        $usuario_id = $this->_usuario->id;
        
        $selectUsuario = new Zend_Form_Element_Select('usuariodestino_id');
        $selectUsuario->setLabel('Usuario')
                      ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT id,nombres || ' ' || apellidos AS usuario 
                                                          FROM usuarios
                                                          WHERE dependencia_id = $dependencia_id AND id <> $usuario_id 
                                                          ORDER BY nombres"));

        $selectUsuario->addMultiOption('', '--- Cualquiera ---');        
        $selectUsuario->setValue('');
        
        /* Imprimo el html del elemento */
        $bootstrap = $this->getInvokeArg('bootstrap');
        $view = $bootstrap->getResource('View');
        echo $selectUsuario->render($view);
    }
    
    public function derivarXXAction()
    {
        /* Desactivo el Layout */
        $this->_helper->layout->disableLayout();
        
        /* Obtengo el objeto Padre*/
        $padre_id = $this->_getParam('padre_id');

        $this->view->titulo = 'Derivar';

        /* Recibo la lista de Ids separado por comas */
        $this->view->padre_id = $padre_id;

        /* Configuración del jqgrid */
        $this->view->colNames = "'Oficina','Usuario'";
        $this->view->colModel = "{name:'concepto', index:'concepto', width:200, sortable:false},
                                 {name:'importe', index:'importe', width:100, sortable:false}";
        $this->view->sortName = "oficina"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */

    }
    
    public function porrecibirAction()
    {
        /* Asigno el título según el controller del navigation */
        $page = $this->view->navigation()->findOneBy('action',$this->_request->getActionName());        
        $this->view->titulo = $page->_title;        

        /* Configuración del jqgrid */
        $this->view->colNames = "'Registro','Orígen','Firma','Cargo','Documento','Fecha Doc.','Asunto','Derivado por','Acciones','tipomovimiento_id'";
        $this->view->colModel = "{name:'documento_id', index:'documento_id', width:10},
                                 {name:'dependencia_origen', index:'dependencia_origen', width:40},
                                 {name:'firma', index:'firma', width:30},
                                 {name:'cargo', index:'cargo', width:30},
                                 {name:'documento', index:'documento', width:40},
                                 {name:'fecha_documento', index:'fecha_documento', width:20, formatter:'date', formatoptions:{srcformat:'Y-m-d',newformat:'d/m/Y'}},
                                 {name:'asunto', index:'asunto', width:40},
                                 {name:'oficina_destino', index:'oficina_destino', width:40},
                                 {name:'acciones', index:'acciones', width:40},
                                 {name:'tipomovimiento_id', index:'tipomovimiento_id', width:10}";
        $this->view->hideCol = "'tipomovimiento_id'";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 800;
        $this->view->fieldFocus = 'firma';
        $this->view->editarSinDialog = true;
        
        $formOficinas = $this->_form->getFrmOficinas();
        $this->view->formOficinas = $formOficinas;
        
    }
    
    public function listarporrecibirAction()
    {
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Recibo otros parámetros que envío */
        $txtBuscar = $this->getRequest()->getParam('txtBuscar'); /* Texto a buscar */
        $todaOficina = $this->getRequest()->getParam('todaOficina'); 
        $dependencia_busca_id = $this->getRequest()->getParam('dependencia_busca_id'); 
     
        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        if(!$dependencia_busca_id){
            $dependencia_busca_id = $this->_usuario->dependencia_id;
        }
        
        $usuario_id = $this->_usuario->id;                

        if($todaOficina){

            $where = "a.dependenciadestino_id = $dependencia_busca_id AND a.tipomovimiento_id IN (2) AND a.procesado = FALSE AND ";
            
        } else {

            $where = "(a.dependenciadestino_id = $dependencia_busca_id AND a.usuariodestino_id = $usuario_id) AND a.tipomovimiento_id IN (2) AND a.procesado = FALSE AND ";
            
        }
        
        $where .= "a.documento_id||c.nombre||b.firma||b.cargo||b.asunto ILIKE '%$txtBuscar%'";

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

        foreach ($rsArray as $fila) {
            $id = $fila['id'];

            $derivadoPor = $fila['oficina_registra'].'/'.$fila['usuario_registra'];                          
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($fila['expediente_id'].'-'.$fila['secuencia'], $fila['dependencia_origen'], $fila['firma'], 
                                                $fila['cargo'], $fila['documento'], $fila['fecha_documento'],
                                                $fila['asunto'], $derivadoPor,
                                                $fila['acciones'], $fila['tipomovimiento_id']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }

    public function porfirmarAction()
    {
        /* Asigno el título según el controller del navigation */
        $this->view->titulo = 'Documentos por firmar';        

        /* Configuración del jqgrid */
        $this->view->colNames = "'Registro','Documento','Fecha Doc.','Asunto','Proyectado por','Acciones'";
        $this->view->colModel = "{name:'documento_id', index:'documento_id', width:10},
                                 {name:'documento', index:'documento', width:30},
                                 {name:'fecha_documento', index:'fecha_documento', width:20, formatter:'date', formatoptions:{srcformat:'Y-m-d',newformat:'d/m/Y'}},
                                 {name:'asunto', index:'asunto', width:40},
                                 {name:'oficina_destino', index:'oficina_destino', width:40},
                                 {name:'', index:'', width:30}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 800;
        $this->view->fieldFocus = 'firma';
        $this->view->editarSinDialog = true;
        
    }
    
    public function listarporfirmarAction()
    {
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        /* Obtengo las dependencias donde es jefe el usuario */
        $modeloDependencia = new Gidoc_Model_DependenciaMapper();
        $dependenciaIds = $modeloDependencia->getDependenciasDondeEsJefe($this->_usuario->id);
        $dependenciaIds = implode(',',$dependenciaIds);
        /**/
        
        $where = "b.tipo = 0 AND b.dependencia_id IN ($dependenciaIds) AND b.firmante_id IS NULL AND a.tipomovimiento_id IN (1,5) AND a.procesado = FALSE ";

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

        foreach ($rsArray as $fila) {
            $id = $fila['id'];
            $documento_id = $fila['documento_id'];
            
            $proyectadoPor = $fila['oficina_registra'].'/'.$fila['usuario_registra'];    

            $urlrptDocumento = "/gidoc/rptdocumento/index/documento_id/$documento_id";            
            
            $linkVerArchivo = "<a target='_blank' href='$urlrptDocumento' >Ver documento</a>"; 
            $linkFirmar = "<a href='#' id='btnRecibir' >Firmar</a>"; 
            $botones = "$linkVerArchivo <button type='button' onclick='firmar($documento_id)' >Firmar</button>";
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($fila['expediente_id'].'-'.$fila['secuencia'], $fila['documento'], $fila['fecha_documento'],
                                                $fila['asunto'], $proyectadoPor, $botones);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }
    
    public function buscarAction()
    {
        /* Asigno el título según el controller del navigation */
        $page = $this->view->navigation()->findOneBy('action',$this->_request->getActionName());        
        $this->view->titulo = $page->_title;        

        /* Configuración del jqgrid */
        $this->view->colNames = "'Registro','Orígen','Firma','Cargo','Documento','Fecha Doc.','Asunto'";
        $this->view->colModel = "{name:'documento_id', index:'documento_id', width:10},
                                 {name:'dependencia_origen', index:'dependencia_origen', width:40},
                                 {name:'firma', index:'firma', width:30},
                                 {name:'cargo', index:'cargo', width:30},
                                 {name:'documento', index:'documento', width:40},
                                 {name:'fecha_documento', index:'fecha_documento', width:20, formatter:'date', formatoptions:{srcformat:'Y-m-d',newformat:'d/m/Y'}},
                                 {name:'asunto', index:'asunto', width:40}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        $this->view->widthDialog = 800;
        $this->view->fieldFocus = 'firma';
        $this->view->editarSinDialog = true;
        
        /* Obtengo formulario para búsqueda */
        $form = $this->_form->getFrmBuscar();
        $this->view->form = $form;
        
    }
    
    public function listarporbuscarAction()
    {
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Recibo otros parámetros que envío */
        $expediente_id = $this->getRequest()->getParam('expediente_id');                 
        $fechaDesde = $this->getRequest()->getParam('fecha_desde'); 
        $fechaHasta = $this->getRequest()->getParam('fecha_hasta'); 
        $tipo_documento_id = $this->getRequest()->getParam('tipo_documento_id');         
        $firma = $this->getRequest()->getParam('firma');         
        $asunto = $this->getRequest()->getParam('asunto');         

        $where = "1=1";        

        if($expediente_id){
            $where .= " AND a.expediente_id = $expediente_id";
        }
        
        if($fechaDesde <> '' and $fechaHasta <> ''){
            $where .= " AND a.fecha_registro::DATE BETWEEN '$fechaDesde' AND '$fechaHasta'";        
        }

        if($tipo_documento_id){
            $where .= " AND a.tipo_documento_id = $tipo_documento_id";
        }
        
        if($firma){
            $where .= " AND a.firma ILIKE '%$firma%'";
        }
        
        if($asunto){
            $where .= " AND a.asunto ILIKE '%$asunto%'";
        }
        
        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

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

            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($fila['expediente_id'].'-'.$fila['secuencia'], $fila['dependencia_origen'], $fila['firma'], 
                                                $fila['cargo'], $fila['documento'], $fila['fecha_documento'],
                                                $fila['asunto']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }
    
    
    public function recibirAction()
    {
        /* Recibo la lista de Ids separado por comas */
        $idList = $this->_getParam('id');
        
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        /* Convierto la lista a un Array */
        $idArray = explode (",", $idList);

        /* Creo el objeto Movimiento */
        $objMovimiento = new Gidoc_Model_MovimientoMapper();

        /* Recorro el array eliminando cada Id */
        foreach ($idArray as $id) {

            /* Como recibo el Id del Movimiento, entonces obtengo el id de la tabla Documento para poder editarlo */
            $movimiento = $objMovimiento->getById($id);
            $documento_id = $movimiento->documento_id;

            /* Creo mi array */
            $data = array('documento_id'    => $documento_id,
                        'dependencia_id'    => $this->_usuario->dependencia_id,
                        'usuario_id'        => $this->_usuario->id,
                        'tipomovimiento_id' => 3,
                        'movimientoprocesado_id' => $id);

            $movimiento = new Gidoc_Model_MovimientoMapper();
            try {
                $movimiento->save($data);
            } catch(Exception $ex) {
                $message = $ex->getMessage();
                echo 'error|'.$message;
            }
        }
            
    }

   public function obtienenumeroAction()
    {
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        /* Recibo parámetros */
        $tipo_documento_id = $this->_getParam('tipo_documento_id');
        $dependencia_id = $this->_getParam('dependencia_id');
        $tipo = $this->_getParam('tipo');
        $periodo = date("Y");
        
        /* Obtengo el número de doc que corresponde según el tipo de documento */
        $modelCorrelativo = new Gidoc_Model_TiposdocumentocorrelativoMapper();
        
        $numero = $modelCorrelativo->getNumeroCorrelativo(0, $tipo, $tipo_documento_id, 
                                                                $dependencia_id, 
                                                                $this->_usuario->id, 
                                                                $periodo, 1);
        
        /* Devuelvo el id del vehículo */
        echo $numero;
    }

   public function obtienedatosdestinoAction()
    {
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        /* Recibo parámetros */
        $dependencia_iddestino = $this->_getParam('dependencia_iddestino');

        $modelDependencia = new Gidoc_Model_DependenciaMapper();        
        
        $objDependencia = $modelDependencia->getById($dependencia_iddestino);
        
        echo $objDependencia->nombre.'|'.$objDependencia->cargo;
        
    }
    
   public function obtienedatosexpdteAction()
    {
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        /* Recibo parámetros */
        $expdte_id = $this->_getParam('expdte_id');

        /* Obtengo el primer documento del expdte */
        $objDocumento = $this->_modelo->getDocumentoInicialDeExpdte($expdte_id);

        /*
        $documento = $objDocumento->documento;
        $fechaRegistro = new Zend_Date($objDocumento->fecha_registro, Zend_Date:: ISO_8601);
        $fechaHora = $fechaRegistro->toString('dd/MM/yyyy h:m:s');
        $origen = $objDocumento->dependencia_origen;
*/

        if($objDocumento){
            echo Zend_Json::encode($objDocumento);             
        } else {
            echo Zend_Json::encode('Error:No existe el expediente');
        }

        
    }

    
   /***
    * Para firmar un documento.
    */ 
   public function firmarAction()
    {
       
        /* Recibo la lista de Ids separado por comas */
        $documento_id = $this->_getParam('documento_id');
        
        /* Traigo el formulario*/
        $form = $this->_form->getFrmFirma();
        $form->setAction($this->view->baseUrlController . '/firmar/documento_id/'. $documento_id .'/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
       
            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo datos del formulario */
            $formData = $this->_request->getPost();                

            /* Verifico el valor del usuario destino */
            $clasificacion_id = $formData['clasificacion_id']; 
            
            try {
                
                /* Verifco que el usuario tenga un mail*/
                if($this->_usuario->email == '' or is_null($this->_usuario->email)){
                    throw new Exception('Usted no tiene asignado ningún correo electrónico.  Actualice su correo electrónico');
                }
                
                /* Me aseguro que exista el pdf */
                $rutaDocs    = $this->_config->general->rutaDocs;
                $filePdfDoc = "{$rutaDocs}$documento_id.pdf";
                
                if(!file_exists($filePdfDoc)) {
                    /* Puedo usar cualquiera de las dos opciones */
                    //throw new Exception('Antes de firmar, usted debe ver el documento');
                    echo 'information|'.'Antes de firmar, usted debe ver el documento';
                    exit;
                    
                }
                
                /* Firmo el documento */
                $this->_modelo->firmarDocumento($documento_id, $this->_usuario->id, $clasificacion_id);

                /* Vuelvo a generar el pdf, para poder obtener el número de documento y la firma y poder enviarlo al correo. */
                $params = array('documento_id' => $documento_id, 'soloGeneraPdf' => 1);
                $xx = $this->_helper->redirector('index', 'rptdocumento', 'gidoc', $params);
//            $aa = $this->_forward('index','rptdocumento','gidoc', $params);        NO FUNCIONA    
            

            } catch(Exception $ex) {

                $message = $ex->getMessage();
                echo 'error|'.$message;
            }
            
        } else {

            $this->view->documento_id = $documento_id;
            $this->view->form = $form;
            $this->_helper->layout->disableLayout();
            $this->render('firmar');
            
        }
    }

   public function enviardocalmailAction()
    {
       
        /* Recibo la lista de Ids separado por comas */
        $documento_id = $this->_getParam('documento_id');
        
            /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        try {

                /* Me aseguro que exista el pdf */
                $rutaDocs    = $this->_config->general->rutaDocs;
                $filePdfDoc = "{$rutaDocs}$documento_id.pdf";
            
                if(!file_exists($filePdfDoc)) {
                    throw new Exception('No existe el PDF para enviar por correo');
                }
            
                /* Envío mail con el doc. que acaba de firmar */
                $emailUsuario = trim($this->_usuario->email);
                $mensaje = "Se adjunta su documento firmado. <BR><BR>
                            <b>Identificador en el Sistema: $documento_id </b> <br><br>
                            <b>Nota:</b> NO RESPONDA A ESTE CORREO.  Ha sido generado de forma automática por el Sistema.
                            ";
                $nombreSistema = $this->_config->general->siglas;                
                
                /* Preparo el archivo a adjuntar */
                $attachment = new Zend_Mime_Part(file_get_contents($filePdfDoc));
                $attachment->type = 'application/pdf';
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                $attachment->filename = $documento_id;                
                /* Fin: Preparo el archivo a adjuntar */
                
                try {

                    $this->seteaMail();                        
                    $mail = new Zend_Mail();

                    $mail->setBodyHtml(utf8_decode($mensaje))
                            ->setFrom($this->_emailAdmin,"$nombreSistema")
                            ->setReplyTo($emailUsuario, 'Usuario')
                            ->addTo("$emailUsuario", "$nombreSistema")
                            ->addAttachment($attachment)
                            ->setSubject(utf8_decode("Mensaje desde: $nombreSistema"))
                            ->send();

                } catch(Exception $ex) {
                    throw new Exception("Error al enviar correo: ". $ex->getMessage()); 
                }
                
                /* Fin de:  Envío mail */
            
        } catch (Exception $ex) {

            $message = $ex->getMessage();
            echo 'error|' . $message;
        }
        
    }

   /***
    * Para Deshacer firma
    */ 
   public function deshacerfirmaAction()
    {
       
        /* Recibo la lista de Ids separado por comas */
        $documento_id = $this->_getParam('documento_id');
        
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        try {

            $this->_modelo->deshacerFirma($documento_id, $this->_usuario->id);

        } catch(Exception $ex) {

            $message = $ex->getMessage();
            echo 'error|'.$message;
        }
            
    }

   public function deshacervisadoAction()
    {
       
        /* Recibo la lista de Ids separado por comas */
        $documento_id = $this->_getParam('documento_id');
        $documento_visto_id = $this->_getParam('documento_visto_id');        
        
        /* Desactivo el auto renderizado */
        $this->disableAutoRender();

        try {

            $this->_modelo->deshacerFirma($documento_id, $this->_usuario->id, 1, $documento_visto_id);

        } catch(Exception $ex) {

            $message = $ex->getMessage();
            echo 'error|'.$message;
        }
            
    }
    
    
    public function usarplantillaAction()
    {
       // Recibo el parámetro Tipo 
        $tipo = (int)$this->_request->getParam('tipo', 1);   /* si no llega el parámetro sigifica que es Documento Personal (1)*/
        $this->view->tipo = $tipo;                    
        
        /* Traigo el formulario*/
        $form = $this->_form->getFrmPlantillas();
        $this->view->form = $form;
        $this->_helper->layout->disableLayout();
    }
 
    /**
     * Listar 
     */
    public function listararchivosAction()
    {
        /* Obtengo los datos del padre */
        $padre_id = $this->getRequest()->getParam('padre_id');
        
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        $where = "documento_id = $padre_id ";
        
        /* Paso Array a formato Json */
        $count = $this->_modeloArchivo->Count($where);

        if( $count > 0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        if ($start<0) $start=0;

        $rsArray = $this->_modeloArchivo->getList($sidx,$sord,$start,$limit,$where);

        $response = new stdClass();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;

        $i = 0;

        foreach ($rsArray as $fila) {
            $id = $fila['id'];

            $opcElimina = "<a href='#' onClick=eliminarArchivo($id)><span class='ui-icon ui-icon-trash'></span></a>";

            $linkVerArchivo =  "/uploads/{$fila['nombre_archivo']}";
            $opcVerArchivo = "<a href='$linkVerArchivo' target='_blank'>{$fila['descripcion']}</a>";
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($opcVerArchivo, $opcElimina);
            $i++;
        }

        $this->_helper->json($response); /* */
        
    }
    
    public function listarderivosAction()
    {
        
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        $where = "a.usuario_id = {$this->_usuario->id} ";
        
        /* Paso Array a formato Json */
        $count = $this->_modeloDerivotemporal->Count($where);

        if( $count > 0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;

        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        if ($start<0) $start=0;

        $rsArray = $this->_modeloDerivotemporal->getList($sidx,$sord,$start,$limit,$where);

        $response = new stdClass();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;

        $i = 0;

        foreach ($rsArray as $fila) {
            $id = $fila['id'];

            $opcElimina = "<a href='#' onClick=eliminarDerivo($id)><span class='ui-icon ui-icon-trash'></span></a>";

            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($fila['oficina_destino'], $fila['usuario_destino'], $fila['acciones'], $opcElimina);
            $i++;
        }

        $this->_helper->json($response); /* */
        
    }
    
   public function eliminarderivoAction()
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
                    $resultado = $this->_modeloDerivotemporal->eliminar($id, $this->_usuario);
					
                    if($resultado == 0) { /* Si no se eliminó */
                        echo 'error|No está autorizado a eliminar este registro';                        
                        exit;
                    }
				
		} catch(Exception $ex) {
                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }
            }
        }
    }
    
   public function subirarchivoAction() {
       
//       http://akrabat.com/file-uploads-with-zend_form_element_file/
//       http://www.developphp.com/video/JavaScript/File-Upload-Progress-Bar-Meter-Tutorial-Ajax-PHP

        $this->disableAutoRender();

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
            /* Recibo los datos del formulario por _POST */
            $formData = $this->_request->getPost();
            $txtDescripcion = $formData['txtDescripcion'];
            $padreId = $formData['padreId'];

            /* Variables del archivo a subir */
            $fileName     = @$_FILES["file1"]["name"]; // The file name
            $fileTmpLoc   = @$_FILES["file1"]["tmp_name"]; // File in the PHP tmp folder
            $fileType     = @$_FILES["file1"]["type"]; // The type of file it is
            $fileSize     = @$_FILES["file1"]["size"]; // File size in bytes
            $fileErrorMsg = @$_FILES["file1"]["error"]; // 0 for false... and 1 for true
            if (!$fileTmpLoc) { // if file not chosen
                echo "ERROR: Por favor seleccione el archivo que va a subir.";
                exit();
            }

            if(!$txtDescripcion){ /*Si no le han colocado ninguna descripción al archivo */
                $txtDescripcion = $fileName;
            } 
            
                    
            $nvoFileName = date("dmY").date("His").mt_rand().substr($fileName, -4);                
            
            if (move_uploaded_file($fileTmpLoc, APPLICATION_PATH . "/../public/uploads/$nvoFileName")) {
//                echo "$fileName upload is complete";

                /* Creo mi objeto */
                $data = array('documento_id' => $padreId,
                              'nombre_archivo'    => $nvoFileName,
                              'descripcion'       => $txtDescripcion,
                              'usuario_id'        => $this->_usuario->id
                             );

                try {
                    $this->_modeloArchivo = new Gidoc_Model_ArchivoMapper();
                    $this->_modeloArchivo->save($data);
                    
                } catch(Exception $ex) {
                    echo 'error|'.$ex->getMessage();
                }

            } else {
                echo "Error al intentar subir el archivo: $fileErrorMsg" ;
            }

            exit;
            
        }
        

    }

   public function eliminararchivoAction()
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
                    $resultado = $this->_modeloArchivo->eliminar($id, $this->_usuario);
					
                    if($resultado == 0) { /* Si no se eliminó */
                        echo 'error|No está autorizado a eliminar este registro';                        
                        exit;
                    }
				
				} catch(Exception $ex) {
                    $message = $ex->getMessage();
                    echo 'error|'.$message;
                }
            }
        }
    }
 
    public function getForEntidadAction()
    {

        $id = $this->_getParam('id');
        $textSearch = $this->_getParam('q');
 
        $modeloDependencia = new Gidoc_Model_DependenciaMapper();
        
        $rs = $modeloDependencia->getForSelectEntidad($id, $textSearch);

        $this->_helper->json($rs);
        
    }
 
    /***
    * Para Guardar Grupo de derivación
    */ 
   public function guardargrupoAction()
    {
       
        /* Traigo el formulario*/
        $form = $this->_form->getFrmGrupo();
        $form->setAction($this->view->baseUrlController . '/guardargrupo/');

        if ($this->_request->isPost()) { /* Si se ha submiteado el formulario, se procede a guardar los datos */
       
            /* Desactivo el auto renderizado */
            $this->disableAutoRender();

            /* Recibo datos del formulario */
            $formData = $this->_request->getPost();                

            /* Verifico el valor del usuario destino */
            $nombre = $formData['nombre']; 
            
            try {
                
                /* Firmo el documento */
                $this->_modelo->guardarGrupoDerivacion("'$nombre'", $this->_usuario->id);

            } catch(Exception $ex) {

                $message = $ex->getMessage();
                echo 'error|'.$message;
            }
            
        } else {

            $this->view->form = $form;
            $this->_helper->layout->disableLayout();
            $this->render('derivar');
            
        }
    }
   
}