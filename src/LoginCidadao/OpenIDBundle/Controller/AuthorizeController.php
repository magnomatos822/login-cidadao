<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\ServerBundle\Controller\AuthorizeController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

class AuthorizeController extends BaseController
{

    /**
     * @Route("/openid/connect/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $request       = $this->getRequest();
        $scope         = $request->request->get('scope');
        $is_authorized = $request->request->has('rejected') === false || $request->request->has('accepted')
            === true;
        $request->request->set('scope', implode(' ', $scope));

        $server = $this->get('oauth2.server');
        $client = $this->getClient($request);

        $response = $this->handleAuthorize($server, $is_authorized);

        $event = new OAuthEvent($this->getUser(), $client, $is_authorized);
        $this->get('event_dispatcher')
            ->dispatch(OAuthEvent::POST_AUTHORIZATION_PROCESS, $event);

        return $response;
    }

    /**
     * @Template()
     */
    public function authorizeAction($client_id, $scope, $response_type,
                                    $redirect_uri, $state = null, $nonce = null)
    {
        $client = $this->getClient($client_id);

        $scope = explode(' ', $scope);
        if (array_search('public_profile', $scope) === false) {
            $scope[] = 'public_profile';
        }

        $scopeManager = $this->getScopeManager();
        $scopes       = array_map(function($value) {
            return $value->getScope();
        }, $scopeManager->findScopesByScopes($scope));

        $warnUntrusted = $this->shouldWarnUntrusted($client);

        $qs = compact('client_id', 'scope', 'response_type', 'redirect_uri',
            'state', 'nonce');
        return compact('qs', 'scopes', 'client', 'warnUntrusted');
    }

    /**
     * @return \OAuth2\ServerBundle\Manager\ScopeManager
     */
    private function getScopeManager()
    {
        return $this->get('oauth2.scope_manager');
    }

    /**
     * @Route("/openid/connect/authorize", name="_authorize_validate")
     * @Method({"GET"})
     * @Template("OAuth2ServerBundle:Authorize:authorize.html.twig")
     */
    public function validateAuthorizeAction()
    {
        $request = $this->getRequest();
        $client  = $this->getClient($request);

        if ($client instanceof \FOS\OAuthServerBundle\Model\ClientInterface) {
            $event = $this->get('event_dispatcher')->dispatch(
                OAuthEvent::PRE_AUTHORIZATION_PROCESS,
                new OAuthEvent($this->getUser(), $client)
            );

            $server = $this->get('oauth2.server');
            if ($event->isAuthorizedClient()) {
                return $this->handleAuthorize($server,
                        $event->isAuthorizedClient());
            }
        }

        return parent::validateAuthorizeAction();
    }

    private function handleAuthorize($server, $is_authorized)
    {
        return $server->handleAuthorizeRequest($this->get('oauth2.request'),
                $this->get('oauth2.response'), $is_authorized,
                $this->getUser()->getId());
    }

    private function getClient($fullId)
    {
        if ($fullId instanceof Request) {
            $fullId = $fullId->get('client_id');
        }

        $id = explode('_', $fullId);
        $er = $this->getDoctrine()
            ->getRepository('LoginCidadaoOAuthBundle:Client');

        return $er->find($id[0]);
    }

    private function shouldWarnUntrusted(ClientInterface $client)
    {
        $authorizeUntrusted = $this->getParameter('warn_untrusted');

        if ($client->getOrganization() instanceof OrganizationInterface) {
            $isTrusted = $client->getOrganization()->isTrusted();
        } else {
            $isTrusted = false;
        }

        if ($isTrusted || $authorizeUntrusted) {
            return false; // do not warn
        }

        return true; // warn
    }
}
