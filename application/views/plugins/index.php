<div class="container mb-4 mt-2">
    <br>
    <h2><?php echo $page_title; ?></h2>

    <div style="display: none;" id="plugin_message_area" role="alert"></div>

    <div class="card">
        <div class="card-header">
            <?= __("How it works"); ?>
        </div>
        <div class="card-body">
            <p class="card-text">
                <?= __("You can install third-party plugins by adding the packages to the folder 'application/plugins'."); ?>
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <?= __("Plugin List"); ?>
        </div>
        <div class="card-body">
            <?php if (empty($all_plugins)) { ?>
                <h4><?= __("No plugins found"); ?></h4>
            <?php } else { ?>
                <div class="table-responsive">
                    <table id="plugin_table" style="width:100%" class="crontable table table-sm table-striped">
                        <thead>
                            <tr>
                                <th><?= __("ID"); ?></th>
                                <th><?= __("Name"); ?></th>
                                <th><?= __("Version"); ?></th>
                                <th><?= __("Description"); ?></th>
                                <th><?= __("Author"); ?></th>
                                <th><?= __("Source"); ?></th>
                                <th><?= __("Status"); ?></th>
                                <th><?= __("Config"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_plugins as $plugin) { ?>
                                <tr>
                                    <td style="vertical-align: middle;" class='plugin_<?php echo $plugin->plugin_id; ?>'><span class="badge bg-info"><?php echo $plugin->plugin_id; ?></span></td>
                                    <td style="vertical-align: middle;"><?php echo $plugin->name; ?></td>
                                    <td style="vertical-align: middle;"><?php echo $plugin->version; ?></td>
                                    <td style="vertical-align: middle;"><?php echo $plugin->description; ?></td>
                                    <td style="vertical-align: middle;"><?php echo $plugin->author; ?></td>
                                    <td style="vertical-align: middle;"><a href="<?php echo $plugin->uri; ?>" target="_blank"><?= __("Go to source..."); ?></a></td>
                                    <td style="vertical-align: middle;"><?php echo $plugin->status ? '<span class="badge bg-success">' . __("Enabled") . '</span>' : '<span class="badge bg-seconday">' . __("Disabled") . '</span>'; ?></td>
                                    <td style="vertical-align: middle;"><button id="plugin_conf_<?php echo $plugin->plugin_id; ?>" class="editPlugin btn btn-primary btn-sm"><i class="fas fa-wrench"></i></button></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                </div>
        </div>
    </div>
</div>