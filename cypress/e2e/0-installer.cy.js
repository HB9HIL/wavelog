describe("Installer Test", () => {

	// Helper function to visit the installer page
	function visitInstallerPage() {
		// Visit the index. We should get redirected
		cy.visit("/index.php");

		// Check if the redirect to the Wavelog Installer was successful
		cy.url().
			should("include", "/install");

		// Check if we see the Wavelog Installer
		cy.get("body")
			.contains("Welcome to the Wavelog Installer")
			.should("be.visible");
	}

	// Helper function to click the "Continue" button
	function clickContinueButton() {
		cy.get('button[id="ContinueButton"]')
			.click();
	}

	// Clear the localStorage to avoid conflicts
	before(() => {
		cy.clearLocalStorageSnapshot();
		cy.setCookie('install_lang', 'en_US');
	});

	// Before each Test we have to call the installer again
	beforeEach(() => {
		cy.restoreLocalStorage();
		visitInstallerPage();
	});

	afterEach(() => {
		cy.saveLocalStorage();
	});

	
	// Test case: Run the complete installer
	it("Should run the installer", () => {
		
		cy.log("Invoking the installer tests. This can take a while...");

		const env_db = Cypress.env('db');
		const env_user = Cypress.env('user');

		clickContinueButton(); // Show Prechecks tab
		clickContinueButton(); // Show Configuration tab
		clickContinueButton(); // Show Database tab

		// Fill the database form with the credentials
		cy.get('input[id="db_hostname"]').type(env_db.host);
		cy.get('input[id="db_name"]').type(env_db.name);
		cy.get('input[id="db_username"]').type(env_db.user);
		cy.get('input[id="db_password"]').type(env_db.password);

		// Click the DB connection test button
		cy.get('button[id="db_connection_test_button"]')
			.click();

		// The result box should be green (class "alert-success")
		cy.get('div[id="db_connection_testresult"]', { timeout: 5000 })
			.should("be.visible")
			.and("have.class", "alert-success");

		clickContinueButton(); // Show First User tab

		// Type the data into the fields
		cy.get('input[id="firstname"]').type(env_user.firstname);
		cy.get('input[id="lastname"]').type(env_user.lastname);
		cy.get('input[id="callsign"]').type(env_user.callsign);
		cy.get('input[id="city"]').type(env_user.city);
		cy.get('input[id="userlocator"]').type(env_user.userlocator);
		cy.get('select[id="dxcc_id"]').select(env_user.dxcc_id);
		cy.get('input[id="username"]').type(env_user.username);
		cy.get('input[id="password"]').type(env_user.password);
		cy.get('input[id="cnfm_password"]').type(env_user.cnfm_password);
		cy.get('input[id="user_email"]').type(env_user.email);

		clickContinueButton(); // Last Tab

		cy.get('button[id="submit"]')
			.click();

		// Check if the installer is running
		cy.get("body", { timeout: 2000 })
			.contains("Installation")
			.should("be.visible");

		// Check if all steps show green after some time
		cy.get('i[id="config_file_check"]', { timeout: 2000 })
			.should("be.visible")
			.and("have.class", "fa-check-circle");

		cy.get('i[id="database_file_check"]', { timeout: 2000 })
			.should("be.visible")
			.and("have.class", "fa-check-circle");

		cy.get('i[id="database_tables_check"]', { timeout: 2000 })
			.should("be.visible")
			.and("have.class", "fa-check-circle");

		// Click the log button to stop the countdown timer
		cy.get('button[id="toggleLogButton"]')
			.click();

		cy.get('i[id="update_dxcc_check"]', { timeout: 60000 })
			.should("be.visible")
			.and("have.class", "fa-check-circle");

		cy.get('i[id="installer_lock_check"]', { timeout: 2000 })
			.should("be.visible")
			.and("have.class", "fa-check-circle");

		// Check the browser language
		cy.setCookie('language', 'english');

		// Click the success button to get to the login page
		cy.get('a.btn.btn-primary')
			.contains('Done. Go to the user login ->')
			.should('be.visible')
			.click();

		// Check if the login page shows up
		cy.get("body")
			.contains("Congrats! Wavelog was successfully installed. You can now login for the first time.")
			.should("be.visible");
	});

});