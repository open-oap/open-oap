'use strict';

module.exports = {
	extends: "stylelint-config-sass-guidelines",
	plugins: [
		"@namics/stylelint-bem",
		"stylelint-order",
		"stylelint-declaration-strict-value",
		"stylelint-scss",
	],
	ignoreFiles: ["../sass/**/_lib.scss", "../sass/utils/*"],
	rules: {
		"at-rule-disallowed-list": ["extend"],
		"at-rule-empty-line-before": ["always", {
			"except": ["first-nested"],
			"ignore": ["after-comment", "blockless-after-same-name-blockless"],
		}],
		"at-rule-name-space-after": "always",
		"at-rule-no-vendor-prefix": true,
		"at-rule-semicolon-newline-after": "always",
		"block-no-empty": [true, {
			ignore: ["comments"],
		}],
		"color-named": null,
		"declaration-block-no-duplicate-properties": true,
		"declaration-no-important": true,
		"font-family-no-duplicate-names": true,
		"font-family-no-missing-generic-family-keyword": true,
		"font-family-name-quotes": "always-where-recommended",
		"function-calc-no-invalid": true,
		"function-calc-no-unspaced-operator": true,
		"indentation": 4,
		"max-nesting-depth": 4,
		// Force usage of CB mixins for screen-width based media queries
		"media-feature-name-disallowed-list": ["min-width", "max-width", "width"],
		"no-duplicate-selectors": true,
		"no-extra-semicolons": true,
		"no-irregular-whitespace": true,
		"order/properties-alphabetical-order": null,
		"order/properties-order": [
			{
				"properties": [
					"content",
					"box-sizing",
					"table-layout",
					"display",
					"grid-gap",
					"grid-template-columns",
					"grid-template-rows",
					"grid-template-areas",
					"grid-area",
					"grid-column",
					"grid-row",
					"position",
					"top",
					"right",
					"bottom",
					"left",
					"z-index",
					"align-content",
					"align-items",
					"align-self",
					"flex",
					"flex-basis",
					"flex-direction",
					"flex-flow",
					"flex-grow",
					"flex-shrink",
					"flex-wrap",
					"justify-content",
					"order",
					"float",
					"clear",
					"width",
					"height",
					"max-width",
					"max-height",
					"min-width",
					"min-height",
					"padding",
					"padding-top",
					"padding-right",
					"padding-bottom",
					"padding-left",
					"margin",
					"margin-top",
					"margin-right",
					"margin-bottom",
					"margin-left",
					"margin-collapse",
					"margin-top-collapse",
					"margin-right-collapse",
					"margin-bottom-collapse",
					"margin-left-collapse",
					"vertical-align",
					"overflow",
					"overflow-x",
					"overflow-y",
					"clip",
					"border",
					"border-collapse",
					"border-top",
					"border-right",
					"border-bottom",
					"border-left",
					"border-color",
					"border-image",
					"border-top-color",
					"border-right-color",
					"border-bottom-color",
					"border-left-color",
					"border-spacing",
					"border-style",
					"border-top-style",
					"border-right-style",
					"border-bottom-style",
					"border-left-style",
					"border-width",
					"border-top-width",
					"border-right-width",
					"border-bottom-width",
					"border-left-width",
					"border-radius",
					"border-top-right-radius",
					"border-bottom-right-radius",
					"border-bottom-left-radius",
					"border-top-left-radius",
					"border-radius-topright",
					"border-radius-bottomright",
					"border-radius-bottomleft",
					"border-radius-topleft",
					"line-height",
					"font",
					"font-family",
					"font-size",
					"font-smoothing",
					"osx-font-smoothing",
					"font-style",
					"font-weight",
					"hyphens",
					"src",
					"letter-spacing",
					"word-spacing",
					"color",
					"text-align",
					"text-decoration",
					"text-indent",
					"text-overflow",
					"text-rendering",
					"text-size-adjust",
					"text-shadow",
					"text-transform",
					"word-break",
					"word-wrap",
					"white-space",
					"list-style",
					"list-style-type",
					"list-style-position",
					"list-style-image",
					"pointer-events",
					"cursor",
					"background",
					"background-attachment",
					"background-color",
					"background-image",
					"background-position",
					"background-repeat",
					"background-size",
					"quotes",
					"outline",
					"outline-offset",
					"outline-width",
					"outline-style",
					"outline-color",
					"opacity",
					"filter",
					"visibility",
					"size",
					"zoom",
					"transform",
					"box-align",
					"box-flex",
					"box-orient",
					"box-pack",
					"box-shadow",
					"animation",
					"animation-delay",
					"animation-duration",
					"animation-iteration-count",
					"animation-name",
					"animation-play-state",
					"animation-timing-function",
					"animation-fill-mode",
					"transition",
					"transition-delay",
					"transition-duration",
					"transition-property",
					"transition-timing-function",
					"background-clip",
					"backface-visibility",
					"resize",
					"appearance",
					"user-select",
					"interpolation-mode",
					"direction",
					"marks",
					"page",
					"set-link-source",
					"unicode-bidi",
					"speak",
					"fill",
					"stroke",
				]
			}
		],
		"plugin/stylelint-bem-namics": {
			"patternPrefixes": [],
			"helperPrefixes": []
		},
		"rule-empty-line-before": [
			"always",
			{
				"except": ["first-nested"],
				"ignore": ["after-comment"],
			}
		],
		"scale-unlimited/declaration-strict-value": ["color", {
			"ignoreFunctions": {
				"color": false
			},
			"ignoreKeywords": ["currentColor", "inherit", "initial", "transparent"],
			"disableFix": true,
	    }],
		"scss/declaration-nested-properties": "never",
		"scss/no-duplicate-mixins": true,
		"scss/no-global-function-names": true,
		"selector-class-pattern": null,
		"selector-max-empty-lines": 0,
		"string-quotes": "double",
		"unit-no-unknown": true,
	},
};