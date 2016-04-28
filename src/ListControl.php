<?php

namespace TomasKarlik\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * This file is part of the ListControl
 *
 * Copyright (c) 2016 Tomáš Karlík (http://tomaskarlik.cz)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */
class ListControl extends Control {

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'dsc';
    
    const COL_STRING = 'string';
    const COL_BOOL = 'boolean';
    const COL_INT = 'integer';
    const COL_FLOAT = 'float';	

    /**
     * @var string
     * @persistent
     */
    public $sortColumn = NULL;

    /**
     * @var string
     * @persistent
     */
    public $sortType = self::ORDER_ASC;

    /**
     * @var string
     * @persistent
     */
    public $filterValues = array();

    /**
     * @var array
     */
    private $sortableColumns = array();

    /**
     * @var array
     */
    private $sortingTypes = array(
	    self::ORDER_ASC => 'ASC',
	    self::ORDER_DESC => 'DESC'
    );

    /**
     * @var \Nette\Database\Table\Selection
     */
    private $model = NULL;

    /**
     * @var ListPaginatorControl
     */
    private $paginator = NULL;

    /**
     * @var array
     */
    private $filters = array(
	    'text' => array(),
	    'select' => array()	
    );

    /**
     * @var string
     */
    private $template = NULL;
    
    /**     
     * @var string
     */
    private $defaultSort = NULL;

    /**
     * @return ListPaginatorControl
     */
    protected function getPaginator() {
        if (!$this->paginator) {
            $this->paginator = new ListPaginatorControl;
            $this->paginator->onReload[] = array($this, 'reload');
        }

        return $this->paginator;
    }

    /**
     * @param string $sortColumn
     * @param string $sortType
     */
    public function handleSort($sortColumn, $sortType) {
        $this->sortColumn = $sortColumn;
        $this->sortType = $sortType;
        
        $this->resetPage();
        $this->reload();
    }

    /**
     * Set paginator to first page
     */
    private function resetPage() {
	    $this->getPaginator()->resetPage();
    }
    
    /**
     * @param Control $control
     */
    public function reload(Control $control = NULL) {
        if ($this->presenter->isAjax()) {
            $this->invalidateControl();
        } else {
            $this->redirect('this');
        }
    }

    /**
     * @return array
     */
    public function getSortableColumns() {
	    return $this->sortableColumns;
    }

    /**
     * @param array $cols
     * @return self
     */
    public function setSortableColumns(array $cols) {
	    $this->sortableColumns = $cols;
	    return $this;
    }

    /**
     * @return string|NULL
     */
    public function getSortColumn() {
	    return $this->sortColumn;
    }

    /**
     * @return string
     */
    public function getSortType() {
	    return $this->sortType;
    }

    /**
     * @return array
     */
    public function getFilterValues() {
	    return $this->filterValues;
    }

    /**
     * @param array $params
     */
    public function loadState(array $params) {
        if ((isset($params['sortColumn']) && (!in_array($params['sortColumn'], $this->sortableColumns)))
            || (isset($params['sortType']) && (!array_key_exists($params['sortType'], $this->sortingTypes)))) {
        $params['sortColumn'] = NULL;
        $params['sortType'] = self::ORDER_ASC;
        }
        parent::loadState($params);
    }

    /**
     * @param \Nette\Database\Table\Selection $selection
     */
    public function setModel(\Nette\Database\Table\Selection $selection) {
	$this->model = $selection;
    }

    private function applyParams() {
	//paginator
	$paginator = $this->getPaginator();
	$paginator->setItemCount($this->getCount());
	$this->model->limit($paginator->getLength(), $paginator->getOffset());

	//sorting
	if ($this->sortColumn !== NULL) {
	    $this->model->order($this->getOrderBy());
	} elseif ($this->defaultSort !== NULL) {
	    $this->model->order($this->defaultSort); //set default sorting
	}
    }

    private function applyFilters() {
	foreach ($this->filters as $values) {
	    foreach ($values as $col => $filter) {
		if (array_key_exists($col, $this->filterValues) && Strings::length($this->filterValues[$col])) {
		    $val = $this->filterValues[$col];
		    if ($this->setColumnType($val, $filter['type'])) { //try to cast specifed data type
			$this->model->where($filter['condition'], $val);
		    } else {
			$this->model->where("FALSE"); //invalid value
		    }
		}
	    }
	}
    }

    /**
     * @return int
     */
    private function getCount() {
	return (int) $this->model->count('*');
    }

    /**
     * @return string
     */
    private function getOrderBy() {
	return sprintf("%s %s", $this->sortColumn, $this->sortingTypes[$this->sortType]);
    }

    /**
     * @return \Nette\Application\UI\ITemplate
     */
    public function getTemplate() {	
	$this->applyFilters(); //apply data filtering
	$this->applyParams(); //set sorting, order, page, ...

	$template = parent::getTemplate();
	$template->sortColumn = $this->getSortColumn();
	$template->sortType = $this->getSortType();
	$template->items = ($this->model ? $this->model->fetchAll() : NULL);

	return $template;
    }

    /**
     * @param string $col
     * @param string $condition
     * @param string $type
     * @return self
     */
    public function addFilterText($col, $condition, $type = self::COL_STRING) {
	$this->filters['text'][$col] = array(
	    'condition' => $condition,
	    'type' => $type
	);
	return $this;
    }

    /**
     * @param string $col
     * @param array $items
     * @param string $condition
     * @param string $type
     * @return self
     */
    public function addFilterSelect($col, array $items, $condition, $type = self::COL_STRING) {
	$this->filters['select'][$col] = array(
	    'items' => $items,
	    'condition' => $condition,
	    'type' => $type
	);
	return $this;
    }

    /**
     * @param string $col
     * @param mixed $value
     * @return \App\Module\AdminModule\Components\ListControl
     */
    public function setDefault($col, $value) {
	$this->filterValues[$col] = $value;
	return $this;
    }

    /**
     * @return ListPaginatorControl
     */
    public function createComponentPaginator() {
	return $this->getPaginator();
    }

    /**
     * @return Form
     */
    public function createComponentFilterForm() {
	$form = new Form;
	$form->setMethod(Form::POST);

	foreach ($this->filters['text'] as $idx => $filter) { //add texboxs
	    $control = $form->addText($idx);
	    if (array_key_exists($idx, $this->filterValues) && Strings::length($this->filterValues[$idx])) {
		$control->setDefaultValue($this->filterValues[$idx]);
	    }
	}
	foreach ($this->filters['select'] as $idx => $filter) { //add selectboxs
	    $control = $form->addSelect($idx, NULL, $filter['items']);
	    if (array_key_exists($idx, $this->filterValues) && Strings::length($this->filterValues[$idx])) {
		$control->setDefaultValue($this->filterValues[$idx]);
	    }
	}

	$form->addSubmit('submit');
	$form->onSuccess[] = array($this, 'filterFormSubmited');

	return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     * @return 
     */
    public function filterFormSubmited(Form $form, ArrayHash $values) {

	foreach ($this->filters['text'] as $idx => $filter) {
	    $this->filterValues[$idx] = (array_key_exists($idx, $values) && Strings::length($values[$idx]) ? (string) $values[$idx] : NULL);
	}
	foreach ($this->filters['select'] as $idx => $filter) {
	    $this->filterValues[$idx] = (array_key_exists($idx, $values) && Strings::length($values[$idx]) ? $values[$idx] : NULL);
	}
		
	$this->resetPage();
	$this->reload();
    }

    /**
     * @param string $file
     */
    public function setTemplateFile($file) {
	$this->template = $file;
    }

    public function render() {
	$template = $this->getTemplate();
	$template->setFile($this->template);
	$template->render();
    }
    
    /**     
     * @paramstring $sort
     * @return self
     */
    public function setDefaultSort($sort = NULL) {
	$this->defaultSort = $sort;
	return $this;
    }
    
    /**     
     * @param mixed $value
     * @param string $type COL_*
     * @return bool
     */
    private function setColumnType(&$value, $type = self::COL_STRING) {
	if (($value === NULL) || settype($value, $type)) {
	    return TRUE;
	}
	
	return FALSE;
    }

}
