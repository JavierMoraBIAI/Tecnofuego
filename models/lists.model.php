<?php

    class lists {
        
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
                    'header'	=>  $this->rndr->renderHeader('Gestionar listas'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/lists/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listar productos y servicios  
        public function listar(array $data){

        	$sql = "SELECT l.name LISTA, 
                        IF(l.son='S', 'SI', 'NO') 'LISTA HIJA', 
                        IF(l.edo_reg='0', 'INACTIVO', 'ACTIVO') ESTADO,
                        CONCAT('<a idreg=\"',l.id,'\" href=\"editar\" rel=\"lists\" action=\"upd\" title=\"Editar lista\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
					FROM ".BD_PREFI."lists l
					WHERE l.id > 0 ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'txtNombre':

                            $sql .= " AND l.name LIKE ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>'%'.$v.'%','typ'=>'string']);
                            
                        break;

                        case 'slcEstado':

                            $sql .= " AND l.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $sql .= ';';

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,2,3,4,5);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(){

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar listas'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'hijo'      =>  self::hijo('return',''),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/lists/nuevo.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){

            $sql = "SELECT l.id, l.lstfather, l.name, l.son, l.edo_reg
                    FROM ".BD_PREFI."lists l
                    WHERE l.id = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar listas'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'hijo'      =>  self::hijo('return',$ar['son']),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/lists/editar.html'
            );

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            if( $ar['son'] == 'N' ){

                $sqld = "SELECT v.id, v.label, v.valfather fath, 'NO APLICA' fathlbl, v.edo_reg edo, 
                            IF(v.edo_reg=0,'INACTIVO','ACTIVO') edolbl
                         FROM tec_valists v
                         WHERE v.idlist = ?
                         ORDER BY 1 DESC;";

            } else {

                $sqld = "SELECT v.id, v.label, v.valfather fath, v1.label fathlbl, v.edo_reg edo, 
                            IF(v.edo_reg=0,'INACTIVO','ACTIVO') edolbl
                         FROM tec_valists v, tec_valists v1
                         WHERE v.valfather = v1.id
                            AND v.idlist = ?
                         ORDER BY 1 DESC;";

            }

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$ar['id'],'typ'=>'int']);
            $awd = $this->crud->select_group($sqld, count($dp), $dp, 'arra');
            $ard = $awd['res'];

            $tr = '';

            foreach ($ard as $k => $v) {

                $btn = '<button class="btn btn-info btn-sm edit-dty" type="button" idfila="'.$k.'"><i class="fa fa-pencil"></i></button>';
                
                $tr .= '<tr id="tr'.$k.'">';
                    $tr .= '<td><input type="hidden" name="hidIdVal'.$k.'" id="hidIdVal'.$k.'" value="'.$v['id'].'">'.$v['label'].'</td>';
                    $tr .= '<td class="text-center"><input type="hidden" name="hidFath'.$k.'" id="hidFath'.$k.'" value="'.$v['fath'].'">'.$v['fathlbl'].'</td>';
                    $tr .= '<td class="text-center"><input type="hidden" name="hidEdo'.$k.'" id="hidEdo'.$k.'" value="'.$v['edo'].'">'.$v['edolbl'].'</td>';
                    $tr .= '<td class="text-center">'.$btn.'</td>';
                $tr .= '</tr>';

            }

            $d['data']['trdet'] = $tr;

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(){

            $data = json_decode($_POST['args']);

            $info = array(
                'lstfather' =>  $data->slcPadre,
                'name'      =>  $data->txtNombre,
                'son'       =>  $data->slcHija,
                'edo_reg'   =>  $data->slcEstado
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'lists',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'lists');

            }

            $datos = json_decode($data->hidIdDty, true);

            foreach ($datos as $k => $v) {

                $idty = array(
                    'idlist'    =>  (empty($data->hidId)) ? $resp['lstId'] : $data->hidId,
                    'valfather' =>  $v['fth'],
                    'label'     =>  $v['lbl'],
                    'edo_reg'   =>  $v['edo']
                );

                if( !empty($v['idv']) ){

                    $idty['usu_mod'] = $this->seda['idu'];
                    $idty['fec_mod'] = date('Y-m-d H:i:s');
                    $idty['ip_mod']  = Firewall::ipCatcher();
    
                    $whr = array('id'=>$v['idv']);
    
                    $r = $this->crud->update($idty,BD_PREFI.'valists',$whr);
    
                } else {
    
                    $idty['usu_crea'] = $this->seda['idu'];
                    $idty['fec_crea'] = date('Y-m-d H:i:s');
                    $idty['ip_crea']  = Firewall::ipCatcher();
    
                    $r = $this->crud->insert($idty,BD_PREFI.'valists');
    
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
                    'header'	=>  $this->rndr->renderHeader('Gestionar listas'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/lists/respsave.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Listado que indica si es hijo o no 
        private function hijo(string $tyre, string $dfval){

            $ar = array(
                array('id'=>'S','label'=>'SI'),
                array('id'=>'N','label'=>'NO')
            );

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE VALOR', $dfval);

            if( $tyre == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado que padres en caso de tenerlo
        public function padres(array $data){

            if( $data['val'] == 'S' ){

                $sql = "SELECT l.id, l.name label
                        FROM ".BD_PREFI."lists l
                        WHERE l.son = ?
                        ORDER BY 2;";

                $dp = array();
                array_push($dp, ['kpa'=>1,'val'=>'N','typ'=>'string']);
                $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
                $ar = $aw['res'];

            } else {
                $ar = array(
                    array('id'=>0,'label'=>'NO APLICA')
                );
            }

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE VALOR', $data['def']);

            if( $data['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado de valores dependientes
        public function valfather(array $data){

            if( $data['son'] == 'S' ){

                $sql = "SELECT v.id, v.label
                        FROM ".BD_PREFI."valists v
                        WHERE v.idlist = ?
                        ORDER BY 2;";

                $dp = array();
                array_push($dp, ['kpa'=>1,'val'=>$data['val'],'typ'=>'int']);
                $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
                $ar = $aw['res'];
                $df = $data['def'];

            } else {
                $ar = array(
                    array('id'=>0,'label'=>'NO APLICA')
                );
                $df = 0;
            }

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE VALOR', $df);

            if( $data['typ'] == 'echo' ){
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