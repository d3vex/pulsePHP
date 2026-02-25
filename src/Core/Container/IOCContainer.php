<?php
namespace D3vex\Pulsephp\Core\Container;

use D3vex\Pulsephp\Core\Container\Exceptions;

class IOCContainer {

    private DefinitionRegistry $registry;
    private array $sharedInstances = [];
    

    public function __construct() {
        $this->registry = new DefinitionRegistry();
        $this->sharedInstances = [];
    }

    public function registerShared(string $class, ?\Closure $factory = null): void {
        $defintion = new IOCContainerDefinition();

        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if($constructor !== null) {
            $constructorParams = $constructor->getParameters();
            $defintion->dependencies = $this->getConstructorDepenciesClass($constructorParams, $class);
        } else {
            $defintion->dependencies = [];
        }
        if ($constructor === null && $factory == null) throw new Exceptions\InvalidNoConstructorException($class);

        $defintion->shared = true;
        $defintion->className = $class;
        $defintion->factory = $factory;
        $this->registry->addDefintion($class, $defintion);
        $this->ensureNoDependenciesLoop();
    }
    public function registerDedicated(string $class, ?\Closure $factory = null): void {
        $defintion = new IOCContainerDefinition();

        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if( $constructor === null ) throw new Exceptions\InvalidNoConstructorException($class);
        $constructorParams = $constructor->getParameters();
        $defintion->dependencies = $this->getConstructorDepenciesClass($constructorParams, $class);
        $defintion->shared = false;
        $defintion->className = $class;
        $defintion->factory = $factory;
        $this->ensureNoDependenciesLoop();
        $this->registry->addDefintion($class, $defintion);
    }

    /**
     * 
     * @template T
     * @param class-string<T> $class
     * @throws \D3vex\Pulsephp\Core\Container\Exceptions\ContainerDefinitionDontExist
     * @return T
     */
    public function get(string $class): object {
        if(!$this->registry->hasDefintion($class)){
            throw new Exceptions\ContainerDefinitionDontExist($class);
        };
        $definition = $this->registry->getDefintion($class);
        if($definition->shared){
            if(isset($this->sharedInstances[$class])){
                return $this->sharedInstances[$class];
            }
            $this->sharedInstances[$class] = $this->createInstance($definition);
            return $this->sharedInstances[$class];
        }
        return $this->createInstance($definition);
    }
    
    public function has(string $class): bool {
        return $this->registry->hasDefintion($class);
    }


    private function createInstance(IOCContainerDefinition $definition): object {
        $dependenciesInstances = [];
        foreach($definition->dependencies as $dependencyClass) {
            $dependenciesInstances[] = $this->get($dependencyClass);
        }
        if($definition->factory !== null) {
            return call_user_func_array($definition->factory, [$this]);
        }
        $reflection = new \ReflectionClass($definition->className);
        return $reflection->newInstanceArgs($dependenciesInstances);
    }

    private function getConstructorDepenciesClass(array $parameters, string $class): array {
        $dependencies = [];
        foreach ($parameters as $param) {
            if($param->isOptional()) throw new Exceptions\InvalidOptionalConstructorParameterException($param->getName(), $class);
            if($param->allowsNull()) throw new Exceptions\InvalidAllowNullConstructorParameterException($param->getName(), $class);
            $type = $param->getType();
            if($type === null) {
                throw new Exceptions\InvalidConstructorParameterException($param->getName(), $class);
            }
            if($type->isBuiltin()) {
                throw new Exceptions\InvalidBuiltinTypeConstructorParameterException( $param->getName(), $type, $class);
            }
            if(!class_exists($type->getName())) {
                throw new Exceptions\InvalidClassTypeConstructorParameterException( $param->getName(), $type, $class);
            }
            if($type == $class) {
                throw new  Exceptions\InvalidAutoLoopConstructorParameterException( $param->getName(), $class);
            }
            $dependencies[] = $param->getType()->getName();
        }
        return $dependencies;
    }
    private function ensureNoDependenciesLoop(): void {
        foreach($this->registry->all() as $definition) {
            $dependencies = $definition->dependencies;
            foreach($dependencies as $dependency) {
                $depDef = $this->registry->getDefintion($dependency);
                if($depDef == null) continue;
                foreach($depDef->dependencies as $subDependency) {
                    if($subDependency == $definition->className) {
                        throw new Exceptions\InvalidLoopConstructorParameterException($dependency, $definition->className);
                    }
                }
            }
        }
    }

}

