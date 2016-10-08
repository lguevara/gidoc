<?php

class Gidoc_Form_Documento extends MyZend_Generic_Form
{

    public function getFrmEdita()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditarDoc');
        $this->setAttrib('enctype', 'multipart/form-data');

        $expediente = $this->createElement('text', 'expediente_id');
        $expediente->setAttribs(array('size' => 10,
                                  'class' => 'number'))
                    ->setLabel('# de Expediente');

        
        $usuario = Zend_Auth::getInstance()->getIdentity();  /* Obtengo el dato directamente del Zend_Auth */
        $usua_id = $usuario->id;
        
        $dependencia_registra_id = $this->createElement('select','dependencia_registra_id');
        $dependencia_registra_id->setLabel('Unidad que registra')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("
                                                        SELECT a.dependencia_id AS id,
                                                               b.nombre AS nombre
                                                        FROM cargos a
                                                             LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                                        WHERE a.usua_id = $usua_id
                                                        UNION ALL
                                                        SELECT a.dependencia_id AS id,
                                                               b.nombre AS nombre
                                                        FROM usuarios a 
                                                             LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                                        WHERE a.id = $usua_id
                                                         "));

        /* Seteo por defecto la oficina principal del usuario */
        $dependencia_registra_id->setValue($usuario->dependencia_id);
        
        
        
        $selectDependencia = $this->createElement('text','dependencia_id');
        $selectDependencia->setLabel('Entidad');

        $firma = $this->createElement('text', 'firma');
        $firma->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Firma')
                    ->addFilter('StringToUpper');

        $cargo = $this->createElement('text', 'cargo');
        $cargo->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Cargo')
                    ->addFilter('StringToUpper');
        
        $selectTipoDocumento = $this->createElement('select','tipo_documento_id');
        $selectTipoDocumento->setLabel('Tipo de documento')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,descripcion
                                                          FROM tipos_documentos
                                                          ORDER BY descripcion'));

        $fechaDocumento = $this->createElement('text', 'fecha_documento');
        $fechaDocumento->setAttribs(array('size' => 10,
                                 'class' => 'datePickerUi required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Fecha Documento')
                    ->setValue(date("d-m-Y"));
        
        $numero = $this->createElement('text', 'numero');
        $numero->setAttribs(array('size' => 10,
                                  'class' => 'number'))
                    ->setLabel('Numero');

        $siglas = $this->createElement('text', 'siglas');
        $siglas->setAttribs(array('size' => 60,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Siglas')
                    ->addFilter('StringToUpper');
        
        $asunto = $this->createElement('textarea', 'asunto');
        $asunto->setAttribs(array('rows' => '2',
                                   'cols' => '60',
                                   'class' => 'required'
                                 ))
                    ->setLabel('Asunto');
                                          
        
        $anexos = $this->createElement('text', 'anexos');
        $anexos->setAttribs(array('size' => 60
                                 ))
                    ->setLabel('Anexos');

        $procedimiento_id = $this->createElement('select','procedimiento_id');
        $procedimiento_id->setLabel('Procedimiento TUPA')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,denominacion
                                                          FROM procedimientos
                                                          ORDER BY denominacion'));
        $procedimiento_id->addMultiOption('', '--- No aplica ---');        
        $procedimiento_id->setValue('');
        
        $this->addElement('hidden', 'test', array(
            'description' => '<h2>Documento Externo</h2>',
            'ignore' => true,
            'decorators' => array(
                array('Description', array('escape'=>false, 'tag'=>'')),
            ),
        ));        
        
        // Agregar los elementos al form:
        $this->addElement($expediente)
             ->addElement($dependencia_registra_id)
             ->addElement($selectDependencia)
             ->addElement($firma)
             ->addElement($cargo)
             ->addElement($selectTipoDocumento)
             ->addElement($fechaDocumento)
             ->addElement($numero)
             ->addElement($siglas)      
             ->addElement($asunto)   
             ->addElement($anexos)   
             ->addElement($procedimiento_id)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

    public function getFrmArchiva()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $usuario = Zend_Auth::getInstance()->getIdentity();  /* Obtengo el dato directamente del Zend_Auth */
        $dependencia_id = $usuario->dependencia_id;
        $selectArchivador = $this->createElement('select','archivador_id');
        $selectArchivador->setLabel('Archivador')
                         ->setAttribs(array('class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT id,nombre
                                                          FROM archivadores
                                                          WHERE dependencia_id = $dependencia_id 
                                                          ORDER BY nombre"));
        
        $acciones = $this->createElement('textarea', 'acciones');
        $acciones->setAttribs(array('rows' => '3',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Acciones');
                                          
        // Agregar los elementos al form:
        $this->addElement($selectArchivador)
             ->addElement($acciones)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }

    public function getFrmAdjunta($idList)
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $usuario = Zend_Auth::getInstance()->getIdentity();  /* Obtengo el dato directamente del Zend_Auth */
        $dependencia_id = $usuario->dependencia_id;
        $selectAdjuntador = $this->createElement('select','documento_idadjuntado');
        $selectAdjuntador->setLabel('Adjuntar al documento')
                         ->setAttribs(array('class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("
                                                SELECT b.id,
                                                       b.expediente_id::TEXT || '-' || b.secuencia::TEXT AS documento
                                                FROM movimientos a 
                                                LEFT JOIN documentos b ON b.id = a.documento_id 
                                                WHERE a.dependencia_id = $dependencia_id AND a.tipomovimiento_id IN (1, 2, 3, 5, 7) AND
                                                      a.procesado = FALSE AND b.id NOT IN (SELECT documento_id FROM movimientos WHERE id IN ($idList))
                                                ORDER BY b.expediente_id,
                                                         b.secuencia
                                                    "));
        
        $acciones = $this->createElement('textarea', 'acciones');
        $acciones->setAttribs(array('rows' => '3',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Acciones');
                                          
        // Agregar los elementos al form:
        $this->addElement($selectAdjuntador)
             ->addElement($acciones)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }
    
    public function getFrmDeriva()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $selectDependencia = $this->createElement('select','dependenciadestino_id');
        $selectDependencia->setLabel('Oficina')
                          ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT 'ofi-' || id,
                                                                nombre AS descripcion 
                                                          FROM dependencias
                                                          WHERE tipo = 1 
                                                          UNION ALL 
                                                          SELECT 'grp-' || id,
                                                            'Grupo: ' || nombre AS  descripcion
                                                          FROM gruposderivos 
                                                          ORDER BY 2
                                                            "));

        /* Agrego div para ajax */
        $divUsuario = $this->createElement('hidden', 'foo', 
                array('description' => '<div id=divUsuario ></div>',
                      'ignore' => true,
                      'decorators' => array(
                          array('Description', 
                              array('escape'=>false, 'tag'=>'')),
            ),
        ));        
        /* Fin de Div */
        
        $acciones = $this->createElement('textarea', 'acciones');
        $acciones->setAttribs(array('rows' => '3',
                                   'cols' => '60',
                                   'class' => 'required'
                                 ))
                    ->setLabel('Acciones');

        
        // Agregar los elementos al form:
        $this->addElement($selectDependencia)   
             ->addElement($divUsuario)   
             ->addElement($acciones)
             ->addElement('submit', 'guardar', array('label' => 'Guardar','class' => 'btnGuardar'));

        return $this;
    }

    public function getFrmPlantillas()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmUsarPlantilla');

        $selectPlantilla = $this->createElement('select','plantilla_id');
        $selectPlantilla->setLabel('Plantilla')
                          ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,nombre
                                                          FROM documentosplantillas 
                                                          ORDER BY nombre'));

        // Agregar los elementos al form:
        $this->addElement($selectPlantilla)
             ->addElement('submit', 'ObtenerPlantilla', array('label' => 'Obtener Plantilla'));

        return $this;
    }

    public function getFrmVerTramite()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $documento = $this->createElement('text', 'documento');
        $documento->setAttribs(array('size' => 60))
                    ->setLabel('Documento');

        $fecha = $this->createElement('text', 'fecha_documento');
        $fecha->setAttribs(array('size' => 60))
                    ->setLabel('Fecha');
        
        $origen = $this->createElement('text', 'dependencia_origen');
        $origen->setAttribs(array('size' => 60,))
                    ->setLabel('Orígen');

        $firma = $this->createElement('text', 'firma');
        $firma->setAttribs(array('size' => 60,))
                    ->setLabel('Firma');

        $cargo = $this->createElement('text', 'cargo');
        $cargo->setAttribs(array('size' => 60))
                    ->setLabel('Cargo');
        
        $asunto = $this->createElement('textarea', 'asunto');
        $asunto->setAttribs(array('rows' => '2',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Asunto');
                                          
        // Agregar los elementos al form:
        $this->addElement($documento)
             ->addElement($fecha)   
             ->addElement($origen)
             ->addElement($firma)
             ->addElement($cargo)
             ->addElement($asunto);

        return $this;
    }
 
   public function getFrmBuscar()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $expediente = $this->createElement('text', 'expediente_id');
        $expediente->setAttribs(array('size' => 10,
                                  'class' => 'number'))
                    ->setLabel('# de Expediente');
        
        $fechaDesde = $this->createElement('text', 'fecha_desde');
        $fechaDesde->setAttribs(array('size' => 10,
                                 'class' => 'datePickerUi required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Fecha Desde');

        $fechaHasta = $this->createElement('text', 'fecha_hasta');
        $fechaHasta->setAttribs(array('size' => 10,
                                 'class' => 'datePickerUi required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Fecha Hasta');
        
        $selectTipoDocumento = $this->createElement('select','tipo_documento_id');
        $selectTipoDocumento->setLabel('Tipo de documento')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,descripcion
                                                          FROM tipos_documentos
                                                          ORDER BY descripcion'));
        $selectTipoDocumento->addMultiOption('', '--- Todos ---');        
        $selectTipoDocumento->setValue('');
        
        

        $firma = $this->createElement('text', 'firma');
        $firma->setAttribs(array('size' => 60,))
                    ->setLabel('Firma');

        $asunto = $this->createElement('text', 'asunto');
        $asunto->setAttribs(array('size' => 60,))
                    ->setLabel('Asunto');
        
        // Agregar los elementos al form:
        $this->addElement($expediente)
             ->addElement($fechaDesde)
             ->addElement($fechaHasta)       
             ->addElement($selectTipoDocumento)
             ->addElement($firma)
             ->addElement($asunto)
             ->addElement('button', 'buscar', array('label' => 'Buscar',
                                                    'onClick' => 'gridReload()'));

        return $this;
    }
    
    
    public function getFrmEditaInterno($tipo = 1, $forma = 'N')
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditarDoc');

        $expediente = $this->createElement('text', 'expediente_id');
        $expediente->setAttribs(array('size' => 10,
                                  'class' => 'number'))
                    ->setLabel('# de Expediente');

        $usuario = Zend_Auth::getInstance()->getIdentity();  /* Obtengo el dato directamente del Zend_Auth */
        $usua_id = $usuario->id;

        $dependencia_registra_id = $this->createElement('select','dependencia_registra_id');
        $dependencia_registra_id->setLabel('Unidad que registra')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("
                                                        SELECT a.dependencia_id AS id,
                                                               b.nombre AS nombre
                                                        FROM cargos a
                                                             LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                                        WHERE a.usua_id = $usua_id
                                                        UNION ALL
                                                        SELECT a.dependencia_id AS id,
                                                               b.nombre AS nombre
                                                        FROM usuarios a 
                                                             LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                                        WHERE a.id = $usua_id
                                                         "));

        /* Seteo por defecto la oficina principal del usuario */
        if($forma === 'N'){        
            $dependencia_registra_id->setValue($usuario->dependencia_id);
        }
        
        
        
        $dependencia_id = $this->createElement('select','dependencia_id');
        $dependencia_id->setLabel('Unidad Orígen')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("
                                                        SELECT a.dependencia_id AS id,
                                                               b.nombre AS nombre
                                                        FROM cargos a
                                                             LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                                        WHERE a.usua_id = $usua_id
                                                        UNION ALL
                                                        SELECT a.dependencia_id AS id,
                                                               b.nombre AS nombre
                                                        FROM usuarios a 
                                                             LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                                        WHERE a.id = $usua_id
                                                         "));

        /* Seteo por defecto la oficina principal del usuario */
        if($forma === 'N'){
            $dependencia_id->setValue($usuario->dependencia_id);
        }
        
        
        $selectJefatura = $this->createElement('select','dependencia_id');
        $selectJefatura->setLabel('Para firma de')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT a.id, 
                                                                 b.nombres || ' ' || b.apellidos || ' - ' || a.nombre
                                                                 || ' [' || a.abreviado || ']' 
                                                          FROM dependencias a 
                                                          LEFT JOIN usuarios b ON b.id = a.representante_id 
                                                          WHERE tipo = 1 AND representante_id IS NOT NULL 
                                                          ORDER BY nombre"));
        $selectJefatura->setAttribs(array('class' => 'required'));

        
        $idDestino = $this->createElement('hidden', 'id_destino');
        $idDestino->setDecorators(array('ViewHelper'));

        $selectPara = $this->createElement('select','dependencia_iddestino');
        $selectPara->setLabel('Destinatario')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs("SELECT a.id, 
                                                                 b.nombres || ' ' || b.apellidos 
                                                                 || ' [' || a.abreviado || ']' 
                                                          FROM dependencias a 
                                                          LEFT JOIN usuarios b ON b.id = a.representante_id 
                                                          WHERE tipo = 1 AND representante_id IS NOT NULL 
                                                          ORDER BY nombre"));
        $selectPara->setAttribs(array('class' => 'required'));
       $selectPara->addMultiOption('', '--- Seleccione Destinatario ---');        
       $selectPara->setValue('');        
        
        $para = $this->createElement('text', 'para_destino');
        $para->setAttribs(array('size' => 60,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Para')
                    ->addFilter('StringToUpper');

        $cargo = $this->createElement('text', 'cargo_destino');
        $cargo->setAttribs(array('size' => 60,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Cargo')
                    ->addFilter('StringToUpper');

        $dependencia = $this->createElement('text', 'dependencia_destino');
        $dependencia->setAttribs(array('size' => 60,
                                  'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Dependencia')
                    ->addFilter('StringToUpper');
        
        $selectTipoDocumento = $this->createElement('select','tipo_documento_id');
        $selectTipoDocumento->setLabel('Tipo de documento')
                     ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,descripcion
                                                          FROM tipos_documentos
                                                          ORDER BY descripcion'));
        $selectTipoDocumento->setAttribs(array('class' => 'required'));
       $selectTipoDocumento->addMultiOption('', '--- Seleccione Tipo de Documento ---');        
       $selectTipoDocumento->setValue('');
        
        $numero = $this->createElement('text', 'numero');
        $numero->setAttribs(array('size' => 5,
                                  'class' => 'number required'))
                    ->setLabel('Numero');

        $asunto = $this->createElement('textarea', 'asunto');
        $asunto->setAttribs(array('rows' => '2',
                                   'cols' => '60',
                                   'class' => 'required'
                                 ))
                    ->setLabel('Asunto');

        $referencia = $this->createElement('textarea', 'referencia');
        $referencia->setAttribs(array('rows' => '1',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Referencia');

        $cuerpo = $this->createElement('textarea', 'cuerpo');
        $cuerpo->setAttribs(array('rows' => '20',
                                   'cols' => '60'
                                 ))
                    ->setLabel('Cuerpo');
        
        
        // Agregar los elementos al form:
        
        if($tipo == 1){ /* Si es documento personal */

            $this->addElement('hidden', 'test', array(
                'description' => '<h2>Documento Personal</h2>',
                'ignore' => true,
                'decorators' => array(
                    array('Description', array('escape'=>false, 'tag'=>'')),
                ),
            ));        
            
            
            $this->addElement($expediente)
                ->addElement($dependencia_id) 
                ->addElement($idDestino)
                ->addElement($selectPara)
                ->addElement($para)
                ->addElement($cargo)
                ->addElement($dependencia)   
                ->addElement($selectTipoDocumento)
                ->addElement($numero)
                ->addElement($asunto)   
                ->addElement($referencia)   
                ->addElement($cuerpo)      
                ->addElement('submit', 'guardar', array('label' => 'Guardar'));
            
        } else { /* Si es documento jefatural */

            $this->addElement('hidden', 'test', array(
                'description' => '<h2>Documento de Jefatura</h2>',
                'ignore' => true,
                'decorators' => array(
                    array('Description', array('escape'=>false, 'tag'=>'')),
                ),
            ));        

            /* Obtengo configuración para determinar si se está trabajando con firma electrónica */
            $appConfig = Zend_Registry::get('appConfig');        

            if($appConfig->general->opcionFirma){

                $this->addElement($expediente)
                    ->addElement($dependencia_registra_id)                                                 
                    ->addElement($selectJefatura)
                    ->addElement($idDestino)
                    ->addElement($selectPara)
                    ->addElement($para)
                    ->addElement($cargo)
                    ->addElement($dependencia)   
                    ->addElement($selectTipoDocumento)
                    ->addElement($asunto)   
                    ->addElement($referencia)   
                    ->addElement($cuerpo)      
                    ->addElement('submit', 'guardar', array('label' => 'Guardar'));
                
                
            } else {
                
                $this->addElement($expediente)
                    ->addElement($dependencia_registra_id)                        
                    ->addElement($dependencia_id)
                    ->addElement($idDestino)
                    ->addElement($selectPara)
                    ->addElement($para)
                    ->addElement($cargo)
                    ->addElement($dependencia)   
                    ->addElement($selectTipoDocumento)
                    ->addElement($numero)                        
                    ->addElement($asunto)   
                    ->addElement($referencia)   
                    ->addElement($cuerpo)      
                    ->addElement('submit', 'guardar', array('label' => 'Guardar'));
                
            }
            
        }
        
        return $this;
    }
 
    public function getFrmOficinas()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmOficinas');

        $usuario = Zend_Auth::getInstance()->getIdentity();  /* Obtengo el dato directamente del Zend_Auth */
        $usua_id = $usuario->id;
        
        $dependencia_busca_id = $this->createElement('select','dependencia_busca_id');
        $dependencia_busca_id->setAttribs(array('style' => 'width: 200px'
                                 ))
                    ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                             ->fetchPairs("
                                    SELECT a.dependencia_id AS id,
                                           b.nombre AS nombre
                                    FROM cargos a
                                         LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                    WHERE a.usua_id = $usua_id
                                    UNION ALL
                                    SELECT a.dependencia_id AS id,
                                           b.nombre AS nombre
                                    FROM usuarios a 
                                         LEFT JOIN dependencias b ON b.id = a.dependencia_id
                                    WHERE a.id = $usua_id
                             "));

        /* Seteo por defecto la oficina principal del usuario */
        $dependencia_busca_id->setValue($usuario->dependencia_id);
        
        
        // Agregar los elementos al form:
        $this->addElement($dependencia_busca_id);

        $this->setElementDecorators(array('ViewHelper')); /* Para eliminar las etiquetas dt y dd y obtener solo el elemento input*/

        //$this->setDecorators( array( array( 'ViewScript', array( 'viewScript' => 'documentos/oficinas.phtml' ) ) ) );
        
        
        return $this;
    }
 
    public function getFrmGrupo()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $nombre = $this->createElement('text', 'nombre');
        $nombre->setAttribs(array('size' => 60,
                                     'class' => 'required'  /* Para validar con plugin jquery.validate */
                                 ))
                    ->setLabel('Nombre del grupo');
                                         
        // Agregar los elementos al form:
        $this->addElement($nombre)
             ->addElement('submit', 'guardar', array('label' => 'Guardar'));

        return $this;
    }
 
    public function getFrmFirma()
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmEditar');

        $selectClasifica = $this->createElement('select','clasificacion_id');
        $selectClasifica->setLabel('Clasificación')
                          ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                            ->fetchPairs('SELECT id,descripcion AS descripcion 
                                                          FROM tablas 
                                                          WHERE tipo = 2 
                                                          ORDER BY id'));
        
        // Agregar los elementos al form:
        $this->addElement($selectClasifica)   
             ->addElement('submit', 'guardar', array('label' => 'Firmar'));

        return $this;
    }
    
}