$(document).on('click','#btnNewMmto',function(){

    $('#modEquipos').modal({
        backdrop: 'static',
        keyboard: false
    });

});

$(document).on('click','#btnSearchEquip',function(){

    let target = '#'+$(this).attr('target');
    let action = $('#frmModalEquip').attr('action');

    let params = {
      'model'  : currentModel,
      'method' : action,
      'args'   : $('#frmModalEquip').formToObject()
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

});

$(document).on('click','#btnCancelModEquip',function(){
  $('#frmModalEquip').clearForm();
  $('#detEquipos').html('');
});

$(document).on('change','#slcMarca',function(){

  let cnf = {
    model  : 'mmtos',
    method : 'listsdep',
    params : {
      idlst : $(this).val(),
      str   : 'SELECCIONE MODELO',
      def   : '',
    },
    target : 'slcModelo'
  };

  cmbdep(cnf);

});

$(document).on('change','#slcMarcaMod',function(){

  let cnf = {
    model  : 'mmtos',
    method : 'listsdep',
    params : {
      idlst : $(this).val(),
      str   : 'SELECCIONE MODELO',
      def   : '',
    },
    target : 'slcModeloMod'
  };

  cmbdep(cnf);

});

$(document).on('change','#slcCliente',function(){

  let cnf = {
    model  : 'mmtos',
    method : 'sitios',
    params : {
      val : $(this).val(),
      def : '',
      typ : 'echo'
    },
    target : 'slcSitios'
  };

  cmbdep(cnf);

});

$(document).on('change','#slcClienteMod',function(){

  let cnf = {
    model  : 'mmtos',
    method : 'sitios',
    params : {
      val : $(this).val(),
      def : '',
      typ : 'echo'
    },
    target : 'slcSitiosMod'
  };

  cmbdep(cnf);

});

// Acciones boton elegir registro para nuevo mantenimiento
$(document).on('click','a[action=sel]',function(e){

  e.preventDefault();

  $('#btnCancelModEquip').click();

  let met = $(this).attr('href');
  let arg = $(this).parent().parent().find('td:eq(0)').text();

  setTimeout(function(){
    loadNew(met,arg);
  }, 200);

});

function loadNew(met,arg){

  $('html, body').animate({ scrollTop: 0 }, 'fast');

  var target = '#module-cont';

  var params = {
    'model'   :   'mmtos',
    'method'  :   met,
    'args'    :   arg
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

}

// Tab con ENTER en el campo de cédula
$(document).on('keypress','#txtCedula',function(){
  var keycode = (event.keyCode ? event.keyCode : event.which);
  if(keycode == '13'){
    $('#txtPorPar').focus();
  }
});

// Búsqueda de técnicos asociados a un proyecto
$(document).on('change','#slcTecnico',function(){

  let params = {
    'model'   : 'mmtos',
    'method'  : 'tecdata',
    'args'    : $(this).val()
  };

  $.ajax({
    url: 'index.php',
    type: 'POST',
    dataType: 'json',
    data: params,
    cache: false, // Appends _={timestamp} to the request query string
    success: function($dres) {
      if( $dres.nombre != null && $dres.grupo != null ){
        $('#hidCurIdTec').val($dres.id);
        $('#txtNombreTec').val($dres.nombre);
        $('#txtGrupo').val($dres.grupo);
      } else {
        $('#txtNombreTec').val('');
        $('#txtGrupo').val('');
      }
    }
  }).done(function() {
    $('#txtPorPar').focus();
  });

});

// Filas de los repuestos
var flt = 0;

// Agregar técnico
$(document).on('click','#btnAddTec',function(){

  destroyTableParam('#tabTecs');

  let idt = $('#slcTecnico').val(), idf = $('#hidCurLinTec').val(), cmb = $("#slcTecnico option:selected").text().split("-"), 
      ced = cmb[1], nom = cmb[0], gru = $('#txtGrupo').val(), por = $('#txtPorPar').val();

  if( ced.length != 0 && nom.length != 0 && gru.length != 0 && por.length != 0 ){

    if( idf.length == 0 ){

      let lin = '';
      let btn = '<button class="btn btn-info btn-sm edit-dty-tec" type="button" idfila="'+flt+'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
          btn += '<button class="btn btn-danger btn-sm dele-dty-tec" type="button" idfila="'+flt+'"><i class="fa fa-times"></i></button>';

      let hid = '<input type="hidden" name="hidCurLinTecTab'+flt+'" id="hidCurLinTecTab'+flt+'" value="'+flt+'">';
          hid += '<input type="hidden" name="hidCurIdTecTab'+flt+'" id="hidCurIdTecTab'+flt+'" value="'+idt+'">';

      lin += '<tr id="trTecTab'+flt+'">';
        lin += '<td id="tdCeduTecTab'+flt+'" class="text-center">'+ced+hid+'</td>';
        lin += '<td id="tdNombTecTab'+flt+'">'+nom+'</td>';
        lin += '<td id="tdGrupTecTab'+flt+'" class="text-center">'+gru+'</td>';
        lin += '<td id="tdPorcTecTab'+flt+'" class="text-center">'+por+'</td>';
        lin += '<td class="text-center">'+btn+'</td>';
      lin += '</tr>';

      $('#tabtecs').append(lin);
      flt++;      

    } else {

      let hid = '<input type="hidden" name="hidCurLinTecTab'+idf+'" id="hidCurLinTecTab'+idf+'" value="'+idf+'">';
          hid += '<input type="hidden" name="hidCurIdTecTab'+idf+'" id="hidCurIdTecTab'+idf+'" value="'+idt+'">';

      $('#tdCeduTecTab'+idf).html(ced+hid);
      $('#tdNombTecTab'+idf).html(nom);
      $('#tdGrupTecTab'+idf).html(gru);
      $('#tdPorcTecTab'+idf).html(por);

    }

    cleanTecs();
    readTabTecs();
    renderDTParam('#tabTecs');

  }

});

// Seleccionar técnico a editar
$(document).on('click','.edit-dty-tec',function(e){

  e.preventDefault();

  let idfila = $(this).attr('idfila');

  let idt = $('#hidCurIdTecTab'+idfila).val(), idf = $('#hidCurLinTecTab'+idfila).val(),
      gru = $('#tdGrupTecTab'+idfila).text(), por = $('#tdPorcTecTab'+idfila).text();

  $('#hidCurIdTec').val(idt); $('#hidCurLinTec').val(idf);
  $('#slcTecnico').val(idt); $('#txtGrupo').val(gru); $('#txtPorPar').val(por);

  $('#btnAddTec').removeClass('btn-info').addClass('btn-success');
  $('#btnAddTec').html('<i class="fa fa-save"></i>');

});

// Seleccionar técnico a borrar
$(document).on('click','.dele-dty-tec',function(e){

  e.preventDefault();

  if( confirm('¿Desea eliminar este técnico?') ){
    let idfila = $(this).attr('idfila');
    $('#trTecTab'+idfila).remove();
    readTabTecs();
  }

});

// Limpiar campos de técnicos
function cleanTecs(){

  $('#hidCurLinTec').val(''); $('#hidCurIdTec').val(''); $('#slcTecnico').val('');
  $('#txtGrupo').val(''); $('#txtPorPar').val('');
  $('#slcTecnico').focus();
  $('#btnAddTec').removeClass('btn-success').addClass('btn-info');
  $('#btnAddTec').html('<i class="fa fa-plus"></i>');

}

// Leer datos de la tabla de técnicos
function readTabTecs(){

  let tecs = [];

  $("#tabtecs tr").each(function(i) {
      tecs.push({ 
          'idetec':$(this).find('td:eq(0) input:eq(1)').val(),
          'portec':$(this).find('td:eq(3)').text()
      });
      $('#hidVlsTecs').val(JSON.stringify(tecs));
      console.log(tecs);
  });

}

// Tab con ENTER en el campo de cédula
$(document).on('keypress','#txtParNum',function(){
  var keycode = (event.keyCode ? event.keyCode : event.which);
  if(keycode == '13'){
    $('#txtCantidad').focus();
  }
});

// Búsqueda de partes o servicios asociados a un proyecto y determinado equipo
$(document).on('focusout','#txtParNum',function(){

  let myval = $(this).val();

  if( myval.length != 0 ){

    let params = {
      'model'   : 'mmtos',
      'method'  : 'compdata',
      'args'    : {
        'proj' : $('#hidIdSite').val(),
        'equi' : $('#hidIdEquip').val(),
        'pnum' : $(this).val()
      }
    };
  
    $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'json',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {
        $('#slcTipo').val($dres.partserv);
        $('#slcFamilia').val($dres.idFamily);
        $('#slcCategoria').val($dres.idCategory);
        $('#hidValUnit').val($dres.valproy);
        $('#hidElemento').val($dres.id);
        $('#slcCategoria').change();
        console.log($dres);
      }
    }).done(function() {
      $('#txtCantidad').focus();
      setTimeout(function(){ $('#slcElemento').val($('#hidElemento').val()); }, 200);
    });

  }

});

// Listar componentes, servicios o repuestos según el tipo, categoría y familia
$(document).on('change','#slcCategoria',function(){

  var params = {
    'model'   : 'mmtos',
    'method'  : 'complst',
    'args'    : {
      'type' : $('#slcTipo').val(),
      'fami' : $('#slcFamilia').val(),
      'proj' : $('#hidIdSite').val(),
      'equi' : $('#hidIdEquip').val(),
      'cate' : $(this).val(),
    }
  };

  //console.log(params.args);

  $.ajax({
    url: 'index.php',
    type: 'POST',
    dataType: 'html',
    data: params,
    cache: false, // Appends _={timestamp} to the request query string
    success: function($dres) {
      $('#slcElemento').html($dres);
    }
  });

});

// Completar campos requeridos cuando se selecciona el elemento a agregar
$(document).on('change','#slcElemento',function(){

  var params = {
    'model'   : 'mmtos',
    'method'  : 'compldata',
    'args'    : {
      'type' : $('#slcTipo').val(),
      'proj' : $('#hidIdSite').val(),
      'equi' : $('#hidIdEquip').val(),
      'elem' : $(this).val(),
    }
  };

  $.ajax({
    url: 'index.php',
    type: 'POST',
    dataType: 'json',
    data: params,
    cache: false, // Appends _={timestamp} to the request query string
    success: function($dres) {
      $('#txtParNum').val($dres.partNum);
      $('#hidValUnit').val($dres.valproj);
    }
  }).done(function() {
    $('#txtCantidad').focus();
  });

});

// Filas de los repuestos
var flc = 0;

// Agregar elemento servicio, componente o repuesto
$(document).on('click','#btnAddEle',function(){

  let idco = $('#hidCurIdCom').val(), idc = $('#hidCurLinCom').val(), pnu = $('#txtParNum').val(), tip = $('#slcTipo').val(), 
      fam = $('#slcFamilia').val(), cat = $('#slcCategoria').val(), com = $('#slcElemento').val(), can = $('#txtCantidad').val(),
      lco = $('#slcElemento option:selected').text(), vun = $('#hidValUnit').val();

  if( pnu.length != 0 && tip.length != 0 && fam.length != 0 && cat.length != 0 && com.length != 0 && can.length != 0 ){
    
    if( idc.length == 0 ){

      destroyTableParam('#tabPrecios');

      let lin = '';
      let btn = '<button class="btn btn-info btn-sm edit-dty-com" type="button" idfila="'+flc+'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
          btn += '<button class="btn btn-danger btn-sm dele-dty-com" type="button" idfila="'+flc+'"><i class="fa fa-times"></i></button>';

      let hid = '<input type="hidden" name="hidCurLinComTab'+flc+'" id="hidCurLinComTab'+flc+'" value="'+flc+'">';
          hid += '<input type="hidden" name="hidCurIdComTab'+flc+'" id="hidCurIdComTab'+flc+'" value="'+com+'">';
          hid += '<input type="hidden" name="hidTipoComTab'+flc+'" id="hidTipoComTab'+flc+'" value="'+tip+'">';
          hid += '<input type="hidden" name="hidFamiComTab'+flc+'" id="hidFamiComTab'+flc+'" value="'+fam+'">';
          hid += '<input type="hidden" name="hidCateComTab'+flc+'" id="hidCateComTab'+flc+'" value="'+cat+'">';

      lin += '<tr id="trComTab'+flc+'">';
        lin += '<td id="tdDescComTab'+flc+'">'+lco+hid+'</td>';
        lin += '<td id="tdCantComTab'+flc+'" class="text-center">'+can+'</td>';
        lin += '<td id="tdPnumComTab'+flc+'" class="text-center">'+pnu+'</td>';
        lin += '<td id="tdVuniComTab'+flc+'" class="text-right">$ '+vun+'</td>';
        lin += '<td id="tdTotaComTab'+flc+'" class="text-right">$ '+vun*can+'</td>';
        lin += '<td class="text-center">'+btn+'</td>';
      lin += '</tr>';

      $('#tpartes').append(lin);
      flc++;      

    } else {

      let hid = '<input type="hidden" name="hidCurLinComTab'+idc+'" id="hidCurLinComTab'+idc+'" value="'+idc+'">';
          hid += '<input type="hidden" name="hidCurIdComTab'+idc+'" id="hidCurIdComTab'+idc+'" value="'+idco+'">';
          hid += '<input type="hidden" name="hidTipoComTab'+idc+'" id="hidTipoComTab'+idc+'" value="'+tip+'">';
          hid += '<input type="hidden" name="hidFamiComTab'+idc+'" id="hidFamiComTab'+idc+'" value="'+fam+'">';
          hid += '<input type="hidden" name="hidCateComTab'+idc+'" id="hidCateComTab'+idc+'" value="'+cat+'">';

      $('#tdDescComTab'+idc).html(lco+hid);
      $('#tdCantComTab'+idc).html(can);
      $('#tdPnumComTab'+idc).html(pnu);
      $('#tdVuniComTab'+idc).html('$ '+vun);
      $('#tdTotaComTab'+idc).html('$ '+vun*can);

    }

    cleanComps();
    readTabComps();
    renderDTParam('#tabPrecios');

  }

});

// Seleccionar técnico a editar
$(document).on('click','.edit-dty-com',function(e){

  e.preventDefault();

  let idfila = $(this).attr('idfila');hidCurIdCom

  let idc = $('#hidCurIdComTab'+idfila).val(), idf = $('#hidCurLinComTab'+idfila).val(), pnu = $('#tdPnumComTab'+idfila).text(), 
      tip = $('#hidTipoComTab'+idfila).val(), fam = $('#hidFamiComTab'+idfila).val(), cat = $('#hidCateComTab'+idfila).val(),
      can = $('#tdCantComTab'+idfila).text(), vun = $('#hidValUnit').val();

  $('#hidCurIdCom').val(idc); $('#hidCurLinCom').val(idf); $('#txtParNum').val(pnu); $('#slcTipo').val(tip);
  $('#slcFamilia').val(fam); $('#slcCategoria').val(cat); $('#txtCantidad').val(can);
  $('#hidValUnit').val(vun); $('#txtCantidad').focus();

  setTimeout(function(){ $('#slcCategoria').change(); }, 200);
  setTimeout(function(){ $('#slcElemento').val(idc); }, 400);

  $('#btnAddEle').removeClass('btn-info').addClass('btn-success');
  $('#btnAddEle').html('<i class="fa fa-save"></i>');

});

// Seleccionar técnico a borrar
$(document).on('click','.dele-dty-com',function(e){

  e.preventDefault();

  if( confirm('¿Desea eliminar este elemento de la tabla?') ){
    let idfila = $(this).attr('idfila');
    $('#trComTab'+idfila).remove();
    readTabComps();
  }

});

// Limpiar campos de técnicos
function cleanComps(){

  $('#hidCurIdCom').val(''); $('#hidCurLinCom').val(''); $('#txtParNum').val('');
  $('#slcTipo').val(''); $('#slcFamilia').val(''); $('#slcCategoria').val('');
  $('#slcElemento').val(''); $('#txtCantidad').val('');
  $('#txtParNum').focus();
  $('#btnAddEle').removeClass('btn-success').addClass('btn-info');
  $('#btnAddEle').html('<i class="fa fa-plus"></i>');

}

// Leer datos de la tabla de componentes, repuestos y servicios
function readTabComps(){

  let comps = [];
  let vtot = 0;

  $("#tpartes tr").each(function(i) {
    comps.push({ 
        'idecom':$(this).find('td:eq(0) input:eq(1)').val(),
        'cancom':$(this).find('td:eq(1)').text(),
        'vuncom':$(this).find('td:eq(3)').text().replace("$ ",""),
        'vtocom':$(this).find('td:eq(4)').text().replace("$ ","")
    });
    vtot = vtot + parseFloat($(this).find('td:eq(4)').text().replace("$ ",""));
    $('#hidVlsComps').val(JSON.stringify(comps));
    console.log(comps);
  });

  $('#totales').html(vtot);

}

// Abrir modal de componentes
$(document).on('click','.openModComp',function(){
  let idreg = $(this).attr('idreg');
  //alert('Modal de componentes para cambiar el id '+idele);
  $('#modCompos').modal({
    backdrop: 'static',
    keyboard: false
  });
});

// Abrir modal de repuestos

var irep = null;

$(document).on('click','.openModRepu',function(){
  let idreg = $(this).attr('idreg');
  irep = idreg;
  //alert('Modal de repuestos '+idele);
  $('#modRepuest').modal({
    backdrop: 'static',
    keyboard: false
  });
});

// Métodos para cambiar repuestos

// Cambiar categorías ventana modal repuestos
$(document).on('change','#slcCategoriasModRep',function(){

  let cnf = {
      model 	: 'mmtos',
      method 	: 'repos',
      params	: {
          val	: $(this).val(), 
          fam	: $('#slcFamiliasModRep').val(), 
          def : '',
          typ : 'echo'
      },
      target	: 'slcRepuesto'
  };

  cmbdep(cnf);

});

/*$(document).on('change','#slcRepuesto',function(){

  var params = {
      'model'  : 'mmtos',
      'method' : 'controlr',
      'args'   : $(this).val()
  };

  $.ajax({
      url: 'index.php',
      type: 'POST',
      dataType: 'html',
      data: params,
      cache: false, // Appends _={timestamp} to the request query string
      success: function($dres) {
          $('#repoAdd').append($dres);
      }
  });

});*/

function lstReps(cont){

  var params = {
    'model'  : 'mmtos',
    'method' : 'controlr',
    'args'   : $('#slcRepuesto').val()
  };

  $.ajax({
    url: 'index.php',
    type: 'POST',
    dataType: 'html',
    data: params,
    cache: false, // Appends _={timestamp} to the request query string
    success: function($dres) {
        $(cont).html($dres);
    }
  });

}

// Botón de agregar repuesto al equipo 
$(document).on('click','#btnSaveModRepus',function(){

  if( valReqModal('rep-fld') ){

    $('#hidVlsDelRep').val($('#hidVlsDelRep').val()+irep+',');
    
    let idrep = $('#hidRepuReemp'+irep).val();
    $('#hidRepuReemp'+irep).addClass('rep-fld-val');
    lstReps('#fldContRepu'+idrep);
    $('#titRepu'+idrep).text($('#slcRepuesto option:selected').text());

    setTimeout(function(){ 
      $("#btnCancelModRepus").click();
    }, 500);

    /*let reps = [];

    $('.rep-fld-val').each(function() {
      let ide = $(this).attr('idcamp');
      reps.push({ 
        'fld':ide,
        'val':$(this).val()
      });
    });

    let adrep = $('#hidVlsAddRep').val();

    if( adrep.length > 0 ){
      let hreps = JSON.parse(adrep);
      let resre = $.extend(hreps,reps);
      $('#hidVlsAddRep').val(JSON.stringify(resre));
    } else {
      $('#hidVlsAddRep').val(JSON.stringify(reps));
    }

    let idrep = $('#hidRepoReemp').val();
    
    $("#fldContRepu"+idrep).html(flds);

    */
    
  } else {
    let conf = {
      'tarmsg'  : 'contMsgModalRepu',
      'tarow'   : 'rowMsgModalRepu',
      'msg'     : 'Faltan campos por diligenciar para agregar el repuesto.'
    };
    alertCustom(conf);
  }

});

// Botón de cerrar ventana modal de los repuestos
$(document).on('click','#btnCancelModRepus',function(){
  delFldCtrl();
});

// Eliminar elementos de campos de control
function delFldCtrl(){
  $('#slcFamiliasModRep').val('');
  $('#slcCategoriasModRep').val('');
  $('#slcRepuesto').html('');
}

// Fin métodos cambiar repuestos

// Validación de campos vacíos
function valReqModal(cls){
  var state = true;
  var campos = $('.'+cls);
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

// Retornar clases de campos al focos
$(document).on('focus','.rep-fld',function(){
  if( $(this).hasClass('is-invalid') ){
    $(this).removeClass('is-invalid');
  }
});

/* Botón de guardar y validaciones */
$(document).on('click','#btnSaveAct',function(){

  let chk = false;

  $('.chk-compo').each(function() {
    if( $(this).prop('checked') ) {
      chk = true;
    } else {
      chk = false;
    }
  });

  if( chk ){
    
    alert('Envío datos');

    
    
  } else {
    let conf = {
      'tarmsg'  : 'contMsg',
      'tarow'   : 'rowMsg',
      'msg'     : 'Faltan Componentes o Repuestos por verificar.'
    };
    alertCustom(conf);
  }

});