<?php

    class positions {
        
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
                    'header'	=>  $this->rndr->renderHeader('Gestionar cargos'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/positions/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT p.id ID, c.name EMPRESA, p.name CARGO,
                        CASE p.edo_reg 
                            WHEN '0' THEN 'INACTIVO'
                            WHEN '1' THEN 'ACTIVO'
                        ELSE 'no es un tipo' END 'ESTADO',                      
                        '<a href=\"editar\" rel=\"positions\" action=\"upd\" title=\"Editar cargo\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>' MODIFICAR
					FROM ".BD_PREFI."positions p, ".BD_PREFI."company c
                    WHERE p.companyId = c.id
                        AND p.id > 0 ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'slcEmpresa':

                            $sql .= " AND p.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'txtNombre':

                            $sql .= " AND p.name LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND p.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $sql .= ';';

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,3,4);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar cargos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/positions/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT p.id, p.name, p.companyId, p.edo_reg 
                    FROM ".BD_PREFI."positions p, ".BD_PREFI."company c
                    WHERE p.companyId = c.id
                        AND p.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar cargos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'empresas'  =>  self::empresas(array('def'=>$ar['companyId'],'typ'=>'retu')),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/positions/editar.html'
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
                'name'          =>  $data->txtNombre,
                'companyId '    =>  $data->slcEmpresa,
                'edo_reg'       =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'positions',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'positions');

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
                    'header'	=>  $this->rndr->renderHeader('Gestionar cargos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/positions/respsave.html'
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
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE EMPRESA', $conf['def']);

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