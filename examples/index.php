<?php
    use tomdom\core\App;

    require __DIR__ . '/../vendor/autoload.php';

    echo '<pre>';
    print_r(App::user());
    echo '</pre>';