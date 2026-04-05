<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}
$meta = $_SERVER['MIRROR_ROUTE_MODEL_PAGE'] ?? null;
if (is_array($meta)) {
    $ModelPage = array_merge($meta, $ModelPage);
}
