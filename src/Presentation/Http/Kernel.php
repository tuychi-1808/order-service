<?php

declare(strict_types=1);

namespace App\Presentation\Http;

use App\Infrastructure\Http\Controller\OrderController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Kernel
{
    public function handle(): void
    {
        $request = Request::createFromGlobals();
        $orderController = new OrderController();

        $routes = new RouteCollection();
        $routes->add(
            'orders_list',
            new Route('/orders', ['_controller' => [$orderController, 'list']], [], [], '', [], ['GET'])
        );
        $routes->add(
            'orders_create',
            new Route('/orders', ['_controller' => [$orderController, 'create']], [], [], '', [], ['POST'])
        );
        $routes->add(
            'orders_show',
            new Route('/orders/{id}', ['_controller' => [$orderController, 'show']], ['id' => '\d+'], [], '', [], ['GET'])
        );

        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($routes, $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());
            $controller = $parameters['_controller'];

            unset($parameters['_controller'], $parameters['_route']);

            $arguments = array_values($parameters);

            if ($request->getMethod() === 'POST') {
                $arguments = [$request];
            }

            call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException) {
            http_response_code(404);
            header('Content-Type: application/json');

            echo json_encode([
                'error' => 'Route not found'
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');

            echo json_encode([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }
}