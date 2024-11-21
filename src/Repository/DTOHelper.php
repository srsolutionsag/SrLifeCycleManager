<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Repository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait DTOHelper
{
    /**
     * This method MUST return an instance of the current repository's DTO
     * built from the given query result (as array-data).
     *
     * @param array<string, string|int> $query_result
     * @return mixed|null
     */
    abstract protected function transformToDTO(array $query_result);

    /**
     * This method returns the first query-result from the given results.
     *
     * To return the raw query-result, true can be passed as second argument.
     *
     * @param array<int, array<string, string|int>> $query_results
     * @param bool $array_data
     * @return mixed|null
     */
    protected function returnSingleQueryResult(array $query_results, bool $array_data = false)
    {
        if (empty($query_results)) {
            return null;
        }

        if (!$array_data) {
            return $this->transformToDTO($query_results[0]);
        }

        return $query_results[0];
    }

    /**
     * This method returns the first query-result from the given results.
     *
     * To return the raw query-result, true can be passed as second argument.
     *
     * @param array<int, array<string, string|int>> $query_results
     * @param bool  $array_data
     * @return array[]
     */
    protected function returnAllQueryResults(array $query_results, bool $array_data = false): array
    {
        if (empty($query_results) || $array_data) {
            return $query_results;
        }

        return array_map(function (array $r) {
            return $this->transformToDTO($r);
        }, $query_results);
    }
}
