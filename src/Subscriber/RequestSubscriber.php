<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\SwaggerFakerBundle\Subscriber;

use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Tebru\SwaggerFaker\SwaggerProvider;

/**
 * Class RequestSubscriber
 *
 * @author Nate Brunette <n@tebru.net>
 */
class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $router = $this->container->get('router');
        $request = $event->getRequest();

        if (false === $this->isEnabled($request)) {
            return;
        }

        // if we should skip trying to handle real routes and only use mock responses
        if (true === $this->shouldHijack($request)) {
            $response = $this->getMockResponse();
            $event->setResponse($response);

            return;
        }

        try {
            $router->getMatcher()->match($request->getPathInfo());
        } catch (\RuntimeException $exception) {
            $response = $this->getMockResponse();
            $event->setResponse($response);
        }
    }

    private function getMockResponse()
    {
        $request = Request::createFromGlobals();
        $path = $request->getPathInfo();
        $operation = strtolower($request->getMethod());
        $responseCode = $this->getResponseCode($request, $operation);
        $schema = $this->container->getParameter('swagger_faker.schema');
        $config = $this->getConfig($request);

        /** @see SwaggerProvider::swaggerSchema */
        $swaggerSchema = $this->getFormatter($request);
        $response = $swaggerSchema($schema, $path, $operation, $responseCode, $config);

        return new JsonResponse($response, $responseCode);
    }

    /**
     * @return callable
     */
    private function getFormatter(Request $request)
    {
        $generator = Factory::create();
        $generator->seed($this->getSeed($request));
        $generator->addProvider(new SwaggerProvider($generator));

        return $generator->getFormatter('swaggerSchema');
    }

    private function getSeed(Request $request)
    {
        return (int) $request->headers->get('x-swagger-faker-seed', $this->container->getParameter('swagger_faker.seed'));
    }

    private function isEnabled(Request $request)
    {
        if ($request->headers->has('x-swagger-faker-enabled')) {
            return 'true' === $request->headers->get('x-swagger-faker-enabled');
        }

        return true;
    }

    private function shouldHijack(Request $request)
    {
        if ($request->headers->has('x-swagger-faker-hijack')) {
            return 'true' === $request->headers->get('x-swagger-faker-hijack');
        }

        return $this->container->getParameter('swagger_faker.hijack');
    }

    private function getResponseCode(Request $request, $operation)
    {
        if ($request->headers->has('x-swagger-faker-response-code')) {
            return (int) $request->headers->get('x-swagger-faker-response-code');
        }

        return $this->container->getParameter('swagger_faker.default_' . $operation);
    }

    private function getConfig(Request $request)
    {
        $maxItems = (int) $request->headers->get('x-swagger-faker-max-items', $this->container->getParameter('swagger_faker.max_items'));
        $minItems = (int) $request->headers->get('x-swagger-faker-min-items', $this->container->getParameter('swagger_faker.min_items'));
        $uniqueItems = $request->headers->get('x-swagger-faker-unique-items', $this->container->getParameter('swagger_faker.unique_items'));
        $multipleOf = (int) $request->headers->get('x-swagger-faker-multiple-of', $this->container->getParameter('swagger_faker.multiple_of'));
        $maximum = (int) $request->headers->get('x-swagger-faker-maximum', $this->container->getParameter('swagger_faker.maximum'));
        $minimum = (int) $request->headers->get('x-swagger-faker-minimum', $this->container->getParameter('swagger_faker.minimum'));
        $chanceRequired = (int) $request->headers->get('x-swagger-faker-chance-required', $this->container->getParameter('swagger_faker.chance_required'));
        $maxLength = (int) $request->headers->get('x-swagger-faker-max-length', $this->container->getParameter('swagger_faker.max_length'));
        $minLength = (int) $request->headers->get('x-swagger-faker-min-length', $this->container->getParameter('swagger_faker.min_length'));

        if (is_string($uniqueItems)) {
            $uniqueItems = 'true' === $uniqueItems;
        }

        return [
            'maxItems' => $maxItems,
            'minItems' => $minItems,
            'uniqueItems' => $uniqueItems,
            'multipleOf' => $multipleOf,
            'maximum' => $maximum,
            'minimum' => $minimum,
            'chanceRequired' => $chanceRequired,
            'maxLength' => $maxLength,
            'minLength' => $minLength,
        ];
    }
}
