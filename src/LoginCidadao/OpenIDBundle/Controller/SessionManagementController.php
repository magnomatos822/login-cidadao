<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/openid/connect")
 */
class SessionManagementController extends Controller
{

    /**
     * @Route("/session/check", name="oidc_check_session_iframe")
     * @Method({"GET"})
     * @Template
     */
    public function checkSessionAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/session/origins", name="oidc_get_origins")
     * @Method({"GET"})
     * @Template
     */
    public function getOriginsAction(Request $request)
    {
        $client = $this->getClient($request->get('client_id'));

        $uris   = array();
        $uris[] = $client->getSiteUrl();
        $uris[] = $client->getTermsOfUseUrl();
        $uris[] = $client->getLandingPageUrl();
        if ($client->getMetadata()) {
            $meta   = $client->getMetadata();
            $uris[] = $meta->getClientUri();
            $uris[] = $meta->getInitiateLoginUri();
            $uris[] = $meta->getPolicyUri();
            $uris[] = $meta->getSectorIdentifierUri();
            $uris[] = $meta->getTosUri();
            $uris   = array_merge($uris, $meta->getRedirectUris(),
                $meta->getRequestUris());
        }

        $result = array_unique(
            array_map(function($value) {
                if ($value === null) {
                    return;
                }
                $uri = parse_url($value);

                $uri[PHP_URL_FRAGMENT] = '';
                $uri[PHP_URL_PATH]     = '';
                $uri[PHP_URL_QUERY]    = '';
                $uri[PHP_URL_USER]     = '';
                $uri[PHP_URL_PASS]     = '';
                return $this->unparseUrl($uri);
            }, array_filter($uris))
        );

        return new JsonResponse(array_values($result));
    }

    /**
     * @Route("/session/end", name="oidc_end_session_endpoint")
     */
    public function endSessionAction(Request $request)
    {
        //
    }

    /**
     * @param string $clientId
     * @return \LoginCidadao\OAuthBundle\Entity\Client
     */
    private function getClient($clientId)
    {
        $clientId = explode('_', $clientId);
        $id       = $clientId[0];
        return $this->getDoctrine()->getManager()
                ->getRepository('LoginCidadaoOAuthBundle:Client')->find($id);
    }

    private function unparseUrl($parsed_url)
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment']
                : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
