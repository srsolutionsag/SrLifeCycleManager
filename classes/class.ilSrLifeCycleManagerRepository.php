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
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @param ilDBInterface $database
     * @param ilTree        $tree
     */
    public function __construct(ilDBInterface $database, ilTree $tree)
    {
        $this->database = $database;
        $this->tree = $tree;
    }

    /**
     * @inheritDoc
     */
    public function getObjectsByTerm(string $term) : array
    {
        $term  = htmlspecialchars($term);
        $term  = $this->database->quote("%$term%", 'text');
        $query = "
            SELECT ref.ref_id AS value, obj.title AS display, obj.title AS searchby FROM object_data AS obj
		        LEFT JOIN object_translation AS trans ON trans.obj_id = obj.obj_id
                LEFT JOIN object_reference AS ref ON ref.obj_id = obj.obj_id
		        WHERE obj.title LIKE $term 
		        OR trans.title LIKE $term
            ;
		";

        return $this->database->fetchAll(
            $this->database->query($query)
        );
    }
}