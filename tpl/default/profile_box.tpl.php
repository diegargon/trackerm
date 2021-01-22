<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="profile_box">
    <a href="?page=login&username=<?= $tdata['username'] ?>">
        <div class="profile_image"><img src="<?= $tdata['REL_PATH'] ?>/img/profile.png"/></div>
        <div class="profile_name"><?= $tdata['username'] ?></div>
    </a>
</div>

