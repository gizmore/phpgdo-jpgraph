<?php
namespace GDO\JPGraph;

use GDO\Core\GDT;
use GDO\Core\GDT_Template;

/**
 * Shows a selection for graphs including the graph image.
 * 
 * @see MethodGraph
 * @author gizmore
 * @version 6.09
 * @since 6.09
 */
class GDT_GraphSelect extends GDT
{
	
	###################
	### GraphMethod ###
	###################
	/**
	 * The method to render
	 * @var MethodGraph
	 */
	public $graphMethod;
	public function graphMethod(MethodGraph $method)
	{
		$this->graphMethod = $method;
		return $this;
	}
	
	
	public function render()
	{
		$tVars = array(
			'gdt' => $this,
		);
		return GDT_Template::php('JPGraph', 'graph_select.php', $tVars);
	}
	
	public $withToday = true;
	public function withToday($withToday=true)
	{
		$this->withToday = $withToday;
		return $this;
	}
	
	public $withYesterday = true;
	public function withYesterday($withYesterday=true)
	{
		$this->withYesterday = $withYesterday;
		return $this;
	}
	
	public $withoutDateInput = false;
	public function withoutDateInput($withoutDateInput=true)
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
