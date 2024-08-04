describe("Login Test", () => {
    it("Should be able to login", () => {
        // Login
        cy.login();

        // Check if we got redirected to the dashboard
        cy.url().
            should("include", "/dashboard");
    });

    it("Should show a warning if using the wrong password", () => {
        // Try to login with wrong credentials
        cy.wrong_login();

        // Check if the 'wrong password' warning appears
        cy.get('.alert-danger')
            .contains("Incorrect username or password!")
            .should("be.visible");
    });
});

describe("Version Info Modal", () => {
	beforeEach(() => {
		cy.login();
	});

	it("should open after login", () => {
		cy.get(".modal-title")
			.contains("Version Info")
			.should("be.visible");
	});

	it("should close after clicking 'Close' button", () => {
		// check if the modal is visible
		cy.get(".modal-title")
			.contains("Version Info")
			.should("be.visible");

		// click the 'Close' button
		cy.get("button")
			.contains("Close")
			.should("be.visible")
			.wait(500)
			.click();

		// check if the modal is not visible
		cy.get(".modal-title")
			.contains("Version Info")
			.should("not.be.visible");
	});

	it("should close after clicking 'Don't show again' button", () => {
		// check if the modal is visible
		cy.get(".modal-title")
			.contains("Version Info")
			.should("be.visible");

		// click the "Don't show again" button
		cy.get("button")
			.contains("Don't show again")
			.should("be.visible")
			.wait(500)
			.click();

		// check if the modal is not visible
		cy.get(".modal-title")
			.contains("Version Info")
			.should("not.be.visible");
	});

	it("should not show the version info dialog after click 'Dont show again' button", () => {
		// check if the modal is not visible
		cy.get(".modal-title")
			.should("not.exist");
	});
});