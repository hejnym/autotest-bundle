<?php

namespace Mano\AutotestBundle\Test;

use Codeception\Module\Symfony;
use Mano\AutotestBundle\Autotest;
use Codeception\Lib\ModuleContainer;
use PHPUnit\Framework\ExpectationFailedException;

class CodeceptionAutotestModule extends \Codeception\Module
{
    /** @var Symfony */
    private $symfony;

    /** @var Autotest */
    private $autotest;


    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        parent::__construct($moduleContainer, $config);

        try {
            $this->symfony = $this->getModule('Symfony');

            if (!method_exists($this->symfony, 'amLoggedInAs')) {
                $this->fail('Codeception module \'Symfony\' must be at least in version 1.2.0.');
            }
        } catch (ModuleException $e) {
            $this->fail('Unable to get module \'Symfony\'');
        }
    }

    public function _initialize()
    {
        ;
        try {
            $this->autotest = $this->symfony->grabService('mano_autotest.autotest');
        } catch (\Exception $e) {
            $this->fail('Autotest service can not be grabbed.');
        }
    }

    // the trick with data provider (as in PHPUnit) is not used because of this https://github.com/Codeception/Codeception/issues/4087
    public function autotest(): void
    {
        $errors = [];

        if ($this->autotest->getAdminEmail()) {
            $this->logIn();
        }

        foreach ($this->autotest->getRelevantRoutes() as $routeDecorator) {
            try {
                $resolvedPath = $routeDecorator->getResolvedPath();
                $this->symfony->amOnPage($resolvedPath);
                // make sure it will not redirect to log-in page TODO: make the assertion meaningful
                $this->symfony->seeCurrentUrlEquals($resolvedPath);
                $this->symfony->seeResponseCodeIsSuccessful();
            } catch (ExpectationFailedException $e) {
                if ($e->getMessage() === 'Failed asserting that two strings are equal.') {
                    $errors[$resolvedPath] = "Redirected to '{$this->symfony->grabFromCurrentUrl()}'. Need authorisation?";
                } else {
                    $errors[$resolvedPath] = $e->getMessage();
                }
            } catch (\Throwable $e) {
                $errors[$resolvedPath] = "'{$e->getMessage()}' in {$e->getFile()}, line {$e->getLine()}";
            }
        }

        if (count($errors)) {
            $output = '';
            foreach ($errors as $path => $error) {
                $output .= $path . " : " . $error . "\n\n";
            }

            $this->fail($output);
        }
    }

    protected function logIn(): void
    {
        $repository = $this->symfony->grabService($this->autotest->getUserRepository());
        if (!$repository) {
            throw new \InvalidArgumentException(sprintf("User repository '%s' not found. Define it in the the 
                config under key user_repository.", $this->autotest->getUserRepository()));
        }

        $user = $repository->findOneByEmail($this->autotest->getAdminEmail());
        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User with email %s not found.', $this->autotest->getAdminEmail()));
        }

        $this->symfony->amLoggedInAs($user);
    }
}
