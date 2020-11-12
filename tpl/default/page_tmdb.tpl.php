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
    <p><?= $tdata['L_SEARCH'] . $tdata['L_MOVIES'] ?>:<input type="text" name="search_movie"></p>
    <p><?= $tdata['L_SEARCH'] . $tdata['L_SHOWS'] ?>:<input type="text" name="search_shows"></p>
    <input class="submit_btn" type="submit" name="search" value="<?= $tdata['L_SEARCH'] ?> ">
</form>