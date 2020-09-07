<?php

    class parts {
        
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
                    'header'    =>  $this->rndr->renderHeader('Gestionar partes y servicios'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'familias'  =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categs'    =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍA','def'=>'')),
                    'estados'   =>  self::estados('return',''),
                    'partype'   =>  self::partype('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/parts/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT p.description DESCRIPCIÓN, c.label CATEGORÍA, f.label FAMILIA, 
                        p.partNum 'P/N', CONCAT('$ ',p.value) 'V/U (USD)', IF(p.edo_reg=1,'ACTIVO','INACTIVO') ESTADO,                      
                        CONCAT('<a idreg=\"',p.id,'\" href=\"editar\" rel=\"parts\" action=\"upd\" title=\"Editar parte o servicio\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."parts p, ".BD_PREFI."valists c, ".BD_PREFI."valists f
					WHERE p.idCategory = c.id
                        AND p.idFamily = f.id ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'txtPartNum':

                            $sql .= " AND p.partNum LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'txtDescripcion':

                            $sql .= " AND p.description LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'slcFamilia':

                            $sql .= " AND p.idFamily = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcCategoria':

                            $sql .= " AND p.idCategory = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcTipo':

                            $sql .= " AND p.partserv = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND p.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,3,4,5,6,7);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar partes y servicios'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'familias'  =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categs'    =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍA','def'=>'')),
                    'campos'    =>  self::lists(array('idlst'=>6,'str'=>'SELECCIONE CAMPO','def'=>'')),
                    'tipos'     =>  self::lists(array('idlst'=>7,'str'=>'SELECCIONE TIPOS CAMPO','def'=>'')),
                    'clientes'  =>  self::clientes(array('def'=>'')),
                    'estados'   =>  self::estados('return',''),
                    'partype'   =>  self::partype('return',''),              
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/parts/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT p.id, p.idCategory, p.idFamily, p.partNum, p.value, p.edo_reg, p.description, p.partserv
                    FROM ".BD_PREFI."parts p
                    WHERE p.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar partes y servicios'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'familias'  =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>$ar['idFamily'])),
                    'categs'    =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍA','def'=>$ar['idCategory'])),
                    'campos'    =>  self::lists(array('idlst'=>6,'str'=>'SELECCIONE CAMPO','def'=>'')),
                    'tipos'     =>  self::lists(array('idlst'=>7,'str'=>'SELECCIONE TIPOS CAMPO','def'=>'')),
                    'clientes'  =>  self::clientes(array('def'=>'')),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'partype'   =>  self::partype('return',$ar['partserv']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/parts/editar.html'
            );

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $sqlVals = "SELECT v.id, v.idPart, s.companyId, c.name company, v.idProject, s.name proyecto, v.value
                        FROM tec_parts_vals v, tec_sites s, tec_company c
                        WHERE v.idProject = s.id
                            AND s.companyId = c.id
                            AND v.idPart = ?;";

            $dpv = array();
            array_push($dpv, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $awv = $this->crud->select_group($sqlVals, count($dpv), $dpv, 'arra');
            $detv = '';
            $hidv = '';
            $btnv = '';

            foreach ($awv['res'] as $kv => $vv) {

                $hidv .= '<input type="hidden" id="hidIdCli'.$kv.'" name="hidIdCli'.$kv.'" value="'.$vv['companyId'].'">';
                $hidv .= '<input type="hidden" id="hidIdPry'.$kv.'" name="hidIdPry'.$kv.'" value="'.$vv['idProject'].'">';
                $hidv .= '<input type="hidden" id="hidIdVal'.$kv.'" name="hidIdVal'.$kv.'" value="'.$vv['value'].'">';
                $hidv .= '<input type="hidden" id="hidIdReg'.$kv.'" name="hidIdReg'.$kv.'" value="'.$vv['id'].'">';

                $btnv .= '<button id="btnEdiV'.$kv.'" type="button" class="btn btn-info btn-sm edi-btn" idfil="'.$kv.'"><i class="fa fa-pencil"></i></button>';
                
                $detv .= '<tr id="fVals'.$kv.'" idfil="'.$kv.'">';
                    $detv .= '<td id="tdValCli'.$kv.'" width="30%">'.$hidv.$vv['company'].'</td>';
                    $detv .= '<td id="tdValPry'.$kv.'" width="30%">'.$vv['proyecto'].'</td>';
                    $detv .= '<td id="tdValVal'.$kv.'" class="text-center" width="20%">$ '.$vv['value'].'</td>';
                    $detv .= '<td class="text-center" width="20%">'.$btnv.'</td>';
                $detv .= '</tr>';

                unset($hidv,$btnv);

            }

            $d['data']['valspr'] = $detv;

            $sqlFlds = "SELECT f.id, f.idPart, f.idField, fi.label campo, f.idType, t.label tipo, f.edo_reg
                        FROM tec_parts_flds f, tec_valists fi, tec_valists t
                        WHERE f.idField = fi.id
                            AND f.idType = t.id
                            AND f.idPart = ?;";

            $dpf = array();
            array_push($dpf, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $awf = $this->crud->select_group($sqlFlds, count($dpf), $dpf, 'arra');
            $detf = '';
            $hidf = '';
            $btnf = '';

            foreach ($awf['res'] as $kf => $vf) {

                $hidf .= '<input type="hidden" id="hidIdCmp'.$kf.'" name="hidIdCmp'.$kf.'" value="'.$vf['idField'].'">';
                $hidf .= '<input type="hidden" id="hidIdTip'.$kf.'" name="hidIdTip'.$kf.'" value="'.$vf['idType'].'">';
                $hidf .= '<input type="hidden" id="hidRegFld'.$kf.'" name="hidRegFld'.$kf.'" value="'.$vf['id'].'">';

                $btnf .= '<button id="btnEdiC'.$kf.'" type="button" class="btn btn-info btn-sm edi-btn-cmp" idfilC="'.$kf.'"><i class="fa fa-pencil"></i></button>';
                
                $detf .= '<tr id="fCamps'.$kf.'" idfilC="'.$kf.'">';
                    $detf .= '<td id="tdValCmp'.$kf.'" width="50%">'.$hidf.$vf['campo'].'</td>';
                    $detf .= '<td id="tdValTip'.$kf.'" width="30%">'.$vf['tipo'].'</td>';
                    $detf .= '<td class="text-center" width="20%">'.$btnf.'</td>';
                $detf .= '</tr>';

                unset($hidf,$btnf);

            }

            $d['data']['fieldsd'] = $detf;

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array(
                'idFamily'      =>  $data->slcFamilias,
                'idCategory'    =>  $data->slcCategorias,
                'partserv'      =>  $data->slcTipo,
                'partNum'       =>  $data->txtProduNam,
                'value'         =>  $data->txtValorUnitario,
                'description'   =>  $data->txtDescripcion,
                'edo_reg'       =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'parts',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'parts');

            }

            // Valores de los campos de control
            $dcamp = json_decode($data->hidCamps, true);

            foreach ($dcamp as $kc => $vc) {

                $infc = array(
                    'idPart'    =>  (empty($data->hidId)) ? $resp['lstId'] : $data->hidId,
                    'idField'   =>  $vc['cmp'],
                    'idType'    =>  $vc['tip'],
                    'edo_reg'   =>  1
                );

                if( strlen(trim($vc['ide'])) > 0 ){

                    $infc['usu_mod'] = $this->seda['idu'];
                    $infc['fec_mod'] = date('Y-m-d H:i:s');
                    $infc['ip_mod']  = Firewall::ipCatcher();
    
                    $wc = array('id'=>$vc['ide']);
    
                    $rc = $this->crud->update($infc,BD_PREFI.'parts_flds',$wc);
    
                } else {
    
                    $infc['usu_crea'] = $this->seda['idu'];
                    $infc['fec_crea'] = date('Y-m-d H:i:s');
                    $infc['ip_crea']  = Firewall::ipCatcher();
    
                    $rc = $this->crud->insert($infc,BD_PREFI.'parts_flds');
    
                }
                
            }

            // Valores de partes o servicios por proyecto
            $dvals = json_decode($data->hidVals, true);

            foreach ($dvals as $kv => $vv) {

                $infv = array(
                    'idPart'    =>  (empty($data->hidId)) ? $resp['lstId'] : $data->hidId,
                    'idProject' =>  $vv['pry'],
                    'value'     =>  $vv['val'],
                    'edo_reg'   =>  1
                );

                if( strlen(trim($vv['ide'])) > 0 ){

                    $infv['usu_mod'] = $this->seda['idu'];
                    $infv['fec_mod'] = date('Y-m-d H:i:s');
                    $infv['ip_mod']  = Firewall::ipCatcher();
    
                    $wv = array('id'=>$vv['ide']);
    
                    $rv = $this->crud->update($infv,BD_PREFI.'parts_vals',$wv);
    
                } else {
    
                    $infv['usu_crea'] = $this->seda['idu'];
                    $infv['fec_crea'] = date('Y-m-d H:i:s');
                    $infv['ip_crea']  = Firewall::ipCatcher();
    
                    $rv = $this->crud->insert($infv,BD_PREFI.'parts_vals');
    
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
                    'header'    =>  $this->rndr->renderHeader('Gestionar partes y servicios'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/parts/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

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

        // Lista de clientes
        private function clientes(array $data){

            $sql = "SELECT c.id, c.name label
                    FROM ".BD_PREFI."company c
                    WHERE c.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            
            return $this->rndr->renderSelect($aw['res'], 'SELECCIONAR CLIENTE', $data['def']);

        }

        // Listado de proyectos por cliente
        public function proyectos(array $conf){

            $sql = "SELECT s.id, s.name label
                    FROM ".BD_PREFI."sites s
                    WHERE s.edo_reg = ?
                        AND s.companyId = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$conf['com'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $sl = $this->rndr->renderSelect($aw['res'], 'SELECCIONE ROYECTO/MINA', $conf['def']);

            if( $conf['typ'] == 'echo' ){ echo $sl; } else { return $sl; }

        }

        // Listado de tipos de parte
        private function partype(string $tyre, string $dfval){

            $ar = array(
                array('id'=>'S','label'=>'Servicio'),
                array('id'=>'P','label'=>'Parte'),
                array('id'=>'R','label'=>'Repuesto')
            );

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE TIPO', $dfval);

            if( $tyre == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

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