<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
!defined('IN_WEB') ? exit : true;

!empty($tdata['pre_content']) ? print $tdata['pre_content'] : null;
?>
<div><?= $LNG['L_IDENTIFIED'] ?> : <?= $tdata['username'] ?></div>

<?php
if (!empty($tdata['edit_profile_btn'])) {
    ?>
    <a href="?page=index&edit_profile=1" class="action_link"><?= $LNG['L_EDIT'] ?></a>
    <?php
}
if (!empty($tdata['logoutbtn'])) {
    ?>
    <a href="?page=logout" class="action_link"><?= $LNG['L_LOGOUT'] ?></a>
    <?php
}
!empty($tdata['post_content']) ? print $tdata['post_content'] : null;

