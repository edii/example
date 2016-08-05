<?php

namespace Araneum\Base\Repository;

/**
 * Class LogOperationsTrait
 *
 * @package Araneum\Base\Repository
 */
trait LogOperationsTrait
{
    /**
     * Delete logs older than specified date
     *
     * @param string $date
     * @return mixed
     */
    public function deleteLogsOlderThanDate($date)
    {
        return $this->createQueryBuilder('REPO')
            ->delete()
            ->where('REPO.createdAt < :olderThan')
            ->setParameter('olderThan', $date)
            ->getQuery()
            ->execute();
    }
}
