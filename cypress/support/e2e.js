// ***********************************************************
// This support/e2e.js file is automatically processed and
// loaded before your test files in a production environment.
//
// Use this file to set up global configurations and
// behaviors that modify Cypress for your test suite.
//
// For more information, refer to:
// https://on.cypress.io/configuration
// ***********************************************************

// Set global variables
Cypress.env('db', {
    host: "wavelog-db",
    name: "wavelog",
    user: "wavelog",
    password: "wavelog"
});

Cypress.env('user', {
    firstname: "John",
    lastname: "Smith",
    callsign: "HB9ABC",
    city: "Zurich",
    userlocator: "JN47RI",
    dxcc_id: "287",
    email: "john@example.com",
    username: "john.smith",
    password: "superSafePa33word",
    cnfm_password: "superSafePa33word",
    wrong_password: "wrongPassword"
});

Cypress.env('stationsetup', {
    public_slug: "cypress"
});


// Import commands.js using ES2015 syntax:
import './commands'

// Support for localStorage
import "cypress-localstorage-commands";

// Alternatively, you can use CommonJS syntax:
// require('./commands')