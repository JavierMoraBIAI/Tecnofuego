function defaMethod(){

    let idc = $('#hidId').val();
    if( idc != '' ){

        let cnf = {
            model  : 'equipment',
            method : 'sitios',
            params : {
                val : $('#slcCliente').val(),
                def : $('#hidIdProy').val(),
                typ : 'echo'
            },
            target : 'slcSitios'
        };
    
        cmbdep(cnf);
    
        let cnf2 = {
            model  : 'equipment',
            method : 'listsdep',
            params : {
                idlst : $('#slcMarca').val(),
                str   : 'SELECCIONE MODELO',
                def   : $('#hidIdModel').val(),
            },
            target : 'slcModelo'
        };
    
        cmbdep(cnf2);
    
        lcomps();
        //renderDTParam('#tabCompos');

        setTimeout(function(){ 
            readTableReps(); 
            renderDTParam('#tabRepos');
        }, 500);

    }

}

function lcomps(){

    destroyTableParam('#tabCompos');

    let pcomps = {
        'model'  : 'equipment',
        'method' : 'addcomps',
        'args'   : $('#hidLstComp').val()
    };

    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'html',
        data: pcomps,
        cache: false, // Appends _={timestamp} to the request query string
        success: function($dres) {

            $('#tcompos').append($dres);
            readTableComp();
            renderDTParam('#tabCompos');

            /*if( $dres.sts == 0 ){

                let row = '';

                $.each($dres.res, function(i, vlsc) {
                    
                    row += '<tr id="trCompo'+vlsc.item+'" idreg="'+vlsc.item+'">';
                        row += '<td id="tdComps'+i+'" class="text-center">'+vlsc.item+'</td>';
                        row += '<td>'+vlsc.descripcion+'</td>';
                        row += '<td class="text-center">'+vlsc.consec+'</td>';
                        row += '<td class="text-center">'+vlsc.familia+'</td>';
                        row += '<td class="text-center">'+vlsc.categoria+'</td>';
                        row += '<td class="text-center">'+vlsc.acciones+'</td>';
                    row += '</tr>';

                });

                $('#tcompos').append(row);
                readTableComp();

            }*/

        }
    });

}

$(document).on('change','#slcCliente',function(){

    let cnf = {
        model  : 'equipment',
        method : 'sitios',
        params : {
        com : $(this).val(),
        def : '',
        typ : 'echo'
        },
        target : 'slcSitios'
    };

    cmbdep(cnf);

});

$(document).on('change','#slcClienteMod',function(){

    let cnf = {
        model  : 'equipment',
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

$(document).on('change','#slcMarca',function(){

    let cnf = {
        model  : 'equipment',
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

// Componentes

$(document).on('click','#btnAddCmp',function(){

    $('#modCompos').modal({
        backdrop: 'static',
        keyboard: false
    });

});

$(document).on('change','#slcCategorias',function(){

    let cnf = {
        model 	: 'equipment',
        method 	: 'repos',
        params	: {
            val	: $(this).val(), 
            fam	: $('#slcFamilias').val(), 
            def : '',
            typ : 'echo'
        },
        target	: 'slcRepuesto'
    };

    cmbdep(cnf);

});

$(document).on('change','#slcRepuesto',function(){

    var params = {
        'model'  : 'equipment',
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
            $('#rboxes').html($dres);
        }
    });

});

$(document).on('click','#btnSearchComp',function(){

    destroyTableParam('#tabComposAdd');

    var params = {
        'model'  : 'equipment',
        'method' : 'lstcompo',
        'args'   : $('#frmModalCompo').formToObject()
    };

    if( $('#hidVlsComp').val() != '' ){

        let acomp = '';

        $("#tcompos tr").each(function(i) {
            acomp += $(this).attr('idreg')+',';
        });

        params.args.comps = acomp;

    } else {
        params.args.comps = '';
    }

    //alert(params.args.comps);

    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'html',
        data: params,
        cache: false, // Appends _={timestamp} to the request query string
        success: function($dres) {
            $('#detCompos').html($dres);
            renderDTParam('#tabComposAdd');
        }
    });

});

$(document).on('click','#btnSaveModCompo',function(){

    let compos = [];
    
    $(".chk-compo").each(function(index) {
        if( $(this).prop('checked') ){
            compos.push($(this).val());            
        }
    });
    
    console.log(compos);

    let params = {
        'model'  : 'equipment',
        'method' : 'addcomps',
        'args'   : compos.toString()
    };

    console.log(params);

    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'html',
        data: params,
        cache: false, // Appends _={timestamp} to the request query string
        success: function($dres) {

            /*if( $dres.sts == 0 ){

                let rwc = '';

                $.each($dres.res, function(i, vals) {
                    
                    rwc += '<tr id="trCompo'+vals.item+'" idreg="'+vals.item+'">';
                        rwc += '<td id="tdComps'+i+'" class="text-center">'+vals.item+'</td>';
                        rwc += '<td>'+vals.descripcion+'</td>';
                        rwc += '<td class="text-center">'+vals.consec+'</td>';
                        rwc += '<td class="text-center">'+vals.familia+'</td>';
                        rwc += '<td class="text-center">'+vals.categoria+'</td>';
                        rwc += '<td class="text-center">'+vals.acciones+'</td>';
                    rwc += '</tr>';

                });

            }*/

            destroyTableParam('#tabCompos');
            $('#tcompos').append($dres);
            $('#btnCancelModCompo').click();
            readTableComp();
            renderDTParam('#tabCompos');
            
        }
    });

});

$(document).on('click','#btnCancelModCompo',function(){
    $('#frmModalCompo').clearForm();
    $('#detCompos').html('');
});

$(document).on('click','.dcomp',function(e){
    e.preventDefault();
    if( confirm('¿Desea eliminar este valor de la lista?') ){
        //let ide = $(this).parent().parent().find('td:eq(0)').text();
        let ide = $(this).attr('ide');
        let idc = $('#hidId').val();
        if( idc != '' ){
            $('#hidVlsDelComp').val($('#hidVlsDelComp').val()+ide+',');            
        }
        destroyTableParam('#tabCompos');
        $('#trCompo'+ide).remove();
        readTableComp();
        setTimeout(() => {
            renderDTParam('#tabCompos');
        }, 300);
    }
});

function readTableComp(){

    let comps = [];
    let filas = 1;

    $("#tcompos tr").each(function(i) {
        comps.push({ 
            'idcomp':$(this).attr('idreg')
        });
        $('#hidVlsComp').val(JSON.stringify(comps));
        console.log(comps);
    });

    $(".spanComp").each(function(i) {
        $(this).html(filas);
        filas++;
    });

}

// Repuestos

$(document).on('click','#btnAddRep',function(){

    destroyTableParam('#tabRepos');

    if( validateFld('.ctrl-fld') ){
        
        let fami = $('#slcFamilias').val(), cats = $('#slcCategorias').val(), repu = $('#slcRepuesto').val();
        let lfam = $('#slcFamilias option:selected').text(), lcat = $('#slcCategorias option:selected').text();
        let lrep = $('#slcRepuesto option:selected').text(), idtr = $('#hidIdTrRep').val(), ideqrep = $('#hidIdTrEqRep').val();
        let valo = {
            'fami':fami,
            'cats':cats,
            'repu':repu,
            'lfam':lfam,
            'lcat':lcat,
            'lrep':lrep,
            'idtr':idtr,
            'ideqrep':ideqrep
        };

        if( idtr == '' ){
            nuevaFilaRep(valo);
        } else {
            editoFilaRep(valo);
        }

        if( $(this).hasClass('btn-success') ){
            $('#btnAddRep').removeClass('btn-success').addClass('btn-info');
            $('#btnAddRep').html('<i class="fa fa-plus"></i>');
        }

        renderDTParam('#tabRepos');

    } else {
        let conf = {
            'tarmsg'  : 'contMsg',
            'tarow'   : 'rowMsg',
            'msg'     : 'Debe llenar todos los campos marcados como obligatorios (asterisco * rojo) para poder agregar un repuesto.'
        };
        alertCustom(conf);
    }

});

$(document).on('change','#slcFamilias',function(){
    $('#slcCategorias').val('');
    $('#slcCategorias').change();
    $('#slcRepuesto').html('');
});

$(document).on('click','#btnCnclRep',function(){

    $('#slcFamilias').val(''); $('#slcCategorias').val(''); $('#slcRepuesto').val(''); $('#rboxes').html('');
    $('#hidIdTrRep').val(''); $('#hidIdTrEqRep').val('');
    if( $('#btnAddRep').hasClass('btn-success') ){
        $('#btnAddRep').removeClass('btn-success').addClass('btn-info');
        $('#btnAddRep').html('<i class="fa fa-plus"></i>');
    }
    $('#slcFamilias').change();

});

// Filas de los repuestos
var fila = $("#detRepus tr").length;

function nuevaFilaRep(valo){

    let btns = '<button class="btn btn-info btn-sm edit-dty-rep" type="button" idfila="'+fila+'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
    btns += '<button class="btn btn-danger btn-sm dele-dty-rep" type="button" idfila="'+fila+'"><i class="fa fa-times"></i></button>';

    let repos = [];

    $(".rep-fld").each(function(i) {
        let ide = $(this).attr('id').split('-');
        repos.push({ 
            'fld':ide[1], 
            'val':$(this).val()
        });
    });

    let hids = '<input type="hidden" name="hidFami'+fila+'" id="hidFami'+fila+'" value="'+valo.fami+'">';
    hids += '<input type="hidden" name="hidCats'+fila+'" id="hidCats'+fila+'" value="'+valo.cats+'">';
    hids += '<input type="hidden" name="hidRepu'+fila+'" id="hidRepu'+fila+'" value="'+valo.repu+'">';
    hids += "<input type='hidden' name='hidVals"+fila+"' id='hidVals"+fila+"' value='"+JSON.stringify(repos,null)+"'>";
    hids += "<input type='hidden' name='hididEqRep"+fila+"' id='hididEqRep"+fila+"' value=''>";
    
    var tr = '<tr id="tr'+fila+'" idx="'+fila+'">';
        tr += '<td id="tdItem'+fila+'" class="text-center">'+hids+'<span class="spanRepu">'+fila+'</span></td>';
        tr += '<td id="tdRepu'+fila+'">'+valo.lrep+'</td>';
        tr += '<td id="tdFami'+fila+'" class="text-center">'+valo.lfam+'</td>';
        tr += '<td id="tdCate'+fila+'" class="text-center">'+valo.lcat+'</td>';
        tr += '<td class="text-center">'+btns+'</td>';
    tr += '</tr>';

    $('#detRepus').append(tr);
    fila++;
    $('#btnCnclRep').click();
    readTableReps();

}

function editoFilaRep(valo){

    let repos = [];

    $(".rep-fld").each(function(i) {
        let ide = $(this).attr('id').split('-');
        repos.push({ 
            'fld':ide[1], 
            'val':$(this).val()
        });
    });

    let hids = '<input type="hidden" name="hidFami'+valo.idtr+'" id="hidFami'+valo.idtr+'" value="'+valo.fami+'">';
    hids += '<input type="hidden" name="hidCats'+valo.idtr+'" id="hidCats'+valo.idtr+'" value="'+valo.cats+'">';
    hids += '<input type="hidden" name="hidRepu'+valo.idtr+'" id="hidRepu'+valo.idtr+'" value="'+valo.repu+'">';
    hids += "<input type='hidden' name='hidVals"+valo.idtr+"' id='hidVals"+valo.idtr+"' value='"+JSON.stringify(repos,null)+"'>";
    hids += "<input type='hidden' name='hididEqRep"+valo.idtr+"' id='hididEqRep"+valo.idtr+"' value='"+valo.ideqrep+"'>";
    
    $('#tdItem'+valo.idtr).html(hids+valo.idtr);
    $('#tdRepu'+valo.idtr).html(valo.lrep);
    $('#tdFami'+valo.idtr).html(valo.lfam);
    $('#tdCate'+valo.idtr).html(valo.lcat);
    readTableReps();
    $('#btnCnclRep').click();
    
}

function readTableReps(){

    let repos = [];
    let firep = 1;

    $("#detRepus tr").each(function(i) {
        repos.push({ 
            'idr'   : $(this).find('td:eq(0) input:eq(4)').val(), 
            'idrep' : $(this).find('td:eq(0) input:eq(2)').val(), 
            'vlrep' : $(this).find('td:eq(0) input:eq(3)').val()
        });
        $('#hidVlsReps').val(JSON.stringify(repos));
        console.log(repos);
    });

    $(".spanRepu").each(function(i) {
        $(this).html(firep);
        firep++;
    });

    fila = firep;

}

// Editar valor detalle repuestos
$(document).on('click','.edit-dty-rep',function(){

    $('#btnAddRep').removeClass('btn-info').addClass('btn-success');
    $('#btnAddRep').html('<i class="fa fa-save"></i>');
    
    let idtr = $(this).attr('idfila');
    let ideqrep = $(this).attr('idx');
    let flds = JSON.parse($('#hidVals'+idtr).val());
    
    $('#hidIdTrRep').val(idtr);
    $('#hidIdTrEqRep').val(ideqrep);
    $('#slcFamilias').val($('#hidFami'+idtr).val());
    $('#slcCategorias').val($('#hidCats'+idtr).val());
    $('#slcCategorias').change();
    
    setTimeout(function(){
        $('#slcRepuesto').val($('#hidRepu'+idtr).val());
        $('#slcRepuesto').change();			
    }, 500);

    setTimeout(function(){
        $.each(flds, function(h, item) {
            $("input[idcamp|='"+flds[h].fld+"']").val(flds[h].val);				
        });
    }, 600);

});

// Eliminar valor detalle repuestos
$(document).on('click','.dele-dty-rep',function(){
    if( confirm('¿Desea eliminar este valor de la lista?') ){        
        //let ide = $(this).parent().parent().find('td:eq(0) input:eq(4)').val();
        let ide = $(this).parent().parent().find('td:eq(0) input:eq(4)').val();
        if( ide != '' ){
            $('#hidVlsDelRep').val($('#hidVlsDelRep').val()+ide+',');
        }       
        destroyTableParam('#tabRepos');
        $('#tr'+$(this).attr('idfila')).remove();
        readTableReps();
        setTimeout(() => {
            renderDTParam('#tabRepos');
        }, 300);
    }    	
});

function validateFld(cls){

    let state = true;
    let campos = $(cls);
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

$(document).on('focus','.ctrl-fld',function(){

    if( $(this).hasClass('is-invalid') ){
        $(this).removeClass('is-invalid');
    }

});

// Verificación de cambios de Número interno y Número de serial
$(document).on('blur','.val-code',function(){

    let valor = $(this).val();
    let scli = $('#slcCliente').val();
    let hcli = $('#hidCliente').val();

    if( valor.length > 0 && ( scli.length > 0 || hcli.length > 0 ) ){

        let vfld = $(this).attr('fld');

        let pcomps = {
            'model'  : 'equipment',
            'method' : 'verifnum',
            'args'   : {
                'vfld' : vfld,
                'scli' : scli,
                'hcli' : hcli,
                'vbus' : valor
            }
        };
    
        $.ajax({
            url: 'index.php',
            type: 'POST',
            dataType: 'text',
            data: pcomps,
            cache: false, // Appends _={timestamp} to the request query string
            success: function($dres) {

                let campo = '';

                if( $dres == 1 ){

                    if( vfld == 1 ){ // Valida número interno
                        campo = 'número interno';                        
                    } else { // Valida serial
                        campo = 'número serial';
                    }

                    let conf = {
                        'tarmsg'  : 'contMsg',
                        'tarow'   : 'rowMsg',
                        'msg'     : 'Ya hay un equipo con ese '+campo+' cargado'
                    };

                    alertCustom(conf);

                    $("#btnSave").addClass('disabled');
                    $('#btnSave').prop('disabled', true);

                } else {
                    $("#btnSave").removeClass('disabled');
                    $('#btnSave').prop('disabled', false);
                }

            }
        });

    }

});