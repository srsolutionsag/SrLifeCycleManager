<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\SrLifeCycleManager\Rule\Requirement\RequirementFactory;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\AttributeFactory;
use srag\Plugins\SrLifeCycleManager\IRepository;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use srag\Plugins\SrLifeCycleManager\Rule\Comparison\Comparison;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrRoutineCronJob extends ilSrAbstractCronJob
{
    /**
     * @var AttributeFactory
     */
    protected $attribute_factory;

    /**
     * @var RequirementFactory
     */
    protected $requirement_factory;

    /**
     * @param ilSrCronJobResultBuilder $builder
     * @param RequirementFactory       $requirement_factory
     * @param AttributeFactory         $attribute_factory
     * @param IRepository              $repository
     * @param ITranslator              $translator
     * @param ilLogger                 $logger
     */
    public function __construct(
        ilSrCronJobResultBuilder $builder,
        RequirementFactory $requirement_factory,
        AttributeFactory $attribute_factory,
        IRepository $repository,
        ITranslator $translator,
        ilLogger $logger
    ) {
        parent::__construct($builder, $repository, $translator, $logger);

        $this->requirement_factory = $requirement_factory;
        $this->attribute_factory = $attribute_factory;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return 'LifeCycleManager Routine Job';
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
    public function run() : ilCronJobResult
    {
        $this->result_builder->request();

        $deletable_objects = [];
        foreach ($this->repository->getRepositoryObjects(1) as $repository_object) {
            try {
                $current_object = ilObjectFactory::getInstanceByRefId($repository_object);
            } catch (Exception $exception) {
                $this->error("Could not retrieve object ($repository_object): {$exception->getMessage()}");
                continue;
            }

            foreach ($this->repository->routine()->getAllByRefId(1) as $routine) {
                if (!$routine->isActive()) {
                    $this->info("Skipping routine ({$routine->getRoutineId()}) because it's inactive.");
                    continue;
                }

                $deletable = true;
                foreach ($this->repository->rule()->getByRoutine($routine) as $rule) {
                    try {
                        $requirement = $this->requirement_factory->getRequirement($current_object);
                    } catch (Exception $exception) {
                        $this->error("Could not retrieve requirement for object-type '{$current_object->getType()}'");
                        break;
                    }

                    $comparison = new Comparison(
                        $this->attribute_factory,
                        $requirement,
                        $rule
                    );

                    if (!$comparison->isApplicable()) {
                        $deletable = false;
                    }
                }

                if ($deletable) {
                    $deletable_objects[] = $repository_object;
                }
            }
        }

        return $this->result_builder
            ->success()
            ->message($this->translator->txt('all_good'))
            ->getResult()
        ;
    }
}