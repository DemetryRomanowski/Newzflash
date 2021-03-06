<?php
require_once './config.php';

use newzflash\Binaries;

$page = new AdminPage();
$bin  = new Binaries(['Settings' => $page->settings]);

$page->title = "Binary Black/Whitelist List";

$binlist = $bin->getBlacklist(false);
$page->smarty->assign('binlist', $binlist);

$page->content = $page->smarty->fetch('binaryblacklist-list.tpl');
$page->render();
