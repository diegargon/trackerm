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
<form method="GET" action="">
    <div class="moviedb_container">
        <div class="search_tag"><?= $tdata['L_MOVIE'] ?>:</div><div class="search_box"><input type="text" name="search_movie_torrents"></div>
        <div class="search_tag"><?= $tdata['L_SHOW'] ?>:</div><div class="search_box"><input type="text" name="search_shows_torrents"></div>
        <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
        <div class="search_btn"><input class="submit_btn" type="submit" value="<?= $tdata['L_SEARCH'] ?>"></div>
    </div>    
</form>

