<?php

class SpecialFlexiblePrefix extends SpecialPage {
	function __construct() {
		parent::__construct( 'FlexiblePrefix' );
	}

	private static function fetch(Title $title, $ns=null, $excludeTitle=null){
		if ($ns === null)
			$ns = $title->getNamespace();
		$dbr = wfGetDB(DB_REPLICA);
		$cond = [
				'page_namespace' => $ns,
				'page_title'.$dbr->buildLike($title->getDBkey(), $dbr->anyString()),
				'page_title NOT LIKE "%/%"', # exclude subpages
				'page_is_redirect=0'
		];
		if ($excludeTitle)
			$cond[] = 'page_title !='. $dbr->addQuotes($excludeTitle->getDBkey());
		return $dbr->select(
			'page',
			['page_namespace', 'page_title'],
			$cond,
			__METHOD__,
			['ORDER BY' => ['page_title', 'page_namespace']]
		);
	}

	private static function generateHTML($res){
		$html = '<ul>';
		foreach ($res as $row){
			$title = Title::newFromRow($row);
			$html .= '<li>'.Linker::linkKnown($title, $title->getText());

			$details = [];

			if ($title->getNamespace() != 0)
				$details[] = str_replace('_', ' ', $title->getNsText());

			Hooks::run('FlexiblePrefixBeforeDisplayDetails', [$title, &$details]);

			if ($details)
				$html .= ' (' . implode($details, ', ') . ')';

			$html .= '</li>';
		}
		return $html . '</ul>';
	}

	public static function getHTML($title, $excludeTitle=null){
		# provide convenient API
		return self::generateHTML(self::fetch($title, null, $excludeTitle));
	}

	function execute( $par ) {
		global $wgFlexiblePrefixNamespaces;
		$this->setHeaders();
		$out = $this->getOutput();

		if (empty($par)){
			$out->addWikiText(wfMessage('notargettext'));
			$out->setPageTitle(wfMessage('notargettitle'));
			return;
		}
		$title = Title::newFromText($par);
		if ($title == null){
			$out->setPageTitle(wfMessage('invalidtitle'));
			return;
		}
		$ns = null;
		if ($title->inNamespace(NS_MAIN) && $par[0] != ':'){
			if ($wgFlexiblePrefixNamespaces)
				$ns = $wgFlexiblePrefixNamespaces;
			else {
				$out->addHTML('$wgFlexiblePrefixNamespaces not set.');
				return;
			}
		}
		$res =  self::fetch($title, $ns);
		if ($res->numRows() == 0)
			$out->addHTML('No results found.');
		elseif ($res->numRows() == 1)
			$out->redirect(Title::newFromRow($res->fetchObject())->getFullURL());
		else
			$out->addHTML(self::generateHTML($res));
	}
}
