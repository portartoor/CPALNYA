<?
define('DIR', $_SERVER['DOCUMENT_ROOT'].'/');
require_once DIR.'/core/config.php';
require_once DIR.'/core/libs/bootstrap_install.php';
portcore_bootstrap_if_needed();
if (!empty($AppTimezone) && is_string($AppTimezone)) {
	date_default_timezone_set($AppTimezone);
} else {
	date_default_timezone_set('Europe/Moscow');
}
require_once DIR.'/core/main.php';

$Render = new Render();
$Render->Get();
?>
