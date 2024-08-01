describe("Installer Test", () => {
	it("Should show the Installer", () => {
		// Visit the main page which should redirect to the installer
		cy.visit("/index.php");

		// Check if it redirects to the installer
		cy.url().should("include", "/install");
	});

	it("Should be able to click 'Continue'", () => {
		// Visit the login page
		cy.visit("/index.php/install");

		// Click the login button
		cy.get('button[id="ContinueButton"]').click();

        // Check if you see the php module php-curl
		cy.get("body")
			.contains("php-curl")
			.should("be.visible")
	});

});
