<?php

    class inicio {
        
        public function __construct(array $res){
        	$this->clstr = $res['cleanstr'];
        	$this->crud = $res['crud'];
        	$this->rndr = $res['render'];
        }

        // Método inicial
        public function index(){

        	if($_SESSION['u']['uAuth']){
                $title = ' - Inicio';
                $page = 'html/index.html';
                $logout = URL_BASE.'inicio/logout';
                $menu = self::makeMenu();
                $usrname = $_SESSION['u']['nom'];
                $pername = $_SESSION['u']['per'];
                $company = $_SESSION['u']['com'];
                $positio = $_SESSION['u']['pos'];
                $proyecto = $_SESSION['u']['pry'];
                $ideperf = $_SESSION['u']['idp'];
                $idecomp = $_SESSION['u']['ico'];
                $ideproy = $_SESSION['u']['isi'];
                $footer = $this->rndr->renderFooter(EMP_NAME,YEARCOPY);
                $lnkfoto = '<img src="'.URL_BASE.'img/pics/'.$_SESSION['u']['fot'].'" class="img-fluid ava-thepic rounded-circle">';
                $foto = (!empty($_SESSION['u']['fot'])) ? $lnkfoto : '<img src="img/avatar-1.jpg" class="img-fluid rounded-circle">';
                $lactiv = self::listactiv();
                $cntmmto = self::cantactiv(2);
                $cntinsp = self::cantactiv(1);
                $cntreca = self::cantactiv(3);
                $cntrein = self::cantactiv(4);
                $cntemer = self::cantactiv(5);
            } else {
                $title = ' - Login';
                $page = 'html/login.html';
                $logout = '';
                $menu = '';
                $usrname = '';
                $pername = '';
                $positio = '';
                $company = '';
                $proyecto = '';
                $ideperf = '';
                $idecomp = '';
                $ideproy = '';
                $footer = '';
                $foto = '';
                $lactiv = '';
                $cntmmto = '';
                $cntinsp = '';
                $cntreca = '';
                $cntrein = '';
                $cntemer = '';
            }

            $d = array(
                'data' => array(
                    'title'     =>  SITE_NAM.$title,
                    'appna'     =>  APP_NAME,
                    'logout'    =>  $logout,
                    'menu'      =>  $menu,
                    'usrname'   =>  $usrname,
                    'pername'   =>  $pername,
                    'positio'   =>  $positio,
                    'company'   =>  $company,
                    'proyecto'  =>  $proyecto,
                    'ideperf'   =>  $ideperf,
                    'idecomp'   =>  $idecomp,
                    'ideproy'   =>  $ideproy,
                    'foto'      =>  $foto,
                    'footer'    =>  $footer,
                    'lactiv'    =>  $lactiv,
                    'cntmmto'   =>  $cntmmto,
                    'cntinsp'   =>  $cntinsp,
                    'cntreca'   =>  $cntreca,
                    'cntrein'   =>  $cntrein,
                    'cntemer'   =>  $cntemer,
                    'time'      =>  time(),
                    'urlbase'   =>  URL_BASE
                ),
                'file' => $page
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Método login
        public function logos($data = array()){

            extract($_POST);

            if( ( $txtUserName != '' && !empty($txtUserName) ) && ( $txtPassField != '' && !empty($txtPassField) ) ){

                $sql = "SELECT u.id, u.positionId, v.label position, u.roleId, r.role, s.companyId, c.name company, 
                            s.name proyecto, u.siteId, u.pass, u.idenum, CONCAT(u.names, ' ', u.lastname) nombre, u.foto
                        FROM tec_userspers u, tec_sites s, tec_roles r, tec_valists v, tec_company c
                        WHERE u.siteId = s.id
                            AND u.roleId = r.id
                            AND u.positionId = v.id
                            AND s.companyId = c.id
                            AND u.edo_reg = 1
                            AND u.dolog = 'S'
                            AND u.user = ?
                        LIMIT 1;";

                $dpa = array();
                array_push($dpa, ['kpa'=>1,'val'=>'AW'.trim($txtUserName),'typ'=>'string']);
                $resu = $this->crud->select_group($sql, count($dpa), $dpa, 'arra');
                $arrd = $resu['res'][0];

                if( !empty($arrd) ){
                    if( Firewall::pwd_verf(trim($txtPassField), $arrd['pass']) ) {
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
                    } else {
                        $_SESSION['u']['uAuth'] = false;
                        $_SESSION['u']['mesg'] = 1; // Usuario y contraseña erróneos
                    }
                } else {
                    $_SESSION['u']['uAuth'] = false;
                    $_SESSION['u']['mesg'] = 1; // Usuario y contraseña erróneos
                }

            } else {
                $_SESSION['u']['uAuth'] = false;
                $_SESSION['u']['mesg'] = 2; // Usuario y contraseña sin digitar
            }

            header('Location: '.URL_BASE);
            exit;

        }

        // Cerrar sesión
        public function logout(){

            Firewall::sessionKill();
            session_start();
            $_SESSION['u']['uAuth'] = false;

            // Sesión cerrada correctamente
            $_SESSION['errSess'] = 2;

            header('Location: '.URL_BASE);
            exit;

        }

        // Generar menú
        private function makeMenu(){

            $mnu = '<ul class="list-unstyled">';

            if( $_SESSION['u']['idp'] == 1 || $_SESSION['u']['idp'] == 2 ){ // Si es superusuario

                $sqlFth = "SELECT o.id, o.option, o.file, o.order, o.icon,
                                (SELECT count(*)
                                    FROM ".BD_PREFI."options o1
                                    WHERE o1.father = o.id
                                ) hijos
                            FROM ".BD_PREFI."options o
                            WHERE o.father = ?
                                AND o.edo_reg = ?
                            ORDER BY 4;";

                $dpa = array();
                array_push($dpa, ['kpa'=>1,'val'=>0,'typ'=>'int']);
                array_push($dpa, ['kpa'=>2,'val'=>1,'typ'=>'int']);
                $aws = $this->crud->select_group($sqlFth, count($dpa), $dpa, 'arra');
                $ard = $aws['res'];

                $mnu .= '<li><a href="'.URL_BASE.'"><i class="fa fa-tachometer"></i>Dashboard </a></li>';

                foreach($ard as $k => $v) {

                    if( $v['hijos'] == 0 ){

                        if( $v['file'] == 'none' ){
                            $cls = 'no-link';
                            $lnk = '#';
                        } else {
                            $cls = 'launch-mod';
                            $lnk = $v['file'];
                        }
                        
                        $mnu .= '<li><a href="'.$lnk.'" class="'.$cls.'"><i class="fa '.$v['icon'].'"></i>'.$v['option'].' </a></li>';

                    } else {

                        $mnu .= '<li><a href="#drop-menu-'.$v['id'].'" aria-expanded="false" data-toggle="collapse"> <i class="fa '.$v['icon'].'"></i>'.$v['option'].' </a>';

                        $dpah = array();
                        array_push($dpah, ['kpa'=>1,'val'=>$v['id'],'typ'=>'int']);
                        array_push($dpah, ['kpa'=>2,'val'=>1,'typ'=>'int']);
                        $awsh = $this->crud->select_group($sqlFth, count($dpah), $dpah, 'arra');
                        $ardh = $awsh['res'];

                        $mnu .= '<ul id="drop-menu-'.$v['id'].'" class="collapse list-unstyled ">';

                        foreach($ardh as $kh => $vh) {

                            if( $vh['file'] == 'none' ){
                                $clsh = 'no-link';
                                $lnkh = '#';
                            } else {
                                $clsh = 'launch-mod';
                                $lnkh = $vh['file'];
                            }

                            $mnu .= '<li><a href="'.$lnkh.'" class="'.$clsh.'"><i class="fa '.$vh['icon'].'"></i>'.$vh['option'].' </a></li>';

                        }

                        $mnu .= '</ul>';
                        $mnu .= '</li>';

                    }

                }

            } else {

                $sqlFth = "SELECT o.id, o.option, o.file, o.order, o.icon,
                                (SELECT count(*)
                                    FROM ".BD_PREFI."options o1
                                    WHERE o1.father = o.id
                                ) hijos
                            FROM ".BD_PREFI."options o, ".BD_PREFI."perfs_opts p
                            WHERE o.id = p.idoption
                                AND o.father = ?
                                AND o.edo_reg = ?
                                AND p.idrole = ?
                            ORDER BY 4;";

                $dpa = array();
                array_push($dpa, ['kpa'=>1,'val'=>0,'typ'=>'int']);
                array_push($dpa, ['kpa'=>2,'val'=>1,'typ'=>'int']);
                array_push($dpa, ['kpa'=>3,'val'=>$_SESSION['u']['idp'],'typ'=>'int']);
                $aws = $this->crud->select_group($sqlFth, count($dpa), $dpa, 'arra');
                $ard = $aws['res'];

                foreach($ard as $k => $v) {

                    if( $v['hijos'] == 0 ){

                        if( $v['file'] == 'none' ){
                            $cls = 'no-link';
                            $lnk = '#';
                        } else {
                            $cls = 'launch-mod';
                            $lnk = $v['file'];
                        }
                        
                        $mnu .= '<li><a href="'.$lnk.'" class="'.$cls.'"><i class="fa '.$v['icon'].'"></i>'.$v['option'].' </a></li>';

                    } else {

                        $mnu .= '<li><a href="#drop-menu-'.$v['id'].'" aria-expanded="false" data-toggle="collapse"> <i class="fa '.$v['icon'].'"></i>'.$v['option'].' </a>';

                        $dpah = array();
                        array_push($dpah, ['kpa'=>1,'val'=>$v['id'],'typ'=>'int']);
                        array_push($dpah, ['kpa'=>2,'val'=>1,'typ'=>'int']);
                        array_push($dpah, ['kpa'=>3,'val'=>$_SESSION['u']['idp'],'typ'=>'int']);
                        $awsh = $this->crud->select_group($sqlFth, count($dpah), $dpah, 'arra');
                        $ardh = $awsh['res'];

                        $mnu .= '<ul id="drop-menu-'.$v['id'].'" class="collapse list-unstyled ">';

                        foreach($ardh as $kh => $vh) {

                            if( $vh['file'] == 'none' ){
                                $clsh = 'no-link';
                                $lnkh = '#';
                            } else {
                                $clsh = 'launch-mod';
                                $lnkh = $vh['file'];
                            }

                            $mnu .= '<li><a href="'.$lnkh.'" class="'.$clsh.'"><i class="fa '.$vh['icon'].'"></i>'.$vh['option'].' </a></li>';

                        }

                        $mnu .= '</ul>';
                        $mnu .= '</li>';

                    }

                }

            }

            $mnu .= '<li><a href="'.URL_BASE.'inicio/logout"><i class="fa fa-sign-out"></i>Salir </a></li>';

            $mnu .= '</ul>';

            return $mnu;

        }

        // Listado de actividades por mina o todas según sea el usuario loggeado
        private function listactiv(){

            $sql = "SELECT /*a.id Ítem, */DATE(a.fec_crea) 'Fecha registro', t.name Actividad, 
                           CONCAT(UPPER(ma.label), ' ', UPPER(m.label)) 'Marca/Modelo', e.internalNumber 'Número interno',
                           c.name Cliente, s.name Proyecto, a.startDate 'Fecha inicio', a.endDate 'Fecha fin',
                           '<a href=\"prntrep\" rel=\"repos\" action=\"prnt\" title=\"Imprimir reporte\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-print\"></i></a>' Reporte
                    FROM tec_equipment e, tec_valists ma, tec_valists m, tec_sites s, tec_company c, tec_activities a, tec_typeactivity t
                    WHERE e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id
                        AND s.companyId = c.id
                        AND a.idTypeAct = t.id
                        AND e.id = a.idEquip
                        -- AND a.id > ? ";

            $dp = array();

            if( $_SESSION['u']['idp'] == 1 && $_SESSION['u']['ico'] == 1 ){
                $sql .= ';';
                //array_push($dp, ['kpa'=>1,'val'=>0,'typ'=>'int']);
            } else {
                $sql .= 'AND e.siteId = ?;';
                array_push($dp, ['kpa'=>1,'val'=>$_SESSION['u']['isi'],'typ'=>'int']);
            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            //$ccols = array(0,1,2,4,7,8,9);
            $ccols = array(0,3,6,7,8);
            return $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Cantidad de actividades por tipo
        private function cantactiv(int $data){

            $sql = "SELECT COUNT(*) cant
                    FROM tec_activities a
                    WHERE a.idTypeAct = ?
                    LIMIT 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            return $ar['cant'];

        }

    }

?>