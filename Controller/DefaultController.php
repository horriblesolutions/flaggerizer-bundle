<?php

namespace HorribleSolutions\FlaggerizerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    const SLACK_USERS_INFO = 'https://slack.com/api/users.info?token=%s&user=%s';

    /**
     * @Route("/", name="flaggerize")
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
                ['error' => 'Missing image parameter not set.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($flag == null) {
            return new JsonResponse(
                ['error' => 'Missing flag parameters not set.'],
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

    /**
     * @Route("/api/slack", name="slack_command")
     * @param Request $request
     *
     * @return Response
     */
    public function slackCommandAction(Request $request)
    {
        $userId = $request->get('user_id');
        $flag   = $request->get('text');

        if ($flag == null) {
            return new Response('Missing flag parameter not set.');
        }

        $token = $this->getParameter('horrible_solutions_flaggerizer.slack_token');

        $slackResponse = file_get_contents(
            sprintf(self::SLACK_USERS_INFO, $token, $userId)
        );

        $slackResponse = json_decode($slackResponse, true);

        if (!is_array($slackResponse)) {
            return new Response('Something went wrong with the Slack API.');
        }

        $imageUrl = $slackResponse['user']['profile']['image_192'];

        return new Response($this->generateUrl('flaggerize', [
            'image' => $imageUrl,
            'flag'  => $flag,
        ]));
    }

}
