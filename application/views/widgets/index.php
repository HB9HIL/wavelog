<div class="container">

    <br>
    <?php $this->load->view('layout/messages'); ?>

    <h2><?php echo $page_title; ?></h2>

    <div class="card mt-3 mb-3">
        <div class="card-body">
            <div class="card-text">
                <p><?= __("Wavelog Widgets are small tools that can be embedded in other web pages to provide additional functionality."); ?></p>
                <p><?= __("Use this page to generate the code to embed the widgets in your website."); ?></p>
            </div>
        </div>
    </div>

    <div class="accordion">

        <!-- OQRS WIDGET -->

        <div class="accordion-item">
            <h2 class="accordion-header" id="panelsStayOpen-H_oqrs">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-B_oqrs" aria-expanded="true" aria-controls="panelsStayOpen-B_oqrs">
                    <?= __("OQRS iFrame Box"); ?><span class="badge bg-success ms-3"><?= __("Javascript free"); ?></span></button>
            </h2>
            <div id="panelsStayOpen-B_oqrs" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-H_oqrs">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col border-end">
                            <p><?= __("The OQRS iFrame Box is a little widget which can be used to quickly request a QSL card."); ?>
                                <?= __("An operator can type in his callsign and will be redirected to the OQRS page if this instance."); ?></p>

                            <p><b><?= __("Settings"); ?></b></p>

                            <div class="row">
                                <div class="col">
                                    <div class="row">
                                        <div class="mb-2">
                                            <label for="oqrs_user_callsign" class="form-label"><?= __("Callsign"); ?></label>
                                            <input id="oqrs_user_callsign" type="text" class="form-control w-auto" placeholder="<?= __("Your Callsign"); ?>" aria-label="Callsign" value="<?php echo $user_callsign; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-2">
                                            <label for="oqrs_theme" class="form-label"><?= __("Theme"); ?></label>
                                            <select id="oqrs_theme" class="form-select w-auto" aria-label="Theme">
                                                <?php
                                                foreach ($themes as $theme) {
                                                    echo '<option value="' . $theme->foldername . '" ' . (( $global_theme == $theme->foldername)?'selected="selected"':"") . '>' . $theme->name . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row">
                                        <div class="mb-2">
                                            <label for="oqrs_slug" class="form-label"><?= __("Slug (optional)"); ?></label>
                                            <input id="oqrs_slug" type="text" class="form-control w-auto" placeholder="<?= __("Slug"); ?>" aria-label="Slug">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-2 mt-4">
                                            <a class="btn btn-primary" id="generate_oqrs"><?= __("Update Preview"); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3"><b><?= __("Additional Information"); ?></b></p>
                            <ul>
                                <li><?= __("OQRS has to be enabled in atleast one of your station locations!"); ?></li>
                                <li><?= __("If you choose a slug, a click on the Wavelog Logo will redirect the user to the visitor page of this logbook. If no slug is chosen the logo will redirect to the Github Page of Wavelog."); ?></li>
                                <li><?= __("The theme can be set to one of the available themes. If no theme is chosen the theme defined in the global options will be used (Admin Menu)."); ?></li>
                                <li><?= sprintf(__("Visit this %slink%s for more technical information."), '<a href="https://github.com/wavelog/wavelog/pull/616" target="_blank">', '</a>'); ?></li>
                            </ul>
                        </div>
                        <div class="col">
                            <div class="bg-black p-3 pb-5">
                                <h4>
                                    <pre><?= __("Preview"); ?></pre>
                                </h4>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div>
                                        <iframe name="iframe" src="<?php echo site_url(); ?>/widgets/oqrs/<?php echo $user_callsign; ?>?theme=<?php echo $global_theme; ?>" height="220" width="670" frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><?= __("Copy this code and paste it somewhere"); ?></p>
                                <code id="oqrs_code">
                                    &lt;iframe name="iframe" src="<?php echo site_url(); ?>/widgets/oqrs/<?php echo $user_callsign; ?>?theme=<?php echo $global_theme; ?>" height="220" width="670" frameborder="0"&gt;&lt;/iframe&gt;
                                </code><span data-bs-toggle="tooltip" title="<?= __("Copy to clipboard"); ?>" onclick='copyLink("oqrs_code")'><i class="copy-icon fas fa-copy"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div>
</div>