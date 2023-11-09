<html>
<head>
<meta charset="utf-8">
<title>Aufgabe</title>
<link rel="shortcut icon" href="img/icon.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="jq/jquery-1.9.1.js"></script>
<script src="jq/jquery.mobile-1.3.0-beta.1.js"></script>
<link href="jq/jquery.mobile-1.3.0-beta.1.css" rel="stylesheet">

<link href="css/aufgabe.css" rel="stylesheet">
</head>

<body>
	<div data-role="page">
		<div data-role="header">
			<?php
				if(isset($_GET['thema'])) {
					$thema= $_GET['thema'];
					echo "<h1>". $thema . "</h1>"; //Thema
				}
			?>
		</div>

		<div data-role="content">
			<div style="text-align: center">
				<?php
				echo "<form action='aufgabe.php?thema=$thema' method='POST' enctype='multipart/form-data'>";
				$fp = fopen("list/tmp/frage.txt", 'a'); //Datei nur für schreiben Öffnen('a')
				ftruncate($fp, 0) // Datei leeren
				?>
				
				<button id="aufgabe" data-inline="true" data-role="button">A&#8594;B</button>
				</form>
			</div>
			<?php
			echo "<form action='antwort.php?thema=$thema' method='POST' enctype='multipart/form-data'>";
			?>
			<div class="pos1">
			<table data-role="table" class="ui-grid-a ui-responsive"> 
				<thead>
					<tr></tr>
				</thead>
				<tbody>
					<tr>
						<td class="column1">
							<?php
								//Datei öffnen
								$file=$_GET['thema'];
								//Datei Zeilenweise in array abspeichern
								$fp = file("list/$file.txt");
								//Anzahl der Zeile in Datei (beginnend von 1)
								$anz_zeile = count($fp);	
								//Zeilen in "Wörte" teilen und abspeichern
								for($e=0; $e<$anz_zeile; $e=$e+1){
									$tmp_array=explode("\t", $fp[$e]);
									$array_sprache2[$e]=$tmp_array[0];	
									$array_aussprache2[$e]=$tmp_array[1];
									$array_sprache1[$e]=$tmp_array[2];
									$array_aussprache1[$e]=$tmp_array[3];
								}
							    /*Frage list erstellen*/
								$f = fopen("list/tmp/frage2.txt", 'a+');	//a+ (lesen + schreiben)
								//Prüfe, ob Datei schon ein mal eingeladet war
								if(isset($_POST['anzahl'])){
									$aufgabe_anz = $_POST['anzahl'];
								} else {
									$aufgabe_anz = 0;
								}
								//Wenn nicht, dann copy erstelle
								if($aufgabe_anz == 0){
									$umkerung = $anz_zeile-1;
									for($a=$umkerung; $a>=0; $a=$a-1){
										fwrite($f, $array_sprache1[$a] .PHP_EOL);	
									}
								}
								//Ablesen der File in array
								$file_out = file("list/tmp/frage2.txt");
								//Anzahl der Zeile in Datei(beginnend von 1)
								$anz_zeile_frage = count($file_out);	
								$umkerung2=$anz_zeile_frage-1;
								fclose($f);
								//Aufgabe erstellen
								$zufall_s1 = $anz_zeile_frage-1;	//starte von letzte Element
								file_put_contents("list/tmp/frage2.txt", $file_out[$zufall_s1] , FILE_APPEND); //schreibt die gewohlte zeile in Datei
								unset($file_out[$zufall_s1]);	//löscht die Zeile
								file_put_contents("list/tmp/frage2.txt", implode("", $file_out)); //der Rest in Datei schreiben
								//Anzahl richtige Antworten
								if (isset($_POST['anz_rich_ant'])){
									$anz_richtige_ant = $_POST['anz_rich_ant'];
								} else {
									$anz_richtige_ant = 0;
								}
								//richtung 2
								$r = "choice2";
								//Wort anzeigen
								echo "<h1 id='word'>" . $array_sprache1[$zufall_s1] . "</h1>";
								echo "<input type='hidden' name='aufgabe' value='$array_sprache1[$zufall_s1]' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='aussprache1' value='$array_aussprache1[$zufall_s1]' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='richtige_a' value='$array_sprache2[$zufall_s1]' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='anzahl_fragen' value='$zufall_s1' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='max_fragen' value='$anz_zeile' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='a_r_a' value='$anz_richtige_ant' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='aufgabe_nummer' value='$aufgabe_anz' style='height:10px; width:100px' readonly>";
								echo "<input type='hidden' name='richtung' value='$r' style='height:10px; width:100px' readonly>";
							?>
						</td>
						<td class="column2">
							<fieldset id="controlgroup" data-role="controlgroup">
								<?php
								//Array erstellen mit 5 Speicher Zellen
								$zufall_s2[] = array(5);
								//Richtige Antwort in array zufügen
								$zufall_s2[0] = $zufall_s1;
								//Andere nicht dupplizierte auswahlmöglichkeiten generieren 
								for($z=1; $z<5; $z=$z+1){
									$t= rand(0,$anz_zeile-1);
									if(!in_array($t ,$zufall_s2)){	//dupplicate aufpassen
											$zufall_s2[$z] = $t;
									} else{
										$z=$z-1;
									}
								}
								//Positionen tauschen
								shuffle($zufall_s2);
								$a_s2[] = array(5);
								$a_s2[0] = $array_sprache2[$zufall_s2[0]];
								$a_s2[1] = $array_sprache2[$zufall_s2[1]];
								$a_s2[2] = $array_sprache2[$zufall_s2[2]];
								$a_s2[3] = $array_sprache2[$zufall_s2[3]];
								$a_s2[4] = $array_sprache2[$zufall_s2[4]];
								//Ausgabe
									echo "<input data-theme='e' type='radio' name='radio-choice-v-2' id='radio-choice-v-2a' value='$a_s2[0]'>";
									echo "<label for='radio-choice-v-2a'>" . $a_s2[0] . "</label>";
									echo "<input data-theme='e' type='radio' name='radio-choice-v-2' id='radio-choice-v-2b' value='$a_s2[1]'>";
									echo "<label for='radio-choice-v-2b'>" . $a_s2[1] . "</label>";
									echo "<input data-theme='e' type='radio' name='radio-choice-v-2' id='radio-choice-v-2c' value='$a_s2[2]'>";
									echo "<label for='radio-choice-v-2c'>" . $a_s2[2] . "</label>";
									echo "<input data-theme='e' type='radio' name='radio-choice-v-2' id='radio-choice-v-2d' value='$a_s2[3]'>";
									echo "<label for='radio-choice-v-2d'>" . $a_s2[3] . "</label>";
									echo "<input data-theme='e' type='radio' name='radio-choice-v-2' id='radio-choice-v-2e' value='$a_s2[4]'>";
									echo "<label for='radio-choice-v-2e'>" . $a_s2[4] . "</label>";
								?>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			</div>
			<!-------------------------------------------------------------------------------------------------------------------------------------------->
			<br>
			<!-- Submit Button -->
			<div style="text-align: center">
				<button id="senden" data-icon="check" data-inline="true" data-role="button">Senden</button>
			</div>
			<br>
			</form>
		</div>
	
		<div data-role="footer" data-position="fixed">
			<div data-role="navbar">
				<ul>
				  <li><a href="index.html"  rel="external" >Auswahl</a></li>
				  <li><a href="statistik.php"  rel="external">Statistik</a></li>
				  <li><a href="setup.php"  rel="external">Setup</a></li>
				</ul>
			</div>
		</div>
	</div>
</body>
</html>