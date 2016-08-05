<?php
namespace Araneum\Base\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;

/**
 * Class StatisticsService
 *
 * @package Araneum\Bundle\MainBundle\Service
 */
abstract class AbstractStatisticsService
{
    const DEFAULT_STATISTIC_PERIOD = '24 hours';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var int
     */
    protected $statisticPeriod;

    /**
     * @var array
     */
    protected $hours;

    /**
     * AbstractStatisticsService constructor.
     *
     * @param EntityManager $entityManager
     * @param string        $statisticPeriod
     */
    public function __construct(
        EntityManager $entityManager,
        $statisticPeriod
    ) {
        $this->entityManager = $entityManager;
        $this->statisticPeriod = $this->formatStatisticPeriod($statisticPeriod);
        $this->hours = $this->createTimeRange(
            date('Y-m-d H:00:00', time() - $this->statisticPeriod),
            date('Y-m-d H:00:00')
        );
    }

    /**
     * Format statistic period from string to int
     *
     * @param string $period Time in format e.g. <24 hour>
     * @return int
     */
    protected function formatStatisticPeriod($period)
    {
        try {
            $periodTime = strtotime($period);
        } catch (\Exception $e) {
            $periodTime = false;
        }

        if ($periodTime === false) {
            $periodTime = strtotime(self::DEFAULT_STATISTIC_PERIOD);
        }

        return $periodTime - time();
    }

    /**
     * Create time range
     *
     * @param  mixed $start start time
     * @param  mixed $end   end time
     * @return array
     */
    protected function createTimeRange($start, $end)
    {
        $times = [];

        $begin = new \DateTime($start);
        $end = new \DateTime($end);

        $interval = new \DateInterval('PT1H');
        $dateRange = new \DatePeriod($begin, $interval, $end);

        foreach ($dateRange as $date) {
            $times[$date->format('H') + 1] = 0;
        }

        return $times;
    }
}
