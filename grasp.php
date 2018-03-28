// Programa   : GRASP.PHP - Aplicação de GRASP para posicionamento de torres de distribuição de sinal de internet
// Autores    : Miter Mayer / Italo Matias / Dalessandro Soares Vianna
// Data       : 18/01/2016

<!DOCTYPE html>
<html>
	<head>
		<title>GRASP-Pmedianas</title>
		<link type='text/css' rel='stylesheet' href='style.css'/>
	</head>
	
	<body>
	  <p>
	  <?php
	  	include("includes.php");
	  	include("classes.php");
	  	include("demandas.php");

		//Numero de facilidades
		$facilidades = 5;

		//Raio de cobertura das torres de radiotransmissão
		$cobertura = 1.5;

		//Valor da variavel alpha utilzida no calculo da LRC	
		$alpha = .1;

		//Array da solução inicial
		$solucaoInicial = array();

		//Array com valroes coletados da solução
		$cargas = array();

		//Criterio de parada
		$parada = 10000;

   		// Loop inicial com numero de facilidades
   		for($j = 0; $j <= $facilidades-1; $j++) 
   		{	
			//Cria a matriz de distâncias
			for($i = 0; $i <= count($ponto)-1; $i++)
			{
				for($k = 0; $k <= count($ponto)-1; $k++)
				{
					$dist = distancia($ponto[$i]->lat, $ponto[$i]->lng, $ponto[$k]->lat, $ponto[$k]->lng);
					
					//echo $i."/".$k." - ".$dist."    ";
					
					if ($dist <= $cobertura and $dist > 0 and $ponto[$k]->atendido == false)
					{
						$ponto[$i]->carga = $ponto[$i]->carga +1;
						$ponto[$i]->atendido = true;
					}	
				}
			}	

			//Ordena o array de pontos em ordem decrescente	de carga
			usort($ponto, array ("Ponto", "cmp_obj"));

			//Calculo do limiar	
			$pontoMaiorCarga = $ponto[0]->carga;
			$pontoMenorCarga = $ponto[count($ponto)-1]->carga;
			$limiar = $pontoMaiorCarga - ($alpha * ($pontoMaiorCarga - $pontoMenorCarga)); 
			
			//Cria um array da LRC;
			$lcr = array();

			for($i = 0; $ponto[$i]->carga >= $limiar; $i++)  	
			{
				$lrc[$i] = clone $ponto[$i];
			}

			if ($j == 0) 
			{
				$valorAleatorio = rand(0, count($lrc)-1);
				$solucaoInicial[$j] = clone $lrc[$valorAleatorio];
			} 
			else {

				$achei = false;

				$string =  serialize($solucaoInicial);

				while ($achei == false)
				{
					//Escolhe ponto aleatoriamente dentre os candidatos
					$valorAleatorio = rand(0, count($lrc)-1);

					$m = 0;

					while ($m <= count($solucaoInicial)-1)
					{	
						$dist = distancia($lrc[$valorAleatorio]->lat, $lrc[$valorAleatorio]->lng,$solucaoInicial[$m]->lat, $solucaoInicial[$m]->lng);

						$m++;
					}	

	 				if(strpos($string, $lrc[$valorAleatorio]->nome) == false)
					{
		 				//Adiciona o ponto candidato na solucao inicial
	 					$solucaoInicial[$j] = clone $lrc[$valorAleatorio];
	 					$achei = true;
		     		}
		     	}
		    }		
 			
 			//Apaga o array LRC
 			unset($lrc);

 			//Zera a carga dos pontos atendidos para para novo calculo no proximo loop
 			for($i = 0; $i <= count($ponto)-1; $i++)  
			{
				$ponto[$i]->carga = 0;
				//$ponto[$i]->atendido = false;
			}
		}	

		echo "Solucao Inicial:<br>";

		$carga_total = 0;
		for($i = 0; $i <= count($solucaoInicial)-1; $i++)  	
		{			
			echo $solucaoInicial[$i]->nome."<br>";
			$carga_total = $carga_total + $solucaoInicial[$i]->carga;
		}
		
		$cargas [0] = $carga_total;

		echo "Carga Total: ".$carga_total."<br><br>";

		// monta_mapa($solucaoInicial,$ponto,$cobertura);	

		// *** AQUI COMEÇA A BUSCA LOCAL ** //
		// ******************************** //

		//numero de iterações
		$x = 0;
		$parar = 0;
		$melhor_carga = $carga_total;
		$nova_carga = 0;
		$best_solucao = array();

		$novaSolucao = $solucaoInicial;

		while ($parar <= $parada)
		{
			$y = 0;

			while ($y <= count($ponto)-1)
			{
				$parar++;

				if ($y >= count($ponto)-1)
				{
			     	// echo "***".$y."***cima<br>";
   					break;
   				}	

				$string =  serialize($novaSolucao);

				unset($novaSolucao[$x]);
		
				//Verificar se quem vai entrar já não esta na solucao inicial, caso esteja avança

				$achei = false;

				while ($achei == false)
				{
	 				if(strpos($string, $ponto[$y]->nome) == false)
					{
		 				//Adiciona o ponto na solucao inicial
						$novaSolucao[$x] = clone($ponto[$y]);
	 					$achei = true;
		
			            // echo " - entra: ".$y." - ".$ponto[$y]->nome."<br>"; 

		     		} 
		     		else
		     		{
						// echo " erro: ".$y. " ponto: ".$ponto[$y]->nome." / ";
	
						$y++; 	

						if ($y >= count($ponto)-1)
						{
					     	// echo "***".$y."***baixo<br>";

							$string2 =  serialize($novaSolucao);

							$achei2 = false;

							while ($achei2 == false)
							{
								$valorAleatorio = rand(0, count($ponto)-1);

	 							if (strpos($string2, $ponto[$valorAleatorio]->nome) == false)
								{
									$novaSolucao[$x] = clone($ponto[$valorAleatorio]);
									// echo " - entra: ".$valorAleatorio." - ".$ponto[$valorAleatorio]->nome."<br>"; 
									$achei2 = true;
									break;
								}
							}		

		   					break;
		   				}	

	     				continue;
		     		}
		     	}
			    		
	           	// echo "Nova Solucao Inicial:<br>";

	 			//Zera a carga dos pontos da solucao inicial  para novo calculo no proximo loop
	 			for($i = 0; $i <= count($novaSolucao)-1; $i++)  
				{
					//echo "zera carga: ".$solucaoinicial[$i]->nome; 
					$novaSolucao[$i]->carga = 0;

					//echo $novaSolucao[$i]->nome."<br>";
				}
				//echo "<br>";

	 			//Zera atendimentos dos pontos  para novo calculo da carga 	
	 			for($i = 0; $i <= count($ponto)-1; $i++)  
				{
					$ponto[$i]->atendido = false;
				}

				//Cria nova matriz de distâncias
				for($i = 0; $i <= count($novaSolucao)-1; $i++)
				{
					for($k = 0; $k <= count($ponto)-1; $k++)
					{
						$dist = distancia($novaSolucao[$i]->lat, $novaSolucao[$i]->lng, $ponto[$k]->lat, $ponto[$k]->lng);
						
						if ($dist <= $cobertura and $dist > 0 and $ponto[$k]->atendido == false)
						{
							$novaSolucao[$i]->carga = $novaSolucao[$i]->carga +1;
							$ponto[$k]->atendido = true;
						}	
					}
				}

				$nova_carga = 0;

				for($i = 0; $i <= count($novaSolucao)-1; $i++)
				{
					$nova_carga = $nova_carga + $novaSolucao[$i]->carga;
				}

				$cargas [$parar] = $carga_total;

				echo "Iteracao: ".$parar." - ".$nova_carga."<br>";

				if ($nova_carga > $melhor_carga)
				{
					$melhor_carga = $nova_carga;

					unset($best_solucao);

					for($i = 0; $i <= count($novaSolucao)-1; $i++)
					{
						$best_solucao[$i] = clone($novaSolucao[$i]);
					}

					$y=0;
					$x=0;

					break;	
				}	
			}

			$x++;

			if ($x >= $facilidades)
			{
				$x=0;
			}
		}

		echo "best solucao:<br>";
		echo "Carga Total: ".$melhor_carga."<br><br>";

		for($i = 0; $i <= count($best_solucao)-1; $i++)
		{
			echo $best_solucao[$i]->nome."<br>";
		}	

		echo "parar: ".$parar."<br>";

		monta_mapa($best_solucao,$ponto,$cobertura);	

		unset($solucaoInicial);
		unset($best_solucao);
		unset($novaSolucao);
		unset($cargas);
		unset($j);

		?> 

		<br>
		<a href="mapa.html" target="_blank">Visualizar pontos no mapa</a>
		</p>
	</body>
</html>

