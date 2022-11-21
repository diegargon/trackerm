<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
$page_link = '?page=view&id=' . $tdata['id'] . '&view_type=' . $tdata['media_type'] . '_library';
?>

<?php
if (isset($tdata['tpl_var_ary_first'])) {
    ?>
    <form method="POST" action="<?= $page_link ?>">
        <select onchange="show_loading();this.form.submit();" name="selected_id">
        <?php } ?>
        <option value="<?= $tdata['file_id'] ?>"
        <?php if ($tdata['file_id'] == $tdata['selected_id']) { ?>
                    selected=""
                <?php } ?>
                >
                    <?= $tdata['name'] ?><?= !empty($tdata['view_mark']) ? '[&#10003;]' : null ?>
        </option>
        <?php if (isset($tdata['tpl_var_ary_last'])) { ?>
        </select>
    </form>
    <br/>
<?php } ?>