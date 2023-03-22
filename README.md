Post as
===========

![Elgg 4.3](https://img.shields.io/badge/Elgg-4.3-green.svg)
![Lint Checks](https://github.com/ColdTrick/post_as/actions/workflows/lint.yml/badge.svg?event=push)
[![Latest Stable Version](https://poser.pugx.org/coldtrick/post_as/v/stable.svg)](https://packagist.org/packages/coldtrick/post_as)
[![License](https://poser.pugx.org/coldtrick/post_as/license.svg)](https://packagist.org/packages/coldtrick/post_as)

Allows creating content as somebody else

Features
--------

- Authorize others to post on your behalf
- Create content on somebody else's behalf

Developers
----------

To get your plugin to support Post As register a plugin hook for `config` `post_as`

The result is an array in the format:

```php
$result[
	'<form_name>' => [
		'type' => '<entity_type>',
		'subtype' => '<entity_subtype>',
		'action' => '<action>', // defaults to '<form_name>'
		'extend_form' => true|false, // defaults to true, set to false if the form already contains the post_as/input view
	],
];
```
