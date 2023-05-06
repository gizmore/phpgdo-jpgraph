<?php
declare(strict_types=1);
namespace GDO\JPGraph;

use GDO\Core\Application;
use GDO\Core\GDO_ArgException;
use GDO\Core\GDO_Exception;
use GDO\Core\GDT_Select;
use GDO\Date\Time;

/**
 * A simple select for timeframes for a graph.
 * This year, last year, yesterday, last month, etc.
 * Converts these options to start- and endtime.
 *
 * @version 7.0.3
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


	public bool $withCustom = true;

	protected function __construct()
	{
		parent::__construct();
	}

	public function getChoices(): array
	{
		$choices = [];
		if ($this->withCustom)
		{
			$choices['custom'] = t('jpgraphsel_custom');
		}
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

	public function validate(int|float|string|array|null|object|bool $value): bool
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

	public function withoutCustom(bool $without=true): self
	{
		$this->withCustom = !$without;
		return $this;
	}

	##################
	### Range calc ###
	##################
	public function getStartDate(): ?string
	{
		return Time::getDate($this->getStartTime());
	}

	/**
	 * Get start timestamp for select setting.
	 */
	public function getStartTime(): ?int
	{
		$now = mktime(0, 0, 0);
		switch ($this->getVar())
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
				return (int) round(Time::getTimestamp(date('Y-m-01 00:00:00')));
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
			default:
				return null;
		}
	}

	public function getEndDate(): ?string
	{
		return Time::getDate($this->getEndTime());
	}

	public function getEndTime(): ?int
	{
		switch ($this->getVar())
		{
			case 'yesterday':
				return mktime(0, 0, 0) - 1;
			case 'today':
			case '7days':
			case '14days':
			case 'this_week':
			case 'this_month':
			case 'this_quartal':
			case 'this_year':
				return Application::$TIME;
			case 'last_week':
				return strtotime('last monday') - 1;
			case 'last_month':
				$m = intval(date('m'), 10);
				return mktime(0, 0, 0, $m, 1) - 1;
			case 'last_quartal':
				$y = intval(date('y'), 10);
				$m = intval(date('m'), 10) - 1;
				if ($m <= 3)
				{
					return mktime(23, 59, 59, 12, 31, $y - 1);
				}
				$m -= $m % 3;
				return mktime(0, 0, 0, $m + 1, 1, $y) - 1;

			case 'last_year':
				$y = intval(date('y'), 10);
				return mktime(23, 59, 59, 12, 31, $y - 1);

			default:
				return null;
		}
	}

}
