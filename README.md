# ListControl
Simple sortable grid for Nette with custom templates.

Requirements
------------

ListControl requires PHP 5.4 or higher.

- [Nette Framework](https://github.com/nette/nette)

Usage
-----

```php
<?php
  public function createComponentYourListName() {
    $listControl = new ListControl;
    $listControl->setModel($model); // \Nette\Database\Table\Selection
    
    $listControl->setSortableColumns(array('code', 'name', 'price', 'category'));
    $listControl->addFilterText("code", "code LIKE ?)");
    $listControl->addFilterText("name", "name ILIKE ?");
    $listControl->addFilterSelect("active",
      array(
        TRUE => "aktivní",
        FALSE => "neaktivní"
      ), "active", ListControl::COL_BOOL);
    $listControl->addFilterSelect("category",
      array (
        1=> 'Cat 1',
        2=> 'Cat 2'
      ), "id IN (SELECT product_id FROM get_child_categories(?))", ListControl::COL_INT);

    $listControl->setTemplateFile(__DIR__ . '/yourListTemplate.latte');
    
    return $listControl;
  }
?>
```
