<html>
<head>
<meta charset="utf-8">
<title>Statistik</title>
<link rel="shortcut icon" href="img/icon.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="jq/jquery-1.9.1.js"></script>
<script src="jq/jquery.mobile-1.3.0-beta.1.js"></script>
<link href="jq/jquery.mobile-1.3.0-beta.1.css" rel="stylesheet">

<link href="css/statistik.css" rel="stylesheet">
</head>
<body>
	<div data-role="page">
		<div id="header" data-role="header">
			<h1 id="h1">Statistik</h1> <!--Page Header-->
		</div>

		<div data-role="content">
			<div class="statistik">
				</br>
				<?php
				//Ordner öffnen
				$folder = opendir("list/statistik");
				$schalter = 0;
				//Alle Dateien dürchgehen
				while (($thema = readdir($folder)) != "") {
					if (preg_match("/\.txt\z/i", $thema)){	//zeile die nur auf .txt enden ausgeben
						$fp = file("list/statistik/$thema");
						$thema = preg_replace("/\.txt/i", "", $thema);	//.txt löschen
						$anz_zeile = count($fp);
						for($x=0; $x<$anz_zeile; $x=$x+1){
							echo "<p class='word'>" .$fp[$x] ."</p>";
						}
						$schalter = 1;
					} 
				}
				if ($schalter == 0){
					echo "<p class='word'>Statistik nicht verfügbar</p>";
					echo "<p class='word'>Wählen sie eine Liste aus!</p>";
				}
				//Ordner schließen
				$folder = closedir($folder);
				?>
				</br>
			</div>
		</div>
	</div>
  
    <div id="footer">
        <ul>
          <li style="float: left; width: 50%"><a href="index.html"  rel="external">Auswahl</a></li>
          <li style="float: left; width: 50%"><a href="setup.php"  rel="external">Setup</a></li>
        </ul>
    </div>
</body>
</html>