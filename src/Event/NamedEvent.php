<?php declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Event;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NamedEvent implements IEvent
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $source
     * @param string $name
     */
    public function __construct(string $source, string $name)
    {
        $this->source = $source;
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getSource() : string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->name;
    }
}
