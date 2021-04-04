<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="delete_user">
    <input type="hidden" name="delete_user_id" value="<?= $tdata['id'] ?>"/>
    <input class="submit_btn" onclick="return confirm(\'Are you sure?\')" type="submit" name="delete_user" value="<?= $LNG['L_DELETE'] ?>"/>
    <span><?= $tdata['username'] ?></span>
</div>