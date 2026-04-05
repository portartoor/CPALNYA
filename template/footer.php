<?
require_once DIR.'/core/main.php';
$Template = new Render();
$TemplatesPath = $Template->TemplatePath();

include ($TemplatesPath.'footer.php');
?>
