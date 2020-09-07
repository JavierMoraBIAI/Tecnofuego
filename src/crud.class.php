<?php

	class Crud {

        private $cnx;

        public function __construct(){

            try {

                $options = array(
                    PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
                );

                switch(TYP_DBAS){

                    case 'mysql':
                                    $dsn = TYP_DBAS.':host='.HST_DBAS.';port='.HST_PORT.';dbname='.DBA_DBAS.';charset='.CHAR_SET;
                    break;

                    case 'pgsql':
                                    $dsn = TYP_DBAS.':host='.HST_DBAS.';port='.HST_PORT.';dbname='.DBA_DBAS;
                    break;                    

                }
                
                $this->cnx = new PDO($dsn, USR_DBAS, PSW_DBAS, $options);
 
            } catch (Exception $e) {
                file_put_contents("log/dberror.log","Date: ".date('M j Y - G:i:s')." -- Error: ".$e->getMessage().PHP_EOL, FILE_APPEND);
                die("Error en la conexión: ".$e->getMessage());
            }

        }

        // Seleccionar registro por id
        public function select_id(string $sql, int $idv, string $tyr){

        	try {

        		$sta = $this->cnx->prepare($sql);
				$sta->bindValue(1, (int)$idv, PDO::PARAM_INT);
				$sta->execute();
				$arr = $sta->fetch();

				if($arr){
					$sts = 0;
					$res = $arr;
					for ($i=0; $i < $sta->columnCount(); $i++) { 
						$f = $sta->getColumnMeta($i);
						$afl .= $f['name'].',';
					}
					$afl = trim($afl, ',');
					$fld = $afl;
					$mes = 'Query executed correctly and brings results.';
					$err = null;
				} else {
					$sts = 1;
					$res = null;
					$fld = null;
					$mes = 'No results found, please check the information entered for the query.';
					$err = 'Erroneous or missing data entered.';
				}

        	} catch(Exception $e) {
				$sts = 2;
				$res = null;
				$fld = null;
				$mes = 'Error during query execution.';
				$err = $e->getMessage();
			}

			$r = array('sts'=>$sts,'res'=>$res,'fld'=>$fld,'msg'=>$mes,'err'=>$err);

			switch ($tyr) {

				case 'arra': return $r; break;

				case 'obje': return (object)$r; break;

				case 'json': return json_encode($r); break;

			}

			unset($r);

        }

        // Seleccionar grupo de valores
        public function select_group(string $sql, int $cpa, array $dpa, string $tyr){

        	try {

        		$sta = $this->cnx->prepare($sql);

	        	if( $cpa > 0 ){

        			foreach ($dpa as $k => $v) {

        				switch ($v['typ']) {

		        			case 'int':
                                $sta->bindValue($v['kpa'], $v['val'], PDO::PARAM_INT);
							break;

							case 'string':
                                $sta->bindValue($v['kpa'], $v['val'], PDO::PARAM_STR);
							break;

							case 'float':
		    					$sta->bindValue($v['kpa'], $v['val'], PDO::PARAM_STR);
							break;

		        		}

		        	}

        		}

	        	$sta->execute();
				$arr = $sta->fetchAll();

				if($arr){
					$sts = 0;
					$res = $arr;
                    $afl = '';
					for ($i=0; $i < $sta->columnCount(); $i++) { 
						$f = $sta->getColumnMeta($i);
						$afl .= $f['name'].',';
					}
					$afl = trim($afl, ',');
					$fld = $afl;
					$mes = 'Query executed correctly and brings results.';
					$err = null;
				} else {
					$sts = 1;
					$res = null;
					$fld = null;
					$mes = 'No results found, please check the information entered for the query.';
					$err = 'Erroneous or missing data entered.';
				}

        	} catch(Exception $e) {
				$sts = 2;
				$res = null;
				$fld = null;
				$mes = 'Error during query execution.';
				$err = $e->getMessage();
			}

			$r = array('sts'=>$sts,'res'=>$res,'fld'=>$fld,'msg'=>$mes,'err'=>$err);

			switch ($tyr) {

				case 'arra': return $r; break;

				case 'obje': return (object)$r; break;

				case 'json': return json_encode($r); break;

			}

			unset($r);

        }

        // Método para insertar un solo registro
        public function insert(array $data, string $tabla){

        	try {

        		$info = array(); $flds = null; $vals = null; $res = array();

        		foreach ($data as $k => $v) {
        			$flds .= $k.',';
        			$vals .= '?,';
                    array_push($info, $v);
        		}

        		$this->cnx->prepare('insert into '.$tabla.' ('.trim($flds,',').') values ('.trim($vals,',').');')->execute($info);
        		$res = array('rta'=>'OK','lstId'=>$this->cnx->lastInsertId(),'saveOp'=>'insert');

        	} catch(Exception $e) {
        		$res = array('rta'=>'ERROR','errmsg'=>$e->getMessage());
        	}

        	return $res;
        	unset($res,$vals,$flds,$info);

        }

        // Método para insertar varios registros
        public function insert_mul(array $fields, array $info, string $tabla){

        	try {

        		$sta = null; $flds = null; $vals = null; $res = array(); $lns = 0;

        		$this->cnx->beginTransaction();

        		foreach ($fields as $k => $v) {
        			$flds .= $v.',';
        			$vals .= '?,';
        		}

        		$sta = $this->cnx->prepare('insert into '.$tabla.' ('.trim($flds,',').') values ('.trim($vals,',').');');

        		foreach ($info as $ky => $va) {
                    $sta->execute($va); $lns++;
        		}

        		$this->cnx->commit();

        		$res = array('rta'=>'OK','cantRows'=>$lns,'saveOp'=>'insert');

        	} catch(Exception $e) {
        		$this->cnx->rollBack();
        		$res = array('rta'=>'ERROR','errmsg'=>$e->getMessage());
        	}

        	return $res;
        	unset($res,$vals,$flds,$sta);

        }

        // Método para actualizar registros
        public function update(array $data, string $tabla, array $where){

            try {

                $info = array(); $flds = null; $wher = null; $res = array();

                foreach ($data as $kf => $vf) {
                    $flds .= $kf." = ?, ";
                    array_push($info, $vf);
                }

                foreach ($where as $kw => $vw) {
                    $wher .= $kw." = ? and ";
                    array_push($info, $vw);
				}
				
				$sql = "update ".$tabla." set ".trim($flds,', ')." where ".trim($wher,' and ')." limit 1;";

                $this->cnx->prepare($sql)->execute($info);
                $res = array('rta'=>'OK','saveOp'=>'update','sql'=>$sql);

            } catch(Exception $e) {
                $res = array('rta'=>'ERROR','errmsg'=>$e->getMessage(),'sql'=>$sql);
            }

            return $res;
            unset($res,$flds,$wher,$info);

        }

        // Borrar registros
        public function delete(string $tabla, array $where){

            try {

				$info = array(); $wher = null;

				foreach ($where as $kw => $vw) {
                    $wher .= $kw." = ? and ";
                    array_push($info, $vw);
				}

                $sta = $this->cnx->prepare("delete from ".$tabla." where ".trim($wher,' and ')." limit 1;");
                $sta->execute($info);
                $res = array('rta'=>'OK','saveOp'=>'delete');

            } catch(Exception $e) {
                $res = array('rta'=>'ERROR','errmsg'=>$e->getMessage());
            }

            return $res;
            unset($res,$tabla,$idtab,$sta);

        }

        // No se puede clonar el objeto 
        public function __clone(){
            trigger_error('La clonación no es permitida!.', E_USER_ERROR);
        }

        public function __destruct(){
            try {
                $this->cnx = null;
            } catch (PDOException $e) {
                file_put_contents("log/dberror.log","Date: ".date('M j Y - G:i:s')." -- Error: ".$e->getMessage().PHP_EOL, FILE_APPEND);
                die($e->getMessage());
            }
        }

    }

?>