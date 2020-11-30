<?php
/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class = "main_menu">
    <a onClick="show_loading()" href="<?= $tdata['REL_PATH'] ?>"><div class="menu_element"><?= strtoupper($cfg['profiles'][$cfg['profile']]) ?></div></a>
    <a onClick="show_loading()" href="?page=biblio"><div class="menu_element"><?= $tdata['L_LIBRARY'] ?></div></a>
    <a onClick="show_loading()" href="?page=news"><div class="menu_element"><?= $tdata['L_RELEASE'] ?></div></a>
    <a onClick="show_loading()" href="?page=wanted"><div class="menu_element"><?= $tdata['L_WANTED'] ?></div></a>
    <a onClick="show_loading()" href="?page=torrents"><div class="menu_element"><?= $tdata['L_TORRENTS'] ?></div></a>
    <a onClick="show_loading()" href="?page=tmdb"><div class="menu_element"><?= 'TMDB' ?></div></a>
    <a onClick="show_loading()" href="?page=transmission"><div class="menu_element"><?= 'T' ?></div></a>
</div>