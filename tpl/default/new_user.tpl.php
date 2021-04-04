<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="new_user_box">
    <form id="new_user" method="POST" >
        <label for="username"><?= $LNG['L_USERNAME'] ?></label>
        <input id="username" size="8" type="text" name="username" value=""/>
        <label for="password"> <?= $LNG['L_PASSWORD'] ?></label>
        <input id="password" size="8" type="password" name="password" value=""/>
        <input type="hidden" name="is_admin" value="0"/>
        <label for="is_admin">  <?= $LNG['L_ADMIN'] ?>   </label>
        <input id="is_admin" type="checkbox" name="is_admin" value="1"/>
        <input type="hidden" name="disable" value = "0"/>
        <label for="disable">  <?= $LNG['L_DISABLED'] ?>   </label>
        <input id="disable" type="checkbox" name="disable" value="1"/>
        <input type="hidden" name="hide_login" value="0"/>
        <label for="hide_login">  <?= $LNG['L_HIDE_LOGIN'] ?>   </label>
        <input id="hide_login" type="checkbox" name="hide_login" value="1"/>
        <input class="submit_btn" type="submit" name="new_user" value="  <?= $LNG['L_CREATE'] . '/' . $LNG['L_MODIFY'] ?>  "/>
    </form>
</div>
