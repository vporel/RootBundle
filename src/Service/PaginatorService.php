<?php

namespace RootBundle\Service;

use Doctrine\ORM\Query;
use RootBundle\Repository\Repository;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginatorService{

    public function __construct(private RequestStack $requestStack){}

    /**
     * Get the data from the repository using the given criteria
     *
     * @param integer $maxPerPage
     * @param Repository $repository
     * @param [type] $criteria
     * @param array $orderBy
     * @return array [$elements, $page, $pagesCount, $total]
     */
    public function paginate(int $maxPerPage, Repository $repository, $criteria = [], $orderBy = []): array{
        $query = $repository->simpleSelectQuery($criteria, $orderBy);
        $total = $repository->count($criteria);
        if($total == 0) $pagesCount = 1;
        else $pagesCount = ceil($total / $maxPerPage);
        $page = $this->requestStack->getCurrentRequest()->query->getInt("page");
        if($page <= 0) $page = 1;
        if($page >= $pagesCount) $page = $pagesCount;
        $offset = ($page - 1) * $maxPerPage;
        $all = $this->requestStack->getCurrentRequest()->query->getBoolean("all");
        $maxResuls = $all ? null : $maxPerPage; //If all, get all the remaining elements
        $elements = $query->setMaxResults($maxResuls)->setFirstResult($offset)->getResult();
        return [$elements, $page, $pagesCount, $total];
    }

    /**
     * Use a given array of elements
     *
     * @param integer $maxPerPage
     * @param array $data
     * @return array [$elements, $page, $pagesCount, $total]
     */
    public function paginateData(int $maxPerPage, array $data){
        $total = count($data);
        if($total == 0) $pagesCount = 1;
        else $pagesCount = ceil($total / $maxPerPage);
        $page = $this->requestStack->getCurrentRequest()->query->getInt("page");
        if($page <= 0) $page = 1;
        if($page >= $pagesCount) $page = $pagesCount;
        $offset = ($page - 1) * $maxPerPage;
        $all = $this->requestStack->getCurrentRequest()->query->getBoolean("all");
        $elements = array_slice($data, $offset, $all ? null : $maxPerPage);
        return [$elements, $page, $pagesCount, $total];
    }
}