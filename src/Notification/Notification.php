<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Notification;

/**
 * Class Notification (DTO)
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Notification implements INotification
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string
     */
    protected $message;

    /**
     * Notification constructor
     *
     * @param int|null $id
     * @param string   $message
     */
    public function __construct(
        ?int $id,
        string $message
    ) {
        $this->id       = $id;
        $this->message  = $message;
    }

    /**
     * @ineritdoc
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @ineritdoc
     */
    public function setId(int $id) : INotification
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @ineritdoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @ineritdoc
     */
    public function setMessage(string $message) : INotification
    {
        $this->message = $message;
        return $this;
    }
}