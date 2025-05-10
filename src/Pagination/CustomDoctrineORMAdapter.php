<?php
namespace App\Pagination;

use Pagerfanta\Adapter\AdapterInterface;
use Doctrine\ORM\QueryBuilder;

class CustomDoctrineORMAdapter implements AdapterInterface
{
    private $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        // Modify the query to apply pagination
        $this->queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($length);

        // Return the paginated results
        return $this->queryBuilder->getQuery()->getResult();
    }

    public function getNbResults(): int
    {
        // Clone the query builder to create a count query
        $queryBuilder = clone $this->queryBuilder;
        $queryBuilder
            ->select('COUNT(m.id)') // COUNT the results based on the main entity
            ->resetDQLPart('orderBy'); // Reset orderBy for the count query to avoid issues

        // Execute the count query and return the result
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
