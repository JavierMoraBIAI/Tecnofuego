<?php

	class Render {

		private $datamix = '', $filemix = '', $html = '';

		public function __construct(){

		}

		// Tabla HTML
		public function table_html(array $data, array $ccols, string $id){

			if( $data['sts'] == 0 ){

				$t = '<table id="'.$id.'" align="center" class="table table-hover table-striped newrone-table">';
				$t.= "<thead><tr>";
				
				$flds = explode(',',$data['fld']);
				array_unshift($flds,'Ítem');
				$camp = count($flds);

				for ($i=0; $i < $camp; $i++) { 
					$t.= '<th class="text-center">'.$flds[$i]."</th>";
				}

				$t.= "</tr></thead><tbody>";
				$r = 1;
				$data['res']['item'] = $r;

				foreach ($data['res'] as $key => $value) {

					if( !empty($value[$flds[1]]) ){

						$t.= "<tr>";
					
						for ($k = 0; $k < $camp; $k++){

							if( $k == 'item' ){
								$t.= '<td align="center">'.$r.'</td>';
							} else {

								if (in_array($k, $ccols)) {
									$t.= '<td align="center">'.$value[$flds[$k]].'</td>';
								} else {
									$t.= "<td>".$value[$flds[$k]]."</td>";
								}

							}
							
						}

						$t.= "</tr>";
						$r++;

					}

				}

				$t.= '</tbody></table>';

			} else {
				$t = 'There are no records to list. '.$data['msg'].' - '.$data['err'];
			}

			return $t;

		}

		// Tabla Excel
		public function table_xls(array $data){

			if( $data['sts'] == 0 ){

				$t = '<table>';
				$t.= "<thead><tr>";
				
				$flds = explode(',',$data['fld']);
				$camp = count($flds);

				for ($i=0; $i < $camp; $i++) { 
					$t.= '<th>'.utf8_decode($flds[$i])."</th>";
				}

				$t.= "</tr></thead><tbody>";

				foreach ($data['res'] as $key => $value) {

					$t.= "<tr>";

					for ($k = 0; $k < $camp; $k++){

						$t.= "<td>".utf8_decode($value[$flds[$k]])."</td>";
						
					}

					$t.= "</tr>";

				}

				$t.= '</tbody></table>';

			} else {
				$t = 'There are no records to list. '.$data['msg'].' - '.$data['err'];
			}

			return $t;
		}

		// Métodos para renderizar plantillas (Páginas)
		public function setData(array $mix){
			$this->datamix = $mix['data'];
			$this->filemix = $mix['file'];
		}

		// Obtiene el HTML de un archivo
		private function getHtml($plantilla){
			$html = file_get_contents($plantilla);
			return $html;
		}

		// Reemplazar tags metidos entre llaves
		private function keys($datamix){
			foreach ($datamix as $key => $value) {
				$keys[] = '{'.$key.'}';
			}
			return $keys;
		}

		// Renderiza la plantilla
		public function rendertpl(){
			$html = self::getHtml($this->filemix);
			$keyname = self::keys($this->datamix);
			return str_replace($keyname, $this->datamix, $html);
		}

		// Renderizar el encabezado de página
		public function renderHeader(string $title){

			return '<header class="page-header">
						<div class="container-fluid">
							<div class="row mt-2 row-nospace">
								<div class="col-md-8">
									<h2 class="no-margin-bottom" style="margin-top: -1.3%;">'.$title.'</h2>
								</div>
								<div class="col-md-3">
									<p class="ansul-slogan">Distribuidor autorizado de:</p>                    
								</div>
								<div class="col-md-1 text-left">
									<img src="img/AnsulLogo.png" class="img-ansul pull-right">
								</div>
							</div>                
						</div>
					</header>';

		}

		// Renderizar el pie de página
		public function renderFooter(string $emp, string $year){

			return '<footer class="main-footer">
			            <div class="container-fluid">
			              <div class="row">
			                <div class="col-sm-6">
			                  <p>'.$emp.' &copy; '.$year.'</p>
			                </div>
			                <div class="col-sm-6 text-right">
			                  <p>Design by <a href="https://bootstrapious.com/p/admin-template" class="external">Bootstrapious</a></p>
			                </div>
			              </div>
			            </div>
			        </footer>';

		}

		// Renderizar Select box. Arreglo de los datos, label de seleccion (EJ Selecciones Ítem) y valor por defecto
		public function renderSelect(array $arr, string $selab, string $dfval){

			$slcData = '<option value="">'.$selab.'</option>';

			foreach ($arr as $key => $value) {
                
                if( ($dfval != '') && ($value['id'] == $dfval) ){
                    $slcData .= '<option value="'.$value['id'].'" selected="selected">'.$value['label'].'</option>';
                } else {
                    $slcData .= '<option value="'.$value['id'].'">'.$value['label'].'</option>';
                }
            }

            return $slcData;

		}

		// No se puede clonar el objeto 
        public function __clone(){
            trigger_error('La clonación no es permitida!.', E_USER_ERROR);
        }

        public function __destruct(){

        }

	}

?>