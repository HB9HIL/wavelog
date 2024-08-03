Cypress.Commands.add("login", () => {
	cy.visit("/index.php");
    cy.url().should("include", "/user/login");
    cy.get("body").contains("Username").should("be.visible");

    const username = "john.smith";
    const password = "superSafePa33word";

    cy.get('input[id="username"]').type(username);
	cy.get('input[id="password"]').type(password);

    cy.get('button[type="submit"]').wait(100).click();

    cy.url().wait(300).should("include", "/dashboard");
});
