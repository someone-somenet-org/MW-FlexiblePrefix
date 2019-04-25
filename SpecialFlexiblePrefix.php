<?php

class SpecialFlexiblePrefix extends SpecialPage {
	function __construct() {
		parent::__construct( 'FlexiblePrefix' );
	}

	static function fetch(Title $title, $ns=null){
		if ($ns === null)
			$ns = $title->getNamespace();
		$dbr = wfGetDB(DB_REPLICA);
		return $dbr->select(
			'page',
			['page_namespace', 'page_title'],
			[
				'page_namespace' => $ns,
				'page_title'.$dbr->buildLike($title->getDBkey(), $dbr->anyString()),
				'page_title NOT LIKE "%/%"', # exclude subpages
				'page_is_redirect=0'
			],
			__METHOD__,
			['ORDER BY' => ['page_title', 'page_namespace']]
		);
	}

	static function getHTML($res, $currentTitle=null){
		$html = '<ul>';
		foreach ($res as $row){
			$title = Title::newFromRow($row);
			$html .= '<li>';

			if ($currentTitle && $title->equals($currentTitle))
				$html .= Linker::makeSelfLinkObj($title, $title->getText());
			else
				$html .= Linker::linkKnown($title, $title->getText());

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
			$out->addHTML(self::getHTML($res));
	}
}
