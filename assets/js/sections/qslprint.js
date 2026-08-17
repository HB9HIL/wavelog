let qsoDialogInstance = null;

// Selected QSO ids, kept here because server side paging drops off-page rows from the DOM
const selectedQsos = new Set();

function qslprintTable() {
	return $('#qslprint_table').DataTable();
}

function reloadQslprintTable() {
	qslprintTable().ajax.reload(null, false);
}

function removeQslRow(id) {
	selectedQsos.delete(String(id));
	reloadQslprintTable();
}

function deleteFromQslQueue(id) {
	BootstrapDialog.confirm({
		title: 'DANGER',
		message: 'Warning! Are you sure you want to removes this QSL from the queue?',
		type: BootstrapDialog.TYPE_DANGER,
		closable: true,
		draggable: true,
		btnOKClass: 'btn-danger',
		callback: function(result) {
			if(result) {
				$.ajax({
					url: base_url + 'index.php/qslprint/delete_from_qsl_queue',
					type: 'post',
					data: {'id': id	},
					success: function(html) {
						removeQslRow(id);
					}
				});
			}
		}
	});
}

function openQsoList(callsign) {
	$.ajax({
		url: base_url + 'index.php/qslprint/open_qso_list',
		type: 'post',
		data: {'callsign': callsign},
		success: function(html) {
			qsoDialogInstance = BootstrapDialog.show({
				title: 'QSO List',
				size: BootstrapDialog.SIZE_WIDE,
				cssClass: 'qso-dialog',
				nl2br: false,
				message: html,
				onshown: function(dialog) {
					$('[data-bs-toggle="tooltip"]').tooltip();
				},
				buttons: [{
					label: lang_admin_close,
					action: function (dialogItself) {
						dialogItself.close();
					}
				}]
			});
		}
	});
}

function addQsoToPrintQueue(id) {
	$.ajax({
		url: base_url + 'index.php/qslprint/add_qso_to_print_queue',
		type: 'post',
		data: {'id': id},
		success: function() {
			if (qsoDialogInstance) {
				qsoDialogInstance.close();
			}
			$("#qsolist_" + id).remove();
			reloadQslprintTable();
		},
		error: function() {
			console.error('Error adding QSO to print queue.');
		}
	});
}

$(".station_id").change(function(){
	reloadQslprintTable();
});

function initQslprintTable() {
	if ($.fn.dataTable.isDataTable('#qslprint_table')) {
		return;
	}

	var table = $('#qslprint_table').DataTable({
		serverSide: true,
		processing: true,
		ajax: {
			url: base_url + 'index.php/qslprint/qsos_datatable',
			type: 'post',
			data: function (d) {
				d.station_id = $('.station_id').val();
			}
		},
		stateSave: true,
		autoWidth: false,
		orderCellsTop: true,
		ordering: true,
		order: [],
		columns: [
			{ data: 'checkbox', className: 'text-center', orderable: false },
			{ data: 'callsign', className: 'text-center' },
			{ data: 'date', className: 'text-center' },
			{ data: 'time', className: 'text-center', orderable: false },
			{ data: 'mode', className: 'text-center' },
			{ data: 'band', className: 'text-center col-band' },
			{ data: 'frequency', className: 'text-center col-freq' },
			{ data: 'rst_sent', className: 'text-center' },
			{ data: 'rst_rcvd', className: 'text-center' },
			{ data: 'qsl_via', className: 'text-center' },
			{ data: 'station', className: 'text-center' },
			{ data: 'profile', className: 'text-center' },
			{ data: 'sent_via', className: 'text-center send-method' },
			{ data: 'previous', className: 'text-center text-nowrap', orderable: false },
			{ data: 'actions', className: 'text-center text-nowrap', orderable: false }
		],
		pageLength: 25,
		lengthMenu: [
			[10, 25, 50, 100, -1],
			[10, 25, 50, 100, lang_export_qslprint_pagination_all]
		],
		paging: 'pagination',
		language: {
			url: getDataTablesLanguageUrl(),
		},
		rowCallback: function (row, data) {
			var checked = selectedQsos.has(qsoIdOfRow(data.DT_RowId));
			$(row).toggleClass('activeRow', checked).find('input[name="selected_qsos[]"]').prop('checked', checked);
		},
		drawCallback: function () {
			$('[data-bs-toggle="tooltip"]', this.api().table().body()).tooltip();
			updateSelectionCount();
		}
	});

	// The dropdown values cannot be derived from the visible page, the server
	// sends them once with the first response
	table.on('xhr.dt', function (e, settings, json) {
		if (json && json.filters) {
			buildFilterDropdowns(table, json.filters);
		}
	});

	$('#qslprint_table tbody').on('change', 'input[name="selected_qsos[]"]', function () {
		var $row = $(this).closest('tr');
		var id = qsoIdOfRow($row.attr('id'));
		if (this.checked) {
			selectedQsos.add(id);
		} else {
			selectedQsos.delete(id);
		}
		$row.toggleClass('activeRow', this.checked);
		updateSelectionCount();
	});

	// Selects the current page; the selection itself survives paging, sorting and filtering
	$('#checkBoxAll').on('change', function () {
		var checked = this.checked;
		table.rows({ page: 'current' }).every(function () {
			var id = qsoIdOfRow(this.data().DT_RowId);
			if (checked) {
				selectedQsos.add(id);
			} else {
				selectedQsos.delete(id);
			}
			$(this.node()).toggleClass('activeRow', checked).find('input[name="selected_qsos[]"]').prop('checked', checked);
		});
		updateSelectionCount();
	});

	switchbandandfrequencydisplay($('#frequency_or_band').val());
}
initQslprintTable();

function qsoIdOfRow(rowId) {
	return String(rowId).replace('qslprint_', '');
}

function buildFilterDropdowns(table, filters) {
	// Filter dropdowns live in their own (second) header row
	var $filterCells = $('#qslprint_table thead tr').last().find('th');

	$.each(filters, function (index, options) {
		var column = table.column(parseInt(index, 10));
		var $cell = $filterCells.eq(parseInt(index, 10));
		if (!$cell.length) { return; }

		var select = $('<select class="form-select form-select-sm" style="width:100%;"><option value=""></option></select>')
			.appendTo($cell.empty())
			.on('click', function (e) { e.stopPropagation(); })	// keep dropdown clicks from re-sorting the column
			.on('change', function () {
				column.search($(this).val()).draw();
			});

		$.each(options, function (i, option) {
			$('<option>').val(option.value).text(option.label).appendTo(select);
		});

		// Reflect any stateSave-restored column search back into the dropdown
		if (column.search()) {
			select.val(column.search());
		}
	});
}

function updateSelectionCount() {
	var table = qslprintTable();
	var rows = table.rows({ page: 'current' }).data();
	var pageSelected = rows.length > 0;

	for (var i = 0; i < rows.length; i++) {
		if (!selectedQsos.has(qsoIdOfRow(rows[i].DT_RowId))) {
			pageSelected = false;
			break;
		}
	}

	$('#checkBoxAll').prop('checked', pageSelected);
	$('#qslprint_selected_count').text(selectedQsos.size);
	$('#qslprint_selection').toggle(selectedQsos.size > 0);
}

function clearQslprintSelection() {
	selectedQsos.clear();
	$('#qslprint_table tbody tr').removeClass('activeRow').find('input[name="selected_qsos[]"]').prop('checked', false);
	updateSelectionCount();
}

function showOqrs(id) {
	$.ajax({
		url: base_url + 'index.php/qslprint/show_oqrs',
		type: 'post',
		data: {'id': id},
		success: function(html) {
			BootstrapDialog.show({
				title: 'OQRS',
				size: BootstrapDialog.SIZE_WIDE,
				cssClass: 'qso-dialog',
				nl2br: false,
				message: html,
				onshown: function(dialog) {
					$('[data-bs-toggle="tooltip"]').tooltip();
				},
				buttons: [{
					label: lang_admin_close,
					action: function (dialogItself) {
						dialogItself.close();
					}
				}]
			});
		}
	});
}

function mark_qsl_sent(id, method) {
    $.ajax({
        url: base_url + 'index.php/qso/qsl_sent_ajax',
        type: 'post',
        data: {'id': id,
            'method': method
        },
        success: function(data) {
            if (data.message == 'OK') {
                removeQslRow(id); // removes choice from menu
            }
            else {
                $(".container").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>You are not allowed to update QSL status!</div>');
            }
        }
    });
}

// Drops the ids the server reported as updated and refreshes the table once
function removeQslRows(data) {
	$.each(data, function (k, row) {
		selectedQsos.delete(String(row.qsoID));
	});
	reloadQslprintTable();
}

// Returns the current selection, or null after telling the user it is empty
function requireSelectedQsos() {
	if (selectedQsos.size === 0) {
		BootstrapDialog.alert({
			title: lang_qslprint_info,
			message: lang_qslprint_select_at_least_one_row,
			type: BootstrapDialog.TYPE_INFO,
			closable: false,
			draggable: false,
			callback: function (result) {
			}
		});
		return null;
	}
	return getSelectedIds();
}

function markSelectedQsos() {
	var id_list = requireSelectedQsos();
	if (id_list === null) {
		return;
	}
	$('.markallprinted').prop("disabled", true);
	$.ajax({
		url: base_url + 'index.php/logbookadvanced/update_qsl',
		type: 'post',
		data: {'id': JSON.stringify(id_list, null, 2),
			'sent' : 'Y',
			'method' : ''
		},
		success: function(data) {
			removeQslRows(data);
			$('.markallprinted').prop("disabled", false);
		}
	});
}

function removeSelectedQsos() {
	var id_list = requireSelectedQsos();
	if (id_list === null) {
		return;
	}
	$('.removeall').prop("disabled", true);

	$.ajax({
		url: base_url + 'index.php/logbookadvanced/update_qsl',
		type: 'post',
		data: {'id': JSON.stringify(id_list, null, 2),
			'sent' : 'N',
			'method' : ''
		},
		success: function(data) {
			removeQslRows(data);
			$('.removeall').prop("disabled", false);
		}
	});
}

function exportSelectedQsos() {
	var id_list = requireSelectedQsos();
	if (id_list === null) {
		return;
	}
	$('.exportselected').prop("disabled", true);

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		var a;
		if (xhttp.readyState === 4 && xhttp.status === 200) {
			// Trick for making downloadable link
			a = document.createElement('a');
			a.href = window.URL.createObjectURL(xhttp.response);
			// Give filename you wish to download
			// Get the current date and time
			const now = new Date();

			// Format the date and time as UTC Ymd-Hi
			const year = now.getUTCFullYear();
			const month = String(now.getUTCMonth() + 1).padStart(2, '0'); // Months are zero-based
			const day = String(now.getUTCDate()).padStart(2, '0');
			const hours = String(now.getUTCHours()).padStart(2, '0');
			const minutes = String(now.getUTCMinutes()).padStart(2, '0');

			// Create the formatted filename
			const filename = `${my_call}-${year}${month}${day}-${hours}${minutes}.adi`;
			a.download = filename;
			a.style.display = 'none';
			document.body.appendChild(a);
			a.click();
		}
	};

	// Post data to URL which handles post request
	xhttp.open("POST", site_url+'/logbookadvanced/export_to_adif', true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	// You should set responseType as blob for binary responses
	xhttp.responseType = 'blob';
	xhttp.send("id=" + JSON.stringify(id_list, null, 2));

	$('.exportselected').prop("disabled", false);
}

function markMethod(){

	//grab the dropdown
	const select = document.getElementById('markqslmethod');

	//grab the selected method
	const methodkey = select.value;
	const method = select.options[select.selectedIndex].text;

	//perform function
	markMethodQSOs(methodkey === "ALL" ? '' : method);
}

function markMethodQSOs(method) {

	//unmark any QSO that is already marked for cleanup purposes
	unmarkallQSOs();

	//grab the table
	const table = document.getElementById('qslprint_table');

    //loop through each row except the header
    Array.from(table.tBodies[0].rows).forEach(row => {

		//get the send-method column
        const sendMethodCell = row.querySelector('td.send-method');

		//check if it contains the right method (or skip check if method is empty)
        if (sendMethodCell && (method === "" || sendMethodCell.textContent.trim() === method)) {

			//find the checkbox in the first cell
            const checkbox = row.querySelector('td:first-child input[type="checkbox"]');

			//check that box
			if (checkbox) {
                checkbox.checked = true;
            }
        }
    });
}

function unmarkallQSOs(){

	//grab the table
	const table = document.getElementById('qslprint_table');

	//loop through each row except the header
    Array.from(table.tBodies[0].rows).forEach(row => {

		//find the checkbox in the first cell
		const checkbox = row.querySelector('td:first-child input[type="checkbox"]');

		//check that box
		if (checkbox) {
			checkbox.checked = false;
		}
    });
}

function switchbandandfrequencydisplay(mode){

	// Column visibility instead of inline styles, so it survives every redraw
	var showBand = (mode !== 'frequency');
	var showFreq = (mode === 'frequency' || mode === 'both');

	qslprintTable().column(5).visible(showBand);	// Band
	qslprintTable().column(6).visible(showFreq);	// Frequency
}

document.getElementById('frequency_or_band').addEventListener('change', function (event) {
	//switch display options
	switchbandandfrequencydisplay(event.target.value);
});

function printDialog(printType, printAll = false) {
	const id_list = getSelectedIds();
	if (id_list.length === 0 && !printAll) {
		BootstrapDialog.alert({
			title: lang_qslprint_info,
			message: lang_qslprint_select_at_least_one_row,
			type: BootstrapDialog.TYPE_INFO,
			closable: false,
			draggable: false,
			callback: function (result) {
			}
		});
		return;
	}
	let title = '';
	if (printType === 'label') {
		title = lang_print_label;
	} else if (printType === 'qslcard') {
		title = lang_print_qslcard;
	}

	$.ajax({
		url: base_url + 'index.php/qslprint/printdialog',
		type: 'post',
		data: {'printType': printType, 'id_list': id_list, 'printAll': printAll},
		success: function (html) {
			BootstrapDialog.show({
				title: '<i class="fas fa-print me-2"></i>' + title,
				size: BootstrapDialog.SIZE_NORMAL,
				cssClass: 'qso-dialog',
				nl2br: false,
				message: html,
				onshown: function(dialog) {
				},
				buttons: [
					{
						label: lang_admin_close,
						cssClass: 'btn btn-secondary btn-sm',
						action: function (dialogItself) {
							dialogItself.close();
						}
					}
				],
			});
		}
	});
}

function requestedQslAction(action) {
	var url = base_url + 'index.php/qslprint/' + action + '/' + $('.station_id').val();
	// if someone wants to export we don't need to confirm, just do it
	if (action !== 'qsl_printed') {
		window.location.href = url;
		return;
	}
	BootstrapDialog.confirm({
			title: lang_qslprint_warning,
			message: lang_qslprint_are_you_sure,
			type: BootstrapDialog.TYPE_DANGER,
			closable: true,
			draggable: true,
			btnOKClass: 'btn-danger',
			callback: function(result) {
				if(result) {
					window.location.href = url;
				}
			},
		});
}

function getSelectedIds() {
	return Array.from(selectedQsos);
}

function printSelectedQsos(printAll) {
	if (printAll == true) {
		const tpl = document.getElementById('qslcard_template_id').value;
        // Print options come from the template's layout.options, not this form.
        window.open(base_url + 'index.php/qslpostcard/pdfqueue/' + tpl, '_blank');
	} else {
		let id_list = getSelectedIds();
		let $container = $('#qslcard_selected_ids');
		if (id_list.length) {
			$container.empty();
			$.each(id_list, function (i, id) {
				$('<input>').attr({ type: 'hidden', name: 'selected_ids[]' }).val(id).appendTo($container);
			});
		}
		let tplId = $('#qslcard_template_id').val();
		if (!tplId) {
			return;
		}
		let $form = $('#printQslCardForm');
		$form.attr('action', base_url + 'index.php/qslpostcard/pdfselected/' + tplId);
		$form.attr('target', '_blank');
		$form[0].submit();
	}
}

function saveAndPrintSelectedQsos(printAll) {
	if (printAll == true) {
		const tpl = document.getElementById('qslcard_template_id').value;
        window.location.href  = base_url + 'index.php/qslpostcard/pdfqueue/' + tpl + '?download=1', '_blank';
	} else {
		let id_list = getSelectedIds();
		let $container = $('#qslcard_selected_ids');
		if (id_list.length) {
			$container.empty();
			$.each(id_list, function (i, id) {
				$('<input>').attr({ type: 'hidden', name: 'selected_ids[]' }).val(id).appendTo($container);
			});
		}
		var tplId = $('#qslcard_template_id').val();
		if (!tplId) {
			return;
		}
		var $form = $('#printQslCardForm');
		$form.attr('action', base_url + 'index.php/qslpostcard/pdfselected/' + tplId + '?download=1');
		$form.attr('target', '_blank');
		$form[0].submit();
		dialog.close();
	}
}

function printLabel(printAll) {
	const id_list = getSelectedIds();
	if (printAll != true && id_list.length === 0) {
		BootstrapDialog.alert({
			title: lang_qslprint_warning,
			message: lang_qslprint_select_at_least_one_row,
			type: BootstrapDialog.TYPE_WARNING,
			closable: false,
			draggable: false,
			callback: function () {}
		});
		return;
	}
	const options = {
			'startat': $('#startat').val(),
			'grid': $('#gridlabel')[0].checked,
			'via': $('#via')[0].checked,
			'tnxmsg': $('#tnxmsg')[0].checked,
			'qslmsg': $('#qslmsg')[0].checked,
			'reference': $('#reference')[0].checked,
			'mycall': $('#mycall')[0].checked,
			'opcall': $('#opcall')[0].checked
		};
	let url, postData;
	if (printAll == true) {
		url = base_url + 'index.php/labels/print/All';
		postData = options;
	} else {
		url = base_url + 'index.php/labels/printids';
		postData = $.extend({'id': JSON.stringify(id_list, null, 2)}, options);
	}
	$.ajax({
		url: url,
		type: 'post',
		data: postData,
		xhr:function(){
			var xhr = new XMLHttpRequest();
			xhr.responseType= 'blob'
			return xhr;
		},
		success: function(data) {
			if(data){
				var file = new Blob([data], {type: 'application/pdf'});
				var fileURL = URL.createObjectURL(file);
				window.open(fileURL);
			}
			$('#printLabel').prop("disabled", false);
		},
		error: function (data) {
			BootstrapDialog.alert({
				title: lang_gen_advanced_logbook_error,
				message: lang_gen_advanced_logbook_label_print_error,
				type: BootstrapDialog.TYPE_DANGER,
				closable: false,
				draggable: false,
				callback: function (result) {
				}
			});
			$.each(id_list, function(k, v) {
				unselectQsoID(this);
			});
			$('#printLabel').prop("disabled", false);
		},
	});
}

function markQslPrinted(printAll) {
	$('#button_markprint').attr("disabled", true).addClass("running");
	if (printAll == true) {
		$.ajax({
			url: base_url + 'index.php/qslprint/qsl_printed/all',
			type: 'get',
			success: function () {
				clearQslprintSelection();
				reloadQslprintTable();
				$('#button_markprint').removeClass("running");
			},
			error: function () {
				$('#button_markprint').prop("disabled", false).removeClass("running");
			}
		});
	} else {
		let id_list = getSelectedIds();
		if (!id_list.length) {
			$('#button_markprint').prop("disabled", false).removeClass("running");
			return;
		}
		$.ajax({
			url: base_url + 'index.php/logbookadvanced/update_qsl',
			type: 'post',
			data: {
				'id': JSON.stringify(id_list, null, 2),
				'sent': 'Y',
				'method': ''
			},
			success: function (data) {
				removeQslRows(data);
				$('#button_markprint').prop("disabled", false).removeClass("running");
			},
			error: function () {
				$('#button_markprint').prop("disabled", false).removeClass("running");
			}
		});
	}
}
