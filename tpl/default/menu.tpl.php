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
    <a href = "<?= $tdata['REL_PATH'] ?>"><div class = "menu_element"><?= $tdata['L_HOME'] ?></div></a>
    <a href = "?page=biblio"><div class = "menu_element"><?= $tdata['L_LIBRARY'] ?></div></a>
    <a href = "?page=news"><div class = "menu_element"><?= $tdata['L_RELEASE'] ?></div></a>
    <a href = "?page=torrents"><div class = "menu_element"><?= $tdata['L_TORRENTS'] ?></div></a>
    <a href = "?page=tmdb"><div class = "menu_element"><?= strtoupper($tdata['search_db']) ?></div></a>
</div>