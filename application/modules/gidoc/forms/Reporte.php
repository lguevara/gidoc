<?php

class Gidoc_Form_Reporte extends MyZend_Generic_Form
{

    public function getFrm($idReporte = '')
    {
        $this->setMethod('post');
        $this->setAttrib('id','frmReporte');

        switch ($idReporte) {
            case 'menu-rptTiempoAtencion':
                
                /* Desde */
                $fechaDesde = $this->createElement('text', 'fecha_desde');
                $fechaDesde->setAttribs(array('size' => 10,
                             'class' => 'required'  /* Para validar con plugin jquery.validate */
                             ))
                ->setLabel('Desde')
                ->setValue(date("d/m/Y"));

                /* Hasta */
                $fechaHasta = $this->createElement('text', 'fecha_hasta');
                $fechaHasta->setAttribs(array('size' => 10,
                             'class' => 'required'  /* Para validar con plugin jquery.validate */
                             ))
                ->setLabel('Hasta')
                ->setValue(date("d/m/Y"));
                
                // Agregar los elementos al form:
                $this->addElements(array($fechaDesde,$fechaHasta));

                break;

            case 'menu-rptPorAtender':
                
                $selectDependencia = $this->createElement('select','dependencia_id');
                $selectDependencia->setLabel('Oficina')
                                ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                                    ->fetchPairs('SELECT id,nombre
                                                                FROM dependencias
                                                                WHERE tipo = 1 
                                                                ORDER BY nombre'));
                $selectDependencia->addMultiOption('', '--- Todas ---');        
                $selectDependencia->setValue('');

                // Agregar los elementos al form:
                $this->addElements(array($selectDependencia));

                break;

            case 'menu-rptAtendidos':

                /* Desde */
                $fechaDesde = $this->createElement('text', 'fecha_desde');
                $fechaDesde->setAttribs(array('size' => 10,
                             'class' => 'required'  /* Para validar con plugin jquery.validate */
                             ))
                ->setLabel('Desde')
                ->setValue(date("d/m/Y"));

                /* Hasta */
                $fechaHasta = $this->createElement('text', 'fecha_hasta');
                $fechaHasta->setAttribs(array('size' => 10,
                             'class' => 'required'  /* Para validar con plugin jquery.validate */
                             ))
                ->setLabel('Hasta')
                ->setValue(date("d/m/Y"));
                
                $selectDependencia = $this->createElement('select','dependencia_id');
                $selectDependencia->setLabel('Oficina')
                                ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                                    ->fetchPairs('SELECT id,nombre
                                                                FROM dependencias
                                                                WHERE tipo = 1 
                                                                ORDER BY nombre'));
                $selectDependencia->addMultiOption('', '--- Todas ---');
                $selectDependencia->setValue('');

                // Agregar los elementos al form:
                $this->addElements(array($fechaDesde,$fechaHasta,$selectDependencia));

                break;

            case 'menu-rptRecibidos':            

                /* Desde */
                $fechaDesde = $this->createElement('text', 'fecha_desde');
                $fechaDesde->setAttribs(array('size' => 10,
                             'class' => 'required'  /* Para validar con plugin jquery.validate */
                             ))
                ->setLabel('Desde')
                ->setValue(date("d/m/Y"));

                /* Hasta */
                $fechaHasta = $this->createElement('text', 'fecha_hasta');
                $fechaHasta->setAttribs(array('size' => 10,
                             'class' => 'required'  /* Para validar con plugin jquery.validate */
                             ))
                ->setLabel('Hasta')
                ->setValue(date("d/m/Y"));
                
                $selectDependencia = $this->createElement('select','dependencia_id');
                $selectDependencia->setLabel('Oficina')
                                ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                                    ->fetchPairs('SELECT id,nombre
                                                                FROM dependencias
                                                                WHERE tipo = 1 
                                                                ORDER BY nombre'));
                $selectDependencia->addMultiOption('', '--- Todas ---');
                $selectDependencia->setValue('');

                $selectOrigen = $this->createElement('select','dependencia_origen_id');
                $selectOrigen->setLabel('Orígen')
                                ->setMultioptions(Zend_Db_Table_Abstract::getDefaultAdapter()
                                                    ->fetchPairs('SELECT id,nombre
                                                                FROM dependencias
                                                                ORDER BY nombre'));
                $selectOrigen->addMultiOption('', '--- Todos ---');
                $selectOrigen->setValue('');
                
                // Agregar los elementos al form:
                $this->addElements(array($fechaDesde,$fechaHasta,$selectDependencia,$selectOrigen));

                break;
            
            default:
                break;
        }
        

        return $this."<script type='text/javascript'>formateo_campos()</script>";
    }

}