<?php

/*
 * This file is part of Packagist.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *     Nils Adermann <naderman@naderman.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Packagist\WebBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PackageRepository extends EntityRepository
{
    public function getStalePackages()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p, v')
            ->from('Packagist\WebBundle\Entity\Package', 'p')
            ->leftJoin('p.versions', 'v')
            ->where('p.crawledAt IS NULL OR p.crawledAt < ?0')
            ->setParameters(array(new \DateTime('-1hour')));
        return $qb->getQuery()->getResult();
    }

    public function findAll()
    {
        return $this->getBaseQueryBuilder()->getQuery()->getResult();
    }

    public function findByTag($name)
    {
        $qb = $this->getBaseQueryBuilder()
            // eliminate tags from the select, otherwise only $name is visible in the results' tags
            ->select('p, v, m')
            ->where('t.name = ?0')
            ->groupBy('p.id')
            ->setParameters(array($name));
        return $qb->getQuery()->getResult();
    }

    public function findByMaintainer(User $user)
    {
        $qb = $this->getBaseQueryBuilder()
            // eliminate maintainers from the select, otherwise only $user is visible in the results' maintainers
            ->select('p, v, t')
            ->where('m.id = ?0')
            ->groupBy('p.id')
            ->setParameters(array($user->getId()));
        return $qb->getQuery()->getResult();
    }

    private function getBaseQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p, v, t, m')
            ->from('Packagist\WebBundle\Entity\Package', 'p')
            ->leftJoin('p.versions', 'v')
            ->leftJoin('p.maintainers', 'm')
            ->leftJoin('v.tags', 't');
        return $qb;
    }
}
