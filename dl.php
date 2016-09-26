<?php
require_once 'app/init.php';

echo 'aFile version ' . app\lib\Registry::get('config')->version;
