<?php

namespace Araneum\Base\Repository;

use Symfony\Component\Security\Acl\Exception\Exception;

trait AdminDataGridTrait
{
    /**
     * Delete entities
     *
     * @param array $idx
     */
    public function delete(array $idx)
    {
        $qb = $this->createQueryBuilder('REPO');

        $qb->delete()
            ->andWhere($qb->expr()->in('REPO.id', ':idx'))
            ->setParameter('idx', $idx)
            ->getQuery()
            ->execute();
    }

    /**
     * Update enable/disable field
     *
     * @param array $idx
     * @param mixed $state
     */
    public function updateEnabled(array $idx, $state)
    {
        $qb = $this->createQueryBuilder('REPO');

        $qb->update()
            ->set('REPO.enabled', ':state')
            ->andWhere($qb->expr()->in('REPO.id', ':idx'))
            ->setParameter('state', $state)
            ->setParameter('idx', $idx)
            ->getQuery()
            ->execute();
    }

    /**
     * Return Locale Query Builder without any conditions
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->createQueryBuilder('REPO');
    }

    /**
     * Get entity names
     * @return array
     */
    public function getNamesArray()
    {
        $result = [];

        try {
            $array = $this->createQueryBuilder('REPO')
                ->select('REPO.name, REPO.id')
                ->getQuery()
                ->getResult();

            foreach ($array as $item) {
                $result[$item['id']] = $item['name'];
            }
        } catch (Exception $e) {
            $result['error'] = 'Can\'t get properties of class';
        }

        return $result;
    }
}
