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
    callsign: "4W7EST",
    city: "Test City",
    userlocator: "JN47RI",
    dxcc: "switzerl",
    dxcc_selectname: "Switzerland - HB",
    email: "john@example.com",
    username: "john.smith",
    password: "superSafePa33word",
    cnfm_password: "superSafePa33word",
    wrong_password: "wrongPassword"
});

Cypress.env('stationsetup', {
    public_slug: "cypress",
    logbook_name: "Log 2",
    location_name: "Portable",
    station_callsign: "4W7EST/P",
    station_dxcc: "287",
    station_gridsquare: "JN48RI"
});


// Import commands.js using ES2015 syntax:
import './commands'

// Support for localStorage
import "cypress-localstorage-commands";

// Alternatively, you can use CommonJS syntax:
// require('./commands')