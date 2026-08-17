<form method="post" action="<?= site_url('qslpostcard/printqueue_selected') ?>" id="postcardQueueForm">
	<table style="width:100%" class="table table-sm table-bordered table-hover table-striped table-condensed qslprint" id="qslprint_table">
		<thead>
			<tr>
				<th><div class="form-check" style="margin-top: -1.5em"><input class="form-check-input" type="checkbox" id="checkBoxAll" /></div></th>
				<th class='select-filter'><?= __("Callsign") ?></th>
				<th class='select-filter'><?= __("Date") ?></th>
				<th><?= __("Time") ?></th>
				<th class='select-filter'><?= __("Mode") ?></th>
				<th class='select-filter'><?= __("Band") ?></th>
				<th><?= __("Frequency") ?></th>
				<th><?= __("RST (S)") ?></th>
				<th><?= __("RST (R)") ?></th>
				<th><?= __("QSL") ?> <?= __("Via") ?></th>
				<th class='select-filter'><?= __("Station") ?></th>
				<th><?= __("Profile name") ?></th>
				<th class='select-filter'><?= __("Send Method") ?></th>
				<th style="white-space: nowrap;"><?= __("Previous QSL") ?></th>
				<th style="white-space: nowrap;"><?= __("Actions") ?></th>
			</tr>
			<tr>
				<th></th>
				<th class='select-filter'></th>
				<th class='select-filter'></th>
				<th></th>
				<th class='select-filter'></th>
				<th class='select-filter'></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th class='select-filter'></th>
				<th></th>
				<th class='select-filter'></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</form>
