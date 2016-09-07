<?php

namespace Kraken\_Unit\Channel\Router\RuleMatch;

use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Router\RuleMatch\RuleMatchDestination;
use Kraken\Channel\Router\RuleMatch\RuleMatchException;
use Kraken\Channel\Router\RuleMatch\RuleMatchName;
use Kraken\Channel\Router\RuleMatch\RuleMatchOrigin;
use Kraken\Channel\Router\RuleMatch\RuleMatchPid;
use Kraken\Channel\Router\RuleMatch\RuleNegate;
use Kraken\Test\TUnit;

class RouterAllTest extends TUnit
{
    /**
     * @dataProvider validRulesProvider
     * @param string $class
     * @param mixed $input
     * @param Protocol $protocol
     * @param mixed $mixed
     */
    public function testApiConstructor_DoesNotThrowException($class, $input, $protocol, $mixed)
    {
        $this->createRule($class, $input);
    }

    /**
     * @dataProvider validRulesProvider
     * @param string $class
     * @param mixed $input
     * @param Protocol $protocol
     * @param mixed $mixed
     */
    public function testApiDestructor_DoesNotThrowException($class, $input, $protocol, $mixed)
    {
        $rule = $this->createRule($class, $input);
        unset($rule);
    }

    /**
     * @dataProvider validRulesProvider
     * @param string $class
     * @param mixed $input
     * @param Protocol $protocol
     * @param mixed $mixed
     */
    public function testApiInvoke_ReturnsTrue_WhenValidPatternSet($class, $input, $protocol, $mixed)
    {
        $rule = $this->createRule($class, $input);
        $protocol = $protocol !== null ? $protocol : new Protocol();
        $this->assertTrue($rule($mixed, $protocol));
    }

    /**
     * @dataProvider validRulesProvider
     * @param string $class
     * @param mixed $input
     * @param Protocol $protocol
     * @param mixed $mixed
     */
    public function testApiInvoke_ReturnsFalse_WhenValidPatternSetInNegate($class, $input, $protocol, $mixed)
    {
        $rule   = $this->createRule($class, $input);
        $negate = $this->createRule(RuleNegate::class, $rule);
        $protocol = $protocol !== null ? $protocol : new Protocol();
        $this->assertFalse($negate($mixed, $protocol));
    }

    /**
     * @dataProvider invalidRulesProvider
     * @param string $class
     * @param mixed $input
     * @param Protocol $protocol
     * @param mixed $mixed
     */
    public function testApiInvoke_ReturnsFalse_WhenInvalidPatternSet($class, $input, $protocol, $mixed)
    {
        $rule = $this->createRule($class, $input);
        $protocol = $protocol !== null ? $protocol : new Protocol();
        $this->assertFalse($rule($mixed, $protocol));
    }

    /**
     * @dataProvider invalidRulesProvider
     * @param string $class
     * @param mixed $input
     * @param Protocol $protocol
     * @param mixed $mixed
     */
    public function testApiInvoke_ReturnsTrue_WhenInvalidPatternSetInNegate($class, $input, $protocol, $mixed)
    {
        $rule   = $this->createRule($class, $input);
        $negate = $this->createRule(RuleNegate::class, $rule);
        $protocol = $protocol !== null ? $protocol : new Protocol();
        $this->assertTrue($negate($mixed, $protocol));
    }

    /**
     * @return mixed[][]
     */
    public function validRulesProvider()
    {
        return [
            [
                RuleMatchDestination::class,
                $this->createRuleDestinationInput(),
                $this->createRuleDestinationData(),
                null
            ],
            [
                RuleMatchException::class,
                $this->createRuleExceptionInput(),
                $this->createRuleExceptionData(),
                null
            ],
            [
                RuleMatchName::class,
                $this->createRuleNameInput(),
                null,
                $this->createRuleNameData()
            ],
            [
                RuleMatchOrigin::class,
                $this->createRuleOriginInput(),
                $this->createRuleOriginData(),
                null
            ],
            [
                RuleMatchPid::class,
                $this->createRulePidInput(),
                $this->createRulePidData(),
                null
            ]
        ];
    }

    /**
     * @return mixed[][]
     */
    public function invalidRulesProvider()
    {
        return [
            [
                RuleMatchDestination::class,
                $this->createRuleDestinationInput(),
                $this->createRuleDestinationWrongData(),
                null
            ],
            [
                RuleMatchException::class,
                $this->createRuleExceptionInput(),
                $this->createRuleExceptionWrongData(),
                null
            ],
            [
                RuleMatchName::class,
                $this->createRuleNameInput(),
                null,
                $this->createRuleNameWrongData()
            ],
            [
                RuleMatchOrigin::class,
                $this->createRuleOriginInput(),
                $this->createRuleOriginWrongData(),
                null
            ],
            [
                RuleMatchPid::class,
                $this->createRulePidInput(),
                $this->createRulePidWrongData(),
                null
            ]
        ];
    }

    /**
     * @return string
     */
    public function createRuleDestinationInput()
    {
        return 'A?iasN*me';
    }

    /**
     * @return string
     */
    public function createRuleDestinationData()
    {
        $protocol = $this->createProtocol();
        $protocol->setDestination('AliasName');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRuleDestinationWrongData()
    {
        $protocol = $this->createProtocol();
        $protocol->setDestination('Name');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRuleExceptionInput()
    {
        return 'T?sk*Exception';
    }

    /**
     * @return string
     */
    public function createRuleExceptionData()
    {
        $protocol = $this->createProtocol();
        $protocol->setException('TaskIncompleteException');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRuleExceptionWrongData()
    {
        $protocol = $this->createProtocol();
        $protocol->setException('IncompleteException');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRuleNameInput()
    {
        return 'A?iasN*me';
    }

    /**
     * @return string
     */
    public function createRuleNameData()
    {
        return 'AliasName';
    }

    /**
     * @return string
     */
    public function createRuleNameWrongData()
    {
        return 'Name';
    }

    /**
     * @return string
     */
    public function createRuleOriginInput()
    {
        return 'A?iasN*me';
    }

    /**
     * @return string
     */
    public function createRuleOriginData()
    {
        $protocol = $this->createProtocol();
        $protocol->setOrigin('AliasName');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRuleOriginWrongData()
    {
        $protocol = $this->createProtocol();
        $protocol->setOrigin('Name');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRulePidInput()
    {
        return 'pid_289';
    }

    /**
     * @return string
     */
    public function createRulePidData()
    {
        $protocol = $this->createProtocol();
        $protocol->setPid('pid_289');

        return $protocol;
    }

    /**
     * @return string
     */
    public function createRulePidWrongData()
    {
        $protocol = $this->createProtocol();
        $protocol->setPid('pid_184');

        return $protocol;
    }

    /**
     * @param string $class
     * @param mixed $input
     * @return object|callable
     */
    public function createRule($class, $input)
    {
        return new $class($input);
    }

    /**
     * @return Protocol
     */
    public function createProtocol()
    {
        return new Protocol();
    }
}
