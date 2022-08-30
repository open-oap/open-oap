# Overwriting xlf settings

## for the installation - complete

Stored for example in a separate extension under
my_extension/Configuration/TypoScript/Extensions/open_oap/Resources/Private/Language/this_installation.xlf

```xml
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<xliff version="1.0">
	<file source-language="en" datatype="plaintext" original="EXT:open_oap/Resources/Private/Language/locallang.xlf" date="2022-02-28T18:19:17Z" product-name="open_oap">
		<header/>
		<body>

<!-- Status of proposal -->
			<trans-unit id="tx_openoap_domain_model_proposal.state.1" resname="tx_openoap_domain_model_proposal.state.1">
				<source>Draft en *</source>
			</trans-unit>

		</body>
	</file>
</xliff>
```
and second language
my_extension/Configuration/TypoScript/Extensions/open_oap/Resources/Private/Language/de.this_installation.xlf
```xml
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<xliff version="1.0">
	<file target-language="de" datatype="plaintext" original="EXT:open_oap/Resources/Private/Language/locallang.xlf" date="2021-11-02T16:46:47Z" product-name="open_oap">
		<header/>
		<body>

<!-- Status of proposal -->
            <trans-unit id="tx_openoap_domain_model_proposal.state.1" resname="tx_openoap_domain_model_proposal.state.1">
                <source>Draft en *</source>
                <target>Entwurf de *</target>
            </trans-unit>

        </body>
    </file>
</xliff>

```
## for clients (multiple domain TYPO3 setup)

Stored for example in a separate extension under
my_extension/Configuration/TypoScript/Extensions/open_oap/Configuration/TypoScript/setup_this_domain.typoscript
and included via template in the web root

```typo3_typoscript
plugin.tx_openoap_dashboard._LOCAL_LANG {
    de {
        tx_openoap_domain_model_proposal {
            state {
                1 = Draft 1
            }
        }
    }
    default {
        tx_openoap_domain_model_proposal {
            state {
                1 = Draft 1
            }
        }
    }
}

plugin.tx_openoap_form._LOCAL_LANG < plugin.tx_openoap_dashboard._LOCAL_LANG

```