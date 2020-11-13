<?php
/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<h2>TheMovieDb.org</h2>
<a href="https://themoviedb.org" target=_blank>The Movie Database</a>
<form method="post">
    <div class="moviedb_container">
        <div class="search_tag"><?= $tdata['L_MOVIE'] ?>:</div><div class="search_box"><input type="text" name="search_movie"></div>
        <div class="search_tag"><?= $tdata['L_SHOW'] ?>:</div><div class="search_box"><input type="text" name="search_shows"></div>
        <div class="search_btn"><input class="submit_btn" type="submit" name="search" value="<?= $tdata['L_SEARCH'] ?> "></div>
    </div>
</form>