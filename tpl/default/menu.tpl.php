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
    <!-- <a onClick="show_loading()" href="<?= $tdata['menu_opt_link'] ?>"><div class="menu_element"><?= $tdata['arrow'] ?></div></a> -->
    <a onClick="show_loading()" href="<?= '?page=index' ?>"><div class="menu_element"><?= strtoupper($user->username()) ?></div></a>
    <a onClick="show_loading()" href="?page=ships"><div class="menu_element"><?= $L['L_SHIPS'] ?></div></a>
    <a onClick="show_loading()" href="?page=planets"><div class="menu_element"><?= $L['L_PLANETS'] ?></div></a>
    <a onClick="show_loading()" href="?page=ports"><div class="menu_element"><?= $L['L_PORTS'] ?></div></a>
    <a onClick="show_loading()" href="?page=production"><div class="menu_element"><?= $L['L_PRODUCTION'] ?></div></a>
    <a onClick="show_loading()" href="?page=research"><div class="menu_element"><?= $L['L_RESEARCH'] ?></div></a>
</div>
<?= (!isset($cfg['hide_opt']) || $cfg['hide_opt'] != 1) ? $tdata['menu_opt'] : null; ?>
