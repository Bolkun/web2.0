<!doctype html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>Game of Life</title>
</head>
<body>
	<?php
		echo "<h1>John Conway's \"Game of Life\"</h1>";
		echo "<p style='color: brown;'>(Hinweis: Alle Seiten sind im Spiel abgegrenzt!)<p>";
		echo "<p>Warning: Only Backend! No Frontend</p>";
	?>
	<form method="post" action="game.php">
		N <input type="number" name="zeile" min="2" max="50"> <!-- checked automatik -->
		x M <input type="number" name="spalte" min="2" max="50"><br><br>
		Stufe <input type="number" name="stufe" min="1" max="1000"><br><br>
		<input type="submit" value="Play" name="submit">
	</form>
</body>
</html>