import { resolve } from 'path';
import { readdirSync, statSync } from 'fs';
import kirby from 'vite-plugin-kirby';
import * as dotenv from 'dotenv';


dotenv.config();


const root = resolve(__dirname, 'src');
const outDir = resolve(__dirname, 'public', 'dist');
const templateDir = resolve(__dirname, root, 'templates');

const templates = readdirSync(templateDir)
  .filter((file) => !/^\./.test(file))
  .filter((file) => statSync(`${templateDir}/${file}`).isDirectory());

const input = Object.fromEntries([
  ...templates.map((template) => [
    template,
    `${templateDir}/${template}/index.js`,
  ]),
  ['shared', resolve(__dirname, `${root}/index.js`)],
]);

export default ({ mode }) => ({
  root,
  base: mode === 'development' ? '/' : '/dist/',

  server: {
    host: process.env.VITE_DEV_HOST || 'localhost',
    port: 3000,
  },
  preview: {
    port: 4000,
  },

  build: {
    outDir,
    emptyOutDir: true,
    rollupOptions: { input },
  },

  plugins: [kirby()],
});
