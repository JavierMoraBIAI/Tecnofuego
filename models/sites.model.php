<?php

    class sites {
        
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
                    'header'	=>  $this->rndr->renderHeader('Gestionar proyectos/minas'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/sites/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT c.name CLIENTE, s.name NOMBRE, s.description DESCRIPCIÓN,
                        CASE s.edo_reg 
                            WHEN '0' THEN 'INACTIVO'
                            WHEN '1' THEN 'ACTIVO'
                        ELSE 'no es un tipo' END 'ESTADO',                      
                        CONCAT('<a idreg=\"',s.id,'\" href=\"editar\" rel=\"sites\" action=\"upd\" title=\"Editar proyecto/mina\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."sites s, ".BD_PREFI."company c
					WHERE s.companyId = c.id ";

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

                        case 'txtNombre':

                            $sql .= " AND s.name LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'txtDescrip':

                            $sql .= " AND s.description LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND s.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,4,5);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar proyectos/minas'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/sites/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT s.id, s.name, s.description, s.edo_reg, s.companyId 
                    FROM ".BD_PREFI."sites s, ".BD_PREFI."company c
                    WHERE s.companyId = c.id
                        AND s.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar proyectos/minas'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>$ar['companyId'],'typ'=>'retu')),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'edosloc'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/sites/editar.html'
            );

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $sqlLocs = "SELECT l.id, l.siteId, l.name area, l.edo_reg, IF(l.edo_reg=1,'ACTIVO','INACTIVO') estado
                        FROM tec_locations l
                        WHERE l.siteId = ?;";

            $dpl = array();
            array_push($dpl, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $awl = $this->crud->select_group($sqlLocs, count($dpl), $dpl, 'arra');
            $detl = '';
            $hidl = '';
            $btnl = '';

            foreach ($awl['res'] as $kl => $vl) {

                $hidl .= '<input type="hidden" id="hidEdoLoc'.$kl.'" name="hidEdoLoc'.$kl.'" value="'.$vl['edo_reg'].'">';
                $hidl .= '<input type="hidden" id="hidIdReg'.$kl.'" name="hidIdReg'.$kl.'" value="'.$vl['id'].'">';

                $btnl .= '<button id="btnEdiV'.$kl.'" type="button" class="btn btn-info btn-sm edi-btn" idfil="'.$kl.'"><i class="fa fa-pencil"></i></button>';
                
                $detl .= '<tr id="fLocs'.$kl.'" idfil="'.$kl.'">';
                    $detl .= '<td id="tdLoca'.$kl.'" width="60%">'.$hidl.$vl['area'].'</td>';
                    $detl .= '<td id="tdEdoLoc'.$kl.'" class="text-center" width="20%">'.$vl['estado'].'</td>';
                    $detl .= '<td class="text-center" width="20%">'.$btnl.'</td>';
                $detl .= '</tr>';

                unset($hidl,$btnl);

            }

            $d['data']['locats'] = $detl;

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array(
                'name'          =>  $data->txtNombre,
                'description'   =>  $data->txtDescrip,
                'companyId'     =>  (empty($data->slcEmpresa)) ? $data->hidCliente : $data->slcEmpresa,
                'edo_reg'       =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'sites',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'sites');

            }

            // Valores las locaciones o áreas
            $dlocs = json_decode($data->hidLocas, true);

            foreach ($dlocs as $kl => $vl) {

                $infl = array(
                    'siteId'    =>  (empty($data->hidId)) ? $resp['lstId'] : $data->hidId,
                    'name'      =>  $vl['loc'],
                    'edo_reg'   =>  $vl['edo']
                );

                if( strlen(trim($vl['ide'])) > 0 ){

                    $infl['usu_mod'] = $this->seda['idu'];
                    $infl['fec_mod'] = date('Y-m-d H:i:s');
                    $infl['ip_mod']  = Firewall::ipCatcher();
    
                    $wl = array('id'=>$vl['ide']);
    
                    $rl = $this->crud->update($infl,BD_PREFI.'locations',$wl);
    
                } else {
    
                    $infl['usu_crea'] = $this->seda['idu'];
                    $infl['fec_crea'] = date('Y-m-d H:i:s');
                    $infl['ip_crea']  = Firewall::ipCatcher();
    
                    $rl = $this->crud->insert($infl,BD_PREFI.'locations');
    
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
                    'header'	=>  $this->rndr->renderHeader('Gestionar proyectos/minas'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/sites/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

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