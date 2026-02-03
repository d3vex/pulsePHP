<?php

class IOCContainer {

    private DefinitionRegistry $registry;
    private array $sharedInstances = [];
    

    public function __construct() {
        $this->registry = new DefinitionRegistry();
        $this->sharedInstances = [];
    }

    public function registerShared(string $class, ?Closure $factory = null): void {
        $defintion = new ContainerDefinition();

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) throw new InvalidNoConstructorException($class);
        $constructorParams = $constructor->getParameters();

        $defintion->dependencies = $this->getConstructorDepenciesClass($constructorParams, $class);
        $defintion->shared = true;
        $defintion->className = $class;
        $defintion->factory = $factory;
        $this->registry->addDefintion($class, $defintion);
        $this->ensureNoDependenciesLoop();
    }
    public function registerDedicated(string $class, ?Closure $factory = null): void {
        $defintion = new ContainerDefinition();

        $reflection = new ReflectionClass($class);
        $constructorParams = $reflection->getConstructor()->getParameters();
        $defintion->dependencies = $this->getConstructorDepenciesClass($constructorParams, $class);
        $defintion->shared = false;
        $defintion->className = $class;
        $defintion->factory = $factory;
        $this->ensureNoDependenciesLoop();
        $this->registry->addDefintion($class, $defintion);
    }

    public function get(string $class): object {
        if(!$this->registry->hasDefintion($class)){
            throw new ContainerDefinitionDontExist($class);
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
    

    private function createInstance(ContainerDefinition $definition): object {
        $dependenciesInstances = [];
        foreach($definition->dependencies as $dependencyClass) {
            $dependenciesInstances[] = $this->get($dependencyClass);
        }
        if($definition->factory !== null) {
            return call_user_func_array($definition->factory, [$this]);
        }
        $reflection = new ReflectionClass($definition->className);
        return $reflection->newInstanceArgs($dependenciesInstances);
    }

    private function getConstructorDepenciesClass(array $parameters, string $class): array {
        $dependencies = [];
        foreach ($parameters as $param) {
            if($param->isOptional()) throw new InvalidOptionalConstructorParameterException($param->getName(), $class);
            if($param->allowsNull()) throw new InvalidAllowNullConstructorParameterException($param->getName(), $class);
            $type = $param->getType();
            if($type === null) {
                throw new InvalidConstructorParameterException($param->getName(), $class);
            }
            if($type->isBuiltin()) {
                throw new InvalidBuiltinTypeConstructorParameterException( $param->getName(), $type, $class);
            }
            if(!class_exists($type->getName())) {
                throw new InvalidClassTypeConstructorParameterException( $param->getName(), $type, $class);
            }
            if($type == $class) {
                throw new  InvalidAutoLoopConstructorParameterException( $param->getName(), $class);
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
                    echo $subDependency . " " . $definition->className . "\n";
                    if($subDependency == $definition->className) {
                        throw new InvalidLoopConstructorParameterException($dependency, $definition->className);
                    }
                }
            }
        }
    }

}

