describe("Station Setup", () => {

	before(() => {
		cy.setCookie('language', 'english');
		cy.login();
		cy.getCookies().then(cookies => {
			cy.writeFile('cypress/fixtures/cookies.json', cookies);
		});
	});

	beforeEach(() => {
		cy.readFile('cypress/fixtures/cookies.json').then(cookies => {
			cookies.forEach(cookie => {
				cy.setCookie(cookie.name, cookie.value);
			});
		});
	});

	it("Should show the Stationsetup page", () => {
		// Visit the Stationsetup Page
		cy.visit("/index.php/stationsetup");

		// Make sure we see both tables. Logbooks and Locations
		cy.get("#station_logbooks_table_wrapper")
			.should("be.visible")
			.contains("Active Logbook");

		cy.get("#station_locations_table_wrapper")
			.should("be.visible")
			.contains("Active Station");
	});

	it("Should be possible to enable visitor site", () => {
		// Visit the Stationsetup Page
		cy.visit("/index.php/stationsetup");

		// Load variables
		const env_stationsetup = Cypress.env('stationsetup');

		// Click the visitor site button
		cy.get('button[id="1"]')
			.should('be.visible')
			.and("have.class", "editVisitorLink")
			.click();

		// Wait until the modal pops up
		cy.get("#NewStationLogbookModal_title", { timeout: 2000 })
			.contains("Edit visitor link")
			.should("be.visible");

		// set the public slug
		cy.get('input[id="publicSlugInput"]')
			.type(env_stationsetup.public_slug);

		// and save it
		cy.get('button')
			.contains("Save")
			.click();

		// now the delete button should be visible
		cy.get('button.deletePublicSlug.btn-outline-danger')
			.should('be.visible');
	});

	it("Should be possible to view the visitor site", () => {

		// Load variables
		const env_stationsetup = Cypress.env('stationsetup');

		// to be absolutely sure this worked we can call the visitor site
		cy.visit("/index.php/visitor/" + env_stationsetup.public_slug);

		// where we should see a button for the redirect to github
		cy.contains("Visit Wavelog on Github");

		// and the QSO breakdown
		cy.contains("QSOs Breakdown");
	});

	it("Should be possible to view the export map", () => {

		// Load variables
		const env_stationsetup = Cypress.env('stationsetup');

		// to be absolutely sure this worked we can call the visitor site
		cy.visit("/index.php/visitor/exportmap/" + env_stationsetup.public_slug);

		// where we should see a button for the redirect to github
		cy.get("#exportmap")
			.should("be.visible");
	});

	it("Should be possible to create a new station logbook", () => {
		// Load variables
		const env_stationsetup = Cypress.env('stationsetup');

		// Visit the Stationsetup Page
		cy.visit("/index.php/stationsetup");

		// Click the button to open the modal
		cy.get('body')
			.contains('Create Station Logbook')
			.click();

		// fill in the new logbook name
		cy.get('input[id="logbook_name"]')
			.type(env_stationsetup.logbook_name);

		// and save it
		cy.get('button')
			.contains("Save")
			.click();

		// The second logbook should appear in the list now
		cy.get('body')
			.contains(env_stationsetup.logbook_name);
	});

	it("Should be possible to create a new station location", () => {
		// Load variables
		const env_stationsetup = Cypress.env('stationsetup');

		// Visit the Stationsetup Page
		cy.visit("/index.php/stationsetup");

		// Click the button to open the modal
		cy.get('body')
			.contains('Create a Station Location')
			.click();

		// fill in the new data
		cy.get('input[id="stationNameInput"]')
			.type(env_stationsetup.location_name);

		cy.get('input[id="stationCallsignInput"]')
			.type(env_stationsetup.station_callsign);

		cy.get('select[id="dxcc_id"]')
			.select(env_stationsetup.station_dxcc);

		cy.get('input[id="stationGridsquareInput"]')
			.type(env_stationsetup.station_gridsquare);

		// and save it
		cy.get('button[type="submit"]')
			.contains("Create Station Location")
			.click();

		// The second location should appear in the list now
		cy.get('body')
			.contains(env_stationsetup.station_callsign);
	});

});