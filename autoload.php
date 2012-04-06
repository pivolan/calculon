<?php
/**
 Реализовал исходя из примера.
 Чем этот способ хуже, лучше, уместнее - надо разбираться.
 */
spl_autoload_register ('autoload');
function autoload ($className) {
    $fileName = $className . '.php';
    include  $fileName;
}