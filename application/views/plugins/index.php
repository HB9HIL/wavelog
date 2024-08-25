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
                <?= sprintf(__("You can install third-party plugins by adding the packages to the folder %s."), "'application/" . basename($this->config->item('plugin_dir')) . "'"); ?>
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
                                    <td style="vertical-align: middle;">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#pluginModal_<?php echo $plugin->plugin_id; ?>"><i class="fas fa-wrench"></i></button>
                                        <div class="modal fade bg-black bg-opacity-50" id="pluginModal_<?php echo $plugin->plugin_id; ?>" tabindex="-1" aria-labelledby="pluginLabel_<?php echo $plugin->plugin_id; ?>" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="pluginLabel_<?php echo $plugin->plugin_id; ?>"><?= sprintf(__("Configuration for %s"), "'" . $plugin->name . "'"); ?></h5>
                                                    </div>
                                                    <div class="modal-body" style="text-align: left !important;">
                                                        <div class="mb-3">
                                                            <p><?= __("Configure the plugin settings."); ?></p>
                                                            <br>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <form action="<?php echo site_url('plugin/edit'); ?>" method="post" style="display:inline;">
                                                                <input type="hidden" name="plugin_id" value="<?php echo $plugin->plugin_id; ?>">
                                                                <button type="submit" class="btn btn-success"><?= __("Save") ?></i></button>
                                                            </form>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __("Cancel") ?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                </div>
        </div>
    </div>
</div>