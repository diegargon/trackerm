<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
!defined('IN_WEB') ? exit : true;

!empty($tdata['pre_content']) ? print $tdata['pre_content'] : null;
?>
<form method="post" id="clear_disabled">
    <input type="submit" name="clear_disabled" value="<?= $LNG['L_CLEAR_DISABLE'] ?>"/>
</form>
<form method="post" id="clear_search_cache">
    <input type="submit" name="clear_search_cache" value="<?= $LNG['L_CLEAR_SEARCH_CACHE'] ?>"/>
</form>
<!--
<form method="post" id="force_fix_perms">
    <input type="submit" name="force_fix_perms" value="<?= $LNG['L_FIX_PERMS'] ?>"/>
</form>
-->
<a class="action_link" href="?page=config"><?= $LNG['L_CONFIG'] ?></a>
<?php
!empty($tdata['post_content']) ? print $tdata['post_content'] : null;
