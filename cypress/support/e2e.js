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

// Default database credentials
const db_host = "wavelog-db";
const db_name = "wavelog";
const db_user = "wavelog";
const db_password = "wavelog";


// Default first user data
const firstname = "John";
const lastname = "Smith";
const callsign = "HB9ABC";
const city = "Zurich";
const userlocator = "JN47RI";
const dxcc_id = "287";
const cnfm_password = "superSafePa33word";
const user_email = "john@example.com";


const username = "john.smith";
const password = "superSafePa33word";
const wrong_password = "wrongPassword";


// Import commands.js using ES2015 syntax:
import './commands'

// Alternatively, you can use CommonJS syntax:
// require('./commands')