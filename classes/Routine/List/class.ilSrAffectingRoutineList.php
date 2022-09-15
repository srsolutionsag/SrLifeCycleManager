<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use srag\Plugins\SrLifeCycleManager\Routine\Provider\RoutineProvider;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\SrLifeCycleManager\Routine\IRoutineRepository;
use srag\Plugins\SrLifeCycleManager\Assignment\IRoutineAssignmentRepository;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistRepository;
use srag\Plugins\SrLifeCycleManager\ITranslator;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use srag\Plugins\SrLifeCycleManager\Whitelist\IWhitelistEntry;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrAffectingRoutineList extends ilSrAbstractRoutineList
{
    // ilSrRoutineList language variables:
    protected const LABEL_AFFECTING_ROUTINES = 'label_affecting_routines';
    protected const LABEL_ROUTINE_DELETION_DATE = 'label_routine_deletion_date';
    protected const LABEL_WHITELIST_EXPIRY_DATE = 'label_whitelist_expiry_date';
    protected const LABEL_POSTPONED_INDEFINITELY = 'label_postponed_indefinitely';

    /**
     * @var IRoutineRepository
     */
    protected $routine_repository;

    /**
     * @var RoutineProvider
     */
    protected $routine_provider;

    /**
     * @param IRoutineRepository $routine_repository
     * @param RoutineProvider    $routine_provider
     *
     * @inheritDoc
     */
    public function __construct(
        IRoutineAssignmentRepository $assignment_repository,
        IWhitelistRepository $whitelist_repository,
        IRoutineRepository $routine_repository,
        RoutineProvider $routine_provider,
        ITranslator $translator,
        ilSrAccessHandler $access_handler,
        ilObject $current_object,
        Factory $ui_factory,
        Renderer $renderer,
        ilCtrl $ctrl
    ) {
        parent::__construct(
            $assignment_repository, $whitelist_repository, $translator, $access_handler, $current_object, $ui_factory, $renderer,
            $ctrl
        );

        $this->routine_repository = $routine_repository;
        $this->routine_provider = $routine_provider;
    }

    /**
     * @inheritDoc
     */
    protected function getRoutineProperties(IRoutine $routine, IWhitelistEntry $whitelist_entry = null): array
    {
        $properties = parent::getRoutineProperties($routine);

        // add initial deletion date to properties.
        $properties[$this->translator->txt(self::LABEL_ROUTINE_DELETION_DATE)] = $this->getPrettyDateString(
            $this->routine_repository->getDeletionDate($routine, $this->object->getRefId())
        );

        if (null !== $whitelist_entry) {
            if ($whitelist_entry->isOptOut()) {
                // add indefinitely as expiry if the object is opted-out.
                $properties[$this->translator->txt(self::LABEL_WHITELIST_EXPIRY_DATE)] = $this->translator->txt(
                    self::LABEL_POSTPONED_INDEFINITELY
                );
            } elseif (null !== $whitelist_entry->getExpiryDate()) {
                // add the expiry-date if the whitelist entry has one.
                $properties[$this->translator->txt(self::LABEL_WHITELIST_EXPIRY_DATE)] = $this->getPrettyDateString(
                    $whitelist_entry->getExpiryDate()
                );
            }
        } else {
            // add an empty string as an expiry date the object isn't whitelisted yet.
            $properties[$this->translator->txt(self::LABEL_WHITELIST_EXPIRY_DATE)] = '';
        }

        return $properties;
    }

    /**
     * @inheritDoc
     */
    protected function getRoutines(): array
    {
        return $this->routine_provider->getAffectingRoutines($this->object);
    }

    /**
     * @inheritDoc
     */
    protected function getTitle(): string
    {
        return $this->translator->txt(self::LABEL_AFFECTING_ROUTINES);
    }
}
