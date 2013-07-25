<?php

namespace Liip\OneallBundle\Oneall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Guzzle\Service\Client;

use Liip\OneallBundle\Security\OneallApiException;

/**
 * Implements Symfony2 session persistence for Oneall.
 */
class OneallApi
{
    const PREFIX = '_liip_oneall_';

    protected $config;
    protected $session;
    protected $guzzle;
    protected $prefix;

   /**
    * @param array $config the application configuration.
    * @see BaseOneall::__construct in oneall.php
    */
    public function __construct($config, Session $session, Client $guzzle, $prefix = self::PREFIX)
    {
        $this->config = $config;
        $this->session = $session;
        $this->guzzle = $guzzle;
        $this->prefix  = $prefix;
    }

    public function getUser(Request $request)
    {
        $token = $request->request->get('connection_token');
        if (empty($token)) {
            return null;
        }

        $site_subdomain = $this->config["site_subdomain"];
        $site_public_key = $this->config["site_public_key"];
        $site_private_key = $this->config["site_private_key"];
        $site_domain = $site_subdomain.'.api.oneall.com';

        //Connection Resource
        $resource_uri = 'https://'.$site_domain.'/connections/'.$token .'.json';

        //Setup connection
        $request = $this->guzzle->get($resource_uri);
        $request->setAuth($site_public_key, $site_private_key);
        $response = $request->send();
        $json = json_decode($response->getBody());
        $data = $json->response->result->data;
        if (empty($data->user->user_token)) {
            throw new OneallApiException('No user token returned');
        }
        return $data->user->user_token;
    }

    public function getUserData($userToken)
    {
        $site_subdomain = $this->config["site_subdomain"];
        $site_public_key = $this->config["site_public_key"];
        $site_private_key = $this->config["site_private_key"];

        $site_domain = $site_subdomain.'.api.oneall.com';
        $resource_uri = 'https://'.$site_domain.'/users/%s.json';

        $request = $this->guzzle->get(sprintf($resource_uri, $userToken));
        $request->setAuth($site_public_key, $site_private_key);
        $response = $request->send();
        $json = json_decode($response->getBody());
        $data = $json->response->result->data;
        if (empty($data->user->user_token)) {
            throw new OneallApiException('No user token returned');
        }

        $user = array(
            'oneallId' => $data->user->user_token,
        );

        foreach ($data->user->identities->identity as $identity) {
            $user['networks'][$identity->provider]['preferredUsername'] = isset($identity->preferredUsername) ? $identity->preferredUsername : null;
            $user['networks'][$identity->provider]['email'] = isset($identity->emails[0]->value) ? $identity->emails[0]->value : null;
            $user['networks'][$identity->provider]['location'] = isset($identity->currentLocation) ? $identity->currentLocation : null;
            $user['networks'][$identity->provider]['gender'] = isset($identity->gender) ? $identity->gender : null;
            $user['networks'][$identity->provider]['picture'] = isset($identity->thumbnailUrl) ? $identity->thumbnailUrl : null;
            $user['networks'][$identity->provider]['friends'] = '?';
        }

        return $user;
    }

    public function postData($path, $body, $headers = null)
    {
        $site_subdomain = $this->config["site_subdomain"];
        $site_public_key = $this->config["site_public_key"];
        $site_private_key = $this->config["site_private_key"];

        $site_domain = $site_subdomain.'.api.oneall.com';
        $resource_uri = 'https://'.$site_domain.'/'.$path;

        $request = $this->guzzle->post($resource_uri, $headers, $body);
        $request->setAuth($site_public_key, $site_private_key);
        $response = $request->send();
        $json = json_decode($response->getBody());
        $data = $json->response;

        return $data;
    }
}
