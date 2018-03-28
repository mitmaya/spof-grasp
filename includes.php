 <?php

	function union(array $files,$name)
	{
		$union = NULL;

		for($i=0;$i<count($files);$i++)
		{
			if(file_exists($files[$i]))
			{
				$union .= file_get_contents($files[$i])."\n";
			}
		}

		$fp = fopen($name,"w");

		fwrite($fp,$union);

		fclose($fp);
	}

	function array_search_multi($busca, $arrays)
	{
   		foreach($arrays as $array)
   		{
       	   	if($i = array_search($busca,$array) !== false)
       	   	{
                return $i;
	        }
       	}
    	return false;

	}

	function distancia($lat1, $lon1, $lat2, $lon2) {

		$lat1 = deg2rad($lat1);
		$lat2 = deg2rad($lat2);
		$lon1 = deg2rad($lon1);
		$lon2 = deg2rad($lon2);

		$latD = $lat2 - $lat1;
		$lonD = $lon2 - $lon1;

		$dist = 2 * asin(sqrt(pow(sin($latD / 2), 2) +
		cos($lat1) * cos($lat2) * pow(sin($lonD / 2), 2)));
		$dist = $dist * 6371;
		return number_format($dist, 2, '.', '');
	}


	function monta_mapa($s,$p,$c)
	{
		$fp = fopen("mapa2.html", "w");

		for($i = 0; $i <= count($s)-1; $i++)  	
		{			
			fwrite($fp,"{lat: ".$s[$i]->lat.", lng: ".$s[$i]->lng.", nome: \"".
				$s[$i]->nome."\", tipo: 1},\n");
		}

		//Marca demandas no MAPA
		for($i = 0; $i <= count($p)-1; $i++)  	
		{	
			$string =  serialize($s);
 	
 			if(strpos($string, $p[$i]->nome) == false)
			{
				fwrite($fp,"{lat: ".$p[$i]->lat.", lng: ".$p[$i]->lng.", nome: \"".
					$p[$i]->nome."\", tipo: 0},\n");
			}
		}

		fwrite($fp,"];\n");
		fwrite($fp,"var raio = ".($c*1000).";\n");

		union(array("mapa1.html","mapa2.html","mapa3.html"),"mapa.html");
	}
?> 