module.exports = {
	"plugins": [
		"stylelint-order"
	],
	"ignoreFiles": [
		"*.js",
		"*.jsx"
	],
	"rules": {
		"order/properties-alphabetical-order": true,
		"selector-class-pattern": [
			"^(?:(?:o|c|u|t|s|is|has|_|js|qa)-)?[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*(?:__[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*)?(?:--[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*)?(?:\\\\[.+\\\\])?$",
			{
				"message": "Selector should use BEM formatting",
				"severity": "warning"
			}
		],
		"selector-id-pattern": [
			"^([a-z][a-z0-9]*)(-[a-z0-9]+)*$'",
			{
				"message": "Selector should use lowercase and separate words with hyphens",
				"severity": "warning"
			}
		],
		"no-descending-specificity": [
			true,
			{
				"severity": "warning"
			}
		],
		"declaration-property-unit-allowed-list": {},
		"font-weight-notation": null,
		"at-rule-no-unknown": null,
		"font-family-no-missing-generic-family-keyword": null,
		"font-family-name-quotes": "always-unless-keyword",
		"declaration-block-no-shorthand-property-overrides": [
			true,
			{
				"severity": "warning"
			}
		],
		"max-line-length": [
			120,
			{
				"ignore": "non-comments",
				"ignorePattern": [
					"/(https?://[0-9,a-z]*.*)|(^description\\:.+)|(^tags\\:.+)/i"
				]
			}
		]
	}
}
