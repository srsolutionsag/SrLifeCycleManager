<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Token;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Token implements IToken
{
    /**
     * @var int
     */
    protected $routine_id;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param int    $routine_id
     * @param int    $ref_id
     * @param string $event
     * @param string $token
     */
    public function __construct(
        int $routine_id,
        int $ref_id,
        string $event,
        string $token
    ) {
        $this->routine_id = $routine_id;
        $this->ref_id = $ref_id;
        $this->event = $event;
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function getRoutineId(): int
    {
        return $this->routine_id;
    }

    /**
     * @inheritDoc
     */
    public function setRoutineId(int $routine_id): IToken
    {
        $this->routine_id = $routine_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRefId(): int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function setRefId(int $ref_id): IToken
    {
        $this->ref_id = $ref_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @inheritDoc
     */
    public function setEvent(string $event): IToken
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function setToken(string $token): IToken
    {
        $this->token = $token;
        return $this;
    }
}
