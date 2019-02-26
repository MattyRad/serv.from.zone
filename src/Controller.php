<?php namespace App;

use Psr\Log\LoggerInterface as Log;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractController
{
    const ERROR_NOT_FOUND = "body>h1{No records found}+p{We didn't find any TXT records on serv.%s}+small{Don't forget about the time it takes for propagation!}";
    const ERROR_BAD_STRING = 'body>h1{Bad Emmet string :(}+p>{Failed to expand the emmet string, try }+a[href=/validate]{validating}+{it first}';

    /**
     * @Route("/", name="main")
     */
    public function index(Request $request, DNS\Record\Resolver $resolver, Emmet\Expander $expander, Log $log)
    {
        $host = getenv('HOSTNAME_OVERRIDE') ?: $request->getHost();

        $records = $resolver->getServRecords($host);

        if (! $abbr = $records->getEmmetRecord()) {
            $log->notice('Emmet record was not found;', ['host' => $host, 'records' => $records->toArray()]);

            $expanded_payload = $expander->expand(sprintf(self::ERROR_NOT_FOUND, $host));
        } else {
            try {
                $expanded_payload = $expander->expand($abbr);
            } catch (Emmet\Exception\FailedExpansion $e) {
                $log->notice('Failed to expand;', ['host' => $host, 'records' => $records->toArray()]);

                $expanded_payload = $expander->expand(self::ERROR_BAD_STRING);
            } catch (Emmet\Exception\LengthExceeded $e) {
                $log->warning('Length exceeded;', ['host' => $host, 'length' => $e->getLength()]);

                $expanded_payload = $expander->expand(self::ERROR_BAD_STRING);
            }
        }

        if ($host !== 'serv.from.zone') {
            $log->info('Successfully rendered;', ['host' => $host, 'records' => $records->toArray()]);
        }

        return $this->render('emmet/index.html.twig', compact('records', 'expanded_payload'));
    }

    /**
     * @Route(
     *     "/validate",
     *     condition="context.getMethod() in ['GET']"
     * )
     */
    public function getValidate(Request $request)
    {
        return $this->render('emmet/validate.html.twig');
    }

    /**
     * @Route(
     *     "/validate",
     *     condition="context.getMethod() in ['POST']"
     * )
     */
    public function postValidate(Request $request, Emmet\Expander $expander)
    {
        $data = json_decode($request->getContent(), true); // FIXME: why isn't `$request->request->get('candidate');` working?

        try {
            $expanded = $expander->expand($data['candidate'] ?? '');
        } catch (Emmet\Exception\FailedExpansion $e) {
            return new JsonResponse(['success' => false]);
        } catch (Emmet\Exception\LengthExceeded $e) {
            $expanded_payload = $expander->expand(self::ERROR_BAD_STRING);
        }

        return new JsonResponse(['success' => true, 'expanded' => $expanded]);
    }
}
