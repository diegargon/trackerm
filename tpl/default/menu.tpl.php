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
    <a onClick="show_loading()" href="<?= $tdata['menu_opt_link'] ?>"><div class="border_round menu_element"><?= $tdata['arrow'] ?></div></a>
    <a onClick="show_loading()" href="<?= $cfg['REL_PATH'] . '?page=index' ?>"><div class="border_round menu_element  <?= $tdata['page'] == 'index' ? 'menu_selected' : null; ?>"><?= strtoupper($user->getUsername()) ?></div></a>
    <?php if ($cfg['want_movies'] == 1 && $cfg['want_shows'] == 1) { ?>
        <a onClick="show_loading()" href="?page=library_movies"><div class="border_left menu_element menu_element_join_right  <?= $tdata['page'] == 'library_movies' ? 'menu_selected' : null; ?>"><?= $LNG['L_MOVIES_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=library_shows"><div class="menu_element menu_element_join_left <?= $tdata['page'] == 'library_shows' ? 'menu_selected' : null; ?>"><?= $LNG['L_SHOWS_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=library"><div class="border_right menu_element menu_element_join_left <?= $tdata['page'] == 'library' ? 'menu_selected' : null; ?>"><?= $LNG['L_LIBRARY'] ?></div></a>
    <?php } else { ?>
        <a onClick="show_loading()" href="?page=library"><div class="border_round menu_element menu_element_join_right  <?= $tdata['page'] == 'library' ? 'menu_selected' : null; ?>"><?= $LNG['L_LIBRARY'] ?></div></a>
    <?php } ?>
    <?php if ($cfg['want_movies'] == 1 && $cfg['want_shows'] == 1) { ?>
        <a onClick="show_loading()" href="?page=new_movies"><div class="border_left menu_element menu_element_join_right  <?= $tdata['page'] == 'new_movies' ? 'menu_selected' : null; ?>"><?= $LNG['L_MOVIES_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=new_shows"><div class="menu_element menu_element_join_left  <?= $tdata['page'] == 'new_shows' ? 'menu_selected' : null; ?>"><?= $LNG['L_SHOWS_MIN'] ?></div></a>
        <a onClick="show_loading()" href="?page=news"><div class="border_right menu_element menu_element_join_left  <?= $tdata['page'] == 'news' ? 'menu_selected' : null; ?>"><?= $LNG['L_RELEASED'] ?></div></a>
    <?php } else { ?>
        <a onClick="show_loading()" href="?page=news"><div class="border_round menu_element menu_element_join_right  <?= $tdata['page'] == 'news' ? 'menu_selected' : null; ?>"><?= $LNG['L_RELEASED'] ?></div></a>
    <?php } ?>
    <a onClick="show_loading()" href="?page=wanted"><div class="border_round menu_element  <?= $tdata['page'] == 'wanted' ? 'menu_selected' : null; ?>"><?= $LNG['L_WANTED'] ?></div></a>
    <a onClick="show_loading()" href="?page=torrents"><div class="border_round menu_element  <?= $tdata['page'] == 'torrents' ? 'menu_selected' : null; ?>"><?= $LNG['L_TORRENTS'] ?></div></a>
    <a onClick="show_loading()" href="?page=tmdb"><div class="border_round menu_element  <?= $tdata['page'] == 'tmdb' ? 'menu_selected' : null; ?>"><?= 'TMDB' ?></div></a>
    <a onClick="show_loading()" href="?page=transmission"><div class="border_round menu_element  <?= $tdata['page'] == 'transmission' ? 'menu_selected' : null; ?>"><?= 'T' ?></div></a>
</div>
<?= !empty($tdata['menu_opt']) ? $tdata['menu_opt'] : null ?>
