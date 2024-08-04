const { defineConfig } = require("cypress");

module.exports = defineConfig({
  projectId: 'Wavelog Cypress Testing',
	e2e: {
		baseUrl: "http://localhost:8087/",
		// video: true,
		viewportWidth: 1920,
		viewportHeight: 1080,
		setupNodeEvents(on, config) {
			require("cypress-localstorage-commands/plugin")(on, config);
      		return config;
		},
	},
});
