<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

class Gidoc_LibreController extends MyZend_Generic_Controller
{
    /**
     *
     * @var Formulario
     */
    private $_form;
    
    private $_modeloMovimiento;

    private $_usuario;
    
    /**
     * Inicialización
     */
    public function init()
    {
        parent::init();
        
        $this->_modelo = new Gidoc_Model_DocumentoMapper();
        $this->_modeloMovimiento = new Gidoc_Model_MovimientoMapper();
        $this->_modeloArchivo = new Gidoc_Model_ArchivoMapper();           
        
        $this->_form = new Gidoc_Form_Documento();

        $this->view->baseUrlModulo = $this->view->baseUrl . '/' . $this->_request->getModuleName();
        $this->view->baseUrlController = $this->view->baseUrl . '/' . $this->_request->getModuleName() . '/' . $this->_request->getControllerName();
        
        /* Como estoy en un controller de acceso libre, Debo obtener el objeto Usuario para cuando se llame a este controller desde dentro del sistema */
        $this->_usuario = Zend_Auth::getInstance()->getIdentity();

        
        
    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
    }

    
    public function vertramiteAction()
    {
        $this->view->procede = true;
        
        /* Recibo datos */
        $id = $this->_getParam('documento_id');
        
        /* verifico si viene con guión */
        $pos = strpos($id, '-');        
        if($pos === false){ /* No hay guión.  No están buscando un documento, sino más bien desean obtener el listado de todos los documentos que pertenecen a un expdte */

            $this->_helper->FlashMessenger(array('error' => 'No existe el registro !!!'));
            $this->view->procede = false;
            return;
            
            
        }
        
        $arrayId = explode('-', $id);
        $expediente_id = $arrayId[0];
        $secuencia = $arrayId[1];
        
        /* Obtengo el id del documento */
         
            /* Ahora lo que recibo es el expediente y secuencia, entonces obtengo el documento_id */
            $objDoc = $this->_modelo->getDocumentoByExpdte($expediente_id, $secuencia);

            if(!$objDoc){
                $this->_helper->FlashMessenger(array('error' => 'No existe el registro !!!'));
                $this->view->procede = false;
                return;
            }
            
            $documento_id = $objDoc->id;
            
        /* Fin de: Obtengo el id del documento */

        /* Obtengo el objeto Padre*/            
            
        if($this->_usuario){ /* Si existe el objeto usuario, si estoy llamando a ver trámite desde dentro del sistema */
            $objPadre = $this->_modelo->getList('','',0,0,"a.id = $documento_id"); 
        } else { /* Estoy llamando desde afuera, por lo tanto solo debe mostrarse el trámite de aquellos doc. que son externos */
            $objPadre = $this->_modelo->getList('','',0,0,"a.origen = 2 AND a.id = $documento_id"); 
        }
        
        if($objPadre->Count() < 1){
            $this->_helper->FlashMessenger(array('error' => 'No existe el registro !!!'));
            $this->view->procede = false;
            return;
        }
        
        $objDocumento = $objPadre[0];
        
        $objDocumento = $objPadre[0];
        
        $this->view->usuario = $this->_usuario;
        

        $this->view->objDocumento = $objDocumento;
        
        /* Verifico si el usuario de la sesión, ha participado en el trámite del documento */
        if($this->_usuario){ /* Si existe el objeto usuario, si estoy llamando a ver trámite desde dentro del sistema */
            $this->view->haParticipadoEnTramite = $this->_modeloMovimiento->getMovIdForUsuario($this->_usuario->id,$documento_id);
        }

        $this->view->documento_id = $documento_id;

        /* Configuración del jqgrid */
        $this->view->colNames = "'Fecha','Movimiento','Oficina','Usuario','Oficina Destino','Usuario','Acciones'";
        $this->view->colModel = "{name:'fecha_registro', index:'fecha_registro', width:70, sortable:false, formatter:'date', formatoptions:{srcformat:'Y-m-d H:i:s',newformat:'d/m/Y H:i:s'}},
                                 {name:'movimiento', index:'movimiento', width:100, sortable:false},
                                 {name:'oficina_registra', index:'oficina_registra', width:100, sortable:false},
                                 {name:'usuario_registra', index:'usuario_registra', width:100, sortable:false},
                                 {name:'oficina_destino', index:'oficina_destino', width:100, sortable:false},
                                 {name:'usuario_destino', index:'usuario_destino', width:100, sortable:false},
                                 {name:'acciones', index:'acciones', width:100, sortable:false}";
        $this->view->sortName = "id"; /* Nombre del campo de la tabla por la que debe ser ordenado al cargar los datos.  Generalmente es el pk de la tabla */
        
        $form = $this->_form->getFrmVerTramite();
        
        /* Paso los campos del objeto a un array  */
        $arrayData = $objDocumento->toArray();

        $date = new Zend_Date($arrayData['fecha_documento'], Zend_Date:: ISO_8601);
        $fecha = $date->toString('dd/MM/yyyy');
        $arrayData['fecha_documento'] = $fecha;

        /* Para mostrar los archivos que se han subido */        
        $where = "documento_id = $documento_id ";
        $rsArchivos = $this->_modeloArchivo->getList('','',0,0,$where);
        $this->view->rsArchivos = $rsArchivos;
        
        /* LLeno los campos del formulario con los datos del array */
        $form->populate($arrayData);
        $form->disable(); 
       
        $this->view->form = $form;        
        
    }
 
    public function listartramiteAction()
    {
        /* Recibo parámetros propios del grid */
        $sidx = $this->getRequest()->getParam('sidx');  /* Obtiene la dirección de ordenamiento ASC o DESC */
        $sord = $this->getRequest()->getParam('sord'); /* Obttiene la columna de orden - Es decir, la columna donde ha dado click el usuario para ordenar */
        $page = $this->getRequest()->getParam('page'); /* La página solicitado */
        $limit = $this->getRequest()->getParam('rows'); /* El número de filas solicitado */

        /* Recibo otros parámetros que envío */
        $documento_id = $this->getRequest()->getParam('documento_id'); /* Texto a buscar */

        /* Al ingresar por primera vez no existe el parámetro 'rows' por lo tanto lo hacemos igual a 1, para que no exista problema en la división $total_pages = ceil($count/$limit) */
        if(!$limit) $limit = 1;

        $where = "a.documento_id = $documento_id ";

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

            $tipomovimiento_id = $fila['tipomovimiento_id'];
            $movimiento = $fila['movimiento'];            
            
            if($tipomovimiento_id == 4){
                $movimiento .= '<br>en: '.$fila['archivador'];
            } 

            if($tipomovimiento_id == 6){
                $movimiento .= '<br>al: ' . $fila['expediente_idadjuntado']; 

                if($this->_usuario and !$fila['procesado']){
                    /* Muestro el botón Desadjuntar solo si estoy en una session.  Cuando se consulta el trámite desde afuera, no se muestra el botón */
                    $idadjuntado = $fila['documento_idadjuntado'];                     
                    $movimiento .= "<br><input type='button' onClick=desadjuntar($id,$idadjuntado) value='Desadjuntar' id='btnDesadjuntar' />";
                }
            } 

            if($tipomovimiento_id == 7){
                $movimiento .= '<br>del: ' . $fila['expediente_idadjuntado']; 
            } 
            
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = array($fila['fecha_registro'], $movimiento, $fila['oficina_registra'], 
                                                $fila['usuario_registra'], $fila['oficina_destino'],
                                                $fila['usuario_destino'], $fila['acciones']);
            $i++;
        }
        
        $this->_helper->json($response); /* */
    }
    
}