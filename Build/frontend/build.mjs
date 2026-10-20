/*
 * Builds Resources/Public/JavaScript from the modules in Resources/Private/Frontend/js-source.
 *
 *   node Build/frontend/build.mjs          minified, with a source map
 *   node Build/frontend/build.mjs --dev    readable output, with a source map
 *
 * Which modules go into which file, and in which order, is in assets.json next to this
 * script. The stylesheets are not built here - Documentation/Build/README.md says why.
 */

import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { basename, dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

import { minify } from 'uglify-js';

const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..', '..');
const jsSource = join(root, 'Resources/Private/Frontend/js-source');

const dev = process.argv.includes('--dev');
const assets = JSON.parse(await readFile(join(here, 'assets.json'), 'utf8'));

const write = async (target, content) => {
    await mkdir(dirname(target), { recursive: true });
    await writeFile(target, content);
    console.log(`  wrote ${relative(root, target)} (${content.length} bytes)`);
};

for (const bundle of assets.javascript) {
    const name = basename(bundle.output);
    const sources = {};

    console.log(`building ${bundle.output}`);

    for (const module of bundle.sources) {
        const path = join(jsSource, module);

        // The list may name a module a pending change will add - skip it, but say so
        if (!existsSync(path)) {
            console.log(`  skipping ${module}, not present`);
            continue;
        }

        sources[module] = await readFile(path, 'utf8');
    }

    const result = minify(sources, {
        compress: !dev,
        mangle: !dev,
        output: { beautify: dev },
        sourceMap: { filename: name, url: `${name}.map` },
    });

    if (result.error) {
        console.error(`  ${result.error}`);
        process.exit(1);
    }

    await write(join(root, bundle.output), result.code);
    await write(join(root, `${bundle.output}.map`), result.map);
}
