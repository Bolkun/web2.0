<html>
<head>
<meta charset="utf-8">
<title>Setup</title>
<link rel="shortcut icon" href="img/icon.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="jq/jquery-1.9.1.js"></script>
<!--<script src="jq/jquery.mobile-1.3.0-beta.1.js"></script>-->
<link href="jq/jquery.mobile-1.3.0-beta.1.css" rel="stylesheet">

<link href="css/setup.css" rel="stylesheet">
</head>
    <body>
		<div data-role="page">
			<div id="header">
				<h1>Setup</h1> <!--Page Header-->
			</div>
		
		<div data-role="content">
			<br><br><br><br>
			<hr class="doppelt">
			<br>
			<?php 
			    if(isset($_FILES['text'])){
					$uploadOk = 1;
					$file_name = $_FILES['text']['name'];
					$file_tmp =  $_FILES['text']['tmp_name'];
					$file_size = $_FILES['text']['size'];	//in bytes
					$file_dir = "list/";
					$char_err = array("<", ">");
					//Prüfen ob extension passt
					if(preg_match("/\.txt\z/i", $file_name)){
						$uploadOk = 1;
					} else {
						echo "<p align=center>Fehler: nur TXT Dateien sind erlaubt.</p>";
						$uploadOk = 0;
					}
					//Prüfe Dateigröße
					if($uploadOk == "1"){
						if($file_size > 2097152) {
							echo "<p align=center>Dateigröße darf 2MB nicht überschreiten.</p>";
							$uploadOk = 0;
						}
					}
					//Prüfung auf unerlaubte Zeichen + Anzahl der Zeilen
					if($uploadOk == "1"){
						$f = fopen($file_tmp, "r");
						$zeile = 0;
						//Jeder Zeile bis Ende der Datei dürchgehen
						while(!feof($f)) { 
							$zeile = $zeile + 1;
							if(preg_match("(<|>)", fgets($f))){
								echo "<p align=center>Datei enthält unerlauble Zeichen.</p>";
								$uploadOk = 0;
								break;
							}
						}
						if($zeile < 5){
							echo "<p align=center>Datei muss mindestens 5 Zeilen enthalten.</p>";
							$uploadOk = 0;
						}
						fclose($f);
					}
					//Prüfung auf 3 Tabulatoren in jeder Zeile
					if($uploadOk == "1"){
						$fp = fopen($file_tmp, "r");
						$zeile = 0;
						//Jeder Zeile bis Ende der Datei dürchgehen
						while(!feof($fp)) { 
							$zeile = $zeile+1;
							if(preg_match("/\t.*\t.*\t/", fgets($fp))){
								$uploadOk = 1;
							} else {
								echo "<p align=center>Zeile '$zeile' muss genau 3 Tabulatoren enthalten.</p>";	//mehrere werden einfach ignoriert
								$uploadOk = 0;
								break;
							}
						}
					}
					//Alles ok -> senden
					if($uploadOk == "1"){
						move_uploaded_file($file_tmp, $file_dir .$file_name);
						echo "<p align=center>Datei <strong>". $file_name ."</strong> erfolgreich geladet.</p>";
					}else{
						echo "<p align=center>Datei nicht geladet.</p>";
					}
				}
			?>
			
			<form action="" method="POST" enctype="multipart/form-data" style="text-align: center">
				<p><input type="file" name="text" /></p>
				<input type="submit" value="Datei hochladen" data-icon="custom" data-inline="true" />
			</form>
			<hr class="doppelt">
			<br>
		</div>
		
		<div id="footer">
			<ul>
			  <li style="float: left; width: 50%"><a href="index.html"  rel="external">Auswahl</a></li>
			  <li style="float: left; width: 50%"><a href="statistik.php"  rel="external">Statistik</a></li>
			</ul>
		</div>
		</div>
    </body>
</html>