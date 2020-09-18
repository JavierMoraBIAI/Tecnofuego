<?php

    class fldscomp {
        
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
                    'header'	    =>  $this->rndr->renderHeader('Gestionar campos por componentes'),
                    'footer'	    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'componentes'   =>  self::componentes(array('def'=>'','typ'=>'retu')),
                    'estados'       =>  self::estados('return',''),
                    'usuario'       =>  $this->seda['idu']
                ),
                'file' => 'html/fldscomp/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT c.idcateg ID, c.categoria CATEGORÍA,
                        CASE c.edo_reg 
                            WHEN '0' THEN 'INACTIVO'
                            WHEN '1' THEN 'ACTIVO'
                        ELSE 'no es un tipo' END 'ESTADO',                      
                        '<a href=\"editar\" rel=\"fldscomp\" action=\"upd\" title=\"Editar categoría\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>' MODIFICAR
					FROM ".BD_PREFI."categenlaces c
					WHERE c.idcateg > 0 ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'slcEstado':

                            $sql .= " AND c.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    	case 'txtNombre':

                            $sql .= " AND c.categoria LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,2,3,4,5);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar campos por componentes'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/fldscomp/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT c.idcateg id, c.categoria, c.edo_reg
                    FROM ".BD_PREFI."categenlaces c
                    WHERE c.idcateg = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar campos por componentes'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/fldscomp/editar.html'
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
                'categoria' =>  $data->txtNombre,
                'edo_reg'   =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('idcateg'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'categenlaces',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'categenlaces');

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
                    'header'	=>  $this->rndr->renderHeader('Gestionar campos por componentes'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/fldscomp/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Tipos de componente 
        private function componentes(array $conf){

            $sql = "SELECT c.id, c.name label
                    FROM ".BD_PREFI."components c
                    WHERE c.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE TIPO', $conf['def']);

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