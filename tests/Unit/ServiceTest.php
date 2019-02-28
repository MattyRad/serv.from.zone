<?php namespace App\Tests\Unit;

use App\Service;
use App\DNS;
use App\Emmet;
use Prophecy\Argument;
use Psr\Log\LoggerInterface as Log;

class ServiceTest extends \PHPUnit\Framework\TestCase
{
    const SAMPLE_ABBR = 'h1{Test}';
    const SAMPLE_EXPAND = '<h1>Test</h1>';

    const SAMPLE_EXPANDED_ERROR = '<h1>error message</h1>';

    private $resolver;
    private $expander;
    private $log;
    private $service;

    public function setUp()
    {
        $this->resolver = $this->prophesize(DNS\Record\Resolver::class);
        $this->expander = $this->prophesize(Emmet\Expander::class);
        $this->log = $this->prophesize(Log::class);

        $this->service = new Service(
            $this->resolver->reveal(),
            $this->expander->reveal(),
            $this->log->reveal()
        );
    }

    /**
     * @test
     * @dataProvider emptyRecordsProvider
     */
    public function missing_dns_records_should_provide_error_html(DNS\Record\Container $records)
    {
        $this->resolver->getRecords('serv.' . ($host = 'example.com'))
            ->willReturn($records);

        $this->expander->expand(sprintf(Service::ERROR_NOT_FOUND, $host))
            ->willReturn(self::SAMPLE_EXPANDED_ERROR);

        $result = $this->service->process($host);

        $this->assertEquals(self::SAMPLE_EXPANDED_ERROR, $result->getExpandedPayload());
    }

    /**
     * @test
     * @dataProvider emptyRecordsProvider
     */
    public function a_missing_emmet_record_be_logged(DNS\Record\Container $records)
    {
        $this->resolver->getRecords('serv.' . ($host = 'example.com'))
            ->willReturn($records);

        $this->expander->expand(sprintf(Service::ERROR_NOT_FOUND, $host))
            ->willReturn(self::SAMPLE_EXPANDED_ERROR);

        $result = $this->service->process($host);

        $this->log->notice('Emmet record was not found;', Argument::type('array'))
            ->shouldHaveBeenCalled();
    }

    public function emptyRecordsProvider()
    {
        return [
            [new Dns\Record\Container],
        ];
    }

    /**
     * @test
     * @dataProvider sampleRecordsProvider
     */
    public function records_and_expanded_payload_will_be_returned(DNS\Record\Container $records)
    {
        $this->resolver->getRecords('serv.' . ($host = 'example.com'))->willReturn($records);

        $this->expander->expand(self::SAMPLE_ABBR)->willReturn(self::SAMPLE_EXPAND);

        $result = $this->service->process($host);

        $this->assertEquals(self::SAMPLE_EXPAND, $result->getExpandedPayload());
        $this->assertEquals($records->toArray(), $result->getRecords()->toArray());
    }

    /**
     * @test
     * @dataProvider sampleRecordsProvider
     */
    public function successful_foreign_hosts_should_be_logged(DNS\Record\Container $records)
    {
        $this->resolver->getRecords('serv.' . ($host = 'example.com'))->willReturn($records);

        $this->expander->expand(self::SAMPLE_ABBR)->willReturn(self::SAMPLE_EXPAND);

        $result = $this->service->process($host);

        $this->log->info('Successfully rendered;', Argument::type('array'))
            ->shouldHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider sampleRecordsProvider
     */
    public function main_host_should_be_not_be_logged(DNS\Record\Container $records)
    {
        $this->resolver->getRecords('serv.' . ($host = 'from.zone'))->willReturn($records);

        $this->expander->expand(self::SAMPLE_ABBR)->willReturn(self::SAMPLE_EXPAND);

        $result = $this->service->process($host);

        $this->log->info('Successfully rendered;', Argument::type('array'))
            ->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider sampleRecordsProvider
     */
    public function bad_emmet_strings_should_be_logged(DNS\Record\Container $records)
    {
        $e = new Emmet\Exception\FailedExpansion(self::SAMPLE_ABBR, 1);

        $this->resolver->getRecords('serv.' . ($host = 'example.com'))->willReturn($records);

        $this->expander->expand(Service::ERROR_BAD_STRING)->willReturn(self::SAMPLE_EXPANDED_ERROR);

        $this->expander->expand(self::SAMPLE_ABBR)->willThrow($e);

        $result = $this->service->process($host);

        $this->log->notice('Failed to expand;', Argument::type('array'))
            ->shouldHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider sampleRecordsProvider
     */
    public function bad_emmet_strings_must_be_caught(DNS\Record\Container $records)
    {
        $e = new Emmet\Exception\FailedExpansion(self::SAMPLE_ABBR, 1);

        $this->resolver->getRecords('serv.' . ($host = 'example.com'))->willReturn($records);

        $this->expander->expand(Service::ERROR_BAD_STRING)->willReturn(self::SAMPLE_EXPANDED_ERROR);

        $this->expander->expand(self::SAMPLE_ABBR)->willThrow($e);

        $result = $this->service->process($host);

        $this->assertEquals(self::SAMPLE_EXPANDED_ERROR, $result->getExpandedPayload());
        $this->assertEquals($records->toArray(), $result->getRecords()->toArray());
    }

    /**
     * @test
     * @dataProvider sampleRecordsProvider
     */
    public function too_long_emmet_strings_should_be_logged(DNS\Record\Container $records)
    {
        $e = new Emmet\Exception\LengthExceeded(Emmet\Expander::ABBR_LENGTH_MAX + 1);

        $this->resolver->getRecords('serv.' . ($host = 'example.com'))->willReturn($records);

        $this->expander->expand(Service::ERROR_BAD_STRING)->willReturn(self::SAMPLE_EXPANDED_ERROR);

        $this->expander->expand(self::SAMPLE_ABBR)->willThrow($e);

        $result = $this->service->process($host);

        $this->log->warning('Length exceeded;', Argument::type('array'))
            ->shouldHaveBeenCalled();
    }

    public function sampleRecordsProvider()
    {
        return [
            [Dns\Record\Container::fromArray([
                'emmet' => self::SAMPLE_ABBR,
                'theme' => '000000',
                'description' => 'desc testing',
                'keywords' => 'key, word, test',
                'ga' => 'ga-abc123',
                'favicon' => 'http://example.com/favicon.ico',
                'title' => 'title test',
                'css' => ['http://example.com/style.css'],
                'scripts' => ['http://example.com/script.js'],
            ])],
        ];
    }
}
