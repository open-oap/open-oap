# Conversion of femanager registration via customised invitation function to standard registration process

Initially, front-end users could register using FE Manager and a customised
routine based on the invitation function.
However, with some mail systems, the double opt-in function causes both the
confirmation and delete links in the email to be executed, and the links in
the email are no longer valid when clicked by the user.
With standard registration, the FE Manager has the option of activating the
links only after confirmation on the website that is called up. However, the
mail systems only call up the page, so the links remain active and the account
is not accidentally activated or deleted by the mail system.

During the conversion, the current status of the FE manager was used for the
partials, and only the adapted partials are now in the OAP extension.
The same procedure applies to the Typoscript setup.

## Upgrade

This conversion/release includes an upgrade wizard that converts the plugins
in the registration.
The wizard is only executed if the "Invitation" function has been selected
in the plugin.

The wizard can only be executed once.

## Configuration

### Confirmation of the registration

The registration mail carries a link the applicant has to follow to activate the account. Some
virus scanners and mail clients preload every link in an incoming mail, which follows that link
before the applicant ever sees the mail - the account is then activated by the scanner.

open-oap therefore sets `confirmUserConfirmation = 1`: following the link opens a page with a
confirmation the applicant has to submit, so a preloaded link activates nothing. The same
mechanism exists for the admin confirmation as `confirmAdminConfirmation`, which is left off
because the admin confirmation is not part of the standard registration here.


These imports should then appear in the SitePackage:

In all.typoscript:

```typoscript
@import "./felogin/Configuration/TypoScript/setup.typoscript"
@import "./femanager/Configuration/TypoScript/setup.typoscript"
```

In all_constants.typoscript:

```typoscript
@import "./open-oap/Configuration/TypoScript/constants.typoscript"
```

In cb_packages/cb_cosmobase/Configuration/TypoScript/Extensions/femanager/Configuration/TypoScript/setup.typoscript:

```typoscript
plugin.tx_femanager {
    _LOCAL_LANG {
        # Field Labels
        default.tx_femanager_domain_model_user\.terms = I have read and acknowledge the terms and conditions.
        de.tx_femanager_domain_model_user\.privacypolicy\.terms = Ich bestätige, dass ich die Datenschutzbestimmungen akzeptiere.

        default.tx_femanager_domain_model_user\.email = Email address
        de.tx_femanager_domain_model_user\.privacypolicy\.email = E-Mail-Adresse
    }
}
```

