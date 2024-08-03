describe("Login Test", () => {
    it("Should be able to login", () => {
        cy.visit("/index.php");
        cy.url().should("include", "/user/login");
        cy.get("body").contains("Username").should("be.visible");

        const username = "john.smith";
        const password = "superSafePa33word";

        cy.get('input[name="user_name"]').type(username);
        cy.get('input[name="user_password"]').type(password);

        cy.get('button[type="submit"]').wait(100).click();

        cy.url().wait(300).should("include", "/dashboard");
    });
});