<!doctype html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>Game</title>
</head>
<body>
	<?php
		define("DEAD", 0);
		define("ALIVE", 1);
		#Post Grid width, height
		if(isset($_POST['submit'])){
			if(!empty($_POST['zeile'])){
				$zeile = $_POST['zeile'];
			}else{
				echo "Go back and fill N";
				exit;
			}
			if(!empty($_POST['spalte'])){
				$spalte = $_POST['spalte'];
			}else{
				echo "Go back and fill M";
				exit;
			}
			if(!empty($_POST['stufe'])){
				$stufe = $_POST['stufe'];
			}else{
				echo "Go back and fill Stufe";
				exit;
			}
			echo "N=$zeile x M=$spalte<br>";
			echo "Stufe: $stufe<br><br>";
		}
		#Cell
		class Cell {
			
			public $zeile;
			public $spalte;
			
			public function __construct($width, $height) {	//"GameOfLifeData" Konstruktor
				$this->zeile = $width;
				$this->spalte = $height;
		    }
			
			public function FillGridRandom(){
				$zeile = $this->zeile;
				$spalte = $this->spalte;
				for($i=0; $i<$zeile; $i++){
					for($j=0; $j<$spalte; $j++){
						$zustand = rand(DEAD,ALIVE);	//0-tot, 1-lebendig
						$grid[$i][$j] = $zustand;
					}		
				}
				return $grid;
			}
			
			public function ShowGrid($gridArray){
				$grid = $gridArray;
				$zeile = $this->zeile;
				$spalte = $this->spalte;
				for($i=0; $i<$zeile; $i++){
					for($j=0; $j<$spalte; $j++){
						echo $grid[$i][$j];
					}		
					echo "<br>";
				}
			}
			
			public function MakeCellAlive($gridArray){
				$grid = $gridArray;
				$zeile = $this->zeile;
				$spalte = $this->spalte;
				$count = 0;
				$newGrid2 = array(array());
				for($i=0; $i<$zeile; $i++){
					for($j=0; $j<$spalte; $j++){
						$newGrid2[$i][$j] = 0;	//Fill mit 0-en
						if($grid[$i][$j] == 0){
							//8-te Nachbarschaft
							if(($i-1)>-1){	//oben
								if($grid[$i-1][$j] == 1)
									$count++;
							}
							if(($i+1)<$zeile){	//unten
								if($grid[$i+1][$j] == 1)
									$count++;
							}
							if(($j-1)>-1){	//links
								if($grid[$i][$j-1] == 1)
									$count++;
							}
							if(($j+1)<$spalte){	//rechts
								if($grid[$i][$j+1] == 1)
									$count++;
							}
							if(($i-1)>-1 && ($j-1)>-1){	//oben links
								if($grid[$i-1][$j-1] == 1)
									$count++;
							}
							if(($i-1)>-1 && ($j+1)<$spalte){	//oben rechts
								if($grid[$i-1][$j+1] == 1)
									$count++;
							}
							if(($i+1)<$zeile && ($j+1)<$spalte){	//unten rechts
								if($grid[$i+1][$j+1] == 1)
									$count++;
							}
							if(($i+1)<$zeile && ($j-1)>-1){	//unten links
								if($grid[$i+1][$j-1] == 1)
									$count++;
							}
						}
						if($count==3)
							$newGrid2[$i][$j] = ALIVE;
						//Nachbarn zurück setzen
						$count = 0;
					}		
				}
				return $newGrid2;
			}
			
			public function MakeCellDead($gridArray){
				$grid = $gridArray;
				$zeile = $this->zeile;
				$spalte = $this->spalte;
				$count = 0;
				$newGrid1 = array(array());
				for($i=0; $i<$zeile; $i++){
					for($j=0; $j<$spalte; $j++){
						$newGrid1[$i][$j] = 0;	//Fill mit 0-en
						if($grid[$i][$j] == 1){
							//8-te Nachbarschaft
							if(($i-1)>-1){	//oben
								if($grid[$i-1][$j] == 1)
									$count++;
							}
							if(($i+1)<$zeile){	//unten
								if($grid[$i+1][$j] == 1)
									$count++;
							}
							if(($j-1)>-1){	//links
								if($grid[$i][$j-1] == 1)
									$count++;
							}
							if(($j+1)<$spalte){	//rechts
								if($grid[$i][$j+1] == 1)
									$count++;
							}
							if(($i-1)>-1 && ($j-1)>-1){	//oben links
								if($grid[$i-1][$j-1] == 1)
									$count++;
							}
							if(($i-1)>-1 && ($j+1)<$spalte){	//oben rechts
								if($grid[$i-1][$j+1] == 1)
									$count++;
							}
							if(($i+1)<$zeile && ($j+1)<$spalte){	//unten rechts
								if($grid[$i+1][$j+1] == 1)
									$count++;
							}
							if(($i+1)<$zeile && ($j-1)>-1){	//unten links
								if($grid[$i+1][$j-1] == 1)
									$count++;
							}
						}
						if($count==0 || $count==1)
							$newGrid1[$i][$j] = DEAD;
						if($count>=4)
							$newGrid1[$i][$j] = DEAD;
						if($count==2 || $count==3)
							$newGrid1[$i][$j] = ALIVE;
						//Nachbarn zurück setzen
						$count = 0;
					}		
				}
				return $newGrid1;
			}
			public function Generation($gridArray1, $gridArray2){
				$grid1 = $gridArray1;
				$grid2 = $gridArray2;
				$zeile = $this->zeile;
				$spalte = $this->spalte;
				for($i=0; $i<$zeile; $i++){
					for($j=0; $j<$spalte; $j++){
						$grid1[$i][$j] += $grid2[$i][$j];
					}
				}
				return $grid1;
			}
			public function TheEnd($gridArray){
				$grid = $gridArray;
				$zeile = $this->zeile;
				$spalte = $this->spalte;
				for($i=0; $i<$zeile; $i++){
					for($j=0; $j<$spalte; $j++){
						if($grid[$i][$j] == 1)
							return false;
					}
				}
				return true;
			}
		}
		//Aufbau
		$game = new Cell($zeile, $spalte);	//Kontruktor Aufruf
		$gridArray = $game->FillGridRandom();		
		$game->ShowGrid($gridArray);
		for($i=1; $i<$stufe+1; $i++){
			//check 1
			$newGrid1 = $game->MakeCellDead($gridArray);
			//check2
			$newGrid2 = $game->MakeCellAlive($gridArray);
			//generation
			echo "<br><strong>Generation $i</strong><br>";
			$newGrid = $game->Generation($newGrid1, $newGrid2);
			//Ausgabe
			$game->ShowGrid($newGrid);
			//Spiel beenden falls keine lebendige Zelle
			$end = $game->TheEnd($newGrid);
			if($end == true){
				echo "<p style='color: red;'>Nach Generation $i bleiben alle Zellen tot!</p>";
				echo "<p style='color: red;'>Spiel ist zu Ende!</p>";
				exit;
			}
			//aktualisieren $gridArray für nächste Generation
			$gridArray = $newGrid;
		}
	?>
</body>
</html>