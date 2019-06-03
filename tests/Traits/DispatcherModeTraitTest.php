<?php declare(strict_types=1);

namespace GitWrapper\Test\Traits;

use GitWrapper\Event\GitEvent;
use GitWrapper\Test\AbstractGitWrapperTestCase;
use GitWrapper\Traits\DispatcherModeTrait;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionMethod;

final class DispatcherModeTraitTest extends AbstractGitWrapperTestCase
{
    use DispatcherModeTrait;

    /**
     * Create mock of ReflectionClass
     *
     * @return ReflectionClass&MockObject
     */
    public function createReflectionClassMock(): MockObject
    {
        return $this->createMock(ReflectionClass::class);
    }

    /**
     * Create mock of ReflectionMethod
     *
     * @return ReflectionMethod&MockObject
     */
    public function createReflectionMethodMock(): MockObject
    {
        return $this->createMock(ReflectionMethod::class);
    }

    /**
     * Create mock of GitEvent
     *
     * @return GitEvent&MockObject
     */
    public function createGitEventMock(): MockObject
    {
        return $this->createMock(GitEvent::class);
    }

    /**
     * Test for new Symfony dispatch argument order
     */
    public function testSetDispatcherWithNewArgumentOrder(): void
    {
        $reflectionMethod = $this->createReflectionMethodMock();
        $reflectionMethod
            ->method('getParameters')
            ->willReturn(['$events']);

        $reflectionClass = $this->createReflectionClassMock();
        $reflectionClass
            ->method('getMethod')
            ->willReturn($reflectionMethod);

        $this->setReflectionClass($reflectionClass);
        $event = $this->createGitEventMock();
        $eventName = 'eventTest';
        $result = $this->arrangeDispatchArguments($event, $eventName);

        $this->assertSame([$event, $eventName], $result);
    }

    /**
     * Test for old Symfony dispatch argument order
     */
    public function testSetDispatcherWithOldArgumentOrder(): void
    {
        $reflectionMethod = $this->createReflectionMethodMock();
        $reflectionMethod
            ->method('getParameters')
            ->willReturn(['$eventName', '$events']);

        $reflectionClass = $this->createReflectionClassMock();
        $reflectionClass
            ->method('getMethod')
            ->willReturn($reflectionMethod);

        $this->setReflectionClass($reflectionClass);
        $event = $this->createGitEventMock();
        $eventName = 'eventTest';
        $result = $this->arrangeDispatchArguments($event, $eventName);

        $this->assertSame([$eventName, $event], $result);
    }
}
