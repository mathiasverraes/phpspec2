<?php

namespace PHPSpec2\Matcher;

use PHPSpec2\Formatter\Presenter\PresenterInterface;
use PHPSpec2\Exception\Example\FailureException;
use PHPSpec2\Exception\FunctionNotFoundException;

class ScalarMatcher implements MatcherInterface
{
    /**
     * @var \PHPSpec2\Formatter\Presenter\PresenterInterface $presenter
     */
    private $presenter;

    /**
     * @var string $regex
     */
    private $regex = '/be(.+)/';

    /**
     * @param \PHPSpec2\Formatter\Presenter\PresenterInterface $presenter
     */
    public function __construct(PresenterInterface $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * Checks if matcher supports provided subject and matcher name.
     *
     * @param string $name
     * @param mixed  $subject
     * @param array  $arguments
     *
     * @return Boolean
     */
    public function supports($name, $subject, array $arguments)
    {
        $checkerName = $this->getCheckerName($name);

        return $checkerName && function_exists($checkerName);
    }

    /**
     * Evaluates positive match.
     *
     * @param string $name
     * @param mixed  $subject
     * @param array  $arguments
     *
     * @throws \PHPSpec2\Exception\FunctionNotFoundException
     * @throws \PHPSpec2\Exception\Example\FailureException
     * @return boolean
     */
    public function positiveMatch($name, $subject, array $arguments)
    {
        $checker = $this->getCheckerName($name);

        if (!call_user_func($checker, $subject)) {
            throw new FailureException(sprintf(
                '%s expected to return %s, but it did not.',
                $this->presenter->presentString(sprintf('%s(%s)',
                    $checker, $this->presenter->presentValue($subject)
                )),
                $this->presenter->presentValue(true)
            ));
        }
    }

    /**
     * Evaluates negative match.
     *
     * @param string $name
     * @param mixed  $subject
     * @param array  $arguments
     *
     * @throws \PHPSpec2\Exception\FunctionNotFoundException
     * @throws \PHPSpec2\Exception\Example\FailureException
     * @return boolean
     */
    public function negativeMatch($name, $subject, array $arguments)
    {
        $checker = $this->getCheckerName($name);

        if (call_user_func($checker, $subject)) {
            throw new FailureException(sprintf(
                '%s not expected to return %s, but it did.',
                $this->presenter->presentString(sprintf('%s(%s)',
                    $checker, $this->presenter->presentValue($subject)
                )),
                $this->presenter->presentValue(true)
            ));
        }
    }

    /**
     * Returns matcher priority.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * @param string $name
     *
     * @return string|boolean
     */
    private function getCheckerName($name)
    {
        if (preg_match($this->regex, $name, $matches)) {
            $expected = strtolower($matches[1]);

            if ($expected == 'boolean') {
                return 'is_bool';
            }

            return 'is_' . $expected;
        }
    }
}
