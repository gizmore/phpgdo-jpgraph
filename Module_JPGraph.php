<?php
namespace GDO\JPGraph;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Array;
use GDO\UI\GDT_Length;

/**
 * This module provides JPGraph for gdo6 applications.
 * So far only jpgraph's ROOT_PATH needs to be adjusted to the module's root folder, and an own autoloader has been implemented.
 * @version 7.0.1
 * @since 6.9.0
 * @author gizmore
 */
final class Module_JPGraph extends GDO_Module
{
	public int $priority = 40; 
	
    public function thirdPartyFolders() : array { return ['jpgraph/', 'vendor/']; }
    
    public function getFriendencies() : array
    {
    	return [
    		'JQuery',
    	];
    }
    
	public function getConfig() : array
	{
		return [
			GDT_Length::make('jpgraph_default_width')->initial('480'),
			GDT_Length::make('jpgraph_default_height')->initial('320'),
		];
	}
	public function cfgDefaultWidth() { return $this->getConfigValue('jpgraph_default_width'); }
	public function cfgDefaultHeight() { return $this->getConfigValue('jpgraph_default_height'); }
	
	/**
	 * Define jpGraph ROOT_PATH on init.
	 */
	public function onModuleInit()
	{
		deff('ROOT_PATH', $this->filePath());
	}
	
	/**
	 * JpGraph src folder
	 * @return string
	 */
	public function jpgraphPath() : string 
	{
		return $this->filePath('jpgraph/src');
	}
	
	/**
	 * Include a JpGraph file.
	 * @param string $path
	 */
	public function includeJPGraph(string $path) : void
	{
		$path2 = $this->filePath('vendor/autoload.php');
		require_once $path2;
		$path2 = $this->jpgraphPath() . "/$path";
		require_once $path2;
		
	}
	
	public function onLoadLanguage() : void
	{
		$this->loadLanguage('lang/jpgraph');
	}
	
	public function onIncludeScripts() : void
	{
		if (module_enabled('JQuery'))
		{
			$this->addJS('js/gdo-jpgraph.js');
		}
	}

}
