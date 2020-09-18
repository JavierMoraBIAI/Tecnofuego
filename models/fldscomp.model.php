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
                    'typfld'        =>  self::typfld('return',''),                    
                    'usuario'       =>  $this->seda['idu']
                ),
                'file' => 'html/fldscomp/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(int $data){

        	$sql = "SELECT f.id ID, c.name 'TIPO COMPONENTE', f.name CAMPO,
                        CASE f.typeFld  
                            WHEN '1' THEN 'NUMÉRICO'
                            WHEN '2' THEN 'TEXTO'
                        ELSE 'no es un tipo' END 'TIPO CAMPO',
                        CASE f.edo_reg 
                            WHEN '0' THEN 'INACTIVO'
                            WHEN '1' THEN 'ACTIVO'
                        ELSE 'no es un tipo' END 'ESTADO',
                        '<a href=\"editar\" rel=\"fldscomp\" action=\"updsame\" title=\"Editar campo\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>' MODIFICAR
					FROM ".BD_PREFI."fldsComponents f, ".BD_PREFI."components c
					WHERE f.componentId = c.id;";

			$dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,3,4,5);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT f.id, f.name, f.typeFld tipo, f.edo_reg
                    FROM ".BD_PREFI."fldsComponents f
                    WHERE f.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];
            echo json_encode($ar,true);

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array(
                'componentId'   =>  $data->slcTipoComp,
                'name'          =>  $data->txtNombre,
                'typeFld'       =>  $data->slcTipoCamp,
                'edo_reg'       =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'fldsComponents',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'fldsComponents');

            }

            if( $resp['rta'] == 'OK' ){
                $cls = 'alert-success';
                $msg = 'Información guardada correctamente.';
            } else {
                $cls = 'alert-danger';
                $msg = 'Hubo un error guardando la información: '.$resp['errmsg'].'.';
            }

            echo json_encode(array('res'=>$resp['rta'],'cls'=>$cls,'msg'=>$msg));

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

        // Tipos de campo
        private function typfld(string $tyre, string $dfval){

            $ar = array(
                array('id'=>1,'label'=>'NUMÉRICO'),
                array('id'=>2,'label'=>'TEXTO')
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