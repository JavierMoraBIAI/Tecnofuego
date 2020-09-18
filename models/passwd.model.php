<?php

    class passwd {
        
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
                    'header'	=>  $this->rndr->renderHeader('Cambiar contraseña'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/passwd/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Verificar contraseña anterior
        public function verifpass(string $pwd){

            $sql = "SELECT u.pass
                    FROM ".BD_PREFI."userspers u
                    WHERE u.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $this->seda['idu'], 'arra');
            $pw = $re['res']['pass'];

            if( Firewall::pwd_verf(trim($pwd), $pw) ) {
                echo 0;
            } else {
                echo 1;
            }

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array('pass'=>Firewall::pwd_hash($data->txtPswdN2));
            $where = array('id'=>$this->seda['idu']);
            $resp = $this->crud->update($info,BD_PREFI.'userspers',$where);

            if( $resp['rta'] == 'OK' ){
                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';
            } else {
                $cls = 'alert-danger';
                $msg = 'Hubo un error guardando la información: '.$resp['errmsg'].' &nbsp;&nbsp;<i class="fa fa-times" aria-hidden="true"></i>';
            }

            $d = array(
                'data' => array(
                    'header'    =>  $this->rndr->renderHeader('Cambiar contraseña'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'urlhome'   =>  URL_BASE,
                    'logout'    =>  URL_BASE.'inicio/logout',
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/passwd/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

    }

?>