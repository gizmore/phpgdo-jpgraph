<?php
namespace GDO\JPGraph;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Array;
use GDO\Core\GDT_UInt;

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
	
    public function thirdPartyFolders() : array { return ['/jpgraph/']; }
    
    public function getFriendencies() : array
    {
    	return [
    		'JQuery',
    	];
    }
    
	public function getConfig() : array
	{
		return [
			GDT_UInt::make('jpgraph_default_width')->initial('480'),
			GDT_UInt::make('jpgraph_default_height')->initial('320'),
		];
	}
	public function cfgDefaultWidth() { return $this->getConfigVar('jpgraph_default_width'); }
	public function cfgDefaultHeight() { return $this->getConfigVar('jpgraph_default_height'); }
	
	/**
	 * Define jpGraph ROOT_PATH on init.
	 * {@inheritDoc}
	 * @see \GDO\Core\GDO_Module::onInit()
	 */
	public function onInit()
	{
		define('ROOT_PATH', $this->filePath());
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
	public function includeJPGraph($path) : void
	{
		$path = $this->jpgraphPath() . "/$path";
		require_once $path;
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

	public function hookIgnoreDocsFiles(GDT_Array $ignore)
	{
	    $ignore->data[] = 'GDO/JPGraph/jpgraph/**/*';
	    $ignore->data[] = 'GDO/JPGraph/vendor/**/*';
	}

}
