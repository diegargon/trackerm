<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<form method="GET">
    <input type="hidden" name="page" value="<?= $tdata['page'] ?>"/>
    <input type="hidden" name="id" value="<?= $tdata['id'] ?>"/>
    <input type="hidden" name="view_type" value="<?= $tdata['view_type'] ?>"/>
    <input class="submit_btn" type="submit" name="more_movies" value="<?= $LNG['L_SEARCH_MOVIES'] ?>" >
    <input class="submit_btn" type="submit" name="more_torrents" value="<?= $LNG['L_SHOW_TORRENTS'] ?>" >
    <input type="text" name="search_movies_db" value="<?= $tdata['stitle'] ?>">
</form>
