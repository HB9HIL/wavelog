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