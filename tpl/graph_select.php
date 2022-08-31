<?php
namespace GDO\JPGraph\tpl;
use GDO\JPGraph\GDT_GraphDateselect;
/**
 * @var $gdt \GDO\JPGraph\GDT_GraphSelect
 */
?>
<form <?=$gdt->htmlID()?> class="gdt-graph-select">
 <div class="gdo-jpgraph">
<?php if (!$gdt->withoutDateInput) : ?>
  <div class="gdo-jpgraph-selection">
<?php echo GDT_GraphDateselect::make('date')->withToday($gdt->withToday)->withYesterday($gdt->withYesterday)->initial('7days')->render(); ?>
   <input type="date" name="start" />
   <input type="date" name="end" />
  </div>
<?php endif; ?>
  <img src="<?=$gdt->hrefImage()?>" />
 </div>
</form>
