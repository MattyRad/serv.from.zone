<?php namespace App\Tests\Unit\DNS\Record;

use App\Service;
use App\DNS;
use App\Emmet;
use Prophecy\Argument;
use Psr\Log\LoggerInterface as Log;

class ResolverTest extends \PHPUnit\Framework\TestCase
{
    private $dig;
    private $resolver;

    public function setUp()
    {
        $this->dig = $this->prophesize(DNS\Dig::class);

        $this->resolver = new DNS\Record\Resolver($this->dig->reveal());
    }

    /**
     * @test
     * @dataProvider txtRecordsProvider
     */
    public function returned_container_must_map_txt_records_to_record_container(array $txt_records)
    {
        $this->dig->host($host = 'serv.example.com', ['TXT'])->willReturn($txt_records);

        $container = $this->resolver->getRecords($host);

        $this->assertEquals([
          'emmet' => '<h1>testing</h1>',
          'theme' => '000000',
          'description' => null,
          'keywords' => null,
          'ga' => 'UA-abc123',
          'favicon' => 'https://example.com/favicon.ico',
          'title' => 'test title',
          'css' => [
            'https://example.com/css/styles.min.css',
          ],
          'scripts' => [
            'https://example.com/script.min.js',
          ],
        ], $container->toArray());
    }

    public function txtRecordsProvider(): array
    {
        return [
            [
                [
                    'title=test title',
                    'theme=000000',
                    '<h1>testing</h1>',
                    'favicon=https://example.com/favicon.ico',
                    'script=https://example.com/script.min.js',
                    'css=https://example.com/css/styles.min.css',
                    'ga=UA-abc123',
                ],
            ],
        ];
    }
}
