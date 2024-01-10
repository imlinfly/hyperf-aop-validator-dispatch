<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/14 14:11:28
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\ValidatorDispatch\Aspect;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Mapping;
use Hyperf\HttpServer\Annotation\PatchMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Validation\Annotation\Scene;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\UnauthorizedException;
use Lynnfly\ValidatorDispatch\Annotation\Valid;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class ValidatorDispatchAspect extends AbstractAspect
{
    private array $implements = [];

    public array $classes = [
        'App\*\Controller\*',
        'App\Controller\*',
    ];

    public array $annotations = [
        Controller::class,
        AutoController::class,
        GetMapping::class,
        Mapping::class,
        PatchMapping::class,
        PostMapping::class,
        PutMapping::class,
        RequestMapping::class,
        Valid::class,
    ];

    public ?int $priority = -100;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly ContainerInterface     $container,
    )
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        /** @var Dispatched $dispatched */
        $dispatched = $this->request->getAttribute(Dispatched::class);

        if (!$dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        if ($this->shouldHandle($dispatched)) {
            try {
                [$requestHandler, $method] = $this->prepareHandler($dispatched->handler->callback);
                if ($method) {
                    $reflectionMethod = ReflectionManager::reflectMethod($requestHandler, $method);
                    $parameters = $reflectionMethod->getParameters();
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() === null) {
                            continue;
                        }
                        $className = $parameter->getType()->getName();
                        if ($this->isImplementedValidatesWhenResolved($className)) {
                            /** @var ValidatesWhenResolved $formRequest */
                            $formRequest = $this->container->get($className);
                            if ($formRequest instanceof FormRequest) {
                                $this->handleSceneAnnotation($formRequest, $requestHandler, $method, $parameter->getName());
                            }
                            $formRequest->validateResolved();
                        }
                    }
                }
            } catch (UnauthorizedException $exception) {
                return $this->handleUnauthorizedException($exception);
            }
        }

        return $proceedingJoinPoint->process();
    }

    public function isImplementedValidatesWhenResolved(string $className): bool
    {
        if (!isset($this->implements[$className]) && class_exists($className)) {
            $implements = class_implements($className);
            $this->implements[$className] = in_array(ValidatesWhenResolved::class, $implements, true);
        }
        return $this->implements[$className] ?? false;
    }

    protected function handleSceneAnnotation(FormRequest $request, string $class, string $method, string $argument): void
    {
        /** @var null|MultipleAnnotation $scene */
        $scene = AnnotationCollector::getClassMethodAnnotation($class, $method)[Scene::class] ?? null;
        if (!$scene) {
            return;
        }

        $annotations = $scene->toAnnotations();
        if (empty($annotations)) {
            return;
        }

        /** @var Scene $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation->argument === null || $annotation->argument === $argument) {
                $request->scene($annotation->scene ?? $method);
                return;
            }
        }
    }

    /**
     * @param UnauthorizedException $exception Keep this argument here even this argument is unused in the method,
     *                                         maybe the user need the details of exception when rewrite this method
     */
    protected function handleUnauthorizedException(UnauthorizedException $exception): ResponseInterface
    {
        return Context::override(ResponseInterface::class, fn(ResponseInterface $response) => $response->withStatus(403));
    }

    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && !$dispatched->handler->callback instanceof Closure;
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     */
    protected function prepareHandler(array|string $handler): array
    {
        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            if (!isset($array[1]) && class_exists($handler) && method_exists($handler, '__invoke')) {
                $array[1] = '__invoke';
            }
            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new RuntimeException('Handler not exist.');
    }
}
