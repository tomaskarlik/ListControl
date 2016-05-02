# ListControl
Simple sortable grid for Nette with custom templates.

Requirements
------------

ListControl requires PHP 5.4 or higher.

- [Nette Framework](https://github.com/nette/nette)

Usage
-----

*Presenter*
```php
<?php
  public function createComponentYourListName() {
    $listControl = new ListControl;
    $listControl->setModel($model); // \Nette\Database\Table\Selection
    
    $listControl->setSortableColumns(array('code', 'name', 'price', 'category'));
    $listControl->addFilterText("code", "code LIKE ?");
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
*Template*
```latte
<div n:snippet="yourControlSnippetName" class="list-control">
  <form n:name="filterForm">
    <table>
      <tr>
        <th class="{$sortColumn == 'code' ? " $sortType" : ""}">
          <a n:if="$sortType != 'asc'" n:href="sort! sortColumn => 'code', sortType => 'asc'">Kód produktu</a>
          <a n:if="$sortType == 'asc'" n:href="sort! sortColumn => 'code', sortType => 'dsc'">Kód produktu</a>
          <input n:name="code">
        </th>
        <th class="{$sortColumn == 'name' ? " $sortType" : ""}">
          <a n:if="$sortType != 'asc'" n:href="sort! sortColumn => 'name', sortType => 'asc'">Název produktu</a>
          <a n:if="$sortType == 'asc'" n:href="sort! sortColumn => 'name', sortType => 'dsc'">Název produktu</a>
          <input n:name="name"></th>
        <th class="{$sortColumn == 'category' ? " $sortType" : ""}">
          <a n:if="$sortType != 'asc'" n:href="sort! sortColumn => 'category', sortType => 'asc'">Kategorie</a>
          <a n:if="$sortType == 'asc'" n:href="sort! sortColumn => 'category', sortType => 'dsc'">Kategorie</a>
          <select n:name="category"></select>
        </th>
        <th class="{$sortColumn == 'price' ? " $sortType" : ""}">
          <a n:if="$sortType != 'asc'" n:href="sort! sortColumn => 'price', sortType => 'asc'">Cena s&nbsp;DPH</a>
          <a n:if="$sortType == 'asc'" n:href="sort! sortColumn => 'price', sortType => 'dsc'">Cena s&nbsp;DPH</a>
        </th>
        <th class="">Aktivní
          <select n:name="active"></select>
        </th>
      </tr>
      {foreach $items as $item}
      <tr>
        <td>{$item->code}</td>
        <td><a href="{plink Product:detail id => $item->id}">{$item->name}</a></td>
        <td>{$item->category}</td>
        <td>{$item->price}</td>
        <td>{$item->active ? 'ano' : 'ne'}</td>
      </tr>
      {/foreach}
    </table>
    <input n:name="submit">
  </form>
  {control paginator}
</div>
```
*JavaScript*
```js
 <script src="list-control.js"></script>
```