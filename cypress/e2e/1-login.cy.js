const cypress = require("cypress");
const { it } = require("mocha");

describe("Login Test", () => {
    it("Should be able to login", () => {
        cy.login();
    });
});