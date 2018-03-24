<?php
$assets = __DIR__.'/../src/assets/';

include 'testweb.html';
echo '<script>', include($assets.'inject.php'), '</script>';
