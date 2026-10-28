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

## Flush all caches

In the same module `Admin Tools > Maintanance` you can also empty all caches by clicking on the button `Flush cache`.

![Flush cache](../Images/clear_cache.png)

## initialising pages

https://docs.typo3.org/m/typo3/tutorial-sitepackage/main/en-us/CreatePages/Index.html

## Upgrade wizard: proposal folder sharding

Proposal uploads used to be stored as `<base>/<applicantUid>/<proposalUid>/`. With a few
thousand applicants that puts a few thousand directories next to each other in one folder, which
file systems and the file list of the backend both handle badly.

The uploads are therefore stored one level deeper, in a bucket derived from the applicant uid:
`<base>/<applicantUid divided by the bucket size>/<applicantUid>/<proposalUid>/`.

Run **OAP: shard proposal upload folders by applicant uid** in Admin Tools > Upgrade > Upgrade
Wizard once after the update. It moves the existing folders through the file abstraction layer,
so the file references of the proposals stay intact. New uploads use the new layout right away.
