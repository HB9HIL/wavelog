describe("Login Test", () => {

    it("Should be able to login", () => {

        cy.login();

        cy.url().should("include", "/dashboard");

    });

});