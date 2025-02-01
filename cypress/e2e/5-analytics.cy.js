import 'cypress-real-events/support';

const pages = [
    { name: "Statistics", url: "/statistics" },
    { name: "Antenna Analytics", url: "/statistics/antennaanalytics" },
    { name: "QSL Statistics", url: "/statistics/qslstats" },
    { name: "Gridsquare Map", url: "/gridmap" },
    { name: "Activated Gridsquares", url: "/activated_gridmap" },
    { name: "Gridsquare Activators", url: "/activators" },
    { name: "Distances Worked", url: "/distances" },
    { name: "Satellite Distance Records", url: "/distancerecords" },
    { name: "Days with QSOs", url: "/dayswithqso" },
    { name: "Timeline", url: "/timeline" },
    { name: "Accumulated Statistics", url: "/accumulated" },
    { name: "Timeplotter", url: "/timeplotter" },
    { name: "Continents", url: "/continents" },
    { name: "Callsign Statistics", url: "/callstats" }
  ];

pages.forEach(page => {
    describe(page.name, () => {
        before(() => {
            cy.setCookie('language', 'english');
            cy.login();
            cy.getCookies().then(cookies => {
                cy.writeFile('cypress/fixtures/cookies.json', cookies);
            });
        });

        beforeEach(() => {
            cy.readFile('cypress/fixtures/cookies.json').then(cookies => {
                cookies.forEach(cookie => {
                    cy.setCookie(cookie.name, cookie.value);
                });
            });
        });

        it("Call the Page from the header menu", () => {
            cy.visit("/index.php/dashboard");

            cy.get(".nav-link")
                .contains("Analytics")
                .realHover();

            cy.get(".dropdown-menu")
                .should("be.visible")
                .contains(page.name)
                .click();

            cy.url().should("include", page.url);
        });
    });
});