<?php
/**
 * Description of IndexController
 *
 * @author Administrador
 */

require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');


class Gidoc_RptdocumentoController extends MyZend_Generic_ControllerAdmin
{
    private $_modeloDestino;
    private $tipo; /* 0 -> Documento, 1-> Plantilla*/
    
    public function init()
    {
        parent::init();
        
        /* Desde el módulo de plantillas, envió tipo = 1 para indicar que es una plantilla */
        $tipo = (int)$this->_request->getParam('tipo', 0);
        $this->tipo = $tipo;
        
        if($tipo == 0){
            $this->_modelo = new Gidoc_Model_DocumentoMapper();            
            $this->_modeloDestino = new Gidoc_Model_DestinoMapper();            
        } else {
            $this->_modelo = new Gidoc_Model_PlantillaMapper();
            $this->_modeloDestino = new Gidoc_Model_DestinoplantillaMapper();
        }
        
    }
    
    /**
     * El index redirecciona a la acción Listar del modulo default
     */
    public function indexAction()
    {
        /* Desactivo el renderizado de la vista */
        $this->_helper->layout->disableLayout();
        $this->disableAutoRender();

        /*
         * Consulto los datos.
         */
        $solo_genera_pdf = (int)$this->_request->getParam('solo_genera_pdf', 0);        
        $documento_id = (int)$this->_request->getParam('documento_id', 0);
        $objDocumento = $this->_modelo->getDocumentoById($documento_id);
 
        $objDestino = $this->_modeloDestino->getByDocumentoId($documento_id);
        
        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        //$pdf->setLanguageArray($l);

        // ---------------------------------------------------------

        // add a page
        $pdf->AddPage();

        // set font
        $pdf->SetFont('helvetica', '', 10);
        
        $objFechaDoc = new Zend_Date($objDocumento->fecha_documento, Zend_Date:: ISO_8601);
        $fechaDocumento = $objFechaDoc->toString('d MMMM YYYY');
        $pdf->Cell(180, 0, 'Chiclayo, '. $fechaDocumento, 0, 0, 'R');        
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', 'B', 10);

        if($this->tipo == 0){       /* Documento */
            $pdf->Cell(0, 0, "$objDocumento->documento [$objDocumento->expediente_id - $objDocumento->secuencia]", 0, 1, 'L');        
        } else { /* Plantilla */
            $pdf->Cell(0, 0, "$objDocumento->documento", 0, 1, 'L');        
        }
        
        $pdf->Ln();
        $pdf->Write(0, $objDestino->para, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, $objDestino->cargo, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, $objDestino->dependencia, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        
        $pdf->Cell(40, 0, 'ASUNTO:', 0, 0, 'R');
        // get current vertical position
        $x= $pdf->getX();        
        $pdf->writeHTMLCell(140, '', $x, '', $objDocumento->asunto, 0, 0, false, true, 'J', true);
        
        $pdf->Ln(10);
        $pdf->Cell(40, 0, 'REFERENCIA:', 0, 0, 'R');
        /* Utilizo MultiCell para poder indicarle que el texto envíado no es html y me reconozca los saltos de línea cuando se colocan varios documentos como referencia */
        $pdf->MultiCell(140, '', $objDocumento->referencia, 0, 'L', false, 1, $x, '', true, 0, false, true, 0, 'T', false);

        
        $pdf->Ln();

        // set core font
        $pdf->SetFont('helvetica', '', 10);

        // create some HTML content
        $html = $objDocumento->cuerpo;
        
        // output the HTML content
        $pdf->writeHTML($html, true, 0, true, true);

        /* Imprimo la firma */
        if($objDocumento->firma){
            $pdf->Ln(20);
            $pdf->Cell(0, 0, $objDocumento->firma, '', 1, 'C');
            $pdf->Cell(0, 0, $objDocumento->cargo, '', 1, 'C');            
            $pdf->Cell(0, 0, 'Firmado electrónicamente', '', true, 'C');                                    
        }

        /**/

        //$pdf->Ln();


        // reset pointer to the last page
        $pdf->lastPage();

         // ---------------------------------------------------------

        $rutaDocs    = $this->_config->general->rutaDocs;
        $nameFile = "{$rutaDocs}$documento_id.pdf";
        
        //Close and output PDF document
        
        if($solo_genera_pdf){
            $pdf->Output($nameFile, 'F');  /* Solo genera el archivo en el server */                    
        } else {
            $pdf->Output($nameFile, 'FI');  /* genera y veo el archivo en el navegador  OK */ 
        }
            
//        $pdf->Output($nameFile, 'I');  /* Solo veo el archivo en el navegador */
//        $pdf->Output($nameFile, 'F');  /* Solo genera el archivo en el server */        
//        $pdf->Output($nameFile, 'FI');  /* genera y veo el archivo en el navegador  */ 
        //$pdf->Output($nameFile, 'FD');  /* genera y muestra ventana para guardar el archivo en pc local */





        
    }


}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        
        /*
         * Obtengo los datos de la empresa
         */
        $usuario = Default_Model_LoginModelo::getIdentity(); /* Obtengo el dato desde mi modelo que a su vez llama al Zend_Auth */
        $modelEmpresa = new Default_Model_EmpresaMapper();
        $objEmpresa = $modelEmpresa->getById(1);
        
        // Set font
        $this->SetFont('helvetica', 'B', 12);
        // Title
        $this->Cell(0, 0, $objEmpresa->nombre, 0, false, 'C');
        
        // Subtitle 
        $this->SetFont('helvetica', 'B', 10);        
        $this->Ln(5);
        $this->Cell(0, 0, $usuario->oficina, 'B', false, 'C');        

        $this->SetFont('helvetica', '', 8);        
        $this->Ln(5);
//        $this->Cell(0, 0, '"Año de la integración nacional y el reconocimiento de nuestra diversidad"', '', false, 'C');        
        
    }

}