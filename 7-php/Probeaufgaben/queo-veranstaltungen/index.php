<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 17.12.2018
 * Time: 08:18
 */

require_once ('class/Db.php');

$db = new Db();
$db->fillEvents();
$db->getAllEvents();

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Veranstaltungen</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body>
<main class="container text-center">
    <h1 class="display-1">Veranstaltungen</h1>
    <form class="form-inline" method="post">
        <div class="form-group mx-auto my-5">
            <label class="sr-only" for="kategorie">Kategorie: </label>
            <select class="form-control" name="kategorieOption">
                <?php
                    foreach ($db->getKategorien() as $kategorie){
                        echo "<option value='$kategorie'>$kategorie</option>";
                    }
                ?>
            </select>
            <button class="btn btn-primary" type="submit" name="suche">Suche</button>
        </div>
    </form>
    <?php
        if(isset($_POST['suche'])){
            $kategorieOption = $_POST['kategorieOption'];
            $data = $db->getDataToKategorie($kategorieOption);
            $anz_veranstaltungen = count($data['id']);
            for($i=0; $i<$anz_veranstaltungen; $i++){
                ?>
                <div class="card p-4" style="max-width: 1000px; margin: 0 auto;">
                    <h2><?php echo "Kategorie: " . $kategorieOption; ?></h2>
                    <h3 class="display-3"><?php echo $data['titel'][$i]; ?></h3>
                    <p class="lead"><?php echo "Ort: " . $data['ort'][$i]; ?></p>
                    <p class="lead"><?php echo "Zeit: " . $data['datetime'][$i]; ?></p>
                    <p class="lead"><?php echo "Link: <a href='" . $data['link'][$i] . "'>mehr dazu.</a>" ?></p>
                </div>
                <?php
            }
        } else {
            $data = $db->getAllEvents();
            $anz_veranstaltungen = count($data['id']);
            for($i=0; $i<$anz_veranstaltungen; $i++) {
                ?>
                <div class="card p-4" style="max-width: 1000px; margin: 0 auto;">
                    <h2><?php echo "Kategorie: " . $data['kategorie'][$i]; ?></h2>
                    <h3 class="display-3"><?php echo $data['titel'][$i]; ?></h3>
                    <p class="lead"><?php echo "Ort: " . $data['ort'][$i]; ?></p>
                    <p class="lead"><?php echo "Zeit: " . $data['datetime'][$i]; ?></p>
                    <p class="lead"><?php echo "Link: <a href='" . $data['link'][$i] . "'>mehr dazu.</a>" ?></p>
                </div>
                <?php
            }
        }
    ?>
</main>
</body>
</html>