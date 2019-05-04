# FlexiblePrefix

A more flexible version of `Special:Prefixindex` that lets you omit namespaces and redirects if there is only one result.

## Usage

* `Special:FlexiblePrefix/Title` searches across `$wgFlexiblePrefixNamespaces`
* `Special:FlexiblePrefix/Namespace:Title` searches in the given namespace
* `Special:FlexiblePrefix/:Title` searches in the main namespace

## Setup

Place the extension in your extensions directory and load it with `wfLoadExtension('FlexiblePrefix');`.

Then configure the default namespaces, e.g:

	$wgFlexiblePrefixNamespaces = [NS_MAIN, NS_PROJECT];

## Tips

* The `FlexiblePrefixDetails(Title $title, &$details, $context)` hook lets you add details to results (details is an associative array mapping keys to HTML strings).
* The `FlexiblePrefixBeforeDisplay(&$items, $context)` hook lets you modify the result list before display.
* The `SpecialFlexiblePrefix` class can be integrated elsewhere (either by direct instantiation or inheritance).

## Credits

This extension is a rewrite of [SimilarNamedArticles](https://fs.fsinf.at/wiki/SimilarNamedArticles) by Mathias Ertl.
