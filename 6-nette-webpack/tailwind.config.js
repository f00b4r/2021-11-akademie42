module.exports = {
	mode: "jit",
	purge: {
		content: [
			"./app/**/*.latte",
			"./assets/**/*.js",
			"./assets/**/*.ts",
			"./assets/**/*.vue",
		],
	},
	darkMode: false,
	theme: {},
	variants: {
		extend: {},
	},
	plugins: [
		require('@tailwindcss/forms'),
	]
};
