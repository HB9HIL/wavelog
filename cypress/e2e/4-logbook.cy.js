import 'cypress-real-events/support';

describe("Menu -> Logbook Overview", () => {
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

	it("Call the Logbook Overview from the header menu", () => {
		cy.visit("/index.php/dashboard");

		cy.get(".nav-link")
			.contains("Logbook")
			.realHover();

		cy.get(".dropdown-menu")
			.should("be.visible")
			.contains("Overview")
			.click();

		// See the Logbook Overview
		cy.url().should("include", "/logbook");
	});

	it("Check some elements on the Logbook Overview page", () => {
		cy.visit("/index.php/logbook");

		cy.get("h2")
			.contains("Logbook")
			.should("be.visible");

		cy.get("#map")
			.should("be.visible");

		cy.get(".contacttable")
			.should("be.visible");
	});

	it("Table and column headers are visible", () => {
		cy.visit("/index.php/logbook");

		cy.get("table.contacttable").should("be.visible");

		cy.get("table.contacttable thead tr.titles").within(() => {
			cy.contains("th", "Date").should("be.visible");
			cy.contains("th", "Time").should("be.visible");
			cy.contains("th", "Call").should("be.visible");
			cy.contains("th", "Mode").should("be.visible");
			cy.contains("th", "RST (S)").should("be.visible");
			cy.contains("th", "RST (R)").should("be.visible");
		});
	});

	it("At least one data row is present", () => {
		cy.visit("/index.php/logbook");

		cy.get("table.contacttable tbody tr").should("have.length.at.least", 1);

		cy.get("table.contacttable tbody tr")
			.first()
			.within(() => {
				cy.get("td").eq(2).invoke("text").should("not.be.empty");
			});
	});

	it("Opens the QSO dropdown on Hover", () => {
		cy.visit("/index.php/logbook");

		cy.get("table.contacttable tbody tr")
			.first()
			.within(() => {
				cy.get("td").eq(10).realHover();
			})
			.get(".menuOnBody > #edit_qso")
			.should("be.visible")
			.contains("Edit QSO")
			.click()
			.wait(300)
			.get(".modal-content")
			.should("be.visible")
			.get(".modal-header")
			.should("be.visible")
			.contains("QSO Data");
	});

});

describe("Menu -> Logbook Advanced", () => {
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

	it("Call the Logbook Advanced from the header menu", () => {
		cy.visit("/index.php/dashboard");

		cy.get(".nav-link")
			.contains("Logbook")
			.realHover();

		cy.get(".dropdown-menu")
			.should("be.visible")
			.contains("Advanced")
			.click();

		// See the Logbook Overview
		cy.url().should("include", "/logbookadvanced");

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
			// .contains("Search")
			.should("be.visible");
		cy.get('button[id="dupeButton"]')
			.contains("Dupes")
			.should("be.visible");
		cy.get('button[id="editButton"]')
			.contains("Edit")
			.should("be.visible");
		cy.get('button[id="deleteQsos"]')
			// .contains("Delete")
			.should("be.visible");
		cy.get('button[id="mapButton"]')
			.contains("Map")
			.should("be.visible");
		cy.get('button[id="optionButton"]')
			// .contains("Options")
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