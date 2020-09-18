<?php 

	class Cleanstr {

		private $whiteTags;

		public function __construct(){
        	$this->whiteTags = WHIT_TGS;
        }
        
        // Limpiar entrada
		public function strFilter($input,$tags=false){

			if( !empty($tags) && is_array($tags) ){
				$this->whiteTags = array_merge($this->whiteTags,$tags);
			}

			if(!is_array($input)){
				return $this->cleanInput($input);
			} else {
				foreach($input as $var => $val) {
                	$input[$var] = $this->cleanInput($val);
                }
				return $input;
			}
			
		}

		// Limpiar cadena exceptuando tags permitidos 
		private function cleanInput($i){
			return $this->cleanAttrs(strip_tags($i,implode('',$this->whiteTags)));
		}

		// Limpiar cadena de atributos no permitidos 
		private function cleanAttrs($i){

			// Fix &entity\n;
            $i = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $i);
            $i = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $i);
            $i = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $i);
            $i = html_entity_decode($i, ENT_COMPAT, 'UTF-8');

            // Remove any attribute starting with "on" or xmlns
            $i = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $i);

            // Remove javascript: and vbscript: protocols
            $i = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $i);
            $i = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $i);
            $i = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $i);

            // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
            $i = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $i);
            $i = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $i);
            $i = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $i);

            // Remove namespaced elements (we do not need them)
            $i = preg_replace('#</*\w+:\w[^>]*+>#i', '', $i);

            do {
                // Remove really unwanted tags
                $old_data = $i;
                $i = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $i);
            } while ($old_data !== $i);

            return $i;

		}

        // No se puede clonar el objeto 
        public function __clone(){
            trigger_error('La clonación no es permitida!.', E_USER_ERROR);
        }

        public function __destruct(){

        }

	}

?>