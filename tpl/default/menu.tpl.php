<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class = "main_menu">
    <a onClick="show_loading()" href="<?= $tdata['menu_opt_link'] ?>"><div class="menu_element"><?= $tdata['arrow'] ?></div></a>
    <a onClick="show_loading()" href="<?= $cfg['REL_PATH'] . '?page=index' ?>"><div class="menu_element"><?= strtoupper($user->getUsername()) ?></div></a>
    <?php if ($cfg['want_movies'] == 1 && $cfg['want_shows'] == 1) { ?>
        <a onClick="show_loading()" href="?page=library_movies"><div class="menu_element_join_right"><?= $LNG['L_MOVIES_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=library_shows"><div class="menu_element_join_left"><?= $LNG['L_SHOWS_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=library"><div class="menu_element_join_left"><?= $LNG['L_LIBRARY'] ?></div></a>
    <?php } else { ?>
        <a onClick="show_loading()" href="?page=library"><div class="menu_element_join_right"><?= $LNG['L_LIBRARY'] ?></div></a>
    <?php } ?>
    <?php if ($cfg['want_movies'] == 1 && $cfg['want_shows'] == 1) { ?>
        <a onClick="show_loading()" href="?page=new_movies"><div class="menu_element_join_right"><?= $LNG['L_MOVIES_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=new_shows"><div class="menu_element_join_left"><?= $LNG['L_SHOWS_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=news"><div class="menu_element_join_left"><?= $LNG['L_RELEASED'] ?></div></a>
    <?php } else { ?>
        <a onClick="show_loading()" href="?page=news"><div class="menu_element_join_right"><?= $LNG['L_RELEASED'] ?></div></a>
    <?php } ?>
    <a onClick="show_loading()" href="?page=wanted"><div class="menu_element"><?= $LNG['L_WANTED'] ?></div></a>
    <a onClick="show_loading()" href="?page=torrents"><div class="menu_element"><?= $LNG['L_TORRENTS'] ?></div></a>
    <a onClick="show_loading()" href="?page=tmdb"><div class="menu_element"><?= 'TMDB' ?></div></a>
    <a onClick="show_loading()" href="?page=transmission"><div class="menu_element"><?= 'T' ?></div></a>
</div>
<?= !empty($tdata['menu_opt']) ? $tdata['menu_opt'] : null ?>
