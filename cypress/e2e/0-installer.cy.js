describe("Installer Test", () => {
	// Helper function to visit the installer page
	function visitInstallerPage() {
		cy.visit("/index.php");
		cy.url().should("include", "/install");
		cy.get("body").contains("Welcome to the Wavelog Installer").should("be.visible");
	}

	// Helper function to click the "Continue" button
	function clickContinueButton() {
		cy.get('button[id="ContinueButton"]').wait(500).click();
	}

	// Test case: Display the installer
	it("Should show the Installer", () => {
		visitInstallerPage();
	});

	// Test case: Click "Continue"
	it("Should be able to click 'Continue'", () => {
		visitInstallerPage();
		clickContinueButton();

		// Check if the php-curl module is visible
		cy.get("body").contains("php-curl").should("be.visible");
	});

	// Test case: Show positive database connection for Docker setup
	it("Should show positive db connection for docker setup", () => {
		visitInstallerPage();
		clickContinueButton(); // Prechecks tab
		clickContinueButton(); // Configuration tab
		clickContinueButton(); // Database tab

		// Default database credentials
		const db_host = "wavelog-db";
		const db_name = "wavelog";
		const db_user = "wavelog";
		const db_password = "wavelog";

		// Type the credentials into the fields
		cy.get('input[id="db_hostname"]').type(db_host);
		cy.get('input[id="db_name"]').type(db_name);
		cy.get('input[id="db_username"]').type(db_user);
		cy.get('input[id="db_password"]').type(db_password);

		// Click the connection test button
		cy.get('button[id="db_connection_test_button"]').wait(500).click();

		// The result box should be green (class "alert-success")
		cy.get('div[id="db_connection_testresult"]')
			.should("be.visible")
			.and("have.class", "alert-success");
	});

	// Test case: Run the complete installer
	it("Should run through the complete installer", () => {
		visitInstallerPage();
		clickContinueButton(); // Prechecks tab
		clickContinueButton(); // Configuration tab
		clickContinueButton(); // Database tab

		// Default database credentials
		const db_host = "wavelog-db";
		const db_name = "wavelog";
		const db_user = "wavelog";
		const db_password = "wavelog";

		// Type the credentials into the fields
		cy.get('input[id="db_hostname"]').type(db_host);
		cy.get('input[id="db_name"]').type(db_name);
		cy.get('input[id="db_username"]').type(db_user);
		cy.get('input[id="db_password"]').type(db_password);

		// Click the connection test button
		cy.get('button[id="db_connection_test_button"]').wait(500).click();

		// The result box should be green (class "alert-success")
		cy.get('div[id="db_connection_testresult"]')
			.should("be.visible")
			.and("have.class", "alert-success");

		clickContinueButton(); // First User tab

		// Default first user data
		const firstname = "John";
		const lastname = "Smith";
		const callsign = "HB9ABC";
		const city = "Zurich";
		const userlocator = "JN47RI";
		const dxcc_id = "287";
		const username = "john.smith";
		const password = "superSafePa33word";
		const cnfm_password = "superSafePa33word";
		const user_email = "john@example.com";

		// Type the data into the fields
		cy.get('input[id="firstname"]').type(firstname);
		cy.get('input[id="lastname"]').type(lastname);
		cy.get('input[id="callsign"]').type(callsign);
		cy.get('input[id="city"]').type(city);
		cy.get('input[id="userlocator"]').type(userlocator);
		cy.get('select[id="dxcc_id"]').select(dxcc_id);
		cy.get('input[id="username"]').type(username);
		cy.get('input[id="password"]').type(password);
		cy.get('input[id="cnfm_password"]').type(cnfm_password);
		cy.get('input[id="user_email"]').type(user_email);

		clickContinueButton(); // Last Tab

		cy.get('button[id="submit"]').wait(500).click();

		// Check if the installer is running
		cy.get("body").wait(1000).contains("Installation").should("be.visible");
	});
});