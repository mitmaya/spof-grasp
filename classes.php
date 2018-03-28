<?php
	class Ponto {
		public $lat;
		public $lng;
		public $nome;
		public $carga;
		public $atendido;

		public function __construct($lat, $lng, $nome,$carga,$atendido) {
						  $this->lat = $lat;
						  $this->lng = $lng;
						  $this->nome = $nome;
						  $this->carga = $carga;
						  $this->atendido = $atendido;
		}

		static function cmp_obj($a, $b)
   		{
       		$al = $a->carga;
       		$bl = $b->carga;
       		if ($al >= $bl) {
       			return 0;
   			}
       		return ($al < $bl) ? +1 : -1;
   		}
	}
?> 