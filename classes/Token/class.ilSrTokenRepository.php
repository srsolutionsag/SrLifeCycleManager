<?php declare(strict_types=1);

use srag\Plugins\SrLifeCycleManager\Repository\DTOHelper;
use srag\Plugins\SrLifeCycleManager\Token\TokenGenerator;
use srag\Plugins\SrLifeCycleManager\Token\ITokenRepository;
use srag\Plugins\SrLifeCycleManager\Token\IToken;
use srag\Plugins\SrLifeCycleManager\Token\Token;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilSrTokenRepository implements ITokenRepository
{
    use TokenGenerator;
    use DTOHelper;

    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function get(int $routine_id, int $ref_id, string $event): ?IToken
    {
        $query = "
            SELECT routine_id, ref_id, event, token FROM srlcm_tokens 
                WHERE routine_id = %s
                AND ref_id = %s
                AND event = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['integer', 'integer', 'text'],
                    [
                        $routine_id,
                        $ref_id,
                        $event,
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getByToken(string $token): ?IToken
    {
        $query = "
            SELECT routine_id, ref_id, event, token FROM srlcm_tokens 
                WHERE token = %s
            ;
        ";

        return $this->returnSingleQueryResult(
            $this->database->fetchAll(
                $this->database->queryF(
                    $query,
                    ['text'],
                    [
                        $token,
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function new(int $routine_id, int $ref_id, string $event): IToken
    {
        return $this->insertToken(
            new Token(
                $routine_id,
                $ref_id,
                $event,
                $this->generateToken()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(int $ref_id): bool
    {
        $query = "DELETE FROM srlcm_tokens WHERE ref_id = %s;";

        $this->database->manipulateF(
            $query,
            ['integer'],
            [
                $ref_id,
            ]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function redeem(IToken $token): bool
    {
        $query = "DELETE FROM srlcm_tokens WHERE token = %s;";

        $this->database->manipulateF(
            $query,
            ['text'],
            [
                $token->getToken(),
            ]
        );

        return true;
    }

    /**
     * @param IToken $token
     * @return IToken
     */
    protected function insertToken(IToken $token): IToken
    {
        $query = "INSERT INTO srlcm_tokens (routine_id, ref_id, event, token) VALUES (%s, %s, %s, %s);";

        $this->database->manipulateF(
            $query,
            ['integer', 'integer', 'text', 'text'],
            [
                $token->getRoutineId(),
                $token->getRefId(),
                $token->getEvent(),
                $token->getToken(),
            ]
        );

        return $token;
    }

    /**
     * @inheritDoc
     */
    protected function transformToDTO(array $query_result): IToken
    {
        return new Token(
            (int) $query_result[IToken::F_ROUTINE_ID],
            (int) $query_result[IToken::F_REF_ID],
            $query_result[IToken::F_EVENT],
            $query_result[IToken::F_TOKEN]
        );
    }
}
