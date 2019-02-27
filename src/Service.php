<?php namespace App;

use Psr\Log\LoggerInterface as Log;

class Service
{
    const ERROR_NOT_FOUND = "body>h1{No records found}+p{We didn't find any TXT records on serv.%s}+small{Don't forget about the time it takes for propagation!}";
    const ERROR_BAD_STRING = 'body>h1{Bad Emmet string :(}+p>{Failed to expand the emmet string, try }+a[href=/validate]{validating}+{it first}';

    private $resolver;
    private $expander;
    private $log;

    public function __construct(DNS\Record\Resolver $resolver, Emmet\Expander $expander, Log $log)
    {
        $this->resolver = $resolver;
        $this->expander = $expander;
        $this->log = $log;
    }

    public function process(string $host)
    {
        $records = $this->resolver->getServRecords($host);

        if (! $abbr = $records->getEmmetRecord()) {
            $this->log->notice('Emmet record was not found;', ['host' => $host, 'records' => $records->toArray()]);

            $expanded_payload = $this->expander->expand(sprintf(self::ERROR_NOT_FOUND, $host));
        } else {
            try {
                $expanded_payload = $this->expander->expand($abbr);
            } catch (Emmet\Exception\FailedExpansion $e) {
                $this->log->notice('Failed to expand;', ['host' => $host, 'records' => $records->toArray()]);

                $expanded_payload = $this->expander->expand(self::ERROR_BAD_STRING);
            } catch (Emmet\Exception\LengthExceeded $e) {
                $this->log->warning('Length exceeded;', ['host' => $host, 'length' => $e->getLength()]);

                $expanded_payload = $this->expander->expand(self::ERROR_BAD_STRING);
            }
        }

        if ($host !== 'serv.from.zone') {
            $this->log->info('Successfully rendered;', ['host' => $host, 'records' => $records->toArray()]);
        }

        return $this->makeResult($records, $expanded_payload);
    }

    private function makeResult(DNS\Record\Container $records, string $payload)
    {
        return new class($records, $payload) {
            public function __construct(DNS\Record\Container $records, string $payload)
            {
                $this->records = $records;
                $this->payload = $payload;
            }

            public function getRecords(): DNS\Record\Container
            {
                return $this->records;
            }

            public function getExpandedPayload(): string
            {
                return $this->payload;
            }
        };
    }

    public function validate(string $abbr)
    {
        try {
            $expanded = $this->expander->expand($abbr);
        } catch (Emmet\Exception\FailedExpansion $e) {
            return new JsonResponse(['success' => false]);
        } catch (Emmet\Exception\LengthExceeded $e) {
            $expanded = $this->expander->expand(self::ERROR_BAD_STRING);
        }

        return new JsonResponse(['success' => true, 'expanded' => $expanded]);
    }
}
