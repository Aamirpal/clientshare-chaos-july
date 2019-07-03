<?php
if(!function_exists('testRoute'))
{
    function testRoute(string $route_name, array $route_parameters = []): string
    {
        $base_route = route($route_name, $route_parameters);
        $invalid_route_pattern = 'http://:/';
        if (!str_contains($base_route, $invalid_route_pattern))
            return $base_route;
        
        $app_url = rtrim(env('APP_URL', 'http://localhost'), '/') . '/';
        if (strpos($app_url, 'http://') !== 0 && strpos($app_url, 'https://') !== 0)
            $app_url = 'http://' . $app_url;
        
        return str_replace($invalid_route_pattern, $app_url, $base_route);
    }
}