describe("Logbook Advanced", () => {
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

	it("Call the advanced logbook", () => {
		// Visit the Logbook Advanced Page
		cy.visit("/index.php/logbookadvanced");

		// Make sure we all buttons
		cy.get("body")
			.contains("Quickfilters")
			.should("be.visible");
		cy.get("body")
			.contains("QSL Filters")
			.should("be.visible");
		cy.get("body")
			.contains("Filters")
			.should("be.visible");
		cy.get("body")
			.contains("Actions")
			.should("be.visible");
		cy.get("body")
			.contains("Results")
			.should("be.visible");
		cy.get("body")
			.contains("Location")
			.should("be.visible");
		cy.get("button[id='searchButton']")
			.contains("Search")
			.should("be.visible");
		cy.get('button[id="dupeButton"]')
			.contains("Dupes")
			.should("be.visible");
		cy.get('button[id="editButton"]')
			.contains("Edit")
			.should("be.visible");
		cy.get('button[id="deleteQsos"]')
			.contains("Delete")
			.should("be.visible");
		cy.get('button[id="mapButton"]')
			.contains("Map")
			.should("be.visible");
		cy.get('button[id="optionButton"]')
			.contains("Options")
			.should("be.visible");
		cy.get('button[id="resetButton"]')
			.contains("Reset")
			.should("be.visible");
	});

	it("Should expand the Action Buttons", () => {
		// Visit the Logbook Advanced Page
		cy.visit("/index.php/logbookadvanced");

		// Clicking on Actions should expand the dropdown
		cy.get('button')
			.contains("Actions")
			.click();
		cy.get('body')
			.contains("Not Sent", { timeout: 1000 });
	});

	it("Should show the map", () => {
		// Visit the Logbook Advanced Page
		cy.visit("/index.php/logbookadvanced");

		// Click the map button
		cy.get('button[id="mapButton"]')
			.click();

		// Make sure we see the map
		cy.get("#advancedmap")
			.should("be.visible", { timeout: 1000 });

	});

	it("Should show the options", () => {
		// Visit the Logbook Advanced Page
		cy.visit("/index.php/logbookadvanced");

		// Click the options button
		cy.get('button[id="optionButton"]')
			.click();

		// Make sure we see the options
		cy.get(".modal-dialog")
			.contains("Options for the Advanced Logbook")
			.should("be.visible", { timeout: 1000 });

	});

});