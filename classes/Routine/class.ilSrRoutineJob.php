<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\_SrLifeCycleManager\Rule\Requirement\Course\CourseRequirement;
use srag\Plugins\_SrLifeCycleManager\Rule\Requirement\IRequirement;
use srag\Plugins\_SrLifeCycleManager\Config\IConfig;
use srag\Plugins\_SrLifeCycleManager\Rule\Requirement\Group\GroupRequirement;
use srag\Plugins\_SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\_SrLifeCycleManager\Rule\Comparison\Comparison;
use srag\Plugins\_SrLifeCycleManager\Routine\IRoutineWhitelist;
use srag\Plugins\_SrLifeCycleManager\Rule\Attribute\Course\CourseAttribute;
use srag\Plugins\_SrLifeCycleManager\Rule\Attribute\Group\GroupAttribute;
use srag\Plugins\_SrLifeCycleManager\Rule\Attribute\Common\CommonAttribute;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineJob extends ilSrAbstractCronJob
{
    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilSrLifeCycleManagerRepository $repository
     * @param ilLogger                       $logger
     * @param IConfig                        $config
     * @param ilDBInterface                  $database
     * @param AttributeFactory               $attribute_factory
     */
    public function __construct(
        ilSrLifeCycleManagerRepository $repository,
        ilLogger $logger,
        IConfig $config,
        ilDBInterface $database,
        AttributeFactory $attribute_factory
    ) {
        parent::__construct($repository, $logger, $config);

        $this->attribute_factory = $attribute_factory;
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Routines';
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '...';
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $starting_time = microtime(true);

        /** @var $deletable_object_ids int[] */
        $deletable_object_ids = [];

        /** @var $notifiable_object_ids int[] */
        $notifiable_object_ids = [];

        foreach ($this->repository->routine()->getAllByActivity(true) as $routine) {
            $rules = $this->repository->rule()->getAll($routine->getRoutineId());
            /** @var $sent_notifications int[] */
            $sent_notifications = $this->repository->notification()->getSentNotifications($routine->getRoutineId());
            foreach ($this->repository->getDeletableObjects($routine->getRefId()) as $object) {
                $ref_id = (int) $object['ref_id'];
                $requirement = $this->getRequirementByType($ref_id, CourseAttribute::class);
                if (null === $requirement) {
                    continue;
                }

                $is_current_object_deletable = true;
                foreach ($rules as $rule) {
                    if (($rule->getLhsType() === CourseAttribute::class || in_array($rule->getLhsType(), CommonAttribute::COMMON_ATTRIBUTES, true))) {

                    }

                    if (!(new Comparison($this->attribute_factory, $requirement, $rule))->isApplicable()) {
                        $is_current_object_deletable = false;
                    }
                }

                if ($is_current_object_deletable) {
                    $whitelist_entry = $this->repository->routine()->whitelist()->get($routine->getRoutineId(), $ref_id);
                    if (null === $whitelist_entry ||
                        IRoutineWhitelist::WHITELIST_TYPE_OPT_OUT === $whitelist_entry->getWhitelistType() ||
                        (new DateTime()) <= $whitelist_entry->getActiveUntil()
                    ) {
                        continue;
                    }

                    // @TODO: implement generator for this monstrosity.
                    if (in_array($ref_id, $sent_notifications, true)) {
                        // needed for deletion job
                        $deletable_object_ids = $ref_id;
                    } else {
                        // needed for notification job
                        $notifiable_object_ids = $ref_id;
                    }
                }
            }
        }

        $ending_time = microtime(true);

        $result = new ilCronJobResult();
        $result->setDuration(($starting_time - $ending_time));
        $result->setMessage($this->getId() . " terminated successfully.");
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }

    /**
     * @TODO: this could be made sexier.
     *
     * @param int    $ref_id
     * @param string $attribute_type
     * @return IRequirement|null
     */
    protected function getRequirementByType(int $ref_id, string $attribute_type) : ?IRequirement
    {
        $type = ilObject2::_lookupType($ref_id, true);
        if (('crs' === $type && $attribute_type !== CourseAttribute::class) ||
            ('grp' === $type && $attribute_type !== GroupAttribute::class)
        ) {
            return null;
        }

        switch ($type) {
            case 'crs':
                return new CourseRequirement(
                    $this->database,
                    new ilObjCourse($ref_id)
                );

            case 'grp':
                return new GroupRequirement(
                    $this->database,
                    new ilObjGroup($ref_id)
                );

            default:
                return null;
        }
    }
}