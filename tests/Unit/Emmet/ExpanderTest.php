<?php namespace App\Tests\Unit\Emmet;

use App\Emmet;

class ExpanderTest extends \PHPUnit\Framework\TestCase
{
    private $expander;

    public function setUp()
    {
        $this->expander = new Emmet\Expander;
    }

    /**
     * @test
     */
    public function expand_must_return_an_expanded_string()
    {
        $expected = '<h1>Hello world!</h1>';

        $actual = $this->expander->expand('h1{Hello world!}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function expand_should_throw_an_exception_when_expansion_fails()
    {
        $this->expectException(Emmet\Exception\FailedExpansion::class);

        $this->expander->expand(':;');
    }
}
