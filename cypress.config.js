const { defineConfig } = require("cypress");

module.exports = defineConfig({
  projectId: 'Wavelog Cypress Testing',
	e2e: {
		baseUrl: "http://localhost/",
		video: true,
		viewportWidth: 1920,
		viewportHeight: 1080,
		setupNodeEvents(on, config) {
			// implement node event listeners here
		},
	},
});
