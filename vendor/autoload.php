<?php
namespace GDO\JPGraph\vendor;

use GDO\Util\Strings;

/**
 * GDO compatible autoloader for JpGraph.
 * @see https://github.com/R0L/Jpgraph/blob/master/autoload.php
 */
spl_autoload_register(function($classname) {
	
	# JPGraph basedir
	$baseDir = dirname(__DIR__) . '/jpgraph/src/';
	
	# Check for package
	if ($classname = Strings::substrFrom($classname, "Amenadiel\\JpGraph\\"))
	{
		# Linux is case sensitive and does not like backslash
		$classname = str_replace("\\", '/', $classname);
		$classname = strtolower(Strings::rsubstrTo($classname, '/')) . '/' . 
			Strings::rsubstrFrom($classname, '/');
		
		# include it!
		$file = $baseDir . $classname . '.php';
		if (is_file($file))
		{
			require $file;
		}
	}
});
