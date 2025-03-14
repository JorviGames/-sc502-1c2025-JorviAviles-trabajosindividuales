<?php

require('../login.php');

if(login("user1@gmail.com", "123456")){
    echo"login exitoso". PHP_EOL;
} else {
    echo "login incorrecto". PHP_EOL;
}

if(login("user1@gil.com", "1456")){
    echo "login exitoso". PHP_EOL;
}else {
    echo "login incorrecto". PHP_EOL;
}

