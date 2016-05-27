<?php

namespace TomasKarlik\Components;

use Nette\Application\UI\Control;
use Nette\Utils\Paginator;

/**
 * This file is part of the ListControl
 *
 * Copyright (c) 2016 Tomáš Karlík (http://tomaskarlik.cz)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */
class ListPaginatorControl extends Control {

    /**
     * @var int actual page
     * @persistent
     */
    public $page = 1;

    /**
     * @var int items per page
     * @persistent
     */
    public $perPage = 50;

    /**
     * @var callable[] function ( ListPaginatorControl $control );
     */
    public $onReload;

    /**
     * @var array
     */
    private $perPageList = array(25, 50, 100, 200);

    /**
     * @var Paginator
     */
    private $paginator = NULL;

    /**
     * @param \Nette\ComponentModel\IContainer $parent
     * @param string $name
     */
    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
	parent::__construct($parent, $name);

	$this->setupPaginator(); //initial setup (for inital persistant params)
    }

    /**
     * @param Paginator $paginator
     * @return array
     */
    public static function getPaginatorSteps(Paginator $paginator) {	
	$page = $paginator->getPage();

	if ($paginator->pageCount < 2) {
	    return array($page);
	} else {
	    $count = 4;
	    $arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
	    $quotient = ($paginator->pageCount - 1) / $count;
	    for ($i = 0; $i <= $count; $i++) {
		$arr[] = round($quotient * $i) + $paginator->firstPage;
	    }
	    sort($arr);

	    return array_values(array_unique($arr));
	}
    }

    /**
     * @param array $params
     */
    public function loadState(array $params) {
	if (isset($params['page']) && ($params['page'] < 1)) {
	    $params['page'] = 1;
	}
	if (isset($params['perPage']) && (!in_array($params['perPage'], $this->getPerPageList()))) {
	    $params['perPage'] = (int) reset($this->perPageList);
	}

	parent::loadState($params);
	$this->setupPaginator();
    }

    /**
     * Set paginator params
     */
    private function setupPaginator() {
	$this->getPaginator()->setPage($this->getPage());
	$this->getPaginator()->setItemsPerPage($this->getPerPage());
    }

    public function render() {
	$template = $this->template;
	$template->setFile(__DIR__ . '/templates/paginator.latte');

	$template->paginator = $this->getPaginator();
	$template->steps = self::getPaginatorSteps($this->paginator);
	$template->perPageList = $this->getPerPageList();
	$template->perPage = $this->getPerPage();

	$template->render();
    }

    /**
     * @return array
     */
    public function getPerPageList() {
	return $this->perPageList;
    }

    /**
     * @param array $pageList
     * @return self
     */
    public function setPerPageList(array $pageList) {
	$this->perPageList = $pageList;
	return $this;
    }

    /**
     * @return Paginator
     */
    public function getPaginator() {
	if (!$this->paginator) {
	    $this->paginator = new Paginator;
	}
	return $this->paginator;
    }

    /**
     * @return int
     */
    public function getPerPage() {
	return $this->perPage;
    }

    /**
     * @return int
     */
    public function getPage() {
	return $this->page;
    }

    /**
     * @return self
     */
    public function resetPage() {
	$this->page = 1;
	$this->setupPaginator(); //refresh paginator params
	return $this;
    }

    /**
     * @param int $count
     * @return self
     */
    public function setItemCount($count) {
	$this->getPaginator()->setItemCount($count);
	return $this;
    }

    /**
     * @return int
     */
    public function getLength() {
	return $this->getPaginator()->getLength();
    }

    /**
     * @return int
     */
    public function getOffset() {
	return $this->getPaginator()->getOffset();
    }

    /**
     * @param int $page
     */
    public function handlePage($page) {	
	$this->page = (int) $page;
	$this->setupPaginator();
	$this->reload();
    }

    /**
     * @param int $perPage
     */
    public function handlePerPage($perPage) {	
	$this->perPage = (int) $perPage;
	$this->resetPage(); //and refresh paginator
	$this->reload();
    }

    /**
     * Fire reload event
     */
    private function reload() {
	$this->onReload($this); //event
    }

}
