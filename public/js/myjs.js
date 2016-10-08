/* Variable para controlar que un formulario no se envíe 2 veces al hacer doble click o al presionar ENTER de manera rápida */
var frmEnviado = false;

/*
 * AbreVentana
 * Abre una nueva ventana del navaegador con la sURL recibida
 * 
 **/
function AbreVentana(sURL) {
    var w = 800, h = 600;
    venrepo = window.open(sURL, 'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=0,left=0,width=" + w + ",height=" + h, 1);
    venrepo.focus();
}

/*
 * Ventana MyAlert
 * Función para ventana MyAlert
 * Forma de llamar: MyAlert('Mi mensaje')
 **/
function MyAlert(mensajeTexto) {
    // $("#wndDialogo").html('<span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 20px 0;'></span><p>'+mensajeTexto+'</p>');
    $("#wndDialogo").html('<p>' + mensajeTexto + '</p>');
    $("#wndDialogo").dialog("open");
}
/* Fin de Ventana MyAlert */

/*
 * Funciones que son utilizada al editar un formulario en una ventana dialogo
 **/

// SE USABA ANTES, con las funciones de myeditar.phtml
//function validarForm(){
//    if(!frmEnviado){
//        ret = $('#frmEditar').validate().form() /* Valido el formulario */
//        if(ret){
//            frmEnviado = true
//$('#guardar').hide();
//        }
//    }else{
//        ret = false;
//    }
//    return ret
// };

//function recibeRespuesta(responseText){
//    frmEnviado = false;
/* responseText es la variable que recibe lo que envía el server */
//    var result = responseText.split("|");
//    if (result[0] == "error"){
//        MyAlert("ERROR: " + result[1])
//        return false
//    }
/* Cierro la ventana dialog  */
//    $('#wndEdicion').dialog('close');
/* Limpio cualquier dato que exista en la caja de texto de b�squeda
 /* Esto para que al grabar se pueda ver el registro, ya que si antes se ha hecho una b�squeda, al grabar no se ve el nuevo registro */
//    jQuery("#txtBusqueda").val('');  /* TODO - My - aplicar solo para cuando se registra un nuevo registro, no al modificar  */
/* Recargo el grid */
//    gridReload()
//};


function validarForm() {
    ret = true;
    if (!frmEnviado) {
        frmEnviado = true
    } else {
        ret = false;
    }
    return ret
}

function recibeRespuesta(responseText) {
    frmEnviado = false;
    /* responseText es la variable que recibe lo que envía el server */
    var result = responseText.split("|");
    if (result[0] == "error") {
        MyAlert("ERROR: " + result[1])
        return false
    }
    /* Cierro la ventana dialog  */
    $(this).text("");
    $('#wndEdicion').dialog('close');
    gridReload()
}
;

/* Fin de funciones para editar formulario en ventana dialogo */

function recibeRespuestaSinDialog(responseText) {
    /* responseText es la variable que recibe lo que envía el server */
    var result = responseText.split("|");

    if (result[0] == "error") {
        MyAlert("ERROR: " + result[1])
        return false
    }

    //alert('registro agregado')
    document.location = '<?php echo $this->baseUrlController ?>/';

}
;


$(function () {
    /* Inicializo la ventana para MyAlert */
    $("#wndDialogo").dialog({
        title: "Mensaje del Sistema",
        modal: true,
        autoOpen: false,
        resizable: false,
        buttons: {
            Aceptar: function () {
                $(this).text("");
                $(this).dialog('close');
            }
        }
    });

    /*
     * Para mostrar un indicador de proceso por cada llamado Ajax desde Jquery
     * */
    var loader = jQuery('<div id="loader" align="center">Procesando...</div>')
            .css({
                position: "fixed",
                background: "#FFF1A8",
                top: "1px",
                left: "500px",
                height: "15px",
                width: "80px",
                padding: "2px 2px 2px 2px",
                color: "#000"
            })
            .appendTo("#header")
            .hide();

    jQuery(document).ajaxStart(function () {
        loader.show();
    }).ajaxStop(function () {
        loader.hide();
    }).ajaxError(function (a, b, e) {
        throw e;
    });

    /* Fin del indicador de proceso por cada llamado Ajax desde Jquery */

    /* Para menú */
    $("#divMenu").hide();

    $("#divMenu").click(function () {
        $("#divMenu").hide('slide', {}, 400);
    });

    $(".item-menu").click(function () {
        $("#divMenu").hide('slide', {}, 400);
    });

    $("#btnMenu").click(function () {
        $("#divMenu").show('slide', {}, 400);
    });

//    Para ocultar el div Menú al hacer click en cualquier parte del escritorio.  Todavía falta ajustar.
//    $(document).click(function(e){
//        if(e.target.id!='divMenu')
//            $('#divMenu').hide();
//        });

    /* Para no recargar la página al elegir opción del menú */
//    $(".item-menu").click(function (event) {
//    
//         /* Evito el comportamiento por defecto */    
//         event.preventDefault();
//         
//         /* Llamo al url dentro de un dialog y ya no recargo la web */
//        var href =  $(this).attr("href");
//
//        /* Oculto el menú */
//        $("#divMenu").hide('slide', {}, 400);
//
//        /* Cargo el link en un iframe en el div wndContent que se se encuentra en mani_1.phtml */
//        $("#wndContent").text("");             
//        var iframe = $('<iframe frameborder="0" marginwidth="0" marginheight="0" width="100%" height="100%" allowfullscreen></iframe>');            
//        $("#wndContent").append($(iframe).attr("src", href))
//        $("#wndContent").dialog('open')
//        
//        //$("#wndHijo").load( href ) /* lo cargo directamente en el div, ya no uso iframe */
//        
//    })


    /* Para ver trámite */
    $('input#txtDocumentoId').keypress(function (event)
    {
        if (event.keyCode == 13)
        {
            var documento_id = $('#txtDocumentoId').val()
            document.location = '/gidoc/libre/vertramite/documento_id/' + documento_id;
        }
    });

    $('#btnVerTramite').button({
    })

    $('#btnVerTramite').click(function () {
        var documento_id = $('#txtDocumentoId').val()
        if (documento_id == '') {
            alert('Digite un número de Registro')
            return
        }
        /*
         if (!/^([0-9])*$/.test(documento_id)){
         alert('Digite un número de Registro')
         return
         }
         */
        document.location = '/gidoc/libre/vertramite/documento_id/' + documento_id;
    })

    /* Para ver trámite2 */
    $('input#txtDocumentoId2').keypress(function (event)
    {
        if (event.keyCode == 13)
        {
            var documento_id = $('#txtDocumentoId2').val()
            document.location = '/gidoc/libre/vertramite/documento_id/' + documento_id;
        }
    });

    $('#btnVerTramite2').button({
    })

    $('#btnVerTramite2').click(function () {
        var documento_id = $('#txtDocumentoId2').val()
        if (documento_id == '') {
            alert('Digite un número de Registro')
            return
        }
        /*
         if (!/^([0-9])*$/.test(documento_id)){
         alert('Digite un número de Registro')
         return
         }
         */
        document.location = '/gidoc/libre/vertramite/documento_id/' + documento_id;
    })

});