<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
if (isset($tdata['tpl_var_ary_first'])) {
    ?>

    <div class="divTable">
        <?php if (isset($tdata['tpl_var_ary_item_break'])) { ?>
            <div class="divTableRow">
            <?php } ?>
        <?php } ?>
        <!-- REPEAT -->
        <?php if (isset($tdata['tpl_var_ary_item_break']) && $tdata['tpl_var_ary_item_break'] === 1 && !isset($tdata['tpl_var_ary_first'])) { ?>
        </div> <!-- close table row -->
        <div class="divTableRow">
        <?php } ?>

        <div class="divTableCell">
            <div class="display display_1">
                <div class="poster_preview">
                    <a  href="?page=<?= $tdata['view_page'] ?>&name=<?= $tdata['name'] ?>&media_type=<?= $tdata['media_type'] ?>">
                        <div class="item_details">
                            <div class="item_poster_genre"><?= $tdata['name'] ?></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- END REPEAT -->

        <?php if (isset($tdata['tpl_var_ary_last'])) { ?>
            <?php if (isset($tdata['tpl_var_ary_item_break'])) { ?>
            </div> <!-- close table row -->
        <?php } ?>
    </div>
<?php } ?>
