<?php
namespace GDO\JPGraph;

use GDO\Core\Method;
use GDO\Core\GDT_UInt;
use GDO\Date\GDT_DateTime;
use GDO\Date\Time;
use GDO\UI\WithTitle;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Text\Text;
use Amenadiel\JpGraph\Plot\LinePlot;
use GDO\Util\Strings;
use GDO\Core\Application;

/**
 * Render a graph.
 * @author gizmore
 */
abstract class MethodGraph extends Method
{
	use WithTitle;
	
	public function defaultWidth() { return Module_JPGraph::instance()->cfgDefaultWidth(); }
	public function defaultHeight() { return Module_JPGraph::instance()->cfgDefaultHeight(); }
	
	public function gdoParameters() : array
	{
		return array(
			GDT_UInt::make('width')->min(48)->max(1024)->initial($this->defaultWidth()),
			GDT_UInt::make('height')->min(32)->max(1024)->initial($this->defaultHeight()),
			GDT_GraphDateselect::make('date')->initial('7days'),
			GDT_DateTime::make('start')->initial(Time::getDate(Application::$TIME-Time::ONE_WEEK)),
			GDT_DateTime::make('end')->initial(Time::getDate()),
		);
	}
	
	public function getWidth()
	{
		return $this->gdoParameterValue('width');
	}
	
	public function getHeight()
	{
		return $this->gdoParameterValue('height');
	}
	
	public function getDate()
	{
		return $this->gdoParameterValue('date');
	}
	
	/**
	 * @return GDT_GraphDateselect
	 */
	public function getDateColumn()
	{
		return $this->gdoParameter('date');
	}
	
	public function isCustomDate()
	{
		return $this->getDate() === 'custom';
	}
	
	public function getStart()
	{
		if ($this->isCustomDate())
		{
			return $this->gdoParameterVar('start');
		}
		else
		{
			return $this->getDateColumn()->getStartDate();
		}
	}
	
	public function getStartTime()
	{
		return Time::getTimestamp($this->getStart());
	}
	
	public function getEnd()
	{
		if ($this->isCustomDate())
		{
			return $this->gdoParameterVar('end');
		}
		else
		{
			return $this->getDateColumn()->getEndDate();
		}
	}
	
	public function getEndTime()
	{
		return Time::getTimestamp($this->getEnd());
	}
	
	public function hrefImage()
	{
		$param = "&date={$this->getDate()}";
		$param .= "&start={$this->getStart()}";
		$param .= "&end={$this->getEnd()}";
		$param .= "&width={$this->getWidth()}";
		$param .= "&height={$this->getHeight()}";
		return $this->hrefNoSEO($param);
	}
	
	public function execute()
	{
		$jp = Module_JPGraph::instance();
		$jp->includeJPGraph('graph/Graph.php');
		
		$ts = $this->getStartTime();
		if (!$ts)
		{
			return $this->showMessage(t('err_jpgraph_no_start_time'));
		}

		$te = $this->getEndTime();
		if (!$te)
		{
			return $this->showMessage(t('err_jpgraph_no_end_time'));
		}
		
		if ($ts > $te)
		{
			return $this->showMessage(t('err_jpgraph_start_greater_end'));
		}
		
		$graph = new Graph();
		$this->renderGraph($graph, $ts, $te);
	}
	
	abstract public function renderGraph(Graph $graph, $ts, $te);
	
	public function getGraph()
	{
		$graph = new Graph($this->getWidth(), $this->getHeight());
		return $graph;
	}
	
	protected function showMessage($text)
	{
		$graph = $this->getGraph();
		$graph->SetScale("textint");
		$graph->xaxis->SetTickLabels(array('', ''));
		$plot = new LinePlot(array(0, 0));
		$graph->yaxis->SetTickLabels(array('', ''));
		$graph->Add($plot);
		$graph->SetMargin(0, 0, 0, 0);

		$text = new Text($text);
		$y = $this->getHeight() - $text->GetTextHeight($graph->img);
		$text->Center(0, $this->getWidth(), $y/2);
		$graph->AddText($text);
		
		$graph->Stroke();
		die();
	}
	
	/**
	 * Format the date axis nicely.
	 * Remove too much ticks.
	 * Remove redundant date metrics like year or month, if they are always the same.
	 * @param array $datax
	 * @return array
	 */
	protected function filterXAxisDaily(array &$datax)
	{
		# Remove last entry
		end($datax);
		$key = key($datax);
		$datax[$key] = '';
		reset($datax);
		
		# Remove year if always equal
		$years = [];
		foreach ($datax as $k => $day)
		{
			if ($year = Strings::substrTo($day, '-'))
			{
				$years[$year] = $year;
			}
		}
		if (count($years) === 1)
		{
			foreach ($datax as $k => $day)
			{
				$datax[$k] = Strings::substrFrom($day, '-', '');
			}
		}
	
		# Remove month if always equal
		$months = [];
		if (count($years) === 1)
		{
			foreach ($datax as $k => $day)
			{
				if ($month = Strings::substrTo($day, '-'))
				{
					$months[$month] = $month;
				}
			}
			if (count($months) === 1)
			{
				foreach ($datax as $k => $day)
				{
					$datax[$k] = Strings::substrFrom($day, '-', '');
				}
			}
			
		}
		
		# Translate
		foreach ($datax as $k => $day)
		{
			if ($day)
			{
				$datax[$k] = Time::displayDate($k, 'jpgraph_datefmt_'.strlen($day));
			}
		}
		
		# Remove too much labels
		$keepEvery = $this->keepEveryNthTick($datax);
		if ($keepEvery > 1)
		{
			$i = 0;
			foreach ($datax as $k => $day)
			{
				if ($i > 0)
				{
					if ($i % $keepEvery)
					{
						$datax[$k] = '';
					}
				}
				$i++;
			}
		}
		
		return array_values($datax);
	}
	
	/**
	 * Calucalte N for keep every n-th tick.
	 * @param array $datax
	 * @return int
	 */
	protected function keepEveryNthTick(array $datax)
	{
		$len = count($datax) - 1;
		$w = $this->getWidth();
		$ppd = $w / $len;
		$wanted = 24; # We want 24px per tick label
		if ($ppd < $wanted)
		{
			return round($wanted / $ppd);
		}
		return 1;
	}
	
}
