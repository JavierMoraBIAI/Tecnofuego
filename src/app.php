<?php

	class App {
		
		//public function app($res = array()){
		public function __construct(array $res) {
			$this->res = $res;
			$this->cleanstr = $res['cleanstr'];
			$this->modelsPermited = $res['models'];
			$this->methodsPermited = $res['methods'];
			$this->settings = $res['error_config'];
			$this->iniModel = $res['ini_model'];
			$this->rndr = $res['render'];
			self::logErrors();
		}

		public function run(){
			self::routing();
		}

		private function routing(){

			// Petición POST
			if($_POST){
				
				$m = $c = (($_POST['model']) ? $_POST['model'] : $this->iniModel);
				$mt = $_POST['method'];
				$arg = $this->cleanstr->strFilter($_POST['args']);
				include('models/'.$m.'.model.php');

				try {
					if(class_exists($c)){
						$cc = new $c($this->res);
						if(!empty($mt)){
							if(!empty($arg)){
								$cc->$mt($arg);
							} else {
								$cc->$mt();
							}	
						} else {
							$cc->index();
						}
					} else {
						//throw new Exception('El Modelo '.$_POST['model'].' que intentas cargar no esta definido.');
						self::show404($_POST['model']);
					}	
				} catch (Exception $e) {
					echo $e;
				}

				ob_end_flush();

			}


			// Petición GET
			if($_GET){

				$m = $c = (($_GET['model']) ? $_GET['model'] : $this->iniModel);
				$mt = $_GET['method'];
				$arg = $this->cleanstr->strFilter($_GET['args']);

				if (in_array($m, $this->modelsPermited) && in_array($mt, $this->methodsPermited)) {
					
					include('models/'.$m.'.model.php');

					try {
					
						if(class_exists($c)){
							$cc = new $c($this->res);
							if(!empty($mt)){
								if(!empty($arg)){
									$cc->$mt($arg);
								} else {
									$cc->$mt();
								}	
							} else {
								$cc->index();
							}
						} else {
							//throw new Exception('El Modelo '.$_GET['model'].' que intentas cargar no esta definido.');
							self::show404($_GET['model']);
						}	

					} catch (Exception $e) {
						echo $e;
					}
					
					ob_end_flush();

				}

			}
		
		}

		private function show404(string $r){

			$d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Página no encontrada'),
                    'module'	=>	$r,
                    'urlhome'	=>	URL_BASE,
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY)
                ),
                'file' => 'html/404.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

		}

		private function logErrors(){
			ini_set('display_errors', $this->settings['display_errors']); 
			ini_set('log_errors', $this->settings['log_errors']); 
			ini_set('error_log', 'log/'.$this->settings['file_log']); 
			error_reporting(E_ALL);
		}

	}

?>