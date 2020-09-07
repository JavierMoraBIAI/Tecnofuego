<?php 

	class Firewall {

        // Capturar la dirección IP del cliente
        public static function ipCatcher(){

        	$ipaddress = '';
	        if (getenv('HTTP_CLIENT_IP'))
	            $ipaddress = getenv('HTTP_CLIENT_IP');
	        else if(getenv('HTTP_X_FORWARDED_FOR'))
	            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	        else if(getenv('HTTP_X_FORWARDED'))
	            $ipaddress = getenv('HTTP_X_FORWARDED');
	        else if(getenv('HTTP_FORWARDED_FOR'))
	            $ipaddress = getenv('HTTP_FORWARDED_FOR');
	        else if(getenv('HTTP_FORWARDED'))
	           $ipaddress = getenv('HTTP_FORWARDED');
	        else if(getenv('REMOTE_ADDR'))
	            $ipaddress = getenv('REMOTE_ADDR');
	        else
	            $ipaddress = 'UNKNOWN';
	        return $ipaddress;

        }

        // Generador de hash de passwords
        public static function pwd_hash(string $str){
        	return password_hash(md5(sha1('PW'.$str)), PASSWORD_DEFAULT, array('cost'=>11));
        }

        // Verificar el hash de passwords $istr (Input String) $sstr (Saved String)
        public static function pwd_verf(string $istr, string $sstr){
        	if( password_verify(md5(sha1('PW'.$istr)), $sstr) ) {
                return true;
            } else {
                return false;
            }
        }

        // Evauluar si la sesión está abierta o no 
        public static function evalSession(){
            if( $_SESSION['u']['uAuth'] == true ){
                return true;
            } else {
                return false;
            }
        }

        // Matar la sesión
        public static function sessionKill(){
            $_SESSION = array();
            session_destroy();
        }

        // No se puede clonar el objeto 
        public function __clone(){
            trigger_error('La clonación no es permitida!.', E_USER_ERROR);
        }

        public function __destruct(){

        }

	}

?>