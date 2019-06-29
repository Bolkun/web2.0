<?php

include "db.php";

$query = "SELECT * FROM chat ORDER BY id DESC";
$run = $con->query($query);

while($row = $run->fetch_array()) :
    ?>
    <div id="chat_data">
        <span id="name"><?php echo $row['name'] . ': ' ?></span>
        <span id="msg"><?php echo $row['msg']; ?></span>
        <span id="date"><?php echo formatDate($row['date']); ?></span>
    </div>
<?php endwhile; ?>