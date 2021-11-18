import path from 'path';
import fs from 'fs';
import { defineConfig } from 'vite';
import createVuePlugin from '@vitejs/plugin-vue';

const createReloadPlugin = () => ({
	name: 'nette:reload',
	handleHotUpdate({file, server}) {
		if (!file.includes('var') && file.endsWith(".php") || file.endsWith(".latte")) {
			server.ws.send({
				type: 'full-reload',
				path: '*',
			});
		}
	}
});

const createDevPlugin = () => ({
	name: 'nette:dev',
	async configureServer({config}) {
		const buildDir = path.resolve(config.root, config.build.outDir);

		const devserver = [
			'@vite/client',
			...(config.build.rollupOptions.input || [])
		].map(asset => {
			return `http://${config.server.host}:${config.server.port}/${asset}`;
		});

		await fs.promises.mkdir(buildDir, {recursive: true});
		await fs.promises.writeFile(
			path.resolve(buildDir, 'devserver.json'),
			JSON.stringify(devserver, null, 2)
		);
	}
});

export default defineConfig(({mode}) => {
	const isDev = mode === 'development';

	return {
		resolve: {
			alias: {
				'@': path.resolve(__dirname, 'assets/js'),
				'~': path.resolve(__dirname, 'node_modules'),
			},
		},
		server: {
			open: false,
			hmr: true,
			port: 3000,
			host: '0.0.0.0'
		},
		build: {
			manifest: true,
			outDir: './www/dist/',
			emptyOutDir: !isDev,
			minify: isDev ? false : 'esbuild',
			rollupOptions: {
				output: {
					manualChunks: undefined,
					chunkFileNames: isDev ? '[name].js' : '[name]-[hash].js',
					entryFileNames: isDev ? '[name].js' : '[name].[hash].js',
					assetFileNames: isDev ? '[name].[ext]' : '[name].[hash].[ext]',
				},
				input: ['assets/main.js'],
			}
		},
		plugins: [
			createVuePlugin(),
			createReloadPlugin(),
			createDevPlugin(),
		],
	}
});
