<?php

    class usuarios {
        
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
                    'header'	=>  $this->rndr->renderHeader('Gestión de usuarios'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'perfiles'	=>	self::perfiles('return',''),
                    'estados'	=>	self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/usuarios/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar usuarios 
        public function listar(array $data){

        	$sql = "SELECT CONCAT(u.names, ' ', u.lastname) NOMBRE, u.idenum CÉDULA, r.role ROL, s.name 'SITIO/PROYECO',
                        IF(u.edo_reg = 1,'ACTIVO','INACTIVO') ESTADO,
                        CONCAT('<a idreg=\"',u.id,'\" href=\"editar\" rel=\"usuarios\" action=\"upd\" title=\"Editar usuario\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."userspers u, ".BD_PREFI."sites s, ".BD_PREFI."roles r
					WHERE u.siteId = s.id
                        AND u.roleId = r.id ";

            $dp = array();
            
            if( $_SESSION['u']['ico'] > 1 && $_SESSION['u']['idp'] > 1 ){
                $sql .= " AND s.companyId = ? ";
                array_push($dp, ['kpa'=>1,'val'=>$_SESSION['u']['ico'],'typ'=>'int']);
                $sql .= " AND u.siteId = ? ";
                array_push($dp, ['kpa'=>2,'val'=>$_SESSION['u']['isi'],'typ'=>'int']);
                $im = 3;
            } else {
                $im = 1;
            }

            foreach ($data as $k => $v) {

            	if( !empty($v) || strlen($v) > 0 ){

            		switch($k) {
            			
            			case 'txtNombres':

                            $sql .= " AND u.names LIKE ? ";
            				array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
        					
        				break;

        				case 'txtApellidos':

                            $sql .= " AND u.lastname LIKE ? ";
            				array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
        					
        				break;

        				case 'txtDocumento':

                            $sql .= " AND u.idenum = ? ";
            				array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
        					
        				break;

        				case 'slcPerfil':

                            $sql .= " AND u.roleId = ? ";
            				array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
        					
        				break;

        				case 'slcEstado':

                            $sql .= " AND u.edo_reg = ? ";
            				array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
        					
        				break;

            		}

            		$im++;

            	}

            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(3,4,5,6,7);
            $lnk = URL_BASE.'usuarios/toexcel';
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

        	$d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestión de usuarios'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'perfiles'  =>  self::perfiles('return',''),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'cargos'    =>  self::cargos(array('def'=>'','typ'=>'retu')),                    
                    'estados'	=>	self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/usuarios/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT u.id, u.positionId, u.roleId, s.companyId, u.siteId, LOWER(SUBSTRING(u.user from 3)) user, 
                        u.idenum, u.names, u.lastname, u.email, u.edo_reg, u.foto
                    FROM ".BD_PREFI."userspers u, ".BD_PREFI."sites s
                    WHERE u.siteId = s.id
                        AND u.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Gestión de usuarios'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'perfiles'  =>  self::perfiles('return',$ar['roleId']),
                    'empresas'  =>  self::empresas(array('def'=>$ar['companyId'],'typ'=>'retu')),
                    'cargos'    =>  self::cargos(array('def'=>$ar['positionId'],'typ'=>'retu')),  
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/usuarios/editar.html'
            );

            $lnkfoto = '<br><br><a href="'.URL_BASE.'img/pics/'.$ar['foto'].'" target="_blank" class="file-view">Ver foto &nbsp;&nbsp;<i class="fa fa-eye"></i></a>';
            $d['data']['fotofile'] = (!empty($ar['foto'])) ? $lnkfoto : '';

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $foto = '';
            if( !empty($_FILES['txtFoto']) ){
                $foto = $this->fima->uploadf($_FILES['txtFoto'], $data->hidFoto, 'img/pics/');
            } else {
                if( !empty($data->hidFoto) ){
                    $foto = $data->hidFoto;
                }
            }

            $info = array(
                'positionId'    =>  $data->slcCargo,
                'roleId'        =>  $data->slcRol,
                'siteId'        =>  (empty($data->slcProyecto)) ? $data->hidProyecto : $data->slcProyecto,
                'dolog'         =>  'S',
                'user'          =>  'AW'.strtoupper($data->txtUsuario),
                'idenum'        =>  $data->txtDocumento,
                'names'         =>  $data->txtNombres,
                'lastname'      =>  $data->txtApellidos,
                'email'         =>  $data->txtEmail,
                'foto'          =>  $foto,
                'edo_reg'       =>  $data->slcEstado
            );

            if( !empty($data->txtPassword) ){
                $info['pass'] = Firewall::pwd_hash($data->txtPassword);
            }

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'userspers',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'userspers');

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
                    'header'    =>  $this->rndr->renderHeader('Gestión de usuarios'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/usuarios/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listado de perfiles
        private function perfiles(string $tyre, string $dfval){

        	$sql = "SELECT r.id, r.role label
					FROM ".BD_PREFI."roles r
					WHERE r.edo_reg = ?
					ORDER BY 2;";

			$dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE ROL', $dfval);

            if( $tyre == 'echo' ){
            	echo $sl;
            } else {
            	return $sl;
            }

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

        // Listado de cargos
        private function cargos(array $conf){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v
                    WHERE v.idlist = ?
                        AND v.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE CARGO', $conf['def']);

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
        		array('id'=>0,'label'=>'RETIRADO')
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