<?php

class LogMiddleware
{
	private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke ($request, $response, $next)
    {
        // add logging of response here, after call to $next
        $uri = $request->getUri();
    	$path = $uri->getPath();
    	$ipAddress = $request->getAttribute('ip_address');
    	$userAgent = $request->getHeader('HTTP_USER_AGENT');

    	if(strpos($path , 'getNotificationList') === false){
    		$this->container->logger->info('IP => '.$ipAddress .' : METHOD => '. $request->getMethod() . ' : ACTION => '. $path . " : FROM => " . $userAgent[0]);
		}
        
        $response = $next($request, $response);
        return $response;
    }
}
