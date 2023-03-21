<?php
namespace GDO\JPGraph;

use GDO\Core\GDT;
use GDO\Core\GDT_Template;

/**
 * Shows a selection for graphs including the graph image.
 *
 * @version 6.09
 * @since 6.09
 * @see MethodGraph
 * @author gizmore
 */
class GDT_GraphSelect extends GDT
{

	###################
	### GraphMethod ###
	###################
	/**
	 * The method to render
	 *
	 * @var MethodGraph
	 */
	public $graphMethod;
	public $withToday = true;
	public $withYesterday = true;
	public $withoutDateInput = false;

	public function graphMethod(MethodGraph $method)
	{
		$this->graphMethod = $method;
		return $this;
	}

	public function render()
	{
		$tVars = [
			'gdt' => $this,
		];
		return GDT_Template::php('JPGraph', 'graph_select.php', $tVars);
	}

	public function withToday($withToday = true)
	{
		$this->withToday = $withToday;
		return $this;
	}

	public function withYesterday($withYesterday = true)
	{
		$this->withYesterday = $withYesterday;
		return $this;
	}

	public function withoutDateInput($withoutDateInput = true)
	{
		$this->withoutDateInput = $withoutDateInput;
		return $this;
	}

	############
	### HREF ###
	############
	public function hrefImage()
	{
		return $this->graphMethod->hrefImage();
	}

}
