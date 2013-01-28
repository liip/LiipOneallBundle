<?php

namespace Liip\OneallBundle\Security\EntryPoint;

use Symfony\Component\HttpFoundation\ParameterBag;
use Liip\OneallBundle\Oneall\OneallApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * OneallAuthenticationEntryPoint starts an authentication via Oneall.
 *
 * @author Thomas Adam <thomas.adam@tebot.de>
 */
class OneallAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    protected $oneall;
    protected $options;
    protected $permissions;

    /**
     * Constructor
     *
     * @param OneallApi $oneall
     * @param array    $options
     */
    public function __construct(OneallApi $oneall, array $options = array(), array $permissions = array())
    {
        $this->oneall = $oneall;
        $this->permissions = $permissions;
        $this->options = new ParameterBag($options);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $redirect_uri = $request->getUriForPath($this->options->get('check_path', ''));
        if ($this->options->get('server_url') && $this->options->get('app_url')) {
            $redirect_uri = str_replace($this->options->get('server_url'), $this->options->get('app_url'), $redirect_uri);
        }
        
        $loginUrl = $this->oneall->getLoginUrl(
           array(
                'display' => $this->options->get('display', 'page'),
                'scope' => implode(',', $this->permissions),
                'redirect_uri' => $redirect_uri,
        ));
        
        if ($this->options->get('server_url') && $this->options->get('app_url')){
            return new Response('<html><head></head><body><script>top.location.href="'.$loginUrl.'";</script></body></html>');
        }
        
        return new RedirectResponse($loginUrl);
    }
}
