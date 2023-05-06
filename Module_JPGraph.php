<?php
declare(strict_types=1);
namespace GDO\JPGraph;

use GDO\Core\GDO_Module;
use GDO\UI\GDT_Length;

/**
 * This module provides JPGraph for gdo6 applications.
 * So far only jpgraph's ROOT_PATH needs to be adjusted to the module's root folder, and an own autoloader has been implemented.
 *
 * @TODO Add a GDT_Enum for the jpgraph theme.
 *
 * @version 7.0.3
 * @since 6.9.0
 * @author gizmore
 */
final class Module_JPGraph extends GDO_Module
{

	public int $priority = 40;

	public string $license = 'QPL-1.0';

	public function getLicenseFilenames(): array
	{
		return [
			'jpgraph/LICENSE.md',
		];
	}

	public function thirdPartyFolders(): array
	{
		return [
			'jpgraph/',
			'vendor/'
		];
	}

	public function getFriendencies(): array
	{
		return [
			'JQuery',
		];
	}

	public function getConfig(): array
	{
		return [
			GDT_Length::make('jpgraph_default_width')->initial('480'),
			GDT_Length::make('jpgraph_default_height')->initial('320'),
		];
	}

	/**
	 * Define jpGraph ROOT_PATH on init.
	 */
	public function onModuleInit(): void
	{
		deff('ROOT_PATH', $this->filePath());
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/jpgraph');
	}

	public function onIncludeScripts(): void
	{
		if (module_enabled('JQuery'))
		{
			$this->addJS('js/gdo-jpgraph.js');
		}
	}

	public function cfgDefaultWidth(): int { return $this->getConfigValue('jpgraph_default_width'); }

	public function cfgDefaultHeight(): int { return $this->getConfigValue('jpgraph_default_height'); }

	/**
	 * Include a JpGraph file.
	 */
	public function includeJPGraph(string $path): void
	{
		$path2 = $this->filePath('vendor/autoload.php');
		require_once $path2;
		$path2 = $this->jpgraphPath($path);
		require_once $path2;
	}

	/**
	 * JpGraph src path
	 */
	public function jpgraphPath(string $append=''): string
	{
		return $this->filePath("jpgraph/src/{$append}");
	}

}
