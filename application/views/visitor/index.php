<?php
function echo_table_header_col($ctx, $name)
{
	switch ($name) {
		case 'Mode':
			echo '<th>' . $ctx->lang->line('gen_hamradio_mode') . '</th>';
			break;
		case 'RSTS':
			echo '<th class="d-none d-sm-table-cell">' . $ctx->lang->line('gen_hamradio_rsts') . '</th>';
			break;
		case 'RSTR':
			echo '<th class="d-none d-sm-table-cell">' . $ctx->lang->line('gen_hamradio_rstr') . '</th>';
			break;
		case 'Country':
			echo '<th>' . $ctx->lang->line('general_word_country') . '</th>';
			break;
		case 'IOTA':
			echo '<th>' . $ctx->lang->line('gen_hamradio_iota') . '</th>';
			break;
		case 'SOTA':
			echo '<th>' . $ctx->lang->line('gen_hamradio_sota') . '</th>';
			break;
		case 'State':
			echo '<th>' . $ctx->lang->line('gen_hamradio_state') . '</th>';
			break;
		case 'Grid':
			echo '<th>' . $ctx->lang->line('gen_hamradio_gridsquare') . '</th>';
			break;
		case 'Distance':
			echo '<th>' . $ctx->lang->line('gen_hamradio_distance') . '</th>';
			break;
		case 'Band':
			echo '<th>' . $ctx->lang->line('gen_hamradio_band') . '</th>';
			break;
		case 'Frequency':
			echo '<th>' . $ctx->lang->line('gen_hamradio_frequency') . '</th>';
			break;
		case 'Operator':
			echo '<th>' . $ctx->lang->line('gen_hamradio_operator') . '</th>';
			break;
	}
}

function echo_table_col($row, $name)
{
	$CI = &get_instance();
	switch ($name) {
		case 'Mode':
			echo '<td>';
			echo $row->COL_SUBMODE == null ? $row->COL_MODE : $row->COL_SUBMODE . '</td>';
			break;
		case 'RSTS':
			echo '<td class="d-none d-sm-table-cell">' . $row->COL_RST_SENT;
			if ($row->COL_STX) {
				echo ' <span data-bs-toggle="tooltip" title="' . ($row->COL_CONTEST_ID != "" ? $row->COL_CONTEST_ID : "n/a") . '" class="badge text-bg-light">';
				printf("%03d", $row->COL_STX);
				echo '</span>';
			}
			if ($row->COL_STX_STRING) {
				echo ' <span data-bs-toggle="tooltip" title="' . ($row->COL_CONTEST_ID != "" ? $row->COL_CONTEST_ID : "n/a") . '" class="badge text-bg-light">' . $row->COL_STX_STRING . '</span>';
			}
			echo '</td>';
			break;
		case 'RSTR':
			echo '<td class="d-none d-sm-table-cell">' . $row->COL_RST_RCVD;
			if ($row->COL_SRX) {
				echo ' <span data-bs-toggle="tooltip" title="' . ($row->COL_CONTEST_ID != "" ? $row->COL_CONTEST_ID : "n/a") . '" class="badge text-bg-light">';
				printf("%03d", $row->COL_SRX);
				echo '</span>';
			}
			if ($row->COL_SRX_STRING) {
				echo ' <span data-bs-toggle="tooltip" title="' . ($row->COL_CONTEST_ID != "" ? $row->COL_CONTEST_ID : "n/a") . '" class="badge text-bg-light">' . $row->COL_SRX_STRING . '</span>';
			}
			echo '</td>';
			break;
		case 'Country':
			echo '<td>' . ucwords(strtolower(($row->COL_COUNTRY))) . '</td>';
			break;
		case 'IOTA':
			echo '<td>' . ($row->COL_IOTA) . '</td>';
			break;
		case 'SOTA':
			echo '<td>' . ($row->COL_SOTA_REF) . '</td>';
			break;
		case 'WWFF':
			echo '<td>' . ($row->COL_WWFF_REF) . '</td>';
			break;
		case 'POTA':
			echo '<td>' . ($row->COL_POTA_REF) . '</td>';
			break;
		case 'Grid':
			$CI->load->library('qra');
			echo '<td>' . ($CI->qra->echoQrbCalcLink($row->station_gridsquare, $row->COL_VUCC_GRIDS, $row->COL_GRIDSQUARE, true)) . '</td>';
			break;
		case 'Distance':
			echo '<td>' . ($row->COL_DISTANCE ? $row->COL_DISTANCE . '&nbsp;km' : '') . '</td>';
			break;
		case 'Band':
			echo '<td>';
			if ($row->COL_SAT_NAME != null) {
				echo '<a href="https://db.satnogs.org/search/?q=' . $row->COL_SAT_NAME . '" target="_blank">' . $row->COL_SAT_NAME . '</a></td>';
			} else {
				echo strtolower($row->COL_BAND);
			}
			echo '</td>';
			break;
		case 'Frequency':
			echo '<td>';
			if ($row->COL_FREQ != null) {
				echo $CI->frequency->hz_to_mhz($row->COL_FREQ);
			} else {
				echo strtolower($row->COL_BAND);
			}
			echo '</td>';
			break;
		case 'State':
			echo '<td>' . ($row->COL_STATE) . '</td>';
			break;
		case 'Operator':
			echo '<td>' . ($row->COL_OPERATOR) . '</td>';
			break;
	}
}

?>
<div class="container dashboard">
</div>

<!-- Map -->
<?php $public_maps_option = $this->optionslib->get_option('public_maps') ?? 'true';
if ($public_maps_option == 'true') { ?>
	<script>
		let user_map_custom = JSON.parse('<?php echo $user_map_custom; ?>');
	</script>
	<div id="map" class="map-leaflet" style="width: 100%; height: 365px"></div>
<?php } ?>

<div id="container" style="padding-top: 0px; margin-top: 5px;" class="container dashboard">

	<!-- Log Data -->
	<div class="row logdata">
		<div class="col-sm-8">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr class="titles">
							<th><?php echo lang('general_word_date'); ?></th>

							<?php if (($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE || ($this->config->item('show_time'))) { ?>
								<th><?php echo lang('general_word_time'); ?></th>
							<?php } ?>
							<th><?php echo lang('gen_hamradio_call'); ?></th>
							<?php
							echo_table_header_col($this, $this->session->userdata('user_column1') == "" ? 'Mode' : $this->session->userdata('user_column1'));
							echo_table_header_col($this, $this->session->userdata('user_column2') == "" ? 'RSTS' : $this->session->userdata('user_column2'));
							echo_table_header_col($this, $this->session->userdata('user_column3') == "" ? 'RSTR' : $this->session->userdata('user_column3'));
							echo_table_header_col($this, $this->session->userdata('user_column4') == "" ? 'Band' : $this->session->userdata('user_column4'));
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						if (!empty($results) > 0) {
							foreach ($results->result() as $row) { ?>
								<?php echo '<tr class="tr' . ($i & 1) . '">'; ?>

								<?php

								// Get Date format
								if ($this->session->userdata('user_date_format')) {
									// If Logged in and session exists
									$custom_date_format = $this->session->userdata('user_date_format');
								} else {
									// Get Default date format from /config/wavelog.php
									$custom_date_format = $this->config->item('qso_date_format');
								}

								?>

								<td><?php $timestamp = strtotime($row->COL_TIME_ON);
									echo date($custom_date_format, $timestamp); ?></td>
								<?php if (($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE || ($this->config->item('show_time'))) { ?>
									<td><?php $timestamp = strtotime($row->COL_TIME_ON);
										echo date('H:i', $timestamp); ?></td>

								<?php } ?>
								<td>
									<?php echo str_replace("0", "&Oslash;", strtoupper($row->COL_CALL)); ?>
								</td>
								<?php
								echo_table_col($row, $this->session->userdata('user_column1') == "" ? 'Mode' : $this->session->userdata('user_column1'));
								echo_table_col($row, $this->session->userdata('user_column2') == "" ? 'RSTS' : $this->session->userdata('user_column2'));
								echo_table_col($row, $this->session->userdata('user_column3') == "" ? 'RSTR' : $this->session->userdata('user_column3'));
								echo_table_col($row, $this->session->userdata('user_column4') == "" ? 'Band' : $this->session->userdata('user_column4'));
								?>
								</tr>
						<?php $i++;
							}
						} ?>
					</tbody>
				</table>
			</div>
			<?php if (isset($this->pagination)) { ?>
				<?php
				$config['full_tag_open'] = '<ul class="pagination">';
				$config['full_tag_close'] = '</ul>';
				$config['attributes'] = ['class' => 'page-link'];
				$config['first_link'] = false;
				$config['last_link'] = false;
				$config['first_tag_open'] = '<li class="page-item">';
				$config['first_tag_close'] = '</li>';
				$config['prev_link'] = '&laquo';
				$config['prev_tag_open'] = '<li class="page-item">';
				$config['prev_tag_close'] = '</li>';
				$config['next_link'] = '&raquo';
				$config['next_tag_open'] = '<li class="page-item">';
				$config['next_tag_close'] = '</li>';
				$config['last_tag_open'] = '<li class="page-item">';
				$config['last_tag_close'] = '</li>';
				$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
				$config['cur_tag_close'] = '<span class="visually-hidden">(current)</span></a></li>';
				$config['num_tag_open'] = '<li class="page-item">';
				$config['num_tag_close'] = '</li>';
				$this->pagination->initialize($config);
				?>

				<?php echo $this->pagination->create_links(); ?>

			<?php } ?>
		</div>

		<div class="col-sm-4">
			<div class="table-responsive">
				<table class="table table-striped">
					<tr class="titles">
						<td colspan="2"><i class="fas fa-chart-bar"></i> <?php echo lang('dashboard_qso_breakdown'); ?></td>
					</tr>

					<tr>
						<td width="50%"><?php echo lang('general_word_total'); ?></td>
						<td width="50%"><?php echo $total_qsos; ?></td>
					</tr>

					<tr>
						<td width="50%"><?php echo lang('general_word_year'); ?></td>
						<td width="50%"><?php echo $year_qsos; ?></td>
					</tr>

					<tr>
						<td width="50%"><?php echo lang('general_word_month'); ?></td>
						<td width="50%"><?php echo $month_qsos; ?></td>
					</tr>
				</table>

				<table class="table table-striped">
					<tr class="titles">
						<td colspan="2"><i class="fas fa-globe-europe"></i> <?php echo lang('dashboard_countries_breakdown'); ?></td>
					</tr>

					<tr>
						<td width="50%"><?php echo lang('general_word_worked'); ?></td>
						<td width="50%"><?php echo $total_countries; ?></td>
					</tr>
					<tr>
						<td width="50%"><a href="#" onclick="return false" title="QSL Cards / eQSL / LoTW" data-bs-toggle="tooltip"><?php echo lang('general_word_confirmed'); ?></a></td>
						<td width="50%">
							<?php echo $total_countries_confirmed_paper; ?> /
							<?php echo $total_countries_confirmed_eqsl; ?> /
							<?php echo $total_countries_confirmed_lotw; ?>
						</td>
					</tr>

					<tr>
						<td width="50%"><?php echo lang('general_word_needed'); ?></td>
						<td width="50%"><?php echo $total_countries_needed; ?></td>
					</tr>
				</table>

				<?php if ((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE) && ($total_qsl_sent != 0 || $total_qsl_rcvd != 0 || $total_qsl_requested != 0)) { ?>
					<table class="table table-striped">
						<tr class="titles">
							<td colspan="2"><i class="fas fa-envelope"></i> <?php echo lang('general_word_qslcards'); ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_sent'); ?></td>
							<td width="50%"><?php echo $total_qsl_sent; ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_received'); ?></td>
							<td width="50%"><?php echo $total_qsl_rcvd; ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_requested'); ?></td>
							<td width="50%"><?php echo $total_qsl_requested; ?></td>
						</tr>
					</table>
				<?php } ?>

				<?php if ((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE) && ($total_eqsl_sent != 0 || $total_eqsl_rcvd != 0)) { ?>
					<table class="table table-striped">
						<tr class="titles">
							<td colspan="2"><i class="fas fa-address-card"></i> <?php echo lang('general_word_eqslcards'); ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_sent'); ?></td>
							<td width="50%"><?php echo $total_eqsl_sent; ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_received'); ?></td>
							<td width="50%"><?php echo $total_eqsl_rcvd; ?></td>
						</tr>
					</table>
				<?php } ?>

				<?php if ((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE) && ($total_lotw_sent != 0 || $total_lotw_rcvd != 0)) { ?>
					<table class="table table-striped">
						<tr class="titles">
							<td colspan="2"><i class="fas fa-list"></i> <?php echo lang('general_word_lotw'); ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_sent'); ?></td>
							<td width="50%"><?php echo $total_lotw_sent; ?></td>
						</tr>

						<tr>
							<td width="50%"><?php echo lang('general_word_received'); ?></td>
							<td width="50%"><?php echo $total_lotw_rcvd; ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div id="partial_view"></div>