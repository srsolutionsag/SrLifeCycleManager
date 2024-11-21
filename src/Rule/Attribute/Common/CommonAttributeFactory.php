<?php /*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Rule\Attribute\Common;

use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttributeValueProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IAttribute;
use srag\Plugins\SrLifeCycleManager\Rule\Attribute\IDynamicAttributeProvider;
use srag\Plugins\SrLifeCycleManager\Rule\Ressource\IRessource;
use srag\Plugins\SrLifeCycleManager\DateTimeHelper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CommonAttributeFactory implements IAttributeValueProvider
{
    use DateTimeHelper;

    /**
     * @var Refinery
     */
    protected $refinery;

    public function __construct(Refinery $refinery)
    {
        $this->refinery = $refinery;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeType(): string
    {
        return CommonAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValues(): array
    {
        return [
            CommonBoolean::class,
            CommonInteger::class,
            CommonString::class,
            CommonList::class,
            CommonDateTime::class,
            CommonNull::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $type, string $value): IAttribute
    {
        switch ($type) {
            case CommonBoolean::class:
                return new CommonBoolean($this->refinery->kindlyTo()->bool()->transform($value));
            case CommonInteger::class:
                return new CommonInteger($this->refinery->kindlyTo()->int()->transform($value));
            case CommonDateTime::class:
                return new CommonDateTime($this->getDate($value));
            case CommonList::class:
                return new CommonList(
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->string()
                    )->transform($value)
                );
            case CommonString::class:
                return new CommonString($value);

            default:
                return new CommonNull();
        }
    }
}