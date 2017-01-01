<?php
require_once 'app/init.php';

echo 'aFile version ' . \lib\Registry::get('config')->version;
