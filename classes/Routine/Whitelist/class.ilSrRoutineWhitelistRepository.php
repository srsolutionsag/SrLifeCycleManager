<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

use srag\Plugins\_SrLifeCycleManager\Routine\IRoutineWhitelistRepository;
use srag\Plugins\_SrLifeCycleManager\Routine\IRoutineWhitelist;
use srag\Plugins\_SrLifeCycleManager\Routine\IRoutine;
use srag\Plugins\_SrLifeCycleManager\Routine\RoutineWhitelist;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilSrRoutineWhitelistRepository implements IRoutineWhitelistRepository
{
    /**
     * @inheritDoc
     */
    public function get(int $routine_id, int $ref_id) : ?IRoutineWhitelist
    {
        /** @var $ar_whitelist_entry IRoutineWhitelist|null */
        $ar_whitelist_entry = ilSrRoutineWhitelist::where([
            IRoutineWhitelist::F_ROUTINE_ID => $routine_id,
            IRoutineWhitelist::F_REF_ID => $ref_id,
        ], '=')->first();

        if (null !== $ar_whitelist_entry) {
            return $this->transformToDTO($ar_whitelist_entry);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAll(IRoutine $routine, bool $array_data = false) : array
    {
        $today = new DateTime();
        /** @var $ar_whitelist_entries IRoutineWhitelist[] */
        $ar_whitelist_entries = ilSrRoutineWhitelist::where([
            IRoutineWhitelist::F_ROUTINE_ID => $routine->getRoutineId(),
            IRoutineWhitelist::F_ACTIVE_UNTIL => $today->format(ilSrRoutineWhitelist::MYSQL_DATE_FORMAT),
        ], [
            '=',
            '>=',
        ])->get();

        $whitelist_entries = [];
        foreach ($ar_whitelist_entries as $ar_entry) {
            $whitelist_entries[] = ($array_data) ?
                $this->transformToArray($ar_entry) :
                $this->transformToDTO($ar_entry)
            ;
        }

        return $whitelist_entries;
    }

    /**
     * @inheritDoc
     */
    public function add(IRoutineWhitelist $entry) : IRoutineWhitelist
    {
        /** @var $ar_whitelist_entry IRoutineWhitelist */
        $ar_whitelist_entry = $this->get($entry->getRoutineId(), $entry->getRefId()) ?? new ilSrRoutineWhitelist();
        $ar_whitelist_entry
            ->setRoutineId($entry->getRoutineId())
            ->setActiveUntil($entry->getActiveUntil())
            ->setRefId($entry->getRefId())
            ->setWhitelistType($entry->getWhitelistType())
            ->store()
        ;

        return $this->transformToDTO($ar_whitelist_entry);
    }

    /**
     * @inheritDoc
     */
    public function remove(IRoutineWhitelist $entry) : bool
    {
        // nothing to do if the given entry has not been stored yet.
        if (null === $entry->getWhitelistId()) {
            return true;
        }

        $ar_whitelist_entry = ilSrRoutineWhitelist::find($entry->getWhitelistId());
        if (null !== $ar_whitelist_entry) {
            $ar_whitelist_entry->delete();
            return true;
        }

        return false;
    }

    /**
     * Helper function to transform a given active-record into array data.
     *
     * @param IRoutineWhitelist $ar_whitelist
     * @return array
     */
    protected function transformToArray(IRoutineWhitelist $ar_whitelist) : array
    {
        return [
            IRoutineWhitelist::F_WHITELIST_ID => $ar_whitelist->getWhitelistId(),
            IRoutineWhitelist::F_ROUTINE_ID => $ar_whitelist->getRoutineId(),
            IRoutineWhitelist::F_REF_ID => $ar_whitelist->getRefId(),
            IRoutineWhitelist::F_WHITELIST_TYPE => $ar_whitelist->getWhitelistType(),
            IRoutineWhitelist::F_ACTIVE_UNTIL => $ar_whitelist->getActiveUntil(),
        ];
    }

    /**
     * Helper function to transform a given active-record into a DTO.
     *
     * @param IRoutineWhitelist $ar_whitelist
     * @return IRoutineWhitelist
     */
    protected function transformToDTO(IRoutineWhitelist $ar_whitelist) : IRoutineWhitelist
    {
        return new RoutineWhitelist(
            $ar_whitelist->getWhitelistType(),
            $ar_whitelist->getRoutineId(),
            $ar_whitelist->getRefId(),
            $ar_whitelist->getActiveUntil(),
            $ar_whitelist->getWhitelistId()
        );
    }
}