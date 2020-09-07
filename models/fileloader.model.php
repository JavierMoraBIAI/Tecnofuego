<?php

    class fileloader {
        
        public function __construct(array $res){
        	$this->clstr = $res['cleanstr'];
        	$this->crud = $res['crud'];
            $this->rndr = $res['render'];
        	$this->fima = $res['fileman'];
        	$this->seda = $_SESSION['u'];
        }

        // Método inicial
        public function index(string $args){

        	if( $_SESSION['token'] && $_SESSION['u']['uAuth'] ){

        		$vals = explode("-", $args);
        		$path = 'dvault/';
        		$crye = '.crypto';

        		switch ($vals[0]) {

        			case 'usuarios':

        				$sql = "SELECT t.".$vals[1]." img
		        				FROM ".BD_PREFI.$vals[0]." t
		        				WHERE t.idusuario = ?
		        				LIMIT 1;";
    				
    				break;

        		}

        		$re = $this->crud->select_id($sql, $vals[2], 'arra');
            	$ar = $re['res'];
            	$im = $ar['img'];
            	$sm = explode('_', $im);

            	// Dividir archivo en extensión y nombre
            	$sep = $this->fima->extGrab($im,'.');
            	$rst = $sep['rst'];
	            $ext = $sep['ext'];

            	// Iniciando proceso de desencriptación
            	chmod($path.$rst.$crye, 0600);
            	$fcnt = file_get_contents($path.$rst.$crye);
            	$decy = $this->fima->decrypto($fcnt,  SALT_FIL.$sm[0]);
            	$ficl = file_put_contents($path.$im,$decy);
            	chmod($path.$rst.$crye, 0000);
            	$ftyp = mime_content_type($path.$im);
            	unlink($path.$im);

            	if( $ftyp == 'image/jpeg' ){
            		header("Content-type: image/jpeg");
            		echo base64_decode(base64_encode($decy));
            	}

            	if( $ftyp == 'image/png' ){
            		header("Content-type: image/png");
            		echo base64_decode(base64_encode($decy));
            	}

            	if( $ftyp == 'application/pdf' ){            		
            		header('Content-Type: application/pdf');
					echo base64_decode(base64_encode($decy));
            	}

        	} else {
        		echo 'Debe visualizar el archivo desde la aplicación.';
        	}

        	unset($_SESSION['token']);

        }

		// Generar token para ver la imagen o archivo
		public function gentoken(){
			$_SESSION['token'] = true;
            echo $_SESSION['token'];
		}

    }

?>