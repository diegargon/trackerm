<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="view_page">
    <div class="view_content">
        <div class="view_poster_container">
            <img class="view_poster" src="<?= $tdata['poster'] ?>" alt=""/>
            <?php
            if (!empty($tdata['guessed_poster'])) {
                ?>
                <div class="guessed_poster"><?= $LNG['L_POSTER_GUESSED'] ?></div>
            <?php } ?>
        </div>
        <div class="view_description_container">
            <h2><?= $tdata['title'] ?></h2>
            <?php if (!empty($tdata['plot'])) { ?>
                <div class="view_plot">
                    <p><?= $tdata['plot'] ?></p>
                </div>
            <?php } ?>
            <div class="view_info">
                <?php if (!empty($tdata['created'])) {
                    ?>
                    <span><?= $LNG['L_ADDED'] ?> :</span>
                    <span class="view_added"><?= custom_strftime("Y", strtotime($tdata['created'])) ?></span>
                    <br/>
                <?php } ?>

                <?php if (!empty($tdata['total_items'])) { ?>
                    <span><?= $LNG['L_N_FILES'] ?> :</span>
                    <span class="view_have_episodes"><?= $tdata['total_items'] ?></span>
                    <br/>
                <?php } ?>
                <div class="view_extra">
                    <?= $tdata['item_list'] ?>
                </div>
            </div> <!-- //view_info -->
        </div> <!-- view_description_container -->
    </div> <!-- view_content -->
</div> <!-- view_page -->