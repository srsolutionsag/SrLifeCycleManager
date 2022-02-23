<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Notification\INotificationRepository;
use srag\Plugins\SrLifeCycleManager\Rule\IRuleRepository;
use ILIAS\DI\RBACServices;
use srag\Plugins\SrLifeCycleManager\IRepository;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Comparison;
use srag\Plugins\SrLifeCycleManager\Rule\Requirement\Course\CourseRequirement;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\Config\IConfigRepository;

/**
 * Class ilSrLifeCycleManagerRepository is a factory for all
 * further plugin repositories.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrLifeCycleManagerRepository implements IRepository
{
    /**
     * @var RBACServices
     */
    protected $rbac;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @var IConfigRepository
     */
    protected $config_repository;

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var INotificationRepository;
     */
    protected $notification_repository;

    /**
     * @var IRuleRepository
     */
    protected $rule_repository;

    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @param ilDBInterface $database
     * @param RBACServices  $rbac
     * @param ilTree        $tree
     */
    public function __construct(
        ilDBInterface $database,
        RBACServices $rbac,
        ilTree $tree
    ) {
        $this->attribute_factory = new AttributeFactory();
        $this->config_repository = new ilSrConfigRepository();
        $this->rule_repository = new ilSrRuleRepository($database);
        $this->notification_repository = new ilSrNotificationRepository($database);
        $this->routine_repository = new ilSrRoutineRepository(
            new ilSrRoutineWhitelistRepository(),
            $database,
            $tree
        );

        $this->database = $database;
        $this->tree = $tree;
        $this->rbac = $rbac;
    }

    /**
     * @inheritDoc
     */
    public function config() : IConfigRepository
    {
        return $this->config_repository;
    }

    /**
     * @inheritDoc
     */
    public function routine() : IRoutineRepository
    {
        return $this->routine_repository;
    }

    /**
     * @inheritDoc
     */
    public function notification() : INotificationRepository
    {
        return $this->notification_repository;
    }

    /**
     * @inheritDoc
     */
    public function rule() : IRuleRepository
    {
        return $this->rule_repository;
    }

    /**
     * Returns all available global-roles as 'id' => 'title' pairs.
     *
     * This method is used for UI input options, therefore this array
     * DOES NOT contain the administrator global role, as it should
     * always be able to everything.
     *
     * @return array
     */
    public function getGlobalRoleOptions() : array
    {
        $role_options = [];
        $global_roles = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);

        if (empty($global_roles)) {
            return $role_options;
        }

        foreach ($global_roles as $role_data) {
            $role_id = (int) $role_data['obj_id'];
            // the administrator role can be ignored, as this
            // role should always be able to do everything.
            if ((int) SYSTEM_ROLE_ID !== $role_id) {
                $role_title = ilObjRole::_getTranslation($role_data['title']);

                // map the role-title to it's role id associatively.
                $role_options[$role_id] = $role_title;
            }
        }

        return $role_options;
    }

    /**
     * Recursively gathers all children of the given ref-id which are
     * either a course or group object.
     *
     * @TODO: might not be very performant and is rather resource greedy.
     *
     * @param int $ref_id
     * @return array
     */
    public function getDeletableObjects(int $ref_id) : array
    {
        $objects = [];

        $deletable_objects = $this->tree->getChildsByTypeFilter($ref_id, ['crs', 'cat', 'grp', 'fold']);
        if (empty($deletable_objects)) {
            return [];
        }

        foreach ($deletable_objects as $deletable_object) {
            if (in_array($deletable_object['type'], AttributeFactory::SUPPORTED_OBJECT_TYPES, true)) {
                $objects[] = $deletable_object;
            } else {
                $deletable_child_objects = $this->getDeletableObjects((int) $deletable_object['ref_id']);
                if (!empty($deletable_child_objects)) {
                    $this->addArrayValues($objects, $deletable_child_objects);
                    // $objects = array_merge($objects, $deletable_child_objects);
                }
            }
        }

        return $objects;
    }

    /**
     * @param int $ref_id
     * @return array
     */
    public function getAdministrators(int $ref_id) : array
    {
        return ilParticipants::getInstance($ref_id)->getAdmins();
    }

    /**
     * Replacement for PHP's built-in array_merge(...) function.
     *
     * @param $original
     * @param $array
     * @return void
     */
    protected function addArrayValues(&$original, $array) : void
    {
        foreach ($array as $value) {
            $original[] = $value;
        }
    }
}