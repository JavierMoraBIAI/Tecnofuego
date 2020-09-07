$(document).on('keypress','input, select',function(e){

    if (e.keyCode == 13) {

       cb = parseInt($(this).attr('tabindex'));

       if ($(':input[tabindex=\'' + (cb + 1) + '\']') != null) {
           $(':input[tabindex=\'' + (cb + 1) + '\']').focus();
           $(':input[tabindex=\'' + (cb + 1) + '\']').select();
           e.preventDefault();

           return false;
       }

    }

});

// Parseadores de JSON
var jsonJobs = { 

    parserPHP: function(jsonStr){           
        var pos01 = jsonStr.lastIndexOf('[');
        var pos02 = jsonStr.lastIndexOf(']');
        var nsjon = jsonStr.substring(pos01+1, pos02);
        var oJSON = this.parserJS(nsjon);
        return oJSON;           
    },

    parserJS: function(jsonStr){
        parObj = (JSON)?JSON.parse(jsonStr): eval('(' + jsonStr + ')');
        return parObj;
    }

};

// Serializar formulario
$.fn.formToObject = function(){
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

// Validación de campos vacíos
function valReq(){
   var state = true;
   var campos = $('input[type="text"]:required, input[type="date"]:required, textarea:required, select:required, .required');
   $(campos).each(function() {
      if($(this).val()==''){
         state = false;
         $(this).addClass('is-invalid');
         var lbl = $(this).parent().find('label').text();
         $(this).parent().append('<div class="invalid-feedback">Debe diligenciar el campo '+lbl.slice(0, -2)+' </div>');
         console.log('Problemas con el campo '+$(this).attr('id'));
      }
   });
   return state;
}

// Resetear formulario
$.fn.clearForm = function(tag1) {
    tag1 = tag1 || 'form';
    return this.each(function() {
        var type = this.type, tag = this.tagName.toLowerCase();
        if (tag == tag1)
            return $(':input',this).clearForm();
        //if (type == 'text' || type == 'password' || type == 'hidden' || tag == 'textarea' || type == 'date')
        if (type == 'text' || type == 'password' || tag == 'textarea' || type == 'date')
            this.value = '';
        else if (type == 'email' || type == 'number' || type == 'file')
            this.value = '';
        else if (type == 'checkbox' || type == 'radio')
            this.checked = false;
        else if (tag == 'select'){
            if (this.getAttribute("multiple") == null) {
                this.value = '';
                this.selectedIndex = 0;
            }else{
                this.value = '';
                this.selectedIndex = -1;
            }
        }
    });
}

var currentModel = '';

function renderDT(){
  $('.newrone-table').dataTable({
    'aLengthMenu'       : [[5,10, 25, 50, -1], [5,10, 25, 50, "Todos"]],
    //'aLengthMenu'       : [[5], [5]],
    'iDisplayStart'     :   0,
    'iDisplayLength'    :   10,
    'aaSorting'         :   [[ 0, 'asc' ]],
    "oLanguage" : {
      "sProcessing"       :   "Procesando...",
      "sLengthMenu"       :   "Mostrar _MENU_ artículos",
      "sZeroRecords"      :   "No se encontraron resultados",
      "sEmptyTable"       :   "Ningún dato disponible en esta tabla",
      "sInfo"             :   "Artículos del _START_ al _END_ de un total de _TOTAL_ artículos",
      "sInfoEmpty"        :   "Artículos del 0 al 0 de un total de 0 artículos",
      "sInfoFiltered"     :   "(filtrado de un total de _MAX_ artículos)",
      "sInfoPostFix"      :   "",
      "sSearch"           :   "Buscar:",
      "sUrl"              :   "",
      "sInfoThousands"    :   ",",
      "sLoadingRecords"   :   "Cargando...",
      "oPaginate": {
        "sFirst"    :   "Primero",
        "sLast"     :   "Último",
        "sNext"     :   "Sig.",
        "sPrevious" :   "Ant."
      },
      "oAria": {
        "sSortAscending"    :   ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending"   :   ": Activar para ordenar la columna de manera descendente"
      }
    }
  });
}

function renderDTParam(tab){
  $(tab).dataTable({
    'aLengthMenu'       : [[5,10, 25, 50, -1], [5,10, 25, 50, "Todos"]],
    //'aLengthMenu'       : [[5], [5]],
    'iDisplayStart'     :   0,
    'iDisplayLength'    :   10,
    'aaSorting'         :   [[ 0, 'asc' ]],
    "oLanguage" : {
      "sProcessing"       :   "Procesando...",
      "sLengthMenu"       :   "Mostrar _MENU_ artículos",
      "sZeroRecords"      :   "No se encontraron resultados",
      "sEmptyTable"       :   "Ningún dato disponible en esta tabla",
      "sInfo"             :   "Artículos del _START_ al _END_ de un total de _TOTAL_ artículos",
      "sInfoEmpty"        :   "Artículos del 0 al 0 de un total de 0 artículos",
      "sInfoFiltered"     :   "(filtrado de un total de _MAX_ artículos)",
      "sInfoPostFix"      :   "",
      "sSearch"           :   "Buscar:",
      "sUrl"              :   "",
      "sInfoThousands"    :   ",",
      "sLoadingRecords"   :   "Cargando...",
      "oPaginate": {
        "sFirst"    :   "Primero",
        "sLast"     :   "Último",
        "sNext"     :   "Sig.",
        "sPrevious" :   "Ant."
      },
      "oAria": {
        "sSortAscending"    :   ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending"   :   ": Activar para ordenar la columna de manera descendente"
      }
    }
  });
}

function destroyTable(){
  $('.newrone-table').DataTable().destroy();
}

function destroyTableParam(tab){
  $(tab).DataTable().destroy();
}

let loader = '<div class="text-center mt-5 mb-5">';
  loader += '<div class="spinner-border" style="width: 5rem; height: 5rem;" role="status">';
  loader += '<span class="sr-only">Cargando...</span>';
  loader += '</div></div>';

// Link sin acción
$(document).on('click','a.no-link',function(e){
  e.preventDefault();
  return null;
});  

// Acciones de link de lanzar módulo u opción
$(document).on('click','a.launch-mod',function(e){

    e.preventDefault();

    $('html, body').animate({ scrollTop: 0 }, 'fast');

    var model  = $(this).attr('href');
    var target = '#module-cont';
    var params = {
        'model'  : model,
        'method' : 'index',
        'args'   : $('form[name='+model+']').formToObject()
    };

    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'html',
        data: params,
        cache: false, // Appends _={timestamp} to the request query string
        beforeSend: function(){
          $(target).html(loader).fadeIn('slow');
        },
        success: function($dres) {
            $(target).html($dres);            
            renderDT();
            valProfile();
        }
    }).done(function() {
      $(target).fadeIn('slow');
    });

    $('ul.list-unstyled li').removeClass('active');
    $(this).parent().addClass('active');

    currentModel = model;

});

// Acciones de los botones del dashboard
$(document).on('click','button.launch-action',function(){

    var model = $(this).attr('model');
    var target = '#module-cont';
    var params = {
        'model'  : model,
        'method' : $(this).attr('href'),
        'args'   : ''
    };

    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'html',
        data: params,
        cache: false, // Appends _={timestamp} to the request query string
        beforeSend: function(){
          $(target).html(loader).fadeIn('slow');
        },
        success: function($dres) {
            $(target).html($dres);
            defaMethod();
            valProfile();
        }
    }).done(function() {
      $(target).fadeIn('slow');
    });

    currentModel = model;

});

// Acciones del botón para ejecutar una búsqueda
$(document).on('click','#btnList',function(e){

  e.preventDefault();

  var edu = true;

  if( $(this).hasClass("date-filter") ){

    var ini = $(this).attr('ini-date');
    var fin = $(this).attr('fin-date');

    if( $('#'+ini).val() != '' && $('#'+fin).val() == '' ){
      edu = false;
      alert('Ambas fechas deben ser ingresadas para realizar la búsqueda.');
    }

  }

  if( edu ){

    var target = '#'+$(this).attr('target');
    var action = $('#'+currentModel).attr('action');

    var params = {
      'model'  : currentModel,
      'method' : action,
      'args'   : $('#'+currentModel).formToObject()
    };

    $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'html',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      beforeSend: function(){
        $(target).html(loader).fadeIn('slow');
      },
      success: function($dres) {
        $(target).html($dres);            
        renderDT();
        valProfile();
      }
    }).done(function() {
      $(target).fadeIn('slow');
    });

  }

});

// Acciones del botón nuevo
$(document).on('click','#btnNew',function(e){

    e.preventDefault();

    $('html, body').animate({ scrollTop: 0 }, 'fast');

    var target = '#'+$(this).attr('target');

    var params = {
        'model'  : currentModel,
        'method' : 'nuevo',
        'args'   : ''
    };

    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'html',
        data: params,
        cache: false, // Appends _={timestamp} to the request query string
        beforeSend: function(){
          $(target).html(loader).fadeIn('slow');
        },
        success: function($dres) {
          $(target).html($dres);
          defaMethod();
          valProfile();
        }
    }).done(function() {
      $(target).fadeIn('slow');
    });

});

// Acciones del botón guardar
$(document).on('click','#btnSave',function(e){

    e.preventDefault();

    $('html, body').animate({ scrollTop: 0 }, 'fast');

    if( valReq() ){

      var target = '#'+$(this).attr('target');
      var file = '';
      var $zonaf = new FormData();

      $zonaf.append('model',$(this).attr('model'));
      $zonaf.append('method',$(this).attr('method'));
      $zonaf.append('args',JSON.stringify($('#'+$(this).attr('model')).formToObject()));

      $(":file").each(function( index ) {
        file = eval(document.getElementById($(this).attr('id'))).files;
        $zonaf.append($(this).attr('id'),file[0]);
      });

      $.ajax({
          url: 'index.php',
          type: 'POST',
          dataType: 'html',
          data: $zonaf,
          cache: false, // Appends _={timestamp} to the request query string
          contentType: false,
          processData: false,
          beforeSend: function(){
            $(target).html(loader).fadeIn('slow');
          },
          success: function($dres) {
            $(target).html($dres);
            valProfile();
          }
      }).done(function() {
        $(target).fadeIn('slow');
      });

    } else {
      let conf = {
        'tarmsg'  : 'contMsg',
        'tarow'   : 'rowMsg',
        'msg'     : 'Debe llenar todos los campos marcados como obligatorios (asterisco * rojo) para poder guardar.'
      };
      alertCustom(conf);
    }

});

// Acciones boton elegir registro para editar
$(document).on('click','a[action=upd]',function(e){

    e.preventDefault();

    $('html, body').animate({ scrollTop: 0 }, 'fast');

    var target = '#module-cont';

    var params = {
      'model'   :   currentModel,
      'method'  :   $(this).attr('href'),
      'args'    :   $(this).attr('idreg')
    };

    $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'html',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      beforeSend: function(){
        $(target).html(loader).fadeIn('slow');
      },
      success: function($dres) {
        $(target).html($dres);
        defaMethod();
        valProfile();
      }
    }).done(function() {
      $(target).fadeIn('slow');
    });

});

// Acciones del botón cancelar
$(document).on('click','#btnCancel',function(e){

    e.preventDefault();

    $('html, body').animate({ scrollTop: 0 }, 'fast');

    var action = $(this).attr('action');
    var target = '#'+$(this).attr('target');

    switch(action){

      case 'clean-form':
        $('#'+currentModel).clearForm();
        $(target).html('').css('display','none');
      break;

      case 'back':
        
        var modbak = $(this).attr('modbak');
        
        var params = {
          'model'  : currentModel,
          'method' : modbak,
          'args'   : ''
        };

        $.ajax({
            url: 'index.php',
            type: 'POST',
            dataType: 'html',
            data: params,
            cache: false, // Appends _={timestamp} to the request query string
            beforeSend: function(){
              $(target).html(loader).fadeIn('slow');
            },
            success: function($dres) {
              $(target).html($dres);
              valProfile();
            }
        }).done(function() {
          $(target).fadeIn('slow');
        });

      break;

    }

});

// Ver detalle del registro
$(document).on('click','a[action=dty]',function(e){

    e.preventDefault();

    var params = {
      'model'   :   currentModel,
      'method'  :   $(this).attr('href'),
      'args'    :   $(this).parent().parent().find('td:eq(0)').text()
    };

    $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'json',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {
        $('#dtyTitle').html($dres.titulo);
        $('#modBodDty').html($dres.html);
        defaMethod();
        valProfile();
      }
    }).done(function() {
      $('#wModDty').modal('show');
    });

});

// Validación de campos de archivo
$(document).on('change','.custom-file-input',function(){

  var type = parseInt($(this).attr('type-allow'));
  var file = eval(document.getElementById($(this).attr('id'))).files;
  var tyup = file[0].type;
  var allow = [];
  var fileName = '';
  var lstlabel = $(this).siblings(".custom-file-label").attr('last-label');

  switch(type){

    case 1: // Solo imágenes
      allow = ['image/jpeg','image/png'];
    break;

    case 2: // Solo imágenes y PDF
      allow = ['image/jpeg','image/png','application/pdf'];
    break;

    case 3: // Solo archivos de texto
      allow = ['text/plain','text/csv'];
    break;

    case 4: // Solo archivos de Excel
      allow = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'];
    break;

    case 5: // Solo archivos zip
      allow = ['application/zip'];
    break;

    case 6: // Para descargas múltiples Zip, PDF Se evalúa mirar si se suben documentos de Word y Excel
      allow = ['application/zip','application/pdf','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'];
    break;

  }
  
  if( allow.indexOf(tyup) !== -1 ){
    fileName = $(this).val().split("\\").pop();
  } else {
    fileName = lstlabel;
    alert('Tipo de archivo no permitido');
    $(this).val('');
  }

  console.log(tyup);

  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);

});

// Generación de tokens para ver los archivos
$(document).on('click','.file-view',function(e){

  var params = {
      'model'  : 'fileloader',
      'method' : 'gentoken',
      'args'   : ''
  };

  $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'html',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {
        console.log($dres);
      }
  });

});

// Función para combos dependientes
function cmbdep(cnf){

  var params = {
      'model'  : cnf.model,
      'method' : cnf.method,
      'args'   : cnf.params
  };

  $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'html',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {
          $('#'+cnf.target).html($dres);
      }
  });

}

// Dependientes por clase
function cmbdepClss(cnf){

  var params = {
      'model'  : cnf.model,
      'method' : cnf.method,
      'args'   : cnf.params
  };

  $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'html',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {
          $('.'+cnf.target).html($dres);
      }
  });

}

// Disable function
jQuery.fn.extend({
  disable: function(state) {
    return this.each(function() {
      this.disabled = state;
    });
  }
});

// Mensajes de alerta
function alertCustom(conf){
  $('#'+conf.tarmsg).html(conf.msg);
  $('#'+conf.tarow).fadeIn('slow');  
  setTimeout(function(){ 
    $('#'+conf.tarmsg).html('');
    $('#'+conf.tarow).fadeOut('slow');
  }, 6000);
}

// Imprimir reporte
$(document).on('click','a[action=prnt]',function(e){
  e.preventDefault();
  return null;
});

// Lanzar ventana de recuperar contraseñas
$(document).on('click','#passRecover',function(e){

  e.preventDefault();
  if( $('#contMsg').hasClass('alert-success') ){
    $('#contMsg').removeClass('alert-success').addClass('alert-danger');
  }
  $('#modRecoPass').modal({
    backdrop: 'static',
    keyboard: false
  });

});

// Cerrar ventana de recuperar contraseñas
$(document).on('click','#btnCloseReco',function(){

  $('#txtEmailReco').removeClass('is-valid');
  $('#frmRecoPass').clearForm();

});

// Botón para recuperar contraseña
$(document).on('click','#btnSoliReco',function(){

  let email = $('#txtEmailReco').val();
  $('#txtEmailReco').removeClass('is-valid');
  if( $('#contMsg').hasClass('alert-success') ){
    $('#contMsg').removeClass('alert-success').addClass('alert-danger');
  }

  if( email.length > 0 ){

    let params = {
      'model'  : 'recopass',
      'method' : 'solpass',
      'args'   : email
    };

    $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'text',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {

        if( parseInt($dres) == 0 ){
          let conf = {
            'tarmsg'  : 'contMsg',
            'tarow'   : 'rowMsg',
            'msg'     : 'No hay usuario registrado con ese correo electrónico. Por favor revise para poder continuar el proceso.'
          };
          alertCustom(conf);
        } else {
          let conf = {
            'tarmsg'  : 'contMsg',
            'tarow'   : 'rowMsg',
            'msg'     : 'El procedimiento para restaurar contraseña se ha enviado correctamente a su correo electrónico <b>'+email+'</b>.'
          };
          $('#contMsg').removeClass('alert-danger').addClass('alert-success');
          alertCustom(conf);
          setTimeout(function(){ 
            $('#btnCloseReco').click();
          }, 8000);
        }

      }
    });

  } else {
    let conf = {
      'tarmsg'  : 'contMsg',
      'tarow'   : 'rowMsg',
      'msg'     : 'Debe ingresar el correo electrónico para poder solicitar la restauración de la contraseña.'
    };
    alertCustom(conf);
  }

});