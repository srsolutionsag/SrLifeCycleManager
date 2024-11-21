<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager\Form;

use ILIAS\UI\Component\Input\Container\Container;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractFormProcessor implements IFormProcessor
{
    /**
     * @var UIForm
     */
    protected Container $form;

    /**
     * @param ServerRequestInterface $request
     * @param mixed $form
     */
    public function __construct(ServerRequestInterface $request, $form)
    {
        $this->form = $form->withRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function processForm(): bool
    {
        $data = $this->form->getData();
        if (null !== $data && $this->isValid($data)) {
            $this->processData($data);
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProcessedForm(): UIForm
    {
        return $this->form;
    }

    /**
     * This method MUST return whether the provided POST-data is valid or not.
     *
     * @param array $post_data
     * @return bool
     */
    abstract protected function isValid(array $post_data): bool;

    /**
     * This method MUST process the provided POST-data.
     *
     * The method is only called when @see AbstractFormProcessor::isValid()
     * returned true.
     *
     * @param array $post_data
     */
    abstract protected function processData(array $post_data): void;
}
