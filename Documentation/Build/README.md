# Build 

some hints for creating and formatting files

## php-cs-fixer

In composer.json, the function "code-styling" is built in, with configuration in the .build folder.

To execute code-styling, run the following command:
```bash
composer code-styling
```

Depending on the environment, ddev must be written in front of it (if outside the ddev container).

More information and parameters about [php-cs-fixer - TYPO3 coding standards](https://github.com/TYPO3/coding-standards)

## Frontend assets

The JavaScript in `Resources/Public/JavaScript` is built from the modules in
`Resources/Private/Frontend/js-source`. Which modules go into the bundle, and in which
order, is in `Build/frontend/assets.json`; the build itself is one script without a task
runner:

```bash
npm install
npm run build        # minified, with a source map
npm run build:dev    # readable output, with a source map
```

`init.js` has to come first, because it creates the `OAP` namespace the other modules
extend. The files under `js-source/lib/` are the third-party libraries the modules build
on. A module named in the list but not present is skipped with a notice, so the list may
already name a module that a pending change will add.

### The stylesheets are not built here

`Resources/Public/Css` is left to the installation, because neither stylesheet can be
compiled from the sources in this repository as they stand:

- `Sass/styles.scss` imports `../../sass/functions/all`, `../../sass/settings`,
  `../../sass/mixins/all` and `../../sass/custom-properties`. Those four paths point above
  `Resources/Private/Frontend/`, into a theme that is not part of this repository.
- `Sass/backend.scss` resolves its own imports, but `backend_modules/_proposal.scss` calls
  `rem()`, a function from that same theme. Current Dart Sass reads an undefined `rem()` as
  the CSS `rem()` math function and stops with "2 arguments required, but only 1 was
  passed".

The compiled CSS in `Resources/Public/Css` is therefore the artifact of a build that has
the theme next to it. To make the stylesheets buildable here, those four partials and the
functions they provide would have to live in the repository - which is a decision about
what belongs to the extension, not something a build script can settle.

## phpDoc
### Version
We currently use version 3.3.x

### Installation
phpDocumentor is not part of this repository. You can include it yourself if you need it - and in the way you prefer.
Information for the installation can be found here: [phpDocumentor installation](https://docs.phpdoc.org/3.0/guide/getting-started/installing.html).

In the .build folder there is a configuration file that creates the documentation from the oap extension.
The target directory is below the documentation directory, i.e. Documentation/phpdoc and is accessed via an index.html.

In case you have downloaded phpDocumentor.phar and it is located in the .build directory, the call looks like this:

```bash
cd .Build
./phpDocumentor.phar --config phpdoc.xml
```

The containing directories are given relative to the configuration file phpdoc. 

### more information

for more information about phpDocumentor see here: [phpDocumentor](https://docs.phpdoc.org/3.0/)