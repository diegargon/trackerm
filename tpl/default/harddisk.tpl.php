<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<h3><?= $LNG['L_LIBRARY'] ?></h3>
<span> <?= $LNG['L_MOVIES'] ?> : <?= $tdata['num_movies'] ?> </span><br/>
<span> <?= $LNG['L_SHOWS'] ?> : <?= $tdata['num_shows'] ?> </span>
<span> (<?= $LNG['L_EPISODES'] ?> : <?= $tdata['num_episodes'] ?>) </span><br/>
<h3><?= $LNG['L_HARDDISK'] ?></h3>
<span><?= $LNG['L_MOVIES'] ?> : <?= $tdata['movies_size'] ?></span><br/>
<span><?= $LNG['L_SHOWS'] ?> : <?= $tdata['shows_size'] ?></span><br/>
<?php
foreach ($tdata['movies_paths'] as $movies_path) {
    ?>
    <div><?= $movies_path['path'] . ':' . $movies_path['free'] . '/' . $movies_path['total'] ?></div>
    <?php
}
?>
<?php
foreach ($tdata['shows_paths'] as $shows_path) {
    ?>
    <div><?= $shows_path['path'] . ':' . $shows_path['free'] . '/' . $shows_path['total'] ?></div>
    <?php
}
?>

<h3><?= $LNG['L_DATABASE'] ?></h3>
<span> <?= $LNG['L_SIZE'] ?> : <?= $tdata['db_size'] ?> </span><br/>