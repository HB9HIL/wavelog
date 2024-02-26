
function displayOperatorDialog() {

	$.ajax({
		url: base_url + "index.php/Operator/displayOperatorDialog",
		type: 'GET',
		dataType: 'html',
		success: function(data) {
			$('body').append(data);

			// Aktiviere das Bootstrap-Modal
			var operatorModal = new bootstrap.Modal(document.getElementById('operatorModal'));
			operatorModal.show();
		},
		error: function() {
			// Behandlung von Fehlern
			console.log('Fehler beim Laden der PHP-Datei.');
		}
	});
}

function saveOperator() {
	$.ajax({
		url: base_url + 'index.php/operator/saveOperator',
		method: 'POST',
	});
}
