<?php

class SpecialFlexiblePrefix extends SpecialPage {
	function __construct() {
		parent::__construct( 'FlexiblePrefix' );
	}

	function getTitles($prefix){
		global $wgFlexiblePrefixNamespaces;
		$title = Title::newFromTextThrow($prefix);
		if ($prefix[0] != ':' && $title->inNamespace(NS_MAIN) && $wgFlexiblePrefixNamespaces){
			$ns = $wgFlexiblePrefixNamespaces;
		} else {
			$ns = $title->getNamespace();
		}
		$dbr = wfGetDB(DB_REPLICA);

		return TitleArray::newFromResult($dbr->select(
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
		));
	}

	function addDetails($titles){
		$items = [];
		foreach ($titles as $title){
			$details = [];
			if ($title->getNamespace() != 0)
				$details['ns'] = str_replace('_', ' ', $title->getNsText());
			Hooks::run('FlexiblePrefixDetails', [clone $title, &$details, $this->getContext()]);
			$items[] = ['title'=>$title, 'details'=>$details];
		}
		Hooks::run('FlexiblePrefixBeforeDisplay', [&$items, $this->getContext()]);
		return $items;
	}

	function makeList($titlesWithDetails, $currentTitle=null){
		$html = '<ul>';
		foreach ($titlesWithDetails as $item){
			$html .= '<li>';

			if ($currentTitle && $item['title']->equals($currentTitle))
				$html .= Linker::makeSelfLinkObj($item['title'], $item['title']->getText());
			else
				$html .= Linker::linkKnown($item['title'], $item['title']->getText());

			if ($item['details'])
				$html .= ' (' . implode(array_values($item['details']), ', ') . ')';

			$html .= '</li>';
		}
		return $html . '</ul>';
	}

	function execute( $par ) {
		global $wgFlexiblePrefixNamespaces;
		$this->setHeaders();
		if ($wgFlexiblePrefixNamespaces == null){
			$out->addHTML('$wgFlexiblePrefixNamespaces not set.');
			return;
		}

		$out = $this->getOutput();

		if (empty($par)){
			$out->addWikiText(wfMessage('notargettext'));
			$out->setPageTitle(wfMessage('notargettitle'));
			return;
		}

		try {
			$titles =  $this->getTitles($par);
		} catch (MalformedTitleException $e){
			$out->setPageTitle(wfMessage('invalidtitle'));
			return;
		}

		if ($titles->count() == 0)
			$out->addHTML('No results found.');
		elseif ($titles->count() == 1)
			$out->redirect($titles->current()->getFullURL());
		else
			$out->addHTML($this->makeList($this->addDetails($titles)));
	}
}
