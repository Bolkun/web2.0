<html>
<head>
<meta charset="utf-8">
<title>Antwort</title>
<link rel="shortcut icon" href="img/icon.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="jq/jquery-1.9.1.js"></script>
<script src="jq/jquery.mobile-1.3.0-beta.1.js"></script>
<link href="jq/jquery.mobile-1.3.0-beta.1.css" rel="stylesheet">
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
	<div id="content_antwort" data-role="content">
		<link href="css/antwort.css" rel="stylesheet">
		<div class="pos">
			<table data-role="table" class="ui-grid-a ui-responsive"> 
				<thead>
					<tr></tr>
				</thead>
				<tbody>
					<tr>
						<td class="column1">
							<?php
							$aufgabe = $_POST["aufgabe"];
							$aussprache1 = $_POST["aussprache1"];
							if(isset($_POST['radio-choice-v-2'])){
								$ausgewehlte_wort = $_POST['radio-choice-v-2'];
							} else {
								$ausgewehlte_wort ="undefined";
							}
							$richtige_antwort = $_POST["richtige_a"];
							echo "<h1 id='word'>" . $aufgabe . "</h1>";
							echo "<p id='aussprache'>" . $aussprache1 . "</p>";
							if (isset($_POST['a_r_a'])){
								$anz_richtige_antworte = $_POST['a_r_a'];
							}
							//Richtige oder Falsche Antwort
							if($ausgewehlte_wort == $richtige_antwort){
								$anz_richtige_antworte = $anz_richtige_antworte + 1;
								echo "<p id='r_antwort'>" . $ausgewehlte_wort . "</p>";
								echo "<style>.column1 { background-color: green;}</style>";
							} else {
								echo "<p id='f_antwort'>" . $ausgewehlte_wort . "</p>";
								echo "<p id='r_antwort'>" . $richtige_antwort . "</p>";
								echo "<style>.column1 { background-color: red;}</style>";
							}
							/*Statistik Datei*/
							if(isset($_POST['max_fragen'])){
								$max_fragen = $_POST['max_fragen'];
							}
							if(isset($_POST['aufgabe_nummer'])){
								$aufgabe_nummer = $_POST['aufgabe_nummer'];
							}
							$s = fopen("list/statistik/$thema.txt", "a");
							$sp = file("list/statistik/$thema.txt");				//Datei Zeilenweise in array abspeichern
							$size = count($sp);
							if($aufgabe_nummer == 0){
								file_put_contents("list/statistik/$thema.txt", '');	//leeren für neu Statistik
								fwrite($s, $thema .PHP_EOL);
								fwrite($s, "Richtig: " .$anz_richtige_antworte ."/" .$max_fragen .PHP_EOL);
							} else {
								file_put_contents("list/statistik/$thema.txt", $sp[$size-1] , FILE_APPEND); 
								unset($sp[$size-1]);								//Zeile löschen 
								file_put_contents("list/statistik/$thema.txt", implode("", $sp));
								fwrite($s, "Richtig: " .$anz_richtige_antworte ."/" .$max_fragen .PHP_EOL);	 //"Aktualisierung der Anzahl von richtigen Antworten"
							}
							fclose($s);
							?>
							<br>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<br>
		<!-- Weiter Button -->
		<div style="text-align: center">
			<?php
			if(isset($_POST['richtung']) && $_POST['richtung'] == "choice2"){
				echo "<form action='aufgabe2.php?thema=$thema' method='POST' enctype='multipart/form-data'>";
				$aufgabe_anz = 0;
				$aufgabe_anz = $aufgabe_anz + 1;
				echo "<input type='hidden' name='anzahl' value='$aufgabe_anz' style='height:10px; width:100px' readonly>";
				echo "<input type='hidden' name='anz_rich_ant' value='$anz_richtige_antworte' style='height:10px; width:100px' readonly>";
				//zeige Button bis der letzte Aufgabe eintritt
				$anzahl_aufgaben = $_POST["anzahl_fragen"];
				if($anzahl_aufgaben != 0){
					echo "<button id='weiter_button' data-icon='forward' data-inline='true'>Weiter</button>";
				}
				echo "</form>";
			} else {
				echo "<form action='aufgabe.php?thema=$thema' method='POST' enctype='multipart/form-data'>";
				$aufgabe_anz = 0;
				$aufgabe_anz = $aufgabe_anz + 1;
				echo "<input type='hidden' name='anzahl' value='$aufgabe_anz' style='height:10px; width:100px' readonly>";
				echo "<input type='hidden' name='anz_rich_ant' value='$anz_richtige_antworte' style='height:10px; width:100px' readonly>";
				//zeige Button bis der letzte Aufgabe eintritt
				$anzahl_aufgaben = $_POST["anzahl_fragen"];
				if($anzahl_aufgaben != 0){
					echo "<button id='weiter_button' data-icon='forward' data-inline='true'>Weiter</button>";
				}
				echo "</form>"; 				
			}
			
			?>
		</div>
		<br>
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
