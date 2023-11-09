<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 26.11.2018
 * Time: 15:33
 */

    require_once ('class/DarkSky.php');

    $darkSky = new DarkSky('https://api.darksky.net/forecast','c5a8beae6b351332dbbb750d1d8e723d','52.520008', '13.404954', '1543186800');

    $darkSky->buildApiUrl();
    $darkSky->jsonDecodeApiUrl();

    echo '<pre>';
        //print_r($darkSky->jsonDecodeApiUrl());
    echo '</pre>';

    if(isset($_POST['datum']) && $_POST['datum'] != null){
        $search_date = $_POST['datum'];
        $seconds = $darkSky->convertDateInSeconds($search_date);
        //echo $seconds . '<br>';
        $human_search_date = $darkSky->convertSecondsInDate($seconds);
        //echo $human_search_date . '<br>';
        $darkSky->setTime($seconds);
        $darkSky->buildApiUrl();
        $darkSky->jsonDecodeApiUrl();
?>
        <main class="container text-center">
            <h1 class="display-1">Wetter am <?php echo $search_date; ?></h1>
            <div class="card p-1" style="max-width: 420px; margin: 0 auto;">
                <h2>Current Forecast</h2>
                <h3 class="display-3">High <?php echo $darkSky->getCertainDateHighTemperature(); ?>&deg;F</h3>
                <h3 class="display-3">Low <?php echo $darkSky->getCertainDateLowTemperature(); ?>&deg;F</h3>
                <p class="lead"><?php echo $darkSky->getCertainDateSummary(); ?></p>
            </div>
        </main>
<?php
    }
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Forecast</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    </head>
    <body>
        <main class="container text-center">
            <h1 class="display-1">Forecast</h1>
            <form class="form-inline" method="post">
                <div class="form-group mx-auto my-5">
                    <label class="sr-only" for="datum">Datum: </label>
                    <input type="date" class="form-control" id="date" placeholder="Datum" name="datum">
                    <button class="btn btn-primary" type="submit">Suche</button>
                </div>
            </form>
            <div class="card p-4" style="max-width: 420px; margin: 0 auto;">
                <h2>Current Forecast</h2>
                <h3 class="display-3"><?php echo $darkSky->getCurrentTemperature(); ?>&deg;F</h3>
                <p class="lead"><?php echo $darkSky->getCurrentSummary(); ?></p>
            </div>
            <ul class="list-group" style="text-align: left;">
                <h1 class="display-1 text-center">Past 30 Days Weather</h1>
                <?php
                $c_date = date("Y-m-d");
                $seconds = $darkSky->convertDateInSeconds($c_date);
                for($i=86400; $i<=864000; $i=$i+86400){
                    $seconds_diff = $seconds - $i;
                    $darkSky->setTime($seconds_diff);
                    $darkSky->buildApiUrl();
                    $darkSky->jsonDecodeApiUrl();
                    echo "<li class='list-group-item'>" . $darkSky->convertSecondsInDate($seconds_diff) . "&nbsp; High " . $darkSky->getCertainDateHighTemperature() . "&deg;F" .
                                                                                                          "&nbsp; Low " . $darkSky->getCertainDateLowTemperature() . "&deg;F" .
                                                                                                          " &nbsp;  " . $darkSky->getCertainDateSummary()  . "</li>";
                }
                ?>
            </ul>
        </main>
    </body>
</html>