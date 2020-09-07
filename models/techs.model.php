<?php

    class techs {
        
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
                    'header'    =>  $this->rndr->renderHeader('Gestionar técnicos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'grupos'    =>  self::grupos(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/techs/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT c.name CLIENTE, s.name 'PROYECTO/MINA', t.document CÉDULA, 
                        t.name NOMBRES, g.label GRUPO, IF(t.edo_reg=1,'ACTIVO','INACTIVO') ESTADO,
                        CONCAT('<a idreg=\"',t.id,'\" href=\"editar\" rel=\"techs\" action=\"upd\" title=\"Editar técnico\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."techs t, ".BD_PREFI."sites s, ".BD_PREFI."company c, ".BD_PREFI."valists g
					WHERE t.siteId = s.id
                        AND s.companyId = c.id
                        AND t.idGroup = g.id ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'slcCliente':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidCliente':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcProyecto':

                            $sql .= " AND t.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidProyecto':

                            $sql .= " AND t.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'txtCedula':

                            $sql .= " AND t.document LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'txtNombres':

                            $sql .= " AND t.name LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'slcGrupo':

                            $sql .= " AND t.idGroup = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND c.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,3,5,6,7);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar técnicos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'grupos'    =>  self::grupos(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/techs/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT t.id, s.companyId, t.siteId, t.document, t.name, t.idGroup, t.edo_reg
                    FROM ".BD_PREFI."techs t, ".BD_PREFI."sites s, ".BD_PREFI."valists g
                    WHERE t.siteId = s.id
                        AND t.idGroup = g.id
                        AND t.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestionar técnicos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>$ar['companyId'],'typ'=>'retu')),
                    'grupos'    =>  self::grupos(array('def'=>$ar['idGroup'],'typ'=>'retu')),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/techs/editar.html'
            );

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array(
                'siteId'    =>  (empty($data->slcProyecto)) ? $data->hidProyecto : $data->slcProyecto,
                'idGroup'   =>  $data->slcGrupo,
                'document'  =>  $data->txtCedula,
                'name'      =>  $data->txtNombres,
                'edo_reg'   =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'techs',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'techs');

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
                    'header'    =>  $this->rndr->renderHeader('Gestionar técnicos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/techs/respsave.html'
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

        // Listado de grupos
        private function grupos(array $conf){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v
                    WHERE v.idlist = ?
                        AND v.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>9,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE GRUPO', $conf['def']);

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