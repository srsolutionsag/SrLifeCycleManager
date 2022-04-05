<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\ILIASRepository;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrLifeCycleManagerRepository implements ILIASRepository
{
    use ilSrRepositoryHelper;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @param ilTree $tree
     */
    public function __construct(ilTree $tree)
    {
        $this->tree = $tree;
    }
}