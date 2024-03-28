<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
//var_dump($tdata);
$PATH = '?page=' . $tdata['page'];

if (!empty($tdata['link_options'])) {
    $PATH .= $tdata['link_options'];
}
?>

<div class="type_pages_numbers">
    <a class="num_pages_link" onClick="show_loading()" href="<?= $PATH ?>&npage=1#<?= $tdata['media_type'] ?>">1</a>
    <a class="num_pages_link" onClick="show_loading()" href="<?= $PATH ?>&npage=<?= $tdata['page_previous'] ?>#<?= $tdata['media_type'] ?>">&#x23F4;</a>
    <a class="num_pages_link_selected"  href="<?= $PATH ?>&npage=<?= $tdata['npage'] ?>#<?= $tdata['media_type'] ?>"><?= $tdata['npage'] ?></a>
    <a class="num_pages_link" onClick="show_loading()"  href="<?= $PATH ?>&npage=<?= $tdata['page_next'] ?>#<?= $tdata['media_type'] ?>">&#x23F5;</a>
    <a class="num_pages_link" onClick="show_loading()"  href="<?= $PATH ?>&npage=<?= $tdata['npages'] ?>#<?= $tdata['media_type'] ?>"><?= $tdata['npages'] ?></a>
</div>