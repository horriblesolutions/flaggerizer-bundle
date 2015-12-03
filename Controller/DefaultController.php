<?php

namespace HorribleSolutions\FlaggerizerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/flaggerize", name="flaggerize")
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $imageUrl = $request->get('image');
        $flag     = $request->get('flag');

        if ($imageUrl == null) {
            return new JsonResponse(
                ['error' => 'Missing parameters image not set.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($flag == null) {
            return new JsonResponse(
                ['error' => 'Missing parameters flag not set.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $image = $this->get('app.flaggerizer')->render($imageUrl, $flag);

        if ($image == null) {
            return new JsonResponse(
                ['error' => sprintf('Image Url "%s" couldn\'t be loaded or no flag "%s" was found.', $imageUrl, $flag)],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new Response(
            $image->getImageBlob(),
            Response::HTTP_OK,
            ['Content-type' => 'image/png']
        );
    }
}
