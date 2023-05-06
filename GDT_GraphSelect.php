<?php
declare(strict_types=1);
namespace GDO\JPGraph;

use GDO\Core\GDT;
use GDO\Core\GDT_Template;

/**
 * Shows a selection for graphs including the graph image.
 *
 * @version 7.0.3
 * @since 6.9.0
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
	 */
	public MethodGraph $graphMethod;
	public bool $withToday = true;
	public bool $withYesterday = true;
	public bool $withoutDateInput = false;

	public function graphMethod(MethodGraph $method): static
	{
		$this->graphMethod = $method;
		return $this;
	}

	public function render(): array|string|null
	{
		$tVars = [
			'gdt' => $this,
		];
		return GDT_Template::php('JPGraph', 'graph_select.php', $tVars);
	}

	public function withToday($withToday = true): static
	{
		$this->withToday = $withToday;
		return $this;
	}

	public function withYesterday($withYesterday = true): static
	{
		$this->withYesterday = $withYesterday;
		return $this;
	}

	public function withoutDateInput($withoutDateInput = true): static
	{
		$this->withoutDateInput = $withoutDateInput;
		return $this;
	}

	############
	### HREF ###
	############
	public function hrefImage(): string
	{
		return $this->graphMethod->hrefImage();
	}

}
