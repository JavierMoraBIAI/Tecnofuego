<?php

class components
{

    public function __construct(array $res)
    {
        $this->clstr = $res['cleanstr'];
        $this->crud = $res['crud'];
        $this->rndr = $res['render'];
        $this->fima = $res['fileman'];
        $this->seda = $_SESSION['u'];
    }

    // Método inicial
    public function index()
    {

        $d = array(
            'data' => array(
                'header'        =>  $this->rndr->renderHeader('Gestionar componentes de equipos'),
                'footer'        =>  $this->rndr->renderFooter(EMP_NAME, YEARCOPY),
                'empresas'      =>  self::empresas(array('def' => '', 'typ' => 'retu')),
                'sistemas'      =>  self::lists(array('idlst' => 10, 'str' => 'SELECCIONE SISTEMA', 'def' => '')),
                'familias'      =>  self::lists(array('idlst' => 4, 'str' => 'SELECCIONE FAMILIA', 'def' => '')),
                'categs'        =>  self::lists(array('idlst' => 5, 'str' => 'SELECCIONE CATEGORÍA', 'def' => '')),
                'estados'       =>  self::estados('return', ''),
                'usuario'       =>  $this->seda['idu']
            ),
            'file' => 'html/components/index.html'
        );

        $this->rndr->setData($d);
        echo $this->rndr->rendertpl();
    }
    public function prueba(){
        $data = json_decode($_POST['args']);

        $info = array(
            'siteId'        => (empty($data->slcProyecto)) ? $data->hidProyecto : $data->slcProyecto,
            'idComponent'   =>  $data->slcComponente,
            'idTySys'       =>  $data->slcTySys,
            'edo_reg'       =>  $data->slcEstado
        );
    }

    public function consultar(array $data)
    {
        $sql = "SELECT
                    COUNT(cv.valField) as cant
                FROM tec_components c, tec_sites s, tec_company cy, tec_parts p, 
                tec_compo_vals cv, tec_valists f, tec_valists ct
                WHERE c.siteId = s.id
                AND s.companyId = cy.id
                AND c.idComponent = p.id
                AND cv.idComponent = c.id
                AND p.idFamily = f.id
                AND p.idCategory = ct.id
                AND cv.idField IN (31,34)
                AND cv.valField = ?
                AND c.idComponent = ? ";
  
        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => $data['consecutivo'], 'typ' => 'string']);
        array_push($dp, ['kpa' => 2, 'val' => $data['componente'], 'typ' => 'string']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'][0];

        return $ar['cant'];

    }
    // Listar productos y servicios  
    public function listar(array $data)
    {

        $sql = "SELECT cy.name CLIENTE, s.name 'PROYECTO/MINA', p.description COMPONENTE, 
                        cv.valField 'SN/CONSEC.', f.label FAMILIA, ct.label CATEGORÍA, 
                        CASE c.edo_reg 
                            WHEN '0' THEN 'INACTIVO'
                            WHEN '1' THEN 'ACTIVO'
                            WHEN '2' THEN 'ASIGNADO'
                        ELSE 'no es un estado' END 'ESTADO',
                        CONCAT('<a idreg=\"',c.id,'\" href=\"editar\" rel=\"components\" action=\"upd\" title=\"Editar componente\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
                    FROM tec_components c, tec_sites s, tec_company cy, tec_parts p, 
                        tec_compo_vals cv, tec_valists f, tec_valists ct
                    WHERE c.siteId = s.id
                        AND s.companyId = cy.id
                        AND c.idComponent = p.id
                        AND cv.idComponent = c.id
                        AND p.idFamily = f.id
                        AND p.idCategory = ct.id
                        AND cv.idField IN (31,34) ";

        $dp = array();
        $im = 1;

        foreach ($data as $k => $v) {

            if (strlen($v) > 0) {

                switch ($k) {

                    case 'slcCliente':

                        $sql .= " AND s.companyId = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'hidCliente':

                        $sql .= " AND s.companyId = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'slcProyecto':

                        $sql .= " AND c.siteId = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'hidProyecto':

                        $sql .= " AND c.siteId = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'slcTySys':

                        $sql .= " AND c.idTySys = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'slcFamilia':

                        $sql .= " AND p.idFamily = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'txtNumParte':

                        $sql .= " AND cv.valField LIKE ? ";
                        array_push($dp, ['kpa' => $im, 'val' => '%' . $v . '%', 'typ' => 'string']);

                        break;

                    case 'txtNombre':

                        $sql .= " AND p.description LIKE ? ";
                        array_push($dp, ['kpa' => $im, 'val' => '%' . $v . '%', 'typ' => 'string']);

                        break;

                    case 'slcCategoria':

                        $sql .= " AND p.idCategory = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;

                    case 'slcEstado':

                        $sql .= " AND c.edo_reg = ? ";
                        array_push($dp, ['kpa' => $im, 'val' => $v, 'typ' => 'int']);

                        break;
                }

                $im++;
            }
        }

        $sql .= ';';

        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ccols = array(0, 4, 5, 6, 7, 8);
        echo $this->rndr->table_html($aw, $ccols, 'tabHtml');
    }

    // Lanzar formulario de nuevo registro
    public function nuevo()
    {

        $d = array(
            'data' => array(
                'header'    =>  $this->rndr->renderHeader('Gestionar componentes de equipos'),
                'footer'    =>  $this->rndr->renderFooter(EMP_NAME, YEARCOPY),
                'empresas'  =>  self::empresas(array('def' => '', 'typ' => 'retu')),
                'sistemas'  =>  self::lists(array('idlst' => 10, 'str' => 'SELECCIONE SISTEMA', 'def' => '')),
                'familias'  =>  self::lists(array('idlst' => 4, 'str' => 'SELECCIONE FAMILIA', 'def' => '')),
                'categs'    =>  self::lists(array('idlst' => 5, 'str' => 'SELECCIONE CATEGORÍA', 'def' => '')),
                'estados'   =>  self::estados('return', ''),
                'usuario'   =>  $this->seda['idu']
            ),
            'file' => 'html/components/nuevo.html'
        );

        $this->rndr->setData($d);
        echo $this->rndr->rendertpl();
    }

    // Mostrar datos para editar
    public function editar(int $data)
    {

        $sql = "SELECT c.id, c.idComponent, c.idTySys, c.siteId, s.companyId, p.idCategory, 
                        p.idFamily, c.edo_reg, p.id idPart
                    FROM tec_components c, tec_sites s, tec_parts p
                    WHERE c.siteId = s.id
                        AND c.idComponent = p.id
                        AND c.id = ?
                    LIMIT 1;";

        $re = $this->crud->select_id($sql, $data, 'arra');
        $ar = $re['res'];

        $d = array(
            'data' => array(
                'header'    =>  $this->rndr->renderHeader('Gestionar campos por componentes'),
                'footer'    =>  $this->rndr->renderFooter(EMP_NAME, YEARCOPY),
                'empresas'  =>  self::empresas(array('def' => $ar['companyId'], 'typ' => 'retu')),
                'sistemas'  =>  self::lists(array('idlst' => 10, 'str' => 'SELECCIONE SISTEMA', 'def' => $ar['idTySys'])),
                'familias'  =>  self::lists(array('idlst' => 4, 'str' => 'SELECCIONE FAMILIA', 'def' => $ar['idFamily'])),
                'categs'    =>  self::lists(array('idlst' => 5, 'str' => 'SELECCIONE CATEGORÍA', 'def' => $ar['idCategory'])),
                'estados'   =>  self::estados('return', $ar['edo_reg']),
                'usuario'   =>  $this->seda['idu']
            ),
            'file' => 'html/components/editar.html'
        );

        foreach ($ar as $key => $value) {
            $d['data'][$key] = $value;
        }

        $fbx = self::control_fill(array('idco' => $ar['id'], 'idpa' => $ar['idPart']));

        $d['data']['fbox'] = $fbx['fbox'];
        $d['data']['lfld'] = $fbx['lflds'];

        $this->rndr->setData($d);
        echo $this->rndr->rendertpl();
    }

    // Acción de guardar
    public function guardar()
    {

        $data = json_decode($_POST['args']);

        $info = array(
            'siteId'        => (empty($data->slcProyecto)) ? $data->hidProyecto : $data->slcProyecto,
            'idComponent'   =>  $data->slcComponente,
            'idTySys'       =>  $data->slcTySys,
            'edo_reg'       =>  $data->slcEstado
        );
            
        // $datosComponente = array();
        // $datosComponente += [ "consecutivo" => $data->{'txtConsecutivo-31'}, "componente" => $data->slcComponente ];
        // $componentesExistente = $this -> consultar($datosComponente);
        // if($componentesExistente == 0){
        
            if (!empty($data->hidId)) {

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id' => $data->hidId);

                $idco = $data->hidId;

                $resp = $this->crud->update($info, BD_PREFI . 'components', $where);
            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info, BD_PREFI . 'components');

                $idco = $resp['lstId'];
            }

            $flds = explode(',', trim($data->hidCtrlFdls, ','));

            foreach ($flds as $k => $v) {

                $cmp = explode('-', $v);
                $hid = str_replace('txt', 'hid', $v);

                $infl = array(
                    'idComponent'   =>  $idco,
                    'idField'       =>  $cmp[1],
                    'valField'      =>  $data->$v,
                    'edo_reg'       =>  1
                );

                if (!empty($data->$hid)) {

                    $infl['usu_mod'] = $this->seda['idu'];
                    $infl['fec_mod'] = date('Y-m-d H:i:s');
                    $infl['ip_mod']  = Firewall::ipCatcher();

                    $whr = array('id' => $data->$hid);

                    $r = $this->crud->update($infl, BD_PREFI . 'compo_vals', $whr);
                } else {

                    $infl['usu_crea'] = $this->seda['idu'];
                    $infl['fec_crea'] = date('Y-m-d H:i:s');
                    $infl['ip_crea']  = Firewall::ipCatcher();

                    $r = $this->crud->insert($infl, BD_PREFI . 'compo_vals');
                }

                unset($infl, $cmp, $hid, $r);
            }

            if ($resp['rta'] == 'OK') {
                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';
            } else {
                $cls = 'alert-danger';
                $msg = 'Hubo un error guardando la información: ' . $resp['errmsg'] . ' &nbsp;&nbsp;<i class="fa fa-times" aria-hidden="true"></i>';
            }
    // }else{
    //     $cls = 'alert-danger';
    //     $msg = 'Ya existe el consecutivo para el componente escogido &nbsp;&nbsp;<i class="fa fa-times" aria-hidden="true"></i>';
    // }

        $d = array(
            'data' => array(
                'header'    =>  $this->rndr->renderHeader('Gestionar componentes de equipos'),
                'footer'    =>  $this->rndr->renderFooter(EMP_NAME, YEARCOPY),
                'cls'       =>  $cls,
                'msg'       =>  $msg
            ),
            'file' => 'html/components/respsave.html'
        );

        $this->rndr->setData($d);
        echo $this->rndr->rendertpl();
    }

    // Listado de empresas
    private function empresas(array $conf)
    {

        $sql = "SELECT c.id, c.name label
                    FROM " . BD_PREFI . "company c
                    WHERE c.edo_reg = ?
                    ORDER BY 2;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => 1, 'typ' => 'int']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'];
        $sl = $this->rndr->renderSelect($ar, 'SELECCIONE CLIENTE', $conf['def']);

        if ($conf['typ'] == 'echo') {
            echo $sl;
        } else {
            return $sl;
        }
    }

    // Listado de sitios por empresa
    public function sitios(array $conf)
    {

        $sql = "SELECT s.id, s.name label
                    FROM " . BD_PREFI . "sites s
                    WHERE s.edo_reg = ?
                        AND s.companyId = ?
                    ORDER BY 2;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => 1, 'typ' => 'int']);
        array_push($dp, ['kpa' => 2, 'val' => $conf['val'], 'typ' => 'int']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'];
        $sl = $this->rndr->renderSelect($ar, 'SELECCIONE PROYECTO/MINA', $conf['def']);

        if ($conf['typ'] == 'echo') {
            echo $sl;
        } else {
            return $sl;
        }
    }

    // Listas configuradas
    private function lists(array $data)
    {

        $sql = "SELECT v.id, v.label
                    FROM " . BD_PREFI . "valists v
                    WHERE v.idlist = ?
                    ORDER BY 2;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => $data['idlst'], 'typ' => 'int']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        return $this->rndr->renderSelect($aw['res'], $data['str'], $data['def']);
    }

    // Listado de componentes
    public function compos(array $conf)
    {

        $sql = "SELECT p.id, p.description label
                    FROM " . BD_PREFI . "parts p, " . BD_PREFI . "parts_flds f
                    WHERE p.id = f.idPart
                        AND p.partserv = 'P'
                        AND f.idField IN (31,34)
                        AND p.idCategory = ?
                        AND p.idFamily = ?
                    ORDER BY 2;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => $conf['val'], 'typ' => 'int']);
        array_push($dp, ['kpa' => 2, 'val' => $conf['fam'], 'typ' => 'int']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'];
        $sl = $this->rndr->renderSelect($ar, 'SELECCIONE COMPONENTE', $conf['def']);

        if ($conf['typ'] == 'echo') {
            echo $sl;
        } else {
            return $sl;
        }
    }

    // Render de campos de control
    public function control(int $data)
    {

        $sql = "SELECT f.id, f.idField, c.label campo, f.idType
                    FROM tec_parts_flds f, tec_valists c
                    WHERE f.idField = c.id
                        AND f.idPart = ?
                        AND f.edo_reg = 1;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => $data, 'typ' => 'int']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'];

        foreach ($ar as $k => $v) {

            $fld = 'txt' . str_replace(" ", "", ucwords($v['campo'])) . '-' . $v['idField'];

            switch ($v['idType']) {

                case 41: // Número
                    $fbox .= '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">' . $v['campo'] . ' <span class="text-danger">*</span></label>
                                            <input type="number" id="' . $fld . '" idfi="' . $v['idField'] . '" name="' . $fld . '" placeholder="' . $v['campo'] . '" class="form-control ctrl-fld" required>
                                        </div>
                                      </div>';
                    break;

                case 42: // Fecha
                    $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">' . $v['campo'] . ' <span class="text-danger">*</span></label>
                                        <input type="date" id="' . $fld . '" idfi="' . $v['idField'] . '" name="' . $fld . '" placeholder="' . $v['campo'] . '" class="form-control ctrl-fld" required>
                                    </div>
                                  </div>';
                    break;

                case 43: // Texto
                    $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">' . $v['campo'] . ' <span class="text-danger">*</span></label>
                                        <input type="text" id="' . $fld . '" idfi="' . $v['idField'] . '" name="' . $fld . '" placeholder="' . $v['campo'] . '" class="form-control ctrl-fld" required>
                                    </div>
                                  </div>';
                    break;
            }
        }

        echo $fbox;
    }

    // Render de campos de control con valores
    public function control_fill(array $data)
    {

        $sql = "SELECT f.id, f.idField, c.label campo, f.idType, v.valField, v.id idVal
                    FROM tec_parts_flds f, tec_valists c, tec_compo_vals v
                    WHERE f.idField = c.id
                        AND f.idField = v.idField
                        AND v.idComponent = ?
                        AND f.idPart = ?;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => $data['idco'], 'typ' => 'int']);
        array_push($dp, ['kpa' => 2, 'val' => $data['idpa'], 'typ' => 'int']);
        $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'];

        foreach ($ar as $k => $v) {

            $fld = 'txt' . str_replace(" ", "", ucwords($v['campo'])) . '-' . $v['idField'];
            $lflds .= $fld . ',';
            $hfld = 'hid' . str_replace(" ", "", ucwords($v['campo'])) . '-' . $v['idField'];

            switch ($v['idType']) {

                case 41: // Número
                    $fbox .= '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">' . $v['campo'] . ' <span class="text-danger">*</span></label>
                                            <input type="number" id="' . $fld . '" idfi="' . $v['idField'] . '" name="' . $fld . '" placeholder="' . $v['campo'] . '" class="form-control ctrl-fld" value="' . $v['valField'] . '" required>
                                            <input type="hidden" id="' . $hfld . '" name="' . $hfld . '" value="' . $v['idVal'] . '">
                                        </div>
                                      </div>';
                    break;

                case 42: // Fecha
                    $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">' . $v['campo'] . ' <span class="text-danger">*</span></label>
                                        <input type="date" id="' . $fld . '" idfi="' . $v['idField'] . '" name="' . $fld . '" placeholder="' . $v['campo'] . '" class="form-control ctrl-fld" value="' . $v['valField'] . '" required>
                                        <input type="hidden" id="' . $hfld . '" name="' . $hfld . '" value="' . $v['idVal'] . '">
                                    </div>
                                  </div>';
                    break;

                case 43: // Texto
                    $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">' . $v['campo'] . ' <span class="text-danger">*</span></label>
                                        <input type="text" id="' . $fld . '" idfi="' . $v['idField'] . '" name="' . $fld . '" placeholder="' . $v['campo'] . '" class="form-control ctrl-fld" value="' . $v['valField'] . '" required>
                                        <input type="hidden" id="' . $hfld . '" name="' . $hfld . '" value="' . $v['idVal'] . '">
                                    </div>
                                  </div>';
                    break;
            }
        }

        return array('fbox' => $fbox, 'lflds' => $lflds);
    }

    // Listado de estados
    private function estados(string $tyre, string $dfval)
    {

        $ar = array(
            array('id' => 1, 'label' => 'ACTIVO'),
            array('id' => 2, 'label' => 'ASIGNADO'),
            array('id' => 0, 'label' => 'INACTIVO')
        );

        $sl = $this->rndr->renderSelect($ar, 'SELECCIONE ESTADO', $dfval);

        if ($tyre == 'echo') {
            echo $sl;
        } else {
            return $sl;
        }
    }

    // Verificar serial y consecutivo de componentes
    public function vercods(array $data)
    {

        if ($data['idfi'] == 31) {
            $fld = 'AND c.siteId = ?';
        } else {
            $fld = '';
        }

        $sql = "SELECT COUNT(*) cant
                    FROM tec_components c, tec_compo_vals v,tec_parts tc
                    WHERE c.id = v.idComponent
                        AND v.valField = ?
                        AND c.idComponent = ?
                        AND v.idField = ?
                        AND c.idComponent=tc.id
                        " . $fld . "
                    LIMIT 1;";

        $dp = array();
        array_push($dp, ['kpa' => 1, 'val' => $data['valu'], 'typ' => 'string']);
        array_push($dp, ['kpa' => 2, 'val' => $data['comp'], 'typ' => 'int']);
        array_push($dp, ['kpa' => 3, 'val' => $data['idfi'], 'typ' => 'int']);
        if ($data['idfi'] == 31) {
            array_push($dp, ['kpa' => 4, 'val' => $data['proy'], 'typ' => 'int']);
        }
            //  var_dump($dp);
   $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
        $ar = $aw['res'][0];
        // $ar['cant']=1;

        echo $ar['cant'];
    }
}
