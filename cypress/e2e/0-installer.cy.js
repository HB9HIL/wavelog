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

		const env_db = Cypress.env('db');
		
		visitInstallerPage();
		clickContinueButton(); // Prechecks tab
		clickContinueButton(); // Configuration tab
		clickContinueButton(); // Database tab

		cy.get('input[id="db_hostname"]').type(env_db.host); 
    	cy.get('input[id="db_name"]').type(env_db.name);     
    	cy.get('input[id="db_username"]').type(env_db.user); 
    	cy.get('input[id="db_password"]').type(env_db.password);
		cy.get('button[id="db_connection_test_button"]').wait(500).click();	// The result box should be green (class "alert-success")
		cy.get('div[id="db_connection_testresult"]')
			.should("be.visible")
			.and("have.class", "alert-success");
	});

	// Test case: Run the complete installer
it("Should run through the complete installer", () => {

    const env_db = Cypress.env('db');
    const env_user = Cypress.env('user');

    visitInstallerPage();
    clickContinueButton(); // Prechecks tab
    clickContinueButton(); // Configuration tab
    clickContinueButton(); // Database tab

    // Type the credentials into the fields
    cy.get('input[id="db_hostname"]').type(env_db.host);
    cy.get('input[id="db_name"]').type(env_db.name);
    cy.get('input[id="db_username"]').type(env_db.user);
    cy.get('input[id="db_password"]').type(env_db.password);

    // Click the connection test button
    cy.get('button[id="db_connection_test_button"]').click();

    // The result box should be green (class "alert-success")
    cy.get('div[id="db_connection_testresult"]', { timeout: 10000 })
        .should("be.visible")
        .and("have.class", "alert-success");

    clickContinueButton(); // First User tab

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

    cy.get('button[id="submit"]').click();

    // Check if the installer is running
		cy.get("body").wait(500).contains("Installation").should("be.visible");

		// Check if all steps show green after some time
		cy.get('i[id="config_file_check"]').wait(1000).should("be.visible").and("have.class", "fa-check-circle");
		cy.get('i[id="database_file_check"]').wait(1000).should("be.visible").and("have.class", "fa-check-circle");
		cy.get('i[id="database_tables_check"]').wait(1000).should("be.visible").and("have.class", "fa-check-circle");
		cy.get('i[id="database_tables_check"]').wait(1000).should("be.visible").and("have.class", "fa-check-circle");
		// Click the log button to stop the countdown timer
		cy.get('button[id="toggleLogButton"]').click();
		cy.get('i[id="update_dxcc_check"]', { timeout: 60000 }).should("be.visible").and("have.class", "fa-check-circle");
		cy.get('i[id="installer_lock_check"]').wait(1000).should("be.visible").and("have.class", "fa-check-circle");

		// Click the success button to get to the login page
		cy.get('a.btn.btn-primary').contains('Done. Go to the user login ->').should('be.visible').wait(300).click();

		// Check if the login page shows up
		cy.get("body").wait(300).contains("Congrats! Wavelog was successfully installed. You can now login for the first time.").should("be.visible");
	});
});