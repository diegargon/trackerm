<?php
/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<h2><?= $tdata['L_SEARCHTORRENTS'] ?></h2>
<p><?= $tdata['L_SEARCHTORRENTS_DESC'] ?></p>
<form method="post">
    <p><?= $tdata['L_SEARCH'] . ' ' . $tdata['L_MOVIES'] ?>:<input type="text" name="search_shows_torrents"></p>
    <p><?= $tdata['L_SEARCH'] . ' ' . $tdata['L_SHOWS'] ?>:<input type="text" name="search_movie_torrents"></p>
    <p><input type="submit" class="submit_btn" name="search" value="<?= $tdata['L_SEARCH'] ?>"></p>
</form>

