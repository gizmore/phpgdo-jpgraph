<?php
declare(strict_types=1);
namespace GDO\JPGraph;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use Amenadiel\JpGraph\Text\Text;
use GDO\Core\Application;
use GDO\Core\GDO_ArgError;
use GDO\Core\GDT;
use GDO\Core\GDT_RegEx;
use GDO\Core\GDT_Response;
use GDO\Core\MethodAjax;
use GDO\Date\GDT_DateTime;
use GDO\Date\Time;
use GDO\UI\GDT_Length;
use GDO\UI\WithTitle;
use GDO\Util\Strings;

/**
 * Render a graph.
 *
 * @author gizmore
 * @version 7.0.3
 */
abstract class MethodGraph extends MethodAjax
{

	use WithTitle;

	public function isSavingLastUrl(): bool { return false; }

	public function gdoParameters(): array
	{
		return [
			GDT_Length::make('width')->min(48)->max(1024)->initialValue($this->defaultWidth()),
			GDT_Length::make('height')->min(32)->max(1024)->initialValue($this->defaultHeight()),
			GDT_GraphDateselect::make('date')->notNull()->initial('7days'),
			GDT_DateTime::make('start')->initial(Time::getDate(Application::$TIME - Time::ONE_WEEK)),
			GDT_DateTime::make('end')->initial(Time::getDate()),
		];
	}

	public function defaultWidth(): int { return Module_JPGraph::instance()->cfgDefaultWidth(); }

	public function defaultHeight(): int { return Module_JPGraph::instance()->cfgDefaultHeight(); }

	/**
	 * @throws GDO_ArgError
	 */
	public function getDate(): string
	{
		return $this->gdoParameterVar('date');
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function execute(): GDT
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
		return $this->renderGraph($graph, $ts, $te);
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getStartTime(): float|int
	{
		return Time::getTimestamp($this->getStart());
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getStart(): ?string
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

	/**
	 * @throws GDO_ArgError
	 */
	public function isCustomDate(): bool
	{
		return $this->getDate() === 'custom';
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getDateColumn(): GDT_GraphDateselect
	{
		return $this->gdoParameter('date');
	}

	/**
	 * @throws GDO_ArgError
	 */
	protected function showMessage($text): GDT
	{
		$graph = $this->getGraph();
		$graph->SetScale('textint');
		$graph->xaxis->SetTickLabels(['', '']);
		$plot = new LinePlot([0, 0]);
		$graph->yaxis->SetTickLabels(['', '']);
		$graph->Add($plot);
		$graph->SetMargin(0, 0, 0, 0);

		$text = new Text($text);
		$y = $this->getHeight() - $text->GetTextHeight($graph->img);
		$text->Center(0, $this->getWidth(), $y / 2);
		$graph->AddText($text);

		$graph->Stroke();
		Application::exit();
		return GDT_Response::make();
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getGraph(): Graph
	{
		$graph = new Graph($this->getWidth(), $this->getHeight());
		return $graph;
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getWidth(): int
	{
		return (int) round($this->gdoParameterValue('width'));
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getHeight(): int
	{
		return (int)round($this->gdoParameterValue('height'));
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getEndTime(): float|int
	{
		return Time::getTimestamp($this->getEnd());
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getEnd(): ?string
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

	abstract public function renderGraph(Graph $graph, $ts, $te) :GDT;

	/**
	 * @throws GDO_ArgError
	 */
	public function hrefImage(): string
	{
		$param = "&date={$this->getDate()}";
		$param .= "&start={$this->getStart()}";
		$param .= "&end={$this->getEnd()}";
		$param .= "&width={$this->getWidth()}";
		$param .= "&height={$this->getHeight()}";
		return $this->hrefNoSEO($param);
	}

	/**
	 * Format the date axis nicely.
	 * Remove too much ticks.
	 * Remove redundant date metrics like year or month, if they are always the same.
	 *
	 * @throws GDO_ArgError
	 */
	protected function filterXAxisDaily(array &$datax): array
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
				$datax[$k] = Time::displayDate($k, 'jpgraph_datefmt_' . strlen($day));
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
	 *
	 * @throws GDO_ArgError
	 */
	protected function keepEveryNthTick(array $datax): int
	{
		$len = count($datax) - 1;
		$w = $this->getWidth();
		$ppd = $w / $len;
		$wanted = 24; # We want 24px per tick label
		if ($ppd < $wanted)
		{
			return (int) round($wanted / $ppd);
		}
		return 1;
	}

}
