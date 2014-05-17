<?php
function pragma_set($name, $value){
    $value = Database::instance()->quote($value);
    Database::instance()->execute("PRAGMA $name = $value");
}

function pragma_get($name){
    $data = Database::instance()->execute("PRAGMA $name");
    return empty($data) ? NULL : $data[0][$name];
}

function pragma_list($name){
    return Database::instance()->execute("PRAGMA $name");
}
?>
