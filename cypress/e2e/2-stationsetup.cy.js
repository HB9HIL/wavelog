describe("Station Setup", () => {
    beforeEach(() => {
		// Login
		cy.login();

		// Visit the Stationsetup Page
		cy.visit("/index.php/stationsetup");
	});

	it("Should show the Stationsetup page", () => {
		// Make sure we see both tables. Logbooks and Locations
		cy.get("#station_logbooks_table_wrapper")
			.should("be.visible")
			.contains("Active Logbook");

		cy.get("#station_locations_table_wrapper")
			.should("be.visible")
			.contains("Active Station");
	});

	it("Should be possible to enable visitor site", () => {

		// Load variables
		const env_stationsetup = Cypress.env('stationsetup');

		// Click the visitor site button
		cy.get('button[id="1"]')
			.should('be.visible')
			.and("have.class", "editVisitorLink")
			.click();
		
		// Wait until the modal pops up
		cy.get("#NewStationLogbookModal_title")
			.contains("Edit visitor link")
			.wait(300)
			.should("be.visible");
		
		// and set the public slug
		cy.get('input[id="publicSlugInput"]')
			.type(env_stationsetup.public_slug);

		cy.get('button')
			.contains("Save")
			.click()

	});
});