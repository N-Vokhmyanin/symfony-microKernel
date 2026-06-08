<?php
/**
 * Единая точка входа
 */

// Загрузка классов
require_once(dirname(__FILE__) . '/vendor/autoload.php');

(new Core\App())->run();
