# Installing this extension

In a composer-based TYPO3 installation, you can install the `open_oap` extension via composer.

```
composer require openoap/open-oap
```

This is the recommended method. The extension will be activated automatically.

## Update the database schema

Open your TYPO3 backend with system maintainer rights.

In the module menu on the left, navigate to `Admin Tools > Maintenance`, then click on `Analyse database` and apply all
the suggested changes.

![Analyze database](../Images/analyza_database.png)

## Upgrade wizards

Run the upgrade wizards in Admin Tools > Upgrade > Upgrade Wizard after updating the extension.

**Migrate "open oap" plugins to content elements**: the open-oap plugins used to be registered
as plugins of the generic "Insert plugin" content element and are now content elements of their
own. The wizard rewrites the existing content records and the backend user permissions that go
with them. Without it, pages keep records the backend no longer offers an editing form for.

## Flush all caches

In the same module `Admin Tools > Maintanance` you can also empty all caches by clicking on the button `Flush cache`.

![Flush cache](../Images/clear_cache.png)

## initialising pages

https://docs.typo3.org/m/typo3/tutorial-sitepackage/main/en-us/CreatePages/Index.html
