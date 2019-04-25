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

* With the `FlexiblePrefixBeforeDisplayDetails($title, &$details)` hook you can add additional details.
* You can use `SpecialFlexiblePrefix::getHTML(SpecialFlexiblePrefix::fetch($prefix), $title)` to embed the prefix index elsewhere.

## Credits

This extension is a rewrite of [SimilarNamedArticles](https://fs.fsinf.at/wiki/SimilarNamedArticles) by Mathias Ertl.
