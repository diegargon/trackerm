<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="profile_box">
    <form  method="POST" action="?page=login">
        <button class="login_button" type="submit"><img src="<?= $cfg['REL_PATH'] ?>/img/profile.png" /></button>
        <div class="profile_name"><input size="15"  onfocus="this.value = ''" class="login_username" type="text" name="username" value="<?= $tdata['username'] ?>"></div>
        <div class="profile_password"><input size="15"  onfocus="this.value = ''" class="login_password" type="password" name="password" value=""/></div>
    </form>
</div>

