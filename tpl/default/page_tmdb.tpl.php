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
<div class="moviedb_container">
    <div class="search_container">
        <form method="GET" action="">
            <div class="search_tag"><?= $tdata['L_MOVIE'] ?>:</div><div class="search_box"><input type="text" name="search_movie"></div>
            <div class="search_tag"><?= $tdata['L_SHOW'] ?>:</div><div class="search_box"><input type="text" name="search_shows"></div>
            <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
            <div class="search_btn"><input class="submit_btn" type="submit"  value="<?= $tdata['L_SEARCH'] ?>"></div>
        </form>
    </div>
</div>