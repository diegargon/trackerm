<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>
<h3><?= $LNG['L_LIBRARY'] ?></h3>
<span> <?= $LNG['L_MOVIES'] ?> : <?= $tdata['num_movies'] ?> </span><br/>
<span> <?= $LNG['L_SHOWS'] ?> : <?= $tdata['num_shows'] ?> </span>
<span> (<?= $LNG['L_EPISODES'] ?> : <?= $tdata['num_episodes'] ?>) </span><br/>
<h3><?= $LNG['L_HARDDISK'] ?></h3>
<span><?= $LNG['L_MOVIES'] ?> : <?= $tdata['movies_size'] ?></span><br/>
<span><?= $LNG['L_SHOWS'] ?> : <?= $tdata['shows_size'] ?></span><br/>
<?= $tdata['movies_paths'] ?>
<?= $tdata['shows_paths'] ?>
<h3><?= $LNG['L_DATABASE'] ?></h3>
<span> <?= $LNG['L_SIZE'] ?> : <?= $tdata['db_size'] ?> </span><br/>