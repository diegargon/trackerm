<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<form method="POST" id="form_user_prefs" action="?page=index&edit_profile=1">
    <span><?= $LNG['L_PASSWORD'] ?></span><input size="8" type="text" name="cur_password" value=""/>
    <span><?= $LNG['L_NEW_PASSWORD'] ?></span><input size="8" type="text" name="new_password" value=""/>
    <span><?= $LNG['L_EMAIL_NOTIFY'] ?> </span>
    <input type="hidden" name="email_notify" value="0"/>
    <input type="checkbox" <?= $tdata['email_checked'] ?> name="email_notify" value="1"/>
    <span><?= $LNG['L_EMAIL'] ?></span><input size="15" type="text" name="email" value="<?= $user->getEmail() ?>"/>
    <br/><span><?= $LNG['L_INDEX_SELECT'] ?></span>
    <select name="index_page">
        <option <?= $tdata['index_selected'] ?> value="index">index</option>
        <option <?= $tdata['library_selected'] ?> value="library"><?= $LNG['L_LIBRARY'] ?></option>
        <option <?= $tdata['news_selected'] ?> value="news"><?= $LNG['L_NEWS'] ?></option>
        <option <?= $tdata['wanted_selected'] ?> value="wanted"><?= $LNG['L_WANTED'] ?></option>
        <option <?= $tdata['torrents_selected'] ?> value="torrents"><?= $LNG['L_TORRENTS'] ?></option>
        <option <?= $tdata['tmdb_selected'] ?> value="tmdb">tmdb</option>
        <option <?= $tdata['transmission_selected'] ?> value="transmission">Transmission</option>
    </select>
    <br/><input type="submit" class="action_link inline" value="<?= $LNG['L_SEND'] ?>"/>
</form>
