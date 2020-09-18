function valProfile(){

    let role = $('#hidPerf').val();
    let comp = $('#hidComp').val();
    let proy = $('#hidProy').val();

    if( role > 1 && comp > 1 ){

        //console.log('Rol '+role+' Cliente '+comp+' Proyecto '+proy);
        $('.cliente').val(comp);
        $('.clihid').val(comp);
        $('.cliente').trigger('change');
        $('.cliente').prop('disabled', 'disabled');

        setTimeout(function(){ 

            let cnf = {
                model 	: currentModel,
                method 	: 'sitios',
                params	: {
                    val	: comp, 
                    def : proy,
                    typ : 'echo'
                },
                target	: 'proyecto'
            };
    
            cmbdepClss(cnf);

            $('.proyecto').prop('disabled', 'disabled');
            $('.proyhid').val(proy);

        }, 200);

    }

}