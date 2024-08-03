Cypress.Commands.add("login", () => {

    const env_user = Cypress.env('user');

	cy.visit("/index.php");
    cy.url().should("include", "/user/login");
    cy.get("body").contains("Username").should("be.visible");

    cy.get('input[name="user_name"]').type(env_user.username);
	cy.get('input[name="user_password"]').type(env_user.password);

    cy.get('button[type="submit"]').wait(100).click();

    cy.url().wait(300).should("include", "/dashboard");
});

Cypress.Commands.add("wrong_login", () => {

    const env_user = Cypress.env('user');

	cy.visit("/index.php");
    cy.url().should("include", "/user/login");
    cy.get("body").contains("Username").should("be.visible");

    cy.get('input[name="user_name"]').type(env_user.username);
	cy.get('input[name="user_password"]').type(env_user.wrong_password);

    cy.get('button[type="submit"]').wait(100).click();

    cy.url().wait(300).should("include", "/dashboard");
});