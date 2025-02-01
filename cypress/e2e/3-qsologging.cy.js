import 'cypress-real-events/support';

describe("QSO Live Logging", () => {
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
		cy.get("#band")
			.select("20m");
		cy.get("#mode")
			.select("SSB");
		cy.get("#callsign")
			.type("DK0TU")
			.blur();
		cy.get('#timesWorked')
			.should('contain.text', 'worked before')
			.wait(300)
			.get('button[id="saveQso"]')
			.click();

		// Check if the QSO has been saved
		cy.get('body')
			.contains("QSO Added");
	});

	it("Log a second QSO to check if worked before", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/qso?manual=0");

		// Fill in the QSO data
		cy.get("#callsign")
			.type("DK0TU")
			.blur();
		cy.get("body")
			.contains("worked before")
			.should("be.visible", { timeout: 1000 });
		cy.get("#band")
			.select("40m");
		cy.get("#mode")
			.select("AM");
		cy.get('button[id="saveQso"]')
			.wait(2000)
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

describe("SimpleFLE", () => {
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

    it("Call SimpleFLE from the header menu", () => {
		cy.visit("/index.php/dashboard");

		cy.get(".nav-link")
			.contains("QSO")
			.realHover();

		cy.get(".dropdown-menu")
			.should("be.visible")
			.contains("Simple Fast Log Entry")
			.click();

		// See SimpleFLE
		cy.url().should("include", "/simplefle");

        // Make sure we see the SimpleFLE form
        cy.get("#qsodate")
            .should("be.visible");

        cy.get("#contest")
            .should("be.visible");

        cy.get("#sfle_textarea")
            .should("be.visible");

        cy.get("table.sfletable thead tr").within(() => {
            cy.contains("th", "Date").should("be.visible");
            cy.contains("th", "Time").should("be.visible");
            cy.contains("th", "Callsign").should("be.visible");
            cy.contains("th", "Band").should("be.visible");
            cy.contains("th", "Mode").should("be.visible");
            cy.contains("th", "RST (S)").should("be.visible");
            cy.contains("th", "RST (R)").should("be.visible");
            cy.contains("th", "Gridsquare").should("be.visible");
            cy.contains("th", "Refs*").should("be.visible");
        });

        // Check if atleast the save button is visible
        cy.get('.js-save-to-log')
            .should("be.visible")
            .contains("Save in Wavelog");
	});

    it("Enter some QSO data", () => {
        cy.visit("/index.php/simplefle");

        // Enter some data
        cy.get("#qsodate")
            .type("2021-01-01");

        cy.get("#sfle_textarea")
            .type("20m ssb\n1200 hb9hil jn47rh")
            .type("{enter}");

        // Check if the QSO is entered
        cy.get("table.sfletable tbody tr")
            .should("have.length", 1)
            .first()
            .within(() => {
                cy.get("td").eq(0).should("contain.text", "2021-01-01");
                cy.get("td").eq(1).should("contain.text", "1200");
                cy.get("td").eq(2).should("contain.text", "HB9HIL");
                cy.get("td").eq(3).should("contain.text", "20m");
                cy.get("td").eq(4).should("contain.text", "SSB");
                cy.get("td").eq(5).should("contain.text", "59");
                cy.get("td").eq(6).should("contain.text", "59");
                cy.get("td").eq(7).should("contain.text", "JN47RH");
            });

        // Save the QSO
        cy.get('.js-save-to-log')
            .click();

        // Check if the modal pops up and hit ok
        cy.get(".modal-dialog")
            .get(".modal-body")
            .contains("Are you sure that you want to add these QSO to the Log and clear the session?")
            .should("be.visible")
            .get(".modal-footer")
            .contains("OK")
            .click();
        
        cy.get("body")
            .contains("QSO Logged!");
    });
});

describe("Live Contest Logging", () => {

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

	it("Call Contest live logging page", () => {
		// Visit the QSO Live Logging Page
		cy.visit("/index.php/contesting?manual=0");

		// Make sure we see the Contest Logging form
		cy.get("#callsign")
			.should("be.visible");
		cy.get("#contestname_select")
			.should("be.visible");
		cy.get("#rst_sent")
			.should("be.visible");
		cy.get('button[onclick="logQso();"]')
			.should("be.visible");
	});

});