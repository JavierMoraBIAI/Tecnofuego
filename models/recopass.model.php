<?php

    class recopass {
        
        public function __construct(array $res){
        	$this->clstr = $res['cleanstr'];
        	$this->crud = $res['crud'];
        	$this->rndr = $res['render'];
        	$this->mail = $res['mailengine'];
        	$this->fima = $res['fileman'];
        }

        // Solicitar password
        public function solpass(string $data){

            $sql = "SELECT COUNT(*) cant
                    FROM tec_userspers u
                    WHERE u.email = ?
                    LIMIT 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'string']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];
            
            if( $ar['cant'] == 1 ){

                $pass = self::sendMailReco($data);

            }

            echo $ar['cant'];

        }

        // Enviar correo con los datos de recuperación de clave
        private function sendMailReco(string $data){
            
            $sql = "SELECT u.id, CONCAT(u.names, ' ', u.lastname) name, LOWER(SUBSTRING(u.user, 3, LENGTH(u.user))) user
                    FROM tec_userspers u
                    WHERE u.email = ?
                    LIMIT 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'string']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            $npassw = $this->fima->getUniqueCode(8, mktime());
            $crypas = Firewall::pwd_hash($npassw);

            // Ingreso de registro al log de cambio de contraseñas
            $inf = array(
                'iduser'    =>  $ar['id'],
                'idchng'    =>  $ar['id'],
                'datsol'    =>  date('Y-m-d H:i:s'),
                'datchng'   =>  date('Y-m-d H:i:s'),
                'edo_reg'   =>  1,
                'usu_crea'  =>  $ar['id'],
                'fec_crea'  =>  date('Y-m-d H:i:s'),
                'ip_crea'   =>  Firewall::ipCatcher()
            );

            $rsp  = $this->crud->insert($inf,BD_PREFI.'pswdchng');

            $this->mail->setFrom(USR_MAIL, SITE_NAM);
            $this->mail->addDestino($data);
            $asunto = 'Solicitud de restauración de contraseña - '.SITE_NAM;
            $asunto = "=?UTF-8?B?".base64_encode($asunto)."=?=";
            $this->mail->addAsunto($asunto);

            $body = 'Cordial saludo,
            		 <br>
            		 <p><b>'.$ar['name'].'</b></p>'.
                     '<p>Usted ha solicitado la restauración de la contraseña. Haciendo click en el link a continuación podrá realizar el cambio de contraseña utilizando la que se generó automáticamente <b>'.$npassw.'</b> 
                     Esta deberá ser ingresada en el primer campo de texto, seguida de la nueva contraseña y su verificación. Una vez validada la contraseña generada y la contraseña nueva podrá iniciar sesión nuevamente al programa <b>SIRAC</b>.</p>
                     <p>Para ingresar a su sesión en otra ocasión, recuerde que su usuario es <b>'.$ar['user'].'</b></p>
                     <p><a href="'.URL_BASE.'recopass/chng/'.$ar['id'].'-'.$rsp['lstId'].'" target="_blank">Restaurar contraseña</a></p>
            		 <br><br>
            		 <p>Atentamente,</p>
            		 <br><br>
            		 El equipo de <b>'.EMP_NAME.'</b>';

            $this->mail->bodyHtml($body);
            $this->mail->sendMail();
            $this->mail->cleanAddrs();

            $info = array(
                'pass'      =>  $crypas,
                'usu_mod'   =>  $ar['id'],
                'fec_mod'   =>  date('Y-m-d H:i:s'),
                'ip_mod'    =>  Firewall::ipCatcher()
            );

            $where = array('id'=>$ar['id']);
            $resp  = $this->crud->update($info,BD_PREFI.'userspers',$where);

            return $npassw;

        }

        // Formulario de modificar contraseña
        public function chng(string $data){

            $inf = explode("-", $data);

            $info = array(
                'datchng'   =>  date('Y-m-d H:i:s'),
                'usu_mod'   =>  $inf[0],
                'fec_mod'   =>  date('Y-m-d H:i:s'),
                'ip_mod'    =>  Firewall::ipCatcher()
            );

            $where = array('id'=>$inf[1]);
            $resp  = $this->crud->update($info,BD_PREFI.'pswdchng',$where);

            $d = array(
                'data' => array(
                    'title'	    =>  'Cambiar contraseña - '.EMP_NAME,
                    'appna'     =>  APP_NAME,
                    'urlbase'   =>  URL_BASE,
                    'usuario'   =>  $inf[0]
                ),
                'file' => 'html/changepass.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Verificar password generado
        public function verifpass(array $data){

            $sql = "SELECT u.pass
                    FROM ".BD_PREFI."userspers u
                    WHERE u.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data['idu'], 'arra');
            $pw = $re['res']['pass'];

            if( Firewall::pwd_verf(trim($data['pwd']), $pw) ) {
                echo 0;
            } else {
                echo 1;
            }

        }

        // Iniciar sesión luego de cambio de contraseña
        public function psschng(array $data){

            //extract($_POST);

            $info = array(
                'pass'      =>  Firewall::pwd_hash($data['txtPswdN2']),
                'usu_mod'   =>  $data['hidId'],
                'fec_mod'   =>  date('Y-m-d H:i:s'),
                'ip_mod'    =>  Firewall::ipCatcher()
            );

            $where = array('id'=>$data['hidId']);
            $resp  = $this->crud->update($info,BD_PREFI.'userspers',$where);

            if( $resp['rta'] == 'OK' ) {

                $sql = "SELECT u.id, u.positionId, v.label position, u.roleId, r.role, s.companyId, c.name company, 
                               s.name proyecto, u.siteId, u.pass, u.idenum, CONCAT(u.names, ' ', u.lastname) nombre, u.foto
                        FROM tec_userspers u, tec_sites s, tec_roles r, tec_valists v, tec_company c
                        WHERE u.siteId = s.id
                            AND u.roleId = r.id
                            AND u.positionId = v.id
                            AND s.companyId = c.id
                            AND u.id = ?
                        LIMIT 1;";

                $dpa = array();
                array_push($dpa, ['kpa'=>1,'val'=>$data['hidId'],'typ'=>'int']);
                $resu = $this->crud->select_group($sql, count($dpa), $dpa, 'arra');
                $arrd = $resu['res'][0];

                if( Firewall::pwd_verf(trim($data['txtPswdN2']), $arrd['pass']) ) {
                    $_SESSION['u']['uAuth'] = true;
                    $_SESSION['u']['idu'] = $arrd['id'];
                    $_SESSION['u']['nom'] = ucwords(mb_strtolower($arrd['nombre'],'UTF-8'));
                    $_SESSION['u']['idp'] = $arrd['roleId'];
                    $_SESSION['u']['ico'] = $arrd['companyId'];
                    $_SESSION['u']['com'] = $arrd['company'];
                    $_SESSION['u']['isi'] = $arrd['siteId'];
                    $_SESSION['u']['ipo'] = $arrd['positionId'];
                    $_SESSION['u']['fot'] = $arrd['foto'];
                    $_SESSION['u']['per'] = ucwords(mb_strtolower($arrd['role'],'UTF-8'));
                    $_SESSION['u']['pos'] = ucwords(mb_strtolower($arrd['position'],'UTF-8'));
                    $_SESSION['u']['pry'] = ucwords(mb_strtolower($arrd['proyecto'],'UTF-8'));
                    echo json_encode(array('edo'=>0,'pag'=>URL_BASE),true);
                } else {
                    $_SESSION['u']['uAuth'] = false;
                    $_SESSION['u']['mesg'] = 1; // Usuario y contraseña erróneos
                }

            }

        }

    }

?>