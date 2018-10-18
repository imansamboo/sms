<?php

$db_host = 'localhost';
$db_username = 'myonlinese_whm';
$db_password = 'z3p9GaA1w';
$db_name = 'myonlinese_whm';


$conn = new mysqli($db_host, $db_username, $db_password,$db_name );
$sql = "CREATE TABLE IMANTESTFORCRON(

    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,

    first_name VARCHAR(30) NOT NULL,

    last_name VARCHAR(30) NOT NULL,

    email VARCHAR(70) NOT NULL UNIQUE

)";

if(mysqli_query($conn, $sql)){

    echo "Table created successfully.";

} else{

    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);

}
?>