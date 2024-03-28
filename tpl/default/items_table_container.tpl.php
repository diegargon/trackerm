<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
?>
<div <?= isset($tdata['table_container_id']) ? 'id=' . $tdata['table_container_id'] : null ?> class="type_head_container">
    <div class="type_head"><h2><?= $tdata['head'] ?></h2></div>
</div>

<?= isset($tdata['pager']) ? $tdata['pager'] : null ?>
<?= isset($tdata['items']) ? $tdata['items'] : null ?>


