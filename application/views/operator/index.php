<div class="modal fade" id="operatorModal" tabindex="-1" aria-labelledby="operatorLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="operatorLabel">Operator Rufzeichen</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="operator_callsign" class="form-label mb-4">Bitte gib dein Rufzeichen an. So kann nachher ausgewertet werden, wer wieviele QSO gemacht hat.</label>
                    <br>
                    <div class="row p-2">
                        <div class="col">
                            <p>Dein persönliches Rufzeichen: </p>
                            <input type="text" class="form-control w-auto" id="operator_callsign" name="operator_callsign">
                            <div class="invalid-feedback">
                                Du musst dein PERSÖNLICHES Rufzeichen angeben.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="saveOperator()">Speichern</button>
            </div>
        </div>
    </div>
</div>