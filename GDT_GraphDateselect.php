<?php
namespace GDO\JPGraph;

use GDO\Core\Application;
use GDO\Core\GDT_Select;
use GDO\Date\Time;

/**
 * A simple select for timeframes for a graph.
 * This year, last year, yesterday, last month, etc.
 * Converts these options to start- and endtime.
 *
 * @version 7.0.1
 * @since 6.9.0
 * @author gizmore
 * @see Time
 */
final class GDT_GraphDateselect extends GDT_Select
{

	public bool $withToday = true;

	###############
	### Choices ###
	###############
	public bool $withYesterday = true;

// 	public function renderHTML() : string
// 	{
// 		$this->initChoices();
// 		return parent::renderHTML();
// 	}

	protected function __construct()
	{
		parent::__construct();
		$this->emptyLabel('jpgraphsel_0');
	}

	public function getChoices(): array
	{
		$choices = [];
		$choices['custom'] = t('jpgraphsel_custom');
		if ($this->withToday)
		{
			$choices['today'] = t('jpgraphsel_today');
		}
		if ($this->withYesterday)
		{
			$choices['yesterday'] = t('jpgraphsel_yesterday');
		}
		$choices['7days'] = t('jpgraphsel_7days');
		$choices['14days'] = t('jpgraphsel_14days');
		$choices['this_week'] = t('jpgraphsel_this_week');
		$choices['last_week'] = t('jpgraphsel_last_week');
		$choices['this_month'] = t('jpgraphsel_this_month');
		$choices['last_month'] = t('jpgraphsel_last_month');
		$choices['this_quartal'] = t('jpgraphsel_this_quartal');
		$choices['last_quartal'] = t('jpgraphsel_last_quartal');
		$choices['this_year'] = t('jpgraphsel_this_year');
		$choices['last_year'] = t('jpgraphsel_last_year');

		return $choices;
	}

	################
	### Settings ###
	################

	public function renderForm(): string
	{
		return $this->renderHTML();
	}

	public function validate($value): bool
	{
		$this->initChoices();
		return parent::validate($value);
	}

	public function withToday(bool $withToday = true): self
	{
		$this->withToday = $withToday;
		return $this;
	}

	public function withYesterday(bool $withYesterday = true): self
	{
		$this->withYesterday = $withYesterday;
		return $this;
	}

	##################
	### Range calc ###
	##################
	public function getStartDate()
	{
		return Time::getDate($this->getStartTime());
	}

	/**
	 * Get start timestamp for select setting.
	 *
	 * @return int
	 */
	public function getStartTime()
	{
		$now = mktime(0, 0, 0);
		switch ($this->getValue())
		{
			case 'today':
				return $now;
			case 'yesterday':
				return $now - Time::ONE_DAY;
			case '7days':
				return strtotime('-7 days', $now);
			case '14days':
				return strtotime('-14 days', $now);
			case 'this_week':
				return strtotime('last monday');
			case 'last_week':
				return strtotime('last monday') - Time::ONE_WEEK;
			case 'this_month':
				return Time::getTimestamp(date('Y-m-01 00:00:00'));
			case 'last_month':
				$m = intval(date('m'), 10) - 1;
				$y = intval(date('y'), 10);
				$y = $m == 0 ? $y - 1 : $y;
				return mktime(0, 0, 0, $m, 1, $y);
			case 'this_quartal':
				$m = intval(date('m'), 10) - 1;
				$m -= $m % 3;
				$m++;
				$y = intval(date('y'), 10);
				return mktime(0, 0, 0, $m, 1, $y);
			case 'last_quartal':
				$y = intval(date('y'), 10);
				$m = intval(date('m'), 10) - 1;
				$m -= $m % 3;
				$m++;
				if ($m === 1)
				{
					$m = 9;
					$y--;
				}
				else
				{
					$m -= 3;
				}
				$m -= $m % 3;
				return mktime(0, 0, 0, $m + 1, 1, $y);
			case 'this_year':
				return mktime(0, 0, 0, 1, 1);
			case 'last_year':
				$y = intval(date('y'), 10);
				return mktime(0, 0, 0, 1, 1, $y - 1);
		}
	}

	public function getEndDate()
	{
		return Time::getDate($this->getEndTime());
	}

	public function getEndTime()
	{
		$now = Application::$TIME;

		switch ($this->getValue())
		{
			case 'today':
				return $now;
			case 'yesterday':
				return mktime(0, 0, 0) - 1;
			case '7days':
				return $now;
			case '14days':
				return $now;
			case 'this_week':
				return $now;
			case 'last_week':
				return strtotime('last monday') - 1;
			case 'this_month':
				return $now;
			case 'last_month':
				$m = intval(date('m'), 10);
				return mktime(0, 0, 0, $m, 1) - 1;
			case 'this_quartal':
				return $now;
			case 'last_quartal':
				$y = intval(date('y'), 10);
				$m = intval(date('m'), 10) - 1;
				if ($m <= 3)
				{
					return mktime(23, 59, 59, 12, 31, $y - 1);
				}
				$m -= $m % 3;
				return mktime(0, 0, 0, $m + 1, 1, $y) - 1;
			case 'this_year':
				return $now;
			case 'last_year':
				$y = intval(date('y'), 10);
				return mktime(23, 59, 59, 12, 31, $y - 1);
		}
	}

}
