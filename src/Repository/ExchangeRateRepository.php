<?php

namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 *
 * @method ExchangeRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeRate[]    findAll()
 * @method ExchangeRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function save(ExchangeRate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExchangeRate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $baseCurrency
     * @param array $targetCurrencies
     * @return void
     */
    public function updateRates(string $baseCurrency, array $targetCurrencies)
    {
        foreach ($targetCurrencies as $currency => $rate) {
            $exchangeRate = $this->findOneBy([
                'baseCurrency' => $baseCurrency,
                'targetCurrency' => $currency,
            ]);

            if (!$exchangeRate) {
                $exchangeRate = new ExchangeRate();
                $exchangeRate->setBaseCurrency($baseCurrency);
                $exchangeRate->setTargetCurrency($currency);
            }

            $exchangeRate->setRate($rate);
            $this->getEntityManager()->persist($exchangeRate);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param string $baseCurrency
     * @param array $targetCurrencies
     * @return array
     * @throws QueryException
     */
    public function findRatesByBaseAndTargetCurrencies(string $baseCurrency, array $targetCurrencies): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.rate, c.targetCurrency')
            ->where('c.baseCurrency = :baseCurrency')
            ->setParameters([
                'baseCurrency' => $baseCurrency,
            ])
        ;

        if(count($targetCurrencies)>1)
        {
            $query->andWhere('c.targetCurrency in (:targetCurrencies)')
                ->setParameters([
                    'baseCurrency' => $baseCurrency,
                    'targetCurrencies' => $targetCurrencies,
                ]);
        }

        return $query->getQuery()->getArrayResult();
    }
}
