<?php

    class equipment {
        
        public function __construct(array $res){
        	$this->clstr = $res['cleanstr'];
        	$this->crud = $res['crud'];
        	$this->rndr = $res['render'];
        	$this->fima = $res['fileman'];
            $this->seda = $_SESSION['u'];
        }

        // Método inicial
        public function index(){

        	$d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar equipos'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'flotas'    =>  self::lists(array('idlst'=>11,'str'=>'SELECCIONE FLOTA','def'=>'')),
                    'marcas'    =>  self::lists(array('idlst'=>2,'str'=>'SELECCIONE MARCA','def'=>'')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/equipment/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){


            $sql = "SELECT e.internalNumber 'N° INTERNO', e.serial 'N° SERIAL', f.label FLOTA, 
                        UPPER(ma.label) MARCA, UPPER(m.label) MODELO, IF(e.edo_reg=1,'ACTIVO','INACTIVO') ESTADO,
                        CONCAT('<a idreg=\"',e.id,'\" href=\"editar\" rel=\"equipment\" action=\"upd\" title=\"Editar equipo\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."equipment e, ".BD_PREFI."valists f, ".BD_PREFI."valists ma, ".BD_PREFI."valists m, ".BD_PREFI."sites s
					WHERE e.typeEquipamentId = f.id
                        AND e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'slcEmpresa':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidCliente':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcSitios':

                            $sql .= " AND e.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidProyecto':

                            $sql .= " AND e.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcFlotas':

                            $sql .= " AND e.typeEquipamentId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'txtNumInter':

                            $sql .= " AND e.internalNumber = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;

                        case 'txtNumSer':

                            $sql .= " AND e.serial = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;

                        case 'slcMarca':

                            $sql .= " AND m.valfather = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcModelo':

                            $sql .= " AND e.idModel = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND e.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $sql .= ';';

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,1,2,6,7);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar equipos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'flotas'    =>  self::lists(array('idlst'=>11,'str'=>'SELECCIONE FLOTA','def'=>'')),
                    'marcas'    =>  self::lists(array('idlst'=>2,'str'=>'SELECCIONE MARCA','def'=>'')),
                    'familia'   =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categoria' =>  self::listrepos(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍAS','def'=>'')), //
                    'sistemas'  =>  self::lists(array('idlst'=>10,'str'=>'SELECCIONE SISTEMA','def'=>'')),
                    'sino'      =>  self::sino('return',''),
                    'estados'   =>  self::estados('return',''),
                    'time'      =>  time(),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/equipment/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT e.id, e.internalNumber, e.serial, e.typeEquipamentId, e.idModel, 
                        m.valfather idBrand, e.edo_reg, e.siteId, s.companyId, e.horometer, 
                        e.detectionSystem, e.extinctionSystem
                    FROM ".BD_PREFI."equipment e, ".BD_PREFI."valists f, ".BD_PREFI."valists ma, 
                        ".BD_PREFI."valists m, ".BD_PREFI."sites s
                    WHERE e.typeEquipamentId = f.id
                        AND e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id
                        AND e.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar equipos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>$ar['companyId'],'typ'=>'retu')),
                    'empmodal'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'flotas'    =>  self::lists(array('idlst'=>11,'str'=>'SELECCIONE FLOTA','def'=>$ar['typeEquipamentId'])),
                    'marcas'    =>  self::lists(array('idlst'=>2,'str'=>'SELECCIONE MARCA','def'=>$ar['idBrand'])),
                    'familia'   =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categoria' =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍAS','def'=>'')),
                    'sistemas'  =>  self::lists(array('idlst'=>10,'str'=>'SELECCIONE SISTEMA','def'=>'')),
                    'sinodet'   =>  self::sino('return',$ar['detectionSystem']),
                    'sinoext'   =>  self::sino('return',$ar['extinctionSystem']),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'time'      =>  time(),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/equipment/editar.html'
            );

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $sqlComp = "SELECT ec.idCompo
                        FROM tec_equip_compos ec
                        WHERE ec.idEquip = ?
                            AND ec.edo_reg = ?;";

            $dpc = array();
            array_push($dpc, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            array_push($dpc, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $awc = $this->crud->select_group($sqlComp, count($dpc), $dpc, 'arra');
            $lco = '';

            foreach ($awc['res'] as $kc => $vc) {
                $lco .= $vc['idCompo'].',';
            }

            $d['data']['lco'] = trim($lco,',');
            $d['data']['rps'] = self::lstrepos($data);

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array(
                'siteId'            =>  (empty($data->slcSitios)) ? $data->hidProyecto : $data->slcSitios,
                'typeEquipamentId'  =>  $data->slcFlotas,
                'idModel'           =>  $data->slcModelo,
                'extinctionSystem'  =>  $data->slcSiExtin,
                'detectionSystem'   =>  $data->slcSiDetec,
                'internalNumber'    =>  $data->txtNumInter,
                'serial'            =>  $data->txtNumSer,
                'horometer'         =>  $data->txtHorometro,
                'edo_reg'           =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'equipment',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'equipment');

            }

            if( $resp['rta'] == 'OK' ){
                
                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';

                // Insertar componentes
                $comps = json_decode($data->hidVlsComp,true);

                foreach ($comps as $kc => $vc) {
                    
                    $infc = array(
                        'idEquip'   =>  (empty($data->hidId)) ? $resp['lstId'] : $data->hidId,
                        'idCompo'   =>  $vc['idcomp'],
                        'edo_reg'   =>  1
                    );

                    $sqlC = "SELECT COUNT(*) cant
                             FROM tec_equip_compos ec
                             WHERE ec.idEquip = ?
                                AND ec.idCompo = ?
                             LIMIT 1;";

                    $dpc = array();
                    array_push($dpc, ['kpa'=>1,'val'=>$infc['idEquip'],'typ'=>'int']);
                    array_push($dpc, ['kpa'=>2,'val'=>$infc['idCompo'],'typ'=>'int']);
                    $awc = $this->crud->select_group($sqlC, count($dpc), $dpc, 'arra');

                    if( $awc['res'][0]['cant'] > 0 ){

                        $infc['usu_mod'] = $this->seda['idu'];
                        $infc['fec_mod'] = date('Y-m-d H:i:s');
                        $infc['ip_mod']  = Firewall::ipCatcher();
        
                        $whrc = array('idEquip'=>$infc['idEquip'],'idCompo'=>$infc['idCompo']);
        
                        $rc = $this->crud->update($infc,BD_PREFI.'equip_compos',$whrc);
        
                    } else {
        
                        $infc['usu_crea'] = $this->seda['idu'];
                        $infc['fec_crea'] = date('Y-m-d H:i:s');
                        $infc['ip_crea']  = Firewall::ipCatcher();
        
                        $rc = $this->crud->insert($infc,BD_PREFI.'equip_compos');
        
                    }

                    unset($infc,$whrc,$rc);

                    // Asingar componentes
                    $inf = array('edo_reg'=>2);
                    $whr = array('id'=>$vc['idcomp']);
                    $rsp = $this->crud->update($inf,BD_PREFI.'components',$whr);
                    unset($inf,$whr,$rsp);

                }

                // Insertar repuestos
                $repos = json_decode($data->hidVlsReps,true);

                foreach ($repos as $kr => $vr) {

                    $infr = array(
                        'idEquip'   =>  (empty($data->hidId)) ? $resp['lstId'] : $data->hidId,
                        'idRepo'    =>  $vr['idrep'],
                        'repvalues' =>  $vr['vlrep'],
                        'edo_reg'   =>  1
                    );

                    if( strlen($vr['idr']) > 0 ){

                        $infr['usu_mod'] = $this->seda['idu'];
                        $infr['fec_mod'] = date('Y-m-d H:i:s');
                        $infr['ip_mod']  = Firewall::ipCatcher();
        
                        $whrr = array('idEqRep'=>$vr['idr']);
        
                        $rr = $this->crud->update($infr,BD_PREFI.'equip_repos',$whrr);
        
                    } else {
        
                        $infr['usu_crea'] = $this->seda['idu'];
                        $infr['fec_crea'] = date('Y-m-d H:i:s');
                        $infr['ip_crea']  = Firewall::ipCatcher();
        
                        $rr = $this->crud->insert($infr,BD_PREFI.'equip_repos');
        
                    }

                    unset($infr,$whrr,$rr);
                    
                }

                // Eliminar componentes
                if( strlen($data->hidVlsDelComp) > 0 ){

                    $cps = explode(',',trim($data->hidVlsDelComp,','));

                    foreach ($cps as $kcomp => $vcomp) {

                        $infcomp = array('edo_reg'=>0,'usu_mod'=>$this->seda['idu'],'fec_mod'=>date('Y-m-d H:i:s'),'ip_mod'=>Firewall::ipCatcher());
                        $whrcomp = array('id'=>$vcomp);
                        $rspcomp = $this->crud->update($infcomp,BD_PREFI.'components',$whrcomp);
                        unset($infcomp,$whrcomp,$rspcomp);

                        $infcomp2 = array('edo_reg'=>0,'usu_mod'=>$this->seda['idu'],'fec_mod'=>date('Y-m-d H:i:s'),'ip_mod'=>Firewall::ipCatcher());
                        $whrcomp2 = array('idCompo'=>$vcomp,'idEquip'=>$data->hidId);
                        // $whrcomp3 = array('idEquip'=>$data->hidId);
                        $rspcomp2 = $this->crud->update($infcomp2,BD_PREFI.'equip_compos',$whrcomp2);
                        unset($infcomp2,$whrcomp2,$rspcomp2);

                    }

                }

                // Eliminar repuestos
                if( strlen($data->hidVlsDelRep) > 0 ){
                    
                    $rps = explode(',',trim($data->hidVlsDelRep,','));

                    foreach ($rps as $k => $v) {
                        $inf = array('edo_reg'=>'0','usu_mod'=>$this->seda['idu'],'fec_mod'=>date('Y-m-d H:i:s'),'ip_mod'=>Firewall::ipCatcher());
                        $whr = array('idEqRep'=>$v);
                        $rsp = $this->crud->update($inf,BD_PREFI.'equip_repos',$whr);
                        unset($inf,$whr,$rsp);
                    }

                }

            } else {
                $cls = 'alert-danger';
                $msg = 'Hubo un error guardando la información: '.$resp['errmsg'].' &nbsp;&nbsp;<i class="fa fa-times" aria-hidden="true"></i>';
            }

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar equipos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/equipment/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listado de componentes
        public function componentes(array $data){
            
            $sql = "SELECT c.id ID, c.name COMPONENTE, c.pn 'NÚM. PARTE',
                        '<a href=\"selcomp\" rel=\"equipment\" action=\"sel\" title=\"Seleccionar componente\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-plus\"></i></a>' SELECCIONAR
                    FROM tec_components c
                    WHERE c.edo_reg = ? ";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $im = 2;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'txtNumPart':

                            $sql .= " AND c.pn LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'txtComponente':

                            $sql .= " AND c.name LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $sql .= ' ORDER BY 2;';

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,2,3);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Listado de empresas
        private function empresas(array $conf){

            $sql = "SELECT c.id, c.name label
                    FROM ".BD_PREFI."company c
                    WHERE c.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE CLIENTE', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado de sitios por empresa
        public function sitios(array $conf){

            $sql = "SELECT s.id, s.name label
                    FROM ".BD_PREFI."sites s
                    WHERE s.edo_reg = ?
                        AND s.companyId = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$conf['val'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE PROYECTO/MINA', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listas configuradas
        private function lists(array $data){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v
                    WHERE v.idlist = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idlst'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            return $this->rndr->renderSelect($aw['res'], $data['str'], $data['def']);

        }

        // Lista de repuestos
        private function listrepos(array $data){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v, ".BD_PREFI."parts p
                    WHERE v.id = p.idCategory
                        AND v.idlist = ?
                        AND p.partserv = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idlst'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>'R','typ'=>'string']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            return $this->rndr->renderSelect($aw['res'], $data['str'], $data['def']);
            
        }

        // Lista dependiente
        public function listsdep(array $data){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v
                    WHERE v.valfather = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idlst'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            echo $this->rndr->renderSelect($aw['res'], $data['str'], $data['def']);

        }

        // Listado de componentes
        public function repos(array $conf){

            $sql = "SELECT DISTINCT p.id, p.description label
                    FROM ".BD_PREFI."parts p, ".BD_PREFI."parts_flds f
                    WHERE p.id = f.idPart
                        AND p.partserv = 'R'
                        AND f.idField NOT IN (31,34)
                        AND p.idCategory = ?
                        AND p.idFamily = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$conf['val'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$conf['fam'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE REPUESTO', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listar repuestos para editar
        public function lstrepos(int $equip){

            $sql = "SELECT er.idEqRep, er.idEquip, er.idRepo repu, p.description lrep, p.idFamily fami, f.label lfam, 
                        p.idCategory cats, c.label lcat, er.repvalues
                    FROM tec_equip_repos er, tec_parts p, tec_valists f, tec_valists c
                    WHERE er.idRepo = p.id
                        AND p.idFamily = f.id
                        AND p.idCategory = c.id
                        AND er.idEquip = ?
                        AND er.edo_reg = ?;";
                    
            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$equip,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $tr = '';
            $item = 1;
            
            foreach ($ar as $k => $v) {

                $btns = '<button class="btn btn-info btn-sm edit-dty-rep" type="button" idfila="'.$k.'" idx="'.$v['idEqRep'].'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
		        $btns .= '<button class="btn btn-danger btn-sm dele-dty-rep" type="button" idfila="'.$k.'" idx="'.$v['idEqRep'].'"><i class="fa fa-times"></i></button>';
                
                $hids = '<input type="hidden" name="hidFami'.$k.'" id="hidFami'.$k.'" value="'.$v['fami'].'">';
                $hids .= '<input type="hidden" name="hidCats'.$k.'" id="hidCats'.$k.'" value="'.$v['cats'].'">';
                $hids .= '<input type="hidden" name="hidRepu'.$k.'" id="hidRepu'.$k.'" value="'.$v['repu'].'">';
                $hids .= "<input type='hidden' name='hidVals".$k."' id='hidVals".$k."' value='".$v['repvalues']."'>";
                $hids .= "<input type='hidden' name='hididEqRep".$k."' id='hididEqRep".$k."' value='".$v['idEqRep']."'>";
                
                $tr .= '<tr id="tr'.$k.'">';
                    //$tr .= '<td id="tdItem'.$k.'" class="text-center">'.$hids.$v['repu'].'</td>';
                    $tr .= '<td id="tdItem'.$k.'" class="text-center">'.$hids.'<span class="spanRepu">'.$item.'</span></td>';
                    $tr .= '<td id="tdRepu'.$k.'">'.$v['lrep'].'</td>';
                    $tr .= '<td id="tdFami'.$k.'" class="text-center">'.$v['lfam'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['lcat'].'</td>';
                    $tr .= '<td class="text-center">'.$btns.'</td>';
                $tr .= '</tr>';

                $item++;

            }

            return $tr;

        }

        // Render de campos de control
        public function controlr(int $data){

            $sql = "SELECT f.id, f.idField, c.label campo, f.idType
                    FROM tec_parts_flds f, tec_valists c
                    WHERE f.idField = c.id
                        AND f.idPart = ?
                        AND f.edo_reg = 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];

            foreach ($ar as $k => $v) {
                
                $fld = 'txt'.str_replace(" ", "", ucwords($v['campo'])).'-'.$v['idField'];

                switch ($v['idType']) {
                    
                    case 41: // Número
                            $fbox .= '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">'.$v['campo'].' <span class="text-danger">*</span></label>
                                            <input type="number" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" idcamp="'.$v['idField'].'" class="form-control ctrl-fld rep-fld">
                                        </div>
                                      </div>';
                    break;

                    case 42: // Fecha
                        $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].' <span class="text-danger">*</span></label>
                                        <input type="date" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" idcamp="'.$v['idField'].'" class="form-control ctrl-fld rep-fld">
                                    </div>
                                  </div>';
                    break;

                    case 43: // Texto
                        $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].' <span class="text-danger">*</span></label>
                                        <input type="text" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" idcamp="'.$v['idField'].'" class="form-control ctrl-fld rep-fld">
                                    </div>
                                  </div>';
                    break;
                    
                }

            }
            
            echo $fbox;

        }
        
        //Listado de componentes por agregar
        public function lstcompo(array $data){

            $sql = "SELECT p.description DESCRIPCIÓN, 
                        f.label FAMILIA, ct.label CATEGORÍA, cv.valField 'SN/CONSEC.',
                        CONCAT('<input class=\"chk-compo\" type=\"checkbox\" value=\"',c.id,'\" id=\"chkCompo',c.id,'\">') SELECCIONAR
                    FROM tec_components c, tec_parts p, tec_compo_vals cv, tec_valists f, tec_valists ct, tec_sites s, tec_company cy
                    WHERE c.siteId = s.id
                        AND s.companyId = cy.id
                        AND c.idComponent = p.id
                        AND cv.idComponent = c.id
                        AND p.idFamily = f.id
                        AND p.idCategory = ct.id
                        AND cv.idField IN (31,34)
                        AND c.edo_reg = 1 ";
                    
            $dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'slcClienteMod':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidClienteMod':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcSitiosMod':

                            $sql .= " AND c.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidProyectoMod':

                            $sql .= " AND c.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcTySysMod':

                            $sql .= " AND c.idTySys = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcFamiliasMod':

                            $sql .= " AND p.idFamily = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcCategoriasMod':

                            $sql .= " AND p.idCategory = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'txtDescMod':

                            $sql .= " AND p.description LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'txtSerialMod':

                            $sql .= " AND cv.valField LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'comps':

                            $valor = trim($v,',');
                            $arr = explode(",", trim($v,','));
                            $cla = implode(',', array_fill(0, count($arr), '?'));
                            $sql .= " AND c.id NOT IN (".$cla.") ";
                            foreach ($arr as $kc => $vc) {
                                array_push($dp, ['kpa'=>$im,'val'=>$vc,'typ'=>'string']);
                                $im++;
                            }

                        break;

                    }

                    $im++;

                }

            }

            $sql .= ';';

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,2,3,4,5);
            echo $this->rndr->table_html($aw, $ccols, 'tabComposAdd');

        }

        // Componentes seleccionados
        public function addcomps(string $data){

            $arr = explode(",", $data);
            $cla = implode(',', array_fill(0, count($arr), '?'));

            $sql = "SELECT c.id item, p.description descripcion, cv.valField consec, f.label familia, ct.label categoria,
                        CONCAT('<a ide=\"',c.id,'\" href=\"delcomp\" rel=\"equipment\" title=\"Eliminar componente\" class=\"btn btn-sm btn-danger dcomp\"><i class=\"fa fa-trash\"></i></a>') acciones
                    FROM tec_components c, tec_parts p, tec_compo_vals cv, tec_valists f, tec_valists ct
                    WHERE c.idComponent = p.id
                        AND cv.idComponent = c.id
                        AND p.idFamily = f.id
                        AND p.idCategory = ct.id
                        AND cv.idField IN (31,34)
                        AND c.id IN (".$cla.") ";

            /*$dp = array();
            $it = 1;
            foreach ($arr as $k => $v) {
                array_push($dp, ['kpa'=>$it,'val'=>$v,'typ'=>'string']);
                $it++;
            }
            $sql .= ';';
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            echo json_encode($aw,true);*/

            $dp = array();
            $it = 1;
            foreach ($arr as $k => $v) {
                array_push($dp, ['kpa'=>$it,'val'=>$v,'typ'=>'string']);
                $it++;
            }
            $sql .= ';';
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $rwc = '';
            $item = 1;

            if($aw['sts']==0){

                foreach($aw['res'] as $ka => $va){

                    $rwc .= '<tr id="trCompo'.$va['item'].'" idreg="'.$va['item'].'">';
                    //$rwc .= '<td id="tdComps'.$ka.'" class="text-center">'.$va['item'].'</td>';
                    $rwc .= '<td id="tdComps'.$ka.'" class="text-center"><span class="spanComp">'.$item.'</span></td>';
                    $rwc .= '<td>'.$va['descripcion'].'</td>';
                    $rwc .= '<td class="text-center">'.$va['consec'].'</td>';
                    $rwc .= '<td class="text-center">'.$va['familia'].'</td>';
                    $rwc .= '<td class="text-center">'.$va['categoria'].'</td>';
                    $rwc .= '<td class="text-center">'.$va['acciones'].'</td>';
                    $rwc .= '</tr>';
                    $item++;

                }

            } else {
                $rwc = '';
            }

            echo $rwc;

        }

        // Listado de estados
        private function estados(string $tyre, string $dfval){

            $ar = array(
                array('id'=>1,'label'=>'ACTIVO'),
                array('id'=>0,'label'=>'INACTIVO')
            );

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE ESTADO', $dfval);

            if( $tyre == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado de si no
        private function sino(string $tyre, string $dfval){

            $ar = array(
                array('id'=>'S','label'=>'SI'),
                array('id'=>'N','label'=>'NO')
            );

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE OPCIÓN', $dfval);

            if( $tyre == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Verificar número de serial y número interno
        public function verifnum(array $data){

            if( $data['vfld'] == 1 ){
                $fld = 'e.internalNumber';
            } else {
                $fld = 'e.serial';
            }

            $sql = "SELECT COUNT(*) cant
                    FROM tec_equipment e, tec_sites s
                    WHERE e.siteId = s.id
                        AND s.companyId = ?
                        AND ".$fld." = ?
                    LIMIT 1;";

            $comp = (empty($data['scli'])) ? $data['hcli'] : $data['scli'];
            
            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$comp,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['vbus'],'typ'=>'string']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            if( $ar['cant'] > 0 ){
                if( $data['vfld'] == 1 ){
                    $msg = 'Número interno ya existe, por favor verifique la información ingresada.';
                } else {
                    
                }
            }

            echo $ar['cant'];


        }

    }

?>