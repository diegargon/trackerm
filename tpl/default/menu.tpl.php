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
    <a onClick="show_loading()" href="<?= $tdata['menu_opt_link'] ?>"><div class="menu_element"><?= $tdata['arrow'] ?></div></a>
    <a onClick="show_loading()" href="<?= $tdata['REL_PATH'] . '?page=index' ?>"><div class="menu_element"><?= strtoupper($tdata['username']) ?></div></a>
    <a onClick="show_loading()" href="?page=library"><div class="menu_element_join_right"><?= $tdata['L_LIBRARY'] ?></div></a>
    <a onClick="show_loading()" href="?page=library_movies"><div class="menu_element_join_left"><?= $tdata['L_MOVIES_MIN'] ?></div></a>
    <a onClick="show_loading()" href="?page=library_shows"><div class="menu_element_join_left"><?= $tdata['L_SHOWS_MIN'] ?></div></a>
    <a onClick="show_loading()" href="?page=news"><div class="menu_element_join_right"><?= $tdata['L_RELEASE'] ?></div></a>
    <a onClick="show_loading()" href="?page=new_movies"><div class="menu_element_join_left"><?= $tdata['L_MOVIES_MIN'] ?></div></a>
    <a onClick="show_loading()" href="?page=new_shows"><div class="menu_element_join_left"><?= $tdata['L_SHOWS_MIN'] ?></div></a>
    <a onClick="show_loading()" href="?page=wanted"><div class="menu_element"><?= $tdata['L_WANTED'] ?></div></a>
    <a onClick="show_loading()" href="?page=torrents"><div class="menu_element"><?= $tdata['L_TORRENTS'] ?></div></a>
    <a onClick="show_loading()" href="?page=tmdb"><div class="menu_element"><?= 'TMDB' ?></div></a>
    <a onClick="show_loading()" href="?page=transmission"><div class="menu_element"><?= 'T' ?></div></a>
</div>
<?= (!isset($cfg['hide_opt']) || $cfg['hide_opt'] != 1) ? $tdata['menu_opt'] : null; ?>
