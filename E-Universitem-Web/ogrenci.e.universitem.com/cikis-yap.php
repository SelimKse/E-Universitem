<?php

try {
    session_start();

    session_unset();

    session_destroy();

    header("Location: http://e-universitem.com/");

    die();

} catch (PDOException $e) {
    echo "Çıkış Başarısız" + $e;
}

?>