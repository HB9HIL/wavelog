describe("QSO Logging", () => {
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

	it("Call QSO live logging page", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/qso?manual=0");

		// Make sure we see the QSO Logging form
		cy.get("#callsign")
			.should("be.visible");
		cy.get("#rst_sent")
			.should("be.visible");
		cy.get('button[id="saveQso"]')
			.should("be.visible");
	});

	it("Call the different tabs", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/qso?manual=0");

		// Click the Station Tab
		cy.get('a[id="station-tab"]')
			.click();

		// Make sure we see the frequency_rx field
		cy.get("#frequency_rx")
			.should("be.visible");
		
		// Click the General Tab
		cy.get('a[id="general-tab"]')
			.click();

		// Make sure we see the continent field
		cy.get("#continent")
			.should("be.visible");

		// Click the Satellite Tab
		cy.get('a[id="satellite-tab"]')
			.click();
		
		// Make sure we see the sat_name field
		cy.get("#sat_name")
			.should("be.visible");

		// Click the Notes Tab
		cy.get('a[id="notes-tab"]')
			.click();

		// Make sure we see the notes field
		cy.get("#notes")
			.should("be.visible");

		// Click the QSL Tab	
		cy.get('a[id="qsl-tab"]')
			.click();
	});

	it("Log a QSO", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/qso?manual=0");

		// Fill in the QSO data
		cy.get("#callsign")
			.type("DK0TU");
		cy.get("#band")
			.select("20m");
		cy.get("#mode")
			.select("SSB");
		cy.get('button[id="saveQso"]')
			.wait(500)
			.click();

		// Check if the QSO has been saved
		cy.get('body')
			.contains("QSO Added");
	});

	it("Check if the QSO is shown in latest Contacts", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/qso?manual=0");

		// Check if the QSO is shown in the latest contacts
		cy.get('body')
			.contains("DKØTU");
	});

	it("Check the Frequency Input", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/qso?manual=0");

		// Choose a mode
		cy.get("#mode")
			.select("SSB");
		// Choose a band
		cy.get("#band")
			.select("17m");

		// Check if the frequency is set correctly
		cy.get("#frequency")
			.should("have.value", "18130000");
	});

	
});