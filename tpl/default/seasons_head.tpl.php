<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
//$url = '?page=view&id=' . $id . '&view_type=' . $view_type;
for ($i = 1; $i <= $tdata['seasons']; $i++) {
    ?>
    <a class="season_link" href="?page=view&id=<?= $tdata['id'] ?>&season=<?= $i ?>&view_type=<?= $tdata['view_type'] ?>"><?= $LNG['L_SEASON'] ?>: <?= $i ?></a>
    <?php
}
?>

<br/><span><?= $LNG['L_SEASONS'] ?>: <?= $tdata['seasons'] ?> <?= $LNG['L_EPISODES'] ?>: <?= $tdata['episodes'] ?> </span><br/>
