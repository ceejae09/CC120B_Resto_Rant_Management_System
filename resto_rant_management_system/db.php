<?php
function conn(){
global $conns;

$conns = mysqli_connect("localhost",'root',"",'resto_rant_management_system');


}




?>