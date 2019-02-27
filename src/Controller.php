<?php namespace App;

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
    public function index(Request $request, Service $service)
    {
        $host = getenv('HOSTNAME_OVERRIDE') ?: $request->getHost();

        $result = $service->process($host);

        return $this->render('emmet/index.html.twig', [
            'records' => $result->getRecords(),
            'expanded_payload' => $result->getExpandedPayload(),
        ]);
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
            $expanded = $expander->expand(self::ERROR_BAD_STRING);
        }

        return new JsonResponse(['success' => true, 'expanded' => $expanded]);
    }
}
