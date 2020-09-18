<?php

    class roles {
        
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
                    'header'	    =>  $this->rndr->renderHeader('Gestionar roles'),
                    'footer'	    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'estados'       =>  self::estados('return',''),
                    'usuario'       =>  $this->seda['idu']
                ),
                'file' => 'html/roles/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT r.role ROL,
                        CASE r.edo_reg 
                            WHEN '0' THEN 'INACTIVO'
                            WHEN '1' THEN 'ACTIVO'
                        ELSE 'no es un tipo' END 'ESTADO',                      
                        CONCAT('<a idreg=\"',r.id,'\" href=\"editar\" rel=\"roles\" action=\"upd\" title=\"Editar rol\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."roles r
					WHERE r.id > 0 ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'txtRole':

                            $sql .= " AND r.role LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND r.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,2,3);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar roles'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'opciones'  =>  self::opciones(),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/roles/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT r.id, r.role, r.edo_reg
                    FROM ".BD_PREFI."roles r
                    WHERE r.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar roles'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'opciones'  =>  self::opciones(),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/roles/editar.html'
            );

            $sqlOps = "SELECT p.idoption, p.perm_add, p.perm_upd, p.perm_del
                        FROM tec_perfs_opts p
                        WHERE p.idrole = ?;";

            $dpo = array();
            array_push($dpo, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $awo = $this->crud->select_group($sqlOps, count($dpo), $dpo, 'arra');
            $aro = $awo['res'];

            foreach ($aro as $ko => $vo) {                
                $opcs .= $vo['idoption'].',';
                $opcrea .= ($vo['perm_add']==1) ? $vo['idoption'].'-'.$vo['perm_add'].',' : '';
                $opmodi .= ($vo['perm_upd']==1) ? $vo['idoption'].'-'.$vo['perm_upd'].',' : '';
                $opdele .= ($vo['perm_del']==1) ? $vo['idoption'].'-'.$vo['perm_del'].',' : '';
            }

            $d['data']['opcs'] = trim($opcs,',');
            $d['data']['opcrea'] = trim($opcrea,',');
            $d['data']['opmodi'] = trim($opmodi,',');
            $d['data']['opdele'] = trim($opdele,',');

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);
            var_dump($data);
            $info = array(
                'role'      =>  mb_strtoupper($data->txtRole),
                'edo_reg'   =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'roles',$where);

                $idrol = $data->hidId;

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'roles');

                $idrol = $resp['lstId'];

            }

            if( !empty($data->hidOpciones) ){

                $aro = explode(",",$data->hidOpciones);
                sort($aro, SORT_NATURAL | SORT_FLAG_CASE);

                foreach ($aro as $k => $v) {
                    
                    $sqlVer = "SELECT COUNT(*) cant FROM ".BD_PREFI."perfs_opts p WHERE p.idoption = ? LIMIT 1;";
                    $respu  = $this->crud->select_id($sqlVer, $v, 'arra');
                    $canti  = $respu['res']['cant'];

                    if( $canti == 0 ){
                        
                        $inf = array(
                            'idrole'    =>  $idrol,
                            'idoption'  =>  $v,
                            'edo_reg'   =>  1,
                            'usu_crea'  =>  $this->seda['idu'],
                            'fec_crea'  =>  date('Y-m-d H:i:s'),
                            'ip_crea'   =>  Firewall::ipCatcher()
                        );

                        $r = $this->crud->insert($inf,BD_PREFI.'perfs_opts');

                    } else {

                        $inf = array(
                            'idrole'    =>  $idrol,
                            'idoption'  =>  $v,
                            'edo_reg'   =>  1,
                            'usu_mod'   =>  $this->seda['idu'],
                            'fec_mod'   =>  date('Y-m-d H:i:s'),
                            'ip_crea'   =>  Firewall::ipCatcher()
                        );

                        $w = array('idrole'=>$idrol,'idoption'=>$v);

                        $r = $this->crud->update($inf,BD_PREFI.'roles',$w);

                    }

                    unset($respu, $canti, $inf, $w, $r);
                    
                }

            }

            if( !empty($data->hidOpcsCrear) ){

                $cre = explode(",",$data->hidOpcsCrear);
                sort($cre, SORT_NATURAL | SORT_FLAG_CASE);
                
                foreach ($cre as $kc => $vc) {
                    
                    $ac = explode("-",$vc);

                    $infc = array(
                        'idrole'    =>  $idrol,
                        'idoption'  =>  $ac[0],
                        'perm_add'  =>  $ac[1],
                        'usu_mod'   =>  $this->seda['idu'],
                        'fec_mod'   =>  date('Y-m-d H:i:s'),
                        'ip_crea'   =>  Firewall::ipCatcher()
                    );

                    $wc = array('idrole'=>$idrol,'idoption'=>$ac[0]);

                    $rc = $this->crud->update($infc,BD_PREFI.'perfs_opts',$wc);

                    unset($infc, $wc, $rc, $ac);

                }

            }

            if( !empty($data->hidOpcsModif) ){

                $mod = explode(",",$data->hidOpcsModif);

                foreach ($mod as $km => $vm) {
                    
                    $am = explode("-",$vm);

                    $infm = array(
                        'idrole'    =>  $idrol,
                        'idoption'  =>  $am[0],
                        'perm_upd'  =>  $am[1],
                        'usu_mod'   =>  $this->seda['idu'],
                        'fec_mod'   =>  date('Y-m-d H:i:s'),
                        'ip_crea'   =>  Firewall::ipCatcher()
                    );

                    $wm = array('idrole'=>$idrol,'idoption'=>$am[0]);

                    $rm = $this->crud->update($infm,BD_PREFI.'perfs_opts',$wm);

                    unset($infm, $wm, $rm, $am);

                }

            }

            if( !empty($data->hidOpcsstatu) ){

                $sta = explode(",",$data->hidOpcsstatu);

                foreach ($sta as $ks => $vs) {
                    
                    $as = explode("-",$vs);

                    $infs = array(
                        'idrole'    =>  $idrol,
                        'idoption'  =>  $as[0],
                        'perm_del'  =>  $as[1],
                        'usu_mod'   =>  $this->seda['idu'],
                        'fec_mod'   =>  date('Y-m-d H:i:s'),
                        'ip_crea'   =>  Firewall::ipCatcher()
                    );

                    $ws = array('idrole'=>$idrol,'idoption'=>$as[0]);

                    $rs = $this->crud->update($infs,BD_PREFI.'perfs_opts',$ws);

                    unset($infs, $ws, $rs, $as);

                }

            }

            if( !empty($data->hidOpcionesN) ){

                $aron = explode(",",$data->hidOpcionesN);
                sort($aron, SORT_NATURAL | SORT_FLAG_CASE);

                foreach ($aron as $kan => $van) {
                    $an = explode("-",$van);
                    $wn = array('idrole'=>$idrol,'idoption'=>$an[0]);
                    $rs = $this->crud->delete(BD_PREFI.'perfs_opts',$wn);
                    unset($wn, $rs, $an);
                }

            }

            if( !empty($data->hidOpcsCrearN) ){

                $cren = explode(",",$data->hidOpcsCrearN);
                sort($cren, SORT_NATURAL | SORT_FLAG_CASE);

                foreach ($cren as $kcn => $vcn) {
                    
                    $acn = explode("-",$vcn);

                    $infcn = array(
                        'perm_add'  =>  0,
                        'usu_mod'   =>  $this->seda['idu'],
                        'fec_mod'   =>  date('Y-m-d H:i:s'),
                        'ip_crea'   =>  Firewall::ipCatcher()
                    );

                    $wcn = array('idrole'=>$idrol,'idoption'=>$acn[0]);

                    $rcn = $this->crud->update($infcn,BD_PREFI.'perfs_opts',$wcn);

                    unset($infcn, $wcn, $rcn, $acn);

                }

            }

            if( !empty($data->hidOpcsModifN) ){

                $modn = explode(",",$data->hidOpcsModifN);

                foreach ($modn as $kmn => $vmn) {
                    
                    $amn = explode("-",$vmn);

                    $infmn = array(
                        'perm_upd'  =>  0,
                        'usu_mod'   =>  $this->seda['idu'],
                        'fec_mod'   =>  date('Y-m-d H:i:s'),
                        'ip_crea'   =>  Firewall::ipCatcher()
                    );

                    $wmn = array('idrole'=>$idrol,'idoption'=>$amn[0]);

                    $rmn = $this->crud->update($infmn,BD_PREFI.'perfs_opts',$wmn);

                    unset($infmn, $wmn, $rmn, $amn);

                }

            }

            if( !empty($data->hidOpcsstatuN) ){

                $stan = explode(",",$data->hidOpcsstatuN);

                foreach ($stan as $ksn => $vsn) {
                    
                    $asn = explode("-",$vsn);

                    $infsn = array(
                        'perm_del'  =>  0,
                        'usu_mod'   =>  $this->seda['idu'],
                        'fec_mod'   =>  date('Y-m-d H:i:s'),
                        'ip_crea'   =>  Firewall::ipCatcher()
                    );

                    $wsn = array('idrole'=>$idrol,'idoption'=>$asn[0]);

                    $rsn = $this->crud->update($infsn,BD_PREFI.'perfs_opts',$wsn);

                    unset($infsn, $wsn, $rsn, $asn);

                }

            }

            if( $resp['rta'] == 'OK' ){
                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';
            } else {
                $cls = 'alert-danger';
                $msg = 'Hubo un error guardando la información: '.$resp['errmsg'].' &nbsp;&nbsp;<i class="fa fa-times" aria-hidden="true"></i>';
            }

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar roles'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/roles/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Lista de opciones
        private function opciones(){

            $sql = "SELECT o.id, o.option
                    FROM ".BD_PREFI."options o
                    WHERE o.father = ?
                        AND o.edo_reg = ?
                    ORDER BY o.order;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>0,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $tb = '';

            foreach ($ar as $k => $v) {
                
                $tb .= '<tr idreg="'.$v['id'].'">';
                    $tb .= '<td><b class="text-primary">'.$v['option'].'</b></td>';
                    $tb .= '<td class="text-center">&nbsp;</td>';
                    $tb .= '<td class="text-center">&nbsp;</td>';
                    $tb .= '<td class="text-center">&nbsp;</td>';
                    $tb .= '<td class="text-center">&nbsp;</td>';
                    /*
                    $tb .= '<td class="text-center"><input class="chk-ver chk-fth" type="checkbox" value="'.$v['id'].'" id="chkVer'.$v['id'].'"></td>';
                    $tb .= '<td class="text-center"><input class="chk-cre" type="checkbox" value="'.$v['id'].'-1" id="chkCre'.$v['id'].'"></td>';
                    $tb .= '<td class="text-center"><input class="chk-mod" type="checkbox" value="'.$v['id'].'-1" id="chkMod'.$v['id'].'"></td>';
                    $tb .= '<td class="text-center"><input class="chk-del" type="checkbox" value="'.$v['id'].'-1" id="chkDel'.$v['id'].'"></td>';
                    */
                $tb .= '</tr>';

                $sqlHi = "SELECT o.id, o.option
                            FROM ".BD_PREFI."options o
                            WHERE o.father = ?
                                AND o.edo_reg = ?
                            ORDER BY o.order;";

                $dph = array();
                array_push($dph, ['kpa'=>1,'val'=>$v['id'],'typ'=>'int']);
                array_push($dph, ['kpa'=>2,'val'=>1,'typ'=>'int']);
                $awh = $this->crud->select_group($sqlHi, count($dph), $dph, 'arra');
                $arh = $awh['res'];

                foreach ($arh as $kh => $vh) {

                    $tb .= '<tr idreg="'.$vh['id'].'">';
                        $tb .= '<td>'.$vh['option'].'</td>';
                        $tb .= '<td class="text-center"><input class="chk-ver chk-son" type="checkbox" fth="'.$v['id'].'" value="'.$vh['id'].'" id="chkVer'.$vh['id'].'"></td>';
                        $tb .= '<td class="text-center"><input class="chk-cre chk-son" type="checkbox" value="'.$vh['id'].'-1" id="chkCre'.$vh['id'].'"></td>';
                        $tb .= '<td class="text-center"><input class="chk-mod chk-son" type="checkbox" value="'.$vh['id'].'-1" id="chkMod'.$vh['id'].'"></td>';
                        $tb .= '<td class="text-center"><input class="chk-del chk-son" type="checkbox" value="'.$vh['id'].'-1" id="chkDel'.$vh['id'].'"></td>';
                    $tb .= '</tr>';

                }

            }

            return $tb;

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

    }

?>