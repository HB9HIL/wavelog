<div class="container gridsquare_map_form">

    <br>

    <h2><?php echo $page_title; ?></h2>

    <?php if ($visitor == false) { ?>
        <form class="form">
            <div class="row mb-2">
                <label class="col-md-1" for="band"><?php echo lang('gridsquares_band'); ?></label>
                <div class="col-sm-2">
                    <select class="form-select form-select-sm" id="band">
                        <option value="All"><?php echo lang('general_word_all') ?></option>
                        <?php foreach ($bands as $band) {
                            echo '<option value="' . $band . '"';
                            if ($user_default_band == $band) {
                                echo ' selected="selected"';
                            }
                            echo '>' . $band . '</option>' . "\n";
                        } ?>
                    </select>
                </div>

                <?php if (count($sats_available) != 0) { ?>
                    <label class="col-md-1" id="satslabel" for="distplot_sats" <?php if ($user_default_band != "SAT") { ?>style="display: none;" <?php } ?>><?php echo lang('gridsquares_sat'); ?></label>
                    <div class="col-sm-2" id="sats_div" <?php if ($user_default_band != "SAT") { ?>style="display: none;" <?php } ?>>
                        <select class="form-select form-select-sm" id="sat">
                            <option value="All"><?php echo lang('general_word_all') ?></option>
                            <?php foreach ($sats_available as $sat) {
                                echo '<option value="' . $sat . '"' . '>' . $sat . '</option>' . "\n";
                            } ?>
                        </select>
                    </div>
                <?php } else { ?>
                    <input id="sats" type="hidden" value="All"></input>
                <?php } ?>

                <label class="col-md-1" for="propagation">Propagation</label>
                <div class="col-sm-4">
                    <select class="form-select form-select-sm w-auto" id="propagation" <?php if ($user_default_band == 'SAT') {echo 'disabled';} ?>>
                        <option value=""><?php echo lang('general_word_all'); ?></option>
                        <option value="AS">Aircraft Scatter</option>
                        <option value="AUR">Aurora</option>
                        <option value="AUE">Aurora-E</option>
                        <option value="BS">Back scatter</option>
                        <option value="ECH">EchoLink</option>
                        <option value="EME">Earth-Moon-Earth</option>
                        <option value="ES">Sporadic E</option>
                        <option value="FAI">Field Aligned Irregularities</option>
                        <option value="F2">F2 Reflection</option>
                        <option value="INTERNET">Internet-assisted</option>
                        <option value="ION">Ionoscatter</option>
                        <option value="IRL">IRLP</option>
                        <option value="MS">Meteor scatter</option>
                        <option value="RPT">Terrestrial or atmospheric repeater or transponder</option>
                        <option value="RS">Rain scatter</option>
                        <option value="SAT" <?php if ($user_default_band == $band) {echo 'selected="selected"';} ?>>Satellite</option>
                        <option value="TEP">Trans-equatorial</option>
                        <option value="TR">Tropospheric ducting</option>
                    </select>
                </div>
                    
            </div>
            <div class="row mb-2">
                <label class="col-md-1" for="mode"><?php echo lang('gridsquares_mode'); ?></label>
                <div class="col-sm-2">
                    <select class="form-select form-select-sm" id="mode">
                        <option value="All"><?php echo lang('general_word_all') ?></option>
                        <?php
                        foreach ($modes as $mode) {
                            if ($mode->submode ?? '' == '') {
                                echo '<option value="' . $mode . '">' . strtoupper($mode) . '</option>' . "\n";
                            }
                        }
                        ?>
                    </select>
                </div>
                <label class="col-md-1" id="orbitslabel" for="orbits" <?php if ($user_default_band != "SAT") { ?>style="display: none;" <?php } ?>><?php echo lang('gridsquares_orbit'); ?></label>
                <div class="col-sm-2" id="orbits_div" <?php if ($user_default_band != "SAT") { ?>style="display: none;" <?php } ?>>
                    <select class="form-select form-select-sm" id="orbits">
                        <option value="All"><?php echo lang('general_word_all') ?></option>
                        <?php
                        foreach ($orbits as $orbit) {
                            echo '<option value="' . $orbit . '">' . strtoupper($orbit) . '</option>' . "\n";
                        }
                        ?>
                    </select>
                </div>

                <label class="col-md-1"><?php echo lang('gridsquares_confirmation'); ?></label>
                <div class="col-sm-4">
                    <div>
                        <div class="form-check-inline">
                            <?php echo '<input class="form-check-input" type="checkbox" name="qsl" id="qsl"';
                            if (isset($user_default_confirmation) && strpos($user_default_confirmation, 'Q') !== false) {
                                echo ' checked';
                            }
                            echo '>'; ?>
                            <label class="form-check-label" for="qsl">QSL</label>
                        </div>
                        <div class="form-check-inline">
                            <?php echo '<input class="form-check-input" type="checkbox" name="lotw" id="lotw"';
                            if (isset($user_default_confirmation) && strpos($user_default_confirmation, 'L') !== false) {
                                echo ' checked';
                            }
                            echo '>'; ?>
                            <label class="form-check-label" for="lotw">LoTW</label>
                        </div>
                        <div class="form-check-inline">
                            <?php echo '<input class="form-check-input" type="checkbox" name="eqsl" id="eqsl"';
                            if (isset($user_default_confirmation) && strpos($user_default_confirmation, 'E') !== false) {
                                echo ' checked';
                            }
                            echo '>'; ?>
                            <label class="form-check-label" for="eqsl">eQSL</label>
                        </div>
                        <div class="form-check-inline">
                            <?php echo '<input class="form-check-input" type="checkbox" name="qrz" id="qrz"';
                            if (isset($user_default_confirmation) && strpos($user_default_confirmation, 'Z') !== false) {
                                echo ' checked';
                            }
                            echo '>'; ?>
                            <label class="form-check-label" for="qrz">QRZ.com</label>
                        </div>
                    </div>
                </div>
            </div>
            <button id="plot" type="button" name="plot" class="btn btn-sm btn-primary me-1 mb-1 ld-ext-right ld-ext-right-plot" onclick="gridPlot(this.form,<?php echo $visitor == true ? "true" : "false"; ?>)"><?php echo lang('gridsquares_button_plot'); ?><div class="ld ld-ring ld-spin"></div></button>
            <button id="clear" type="button" name="clear" class="btn btn-sm btn-primary me-1 mb-1 ld-ext-right ld-ext-right-clear" onclick="clearMarkers()"><?php echo lang('gridsquares_button_clear_markers'); ?><div class="ld ld-ring ld-spin"></div></button>
            
        </form>
    <?php } ?>

    <?php if ($this->session->flashdata('message')) { ?>
        <!-- Display Message -->
        <div class="alert-message error">
            <p><?php echo $this->session->flashdata('message'); ?></p>
        </div>
    <?php } ?>
</div>

<div id="gridmapcontainer">
    <div id="gridsquare_map" class="map-leaflet" style="width: 100%;"></div>
</div>
<div class="coordinates d-flex">
    <div class="cohidden"><?php echo lang('gen_hamradio_latitude') ?>:&nbsp;</div>
    <div class="cohidden col-auto text-success fw-bold" id="latDeg"></div>
    <div class="cohidden"><?php echo lang('gen_hamradio_longitude') ?>:&nbsp;</div>
    <div class="cohidden col-auto text-success fw-bold" id="lngDeg"></div>
    <div class="cohidden"><?php echo lang('gen_hamradio_gridsquare') ?>:&nbsp;</div>
    <div class="cohidden col-auto text-success fw-bold" id="locator"></div>
    <div class="cohidden"><?php echo lang('gen_hamradio_distance') ?>:&nbsp;</div>
    <div class="cohidden col-auto text-success fw-bold" id="distance"></div>
    <div class="cohidden"><?php echo lang('gen_hamradio_bearing') ?>:&nbsp;</div>
    <div class="cohidden col-auto text-success fw-bold" id="bearing"></div>
</div>
<script>
    var gridsquaremap = true;
    var type = "worked";
    <?php if ($visitor == true) { ?>
        var visitor = true;
    <?php } else { ?>
        var visitor = false;
    <?php } ?>
    <?php
    echo "var jslayer = \"" . $layer . "\";\n";
    echo "var jsattribution ='" . $attribution . "';";
    if ($visitor == false) {
        echo "var homegrid = \"" . strtoupper($homegrid[0]) . "\";\n";
    }

    echo "var gridsquares_gridsquares = \"" . $gridsquares_gridsquares . "\";\n";
    echo "var gridsquares_gridsquares_confirmed = \"" . $gridsquares_gridsquares_confirmed . "\";\n";
    echo "var gridsquares_gridsquares_not_confirmed = \"" . $gridsquares_gridsquares_not_confirmed . "\";\n";
    echo "var gridsquares_gridsquares_total_worked = \"" . $gridsquares_gridsquares_total_worked . "\";\n";
    ?>
</script>
