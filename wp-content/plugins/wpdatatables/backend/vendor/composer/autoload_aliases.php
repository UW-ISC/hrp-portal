<?php

// Functions and constants

namespace {

}
namespace DI {
    if(!function_exists('\\DI\\value')){
        function value(...$args) {
            return \WPDataTables\Vendor\DI\value(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\create')){
        function create(...$args) {
            return \WPDataTables\Vendor\DI\create(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\autowire')){
        function autowire(...$args) {
            return \WPDataTables\Vendor\DI\autowire(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\factory')){
        function factory(...$args) {
            return \WPDataTables\Vendor\DI\factory(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\decorate')){
        function decorate(...$args) {
            return \WPDataTables\Vendor\DI\decorate(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\get')){
        function get(...$args) {
            return \WPDataTables\Vendor\DI\get(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\env')){
        function env(...$args) {
            return \WPDataTables\Vendor\DI\env(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\add')){
        function add(...$args) {
            return \WPDataTables\Vendor\DI\add(...func_get_args());
        }
    }
    if(!function_exists('\\DI\\string')){
        function string(...$args) {
            return \WPDataTables\Vendor\DI\string(...func_get_args());
        }
    }
}


namespace WPDataTables\Vendor {

    class AliasAutoloader
    {
        private string $includeFilePath;

        private array $autoloadAliases = array (
  'Laravel\\SerializableClosure\\Exceptions\\InvalidSignatureException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidSignatureException',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Exceptions',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Exceptions\\InvalidSignatureException',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Exceptions\\MissingSecretKeyException' => 
  array (
    'type' => 'class',
    'classname' => 'MissingSecretKeyException',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Exceptions',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Exceptions\\MissingSecretKeyException',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Exceptions\\PhpVersionNotSupportedException' => 
  array (
    'type' => 'class',
    'classname' => 'PhpVersionNotSupportedException',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Exceptions',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Exceptions\\PhpVersionNotSupportedException',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\SerializableClosure' => 
  array (
    'type' => 'class',
    'classname' => 'SerializableClosure',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\SerializableClosure',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Serializers\\Native' => 
  array (
    'type' => 'class',
    'classname' => 'Native',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Serializers',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Serializers\\Native',
    'implements' => 
    array (
      0 => 'Laravel\\SerializableClosure\\Contracts\\Serializable',
    ),
  ),
  'Laravel\\SerializableClosure\\Serializers\\Signed' => 
  array (
    'type' => 'class',
    'classname' => 'Signed',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Serializers',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Serializers\\Signed',
    'implements' => 
    array (
      0 => 'Laravel\\SerializableClosure\\Contracts\\Serializable',
    ),
  ),
  'Laravel\\SerializableClosure\\Signers\\Hmac' => 
  array (
    'type' => 'class',
    'classname' => 'Hmac',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Signers',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Signers\\Hmac',
    'implements' => 
    array (
      0 => 'Laravel\\SerializableClosure\\Contracts\\Signer',
    ),
  ),
  'Laravel\\SerializableClosure\\Support\\ClosureScope' => 
  array (
    'type' => 'class',
    'classname' => 'ClosureScope',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Support',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Support\\ClosureScope',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Support\\ClosureStream' => 
  array (
    'type' => 'class',
    'classname' => 'ClosureStream',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Support',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Support\\ClosureStream',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Support\\ReflectionClosure' => 
  array (
    'type' => 'class',
    'classname' => 'ReflectionClosure',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Support',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Support\\ReflectionClosure',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Support\\SelfReference' => 
  array (
    'type' => 'class',
    'classname' => 'SelfReference',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure\\Support',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Support\\SelfReference',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\UnsignedSerializableClosure' => 
  array (
    'type' => 'class',
    'classname' => 'UnsignedSerializableClosure',
    'isabstract' => false,
    'namespace' => 'Laravel\\SerializableClosure',
    'extends' => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\UnsignedSerializableClosure',
    'implements' => 
    array (
    ),
  ),
  'Invoker\\CallableResolver' => 
  array (
    'type' => 'class',
    'classname' => 'CallableResolver',
    'isabstract' => false,
    'namespace' => 'Invoker',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\CallableResolver',
    'implements' => 
    array (
    ),
  ),
  'Invoker\\Exception\\InvocationException' => 
  array (
    'type' => 'class',
    'classname' => 'InvocationException',
    'isabstract' => false,
    'namespace' => 'Invoker\\Exception',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\Exception\\InvocationException',
    'implements' => 
    array (
    ),
  ),
  'Invoker\\Exception\\NotCallableException' => 
  array (
    'type' => 'class',
    'classname' => 'NotCallableException',
    'isabstract' => false,
    'namespace' => 'Invoker\\Exception',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\Exception\\NotCallableException',
    'implements' => 
    array (
    ),
  ),
  'Invoker\\Exception\\NotEnoughParametersException' => 
  array (
    'type' => 'class',
    'classname' => 'NotEnoughParametersException',
    'isabstract' => false,
    'namespace' => 'Invoker\\Exception',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\Exception\\NotEnoughParametersException',
    'implements' => 
    array (
    ),
  ),
  'Invoker\\Invoker' => 
  array (
    'type' => 'class',
    'classname' => 'Invoker',
    'isabstract' => false,
    'namespace' => 'Invoker',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\Invoker',
    'implements' => 
    array (
      0 => 'Invoker\\InvokerInterface',
    ),
  ),
  'Invoker\\ParameterResolver\\AssociativeArrayResolver' => 
  array (
    'type' => 'class',
    'classname' => 'AssociativeArrayResolver',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\AssociativeArrayResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\ParameterResolver\\Container\\ParameterNameContainerResolver' => 
  array (
    'type' => 'class',
    'classname' => 'ParameterNameContainerResolver',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver\\Container',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\Container\\ParameterNameContainerResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\ParameterResolver\\Container\\TypeHintContainerResolver' => 
  array (
    'type' => 'class',
    'classname' => 'TypeHintContainerResolver',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver\\Container',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\Container\\TypeHintContainerResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\ParameterResolver\\DefaultValueResolver' => 
  array (
    'type' => 'class',
    'classname' => 'DefaultValueResolver',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\DefaultValueResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\ParameterResolver\\NumericArrayResolver' => 
  array (
    'type' => 'class',
    'classname' => 'NumericArrayResolver',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\NumericArrayResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\ParameterResolver\\ResolverChain' => 
  array (
    'type' => 'class',
    'classname' => 'ResolverChain',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\ResolverChain',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\ParameterResolver\\TypeHintResolver' => 
  array (
    'type' => 'class',
    'classname' => 'TypeHintResolver',
    'isabstract' => false,
    'namespace' => 'Invoker\\ParameterResolver',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\TypeHintResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'Invoker\\Reflection\\CallableReflection' => 
  array (
    'type' => 'class',
    'classname' => 'CallableReflection',
    'isabstract' => false,
    'namespace' => 'Invoker\\Reflection',
    'extends' => 'WPDataTables\\Vendor\\Invoker\\Reflection\\CallableReflection',
    'implements' => 
    array (
    ),
  ),
  'DI\\Annotation\\Inject' => 
  array (
    'type' => 'class',
    'classname' => 'Inject',
    'isabstract' => false,
    'namespace' => 'DI\\Annotation',
    'extends' => 'WPDataTables\\Vendor\\DI\\Annotation\\Inject',
    'implements' => 
    array (
    ),
  ),
  'DI\\Annotation\\Injectable' => 
  array (
    'type' => 'class',
    'classname' => 'Injectable',
    'isabstract' => false,
    'namespace' => 'DI\\Annotation',
    'extends' => 'WPDataTables\\Vendor\\DI\\Annotation\\Injectable',
    'implements' => 
    array (
    ),
  ),
  'DI\\CompiledContainer' => 
  array (
    'type' => 'class',
    'classname' => 'CompiledContainer',
    'isabstract' => true,
    'namespace' => 'DI',
    'extends' => 'WPDataTables\\Vendor\\DI\\CompiledContainer',
    'implements' => 
    array (
    ),
  ),
  'DI\\Compiler\\Compiler' => 
  array (
    'type' => 'class',
    'classname' => 'Compiler',
    'isabstract' => false,
    'namespace' => 'DI\\Compiler',
    'extends' => 'WPDataTables\\Vendor\\DI\\Compiler\\Compiler',
    'implements' => 
    array (
    ),
  ),
  'DI\\Compiler\\ObjectCreationCompiler' => 
  array (
    'type' => 'class',
    'classname' => 'ObjectCreationCompiler',
    'isabstract' => false,
    'namespace' => 'DI\\Compiler',
    'extends' => 'WPDataTables\\Vendor\\DI\\Compiler\\ObjectCreationCompiler',
    'implements' => 
    array (
    ),
  ),
  'DI\\Compiler\\RequestedEntryHolder' => 
  array (
    'type' => 'class',
    'classname' => 'RequestedEntryHolder',
    'isabstract' => false,
    'namespace' => 'DI\\Compiler',
    'extends' => 'WPDataTables\\Vendor\\DI\\Compiler\\RequestedEntryHolder',
    'implements' => 
    array (
      0 => 'DI\\Factory\\RequestedEntry',
    ),
  ),
  'DI\\Container' => 
  array (
    'type' => 'class',
    'classname' => 'Container',
    'isabstract' => false,
    'namespace' => 'DI',
    'extends' => 'WPDataTables\\Vendor\\DI\\Container',
    'implements' => 
    array (
      0 => 'Psr\\Container\\ContainerInterface',
      1 => 'DI\\FactoryInterface',
      2 => 'Invoker\\InvokerInterface',
    ),
  ),
  'DI\\ContainerBuilder' => 
  array (
    'type' => 'class',
    'classname' => 'ContainerBuilder',
    'isabstract' => false,
    'namespace' => 'DI',
    'extends' => 'WPDataTables\\Vendor\\DI\\ContainerBuilder',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\ArrayDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'ArrayDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\ArrayDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
    ),
  ),
  'DI\\Definition\\ArrayDefinitionExtension' => 
  array (
    'type' => 'class',
    'classname' => 'ArrayDefinitionExtension',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\ArrayDefinitionExtension',
    'implements' => 
    array (
      0 => 'DI\\Definition\\ExtendsPreviousDefinition',
    ),
  ),
  'DI\\Definition\\AutowireDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'AutowireDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\AutowireDefinition',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\DecoratorDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'DecoratorDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\DecoratorDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
      1 => 'DI\\Definition\\ExtendsPreviousDefinition',
    ),
  ),
  'DI\\Definition\\Dumper\\ObjectDefinitionDumper' => 
  array (
    'type' => 'class',
    'classname' => 'ObjectDefinitionDumper',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Dumper',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Dumper\\ObjectDefinitionDumper',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\EnvironmentVariableDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'EnvironmentVariableDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\EnvironmentVariableDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
    ),
  ),
  'DI\\Definition\\Exception\\InvalidAnnotation' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidAnnotation',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Exception',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Exception\\InvalidAnnotation',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Exception\\InvalidDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Exception',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Exception\\InvalidDefinition',
    'implements' => 
    array (
      0 => 'Psr\\Container\\ContainerExceptionInterface',
    ),
  ),
  'DI\\Definition\\FactoryDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'FactoryDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\FactoryDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
    ),
  ),
  'DI\\Definition\\Helper\\AutowireDefinitionHelper' => 
  array (
    'type' => 'class',
    'classname' => 'AutowireDefinitionHelper',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Helper',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Helper\\AutowireDefinitionHelper',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Helper\\CreateDefinitionHelper' => 
  array (
    'type' => 'class',
    'classname' => 'CreateDefinitionHelper',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Helper',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Helper\\CreateDefinitionHelper',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Helper\\DefinitionHelper',
    ),
  ),
  'DI\\Definition\\Helper\\FactoryDefinitionHelper' => 
  array (
    'type' => 'class',
    'classname' => 'FactoryDefinitionHelper',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Helper',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Helper\\FactoryDefinitionHelper',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Helper\\DefinitionHelper',
    ),
  ),
  'DI\\Definition\\InstanceDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'InstanceDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\InstanceDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
    ),
  ),
  'DI\\Definition\\ObjectDefinition\\MethodInjection' => 
  array (
    'type' => 'class',
    'classname' => 'MethodInjection',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\ObjectDefinition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\ObjectDefinition\\MethodInjection',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
    ),
  ),
  'DI\\Definition\\ObjectDefinition\\PropertyInjection' => 
  array (
    'type' => 'class',
    'classname' => 'PropertyInjection',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\ObjectDefinition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\ObjectDefinition\\PropertyInjection',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Reference' => 
  array (
    'type' => 'class',
    'classname' => 'Reference',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Reference',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
      1 => 'DI\\Definition\\SelfResolvingDefinition',
    ),
  ),
  'DI\\Definition\\Resolver\\ArrayResolver' => 
  array (
    'type' => 'class',
    'classname' => 'ArrayResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\ArrayResolver',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\Resolver\\DecoratorResolver' => 
  array (
    'type' => 'class',
    'classname' => 'DecoratorResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\DecoratorResolver',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\Resolver\\EnvironmentVariableResolver' => 
  array (
    'type' => 'class',
    'classname' => 'EnvironmentVariableResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\EnvironmentVariableResolver',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\Resolver\\FactoryResolver' => 
  array (
    'type' => 'class',
    'classname' => 'FactoryResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\FactoryResolver',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\Resolver\\InstanceInjector' => 
  array (
    'type' => 'class',
    'classname' => 'InstanceInjector',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\InstanceInjector',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Resolver\\ObjectCreator' => 
  array (
    'type' => 'class',
    'classname' => 'ObjectCreator',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\ObjectCreator',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\Resolver\\ParameterResolver' => 
  array (
    'type' => 'class',
    'classname' => 'ParameterResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\ParameterResolver',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Resolver\\ResolverDispatcher' => 
  array (
    'type' => 'class',
    'classname' => 'ResolverDispatcher',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\ResolverDispatcher',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\Source\\AnnotationBasedAutowiring' => 
  array (
    'type' => 'class',
    'classname' => 'AnnotationBasedAutowiring',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\AnnotationBasedAutowiring',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Source\\DefinitionSource',
      1 => 'DI\\Definition\\Source\\Autowiring',
    ),
  ),
  'DI\\Definition\\Source\\DefinitionArray' => 
  array (
    'type' => 'class',
    'classname' => 'DefinitionArray',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\DefinitionArray',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Source\\DefinitionSource',
      1 => 'DI\\Definition\\Source\\MutableDefinitionSource',
    ),
  ),
  'DI\\Definition\\Source\\DefinitionFile' => 
  array (
    'type' => 'class',
    'classname' => 'DefinitionFile',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\DefinitionFile',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Source\\DefinitionNormalizer' => 
  array (
    'type' => 'class',
    'classname' => 'DefinitionNormalizer',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\DefinitionNormalizer',
    'implements' => 
    array (
    ),
  ),
  'DI\\Definition\\Source\\NoAutowiring' => 
  array (
    'type' => 'class',
    'classname' => 'NoAutowiring',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\NoAutowiring',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Source\\Autowiring',
    ),
  ),
  'DI\\Definition\\Source\\ReflectionBasedAutowiring' => 
  array (
    'type' => 'class',
    'classname' => 'ReflectionBasedAutowiring',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\ReflectionBasedAutowiring',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Source\\DefinitionSource',
      1 => 'DI\\Definition\\Source\\Autowiring',
    ),
  ),
  'DI\\Definition\\Source\\SourceCache' => 
  array (
    'type' => 'class',
    'classname' => 'SourceCache',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\SourceCache',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Source\\DefinitionSource',
      1 => 'DI\\Definition\\Source\\MutableDefinitionSource',
    ),
  ),
  'DI\\Definition\\Source\\SourceChain' => 
  array (
    'type' => 'class',
    'classname' => 'SourceChain',
    'isabstract' => false,
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\SourceChain',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Source\\DefinitionSource',
      1 => 'DI\\Definition\\Source\\MutableDefinitionSource',
    ),
  ),
  'DI\\Definition\\StringDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'StringDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\StringDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
      1 => 'DI\\Definition\\SelfResolvingDefinition',
    ),
  ),
  'DI\\Definition\\ValueDefinition' => 
  array (
    'type' => 'class',
    'classname' => 'ValueDefinition',
    'isabstract' => false,
    'namespace' => 'DI\\Definition',
    'extends' => 'WPDataTables\\Vendor\\DI\\Definition\\ValueDefinition',
    'implements' => 
    array (
      0 => 'DI\\Definition\\Definition',
      1 => 'DI\\Definition\\SelfResolvingDefinition',
    ),
  ),
  'DI\\DependencyException' => 
  array (
    'type' => 'class',
    'classname' => 'DependencyException',
    'isabstract' => false,
    'namespace' => 'DI',
    'extends' => 'WPDataTables\\Vendor\\DI\\DependencyException',
    'implements' => 
    array (
      0 => 'Psr\\Container\\ContainerExceptionInterface',
    ),
  ),
  'DI\\Invoker\\DefinitionParameterResolver' => 
  array (
    'type' => 'class',
    'classname' => 'DefinitionParameterResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Invoker',
    'extends' => 'WPDataTables\\Vendor\\DI\\Invoker\\DefinitionParameterResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'DI\\Invoker\\FactoryParameterResolver' => 
  array (
    'type' => 'class',
    'classname' => 'FactoryParameterResolver',
    'isabstract' => false,
    'namespace' => 'DI\\Invoker',
    'extends' => 'WPDataTables\\Vendor\\DI\\Invoker\\FactoryParameterResolver',
    'implements' => 
    array (
      0 => 'Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'DI\\NotFoundException' => 
  array (
    'type' => 'class',
    'classname' => 'NotFoundException',
    'isabstract' => false,
    'namespace' => 'DI',
    'extends' => 'WPDataTables\\Vendor\\DI\\NotFoundException',
    'implements' => 
    array (
      0 => 'Psr\\Container\\NotFoundExceptionInterface',
    ),
  ),
  'DI\\Proxy\\ProxyFactory' => 
  array (
    'type' => 'class',
    'classname' => 'ProxyFactory',
    'isabstract' => false,
    'namespace' => 'DI\\Proxy',
    'extends' => 'WPDataTables\\Vendor\\DI\\Proxy\\ProxyFactory',
    'implements' => 
    array (
    ),
  ),
  'PhpDocReader\\AnnotationException' => 
  array (
    'type' => 'class',
    'classname' => 'AnnotationException',
    'isabstract' => false,
    'namespace' => 'PhpDocReader',
    'extends' => 'WPDataTables\\Vendor\\PhpDocReader\\AnnotationException',
    'implements' => 
    array (
    ),
  ),
  'PhpDocReader\\PhpDocReader' => 
  array (
    'type' => 'class',
    'classname' => 'PhpDocReader',
    'isabstract' => false,
    'namespace' => 'PhpDocReader',
    'extends' => 'WPDataTables\\Vendor\\PhpDocReader\\PhpDocReader',
    'implements' => 
    array (
    ),
  ),
  'PhpDocReader\\PhpParser\\TokenParser' => 
  array (
    'type' => 'class',
    'classname' => 'TokenParser',
    'isabstract' => false,
    'namespace' => 'PhpDocReader\\PhpParser',
    'extends' => 'WPDataTables\\Vendor\\PhpDocReader\\PhpParser\\TokenParser',
    'implements' => 
    array (
    ),
  ),
  'PhpDocReader\\PhpParser\\UseStatementParser' => 
  array (
    'type' => 'class',
    'classname' => 'UseStatementParser',
    'isabstract' => false,
    'namespace' => 'PhpDocReader\\PhpParser',
    'extends' => 'WPDataTables\\Vendor\\PhpDocReader\\PhpParser\\UseStatementParser',
    'implements' => 
    array (
    ),
  ),
  'Laravel\\SerializableClosure\\Contracts\\Serializable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Serializable',
    'namespace' => 'Laravel\\SerializableClosure\\Contracts',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Contracts\\Serializable',
    ),
  ),
  'Laravel\\SerializableClosure\\Contracts\\Signer' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Signer',
    'namespace' => 'Laravel\\SerializableClosure\\Contracts',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Laravel\\SerializableClosure\\Contracts\\Signer',
    ),
  ),
  'Invoker\\InvokerInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'InvokerInterface',
    'namespace' => 'Invoker',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Invoker\\InvokerInterface',
    ),
  ),
  'Invoker\\ParameterResolver\\ParameterResolver' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ParameterResolver',
    'namespace' => 'Invoker\\ParameterResolver',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Invoker\\ParameterResolver\\ParameterResolver',
    ),
  ),
  'DI\\Definition\\Definition' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Definition',
    'namespace' => 'DI\\Definition',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\Definition',
    ),
  ),
  'DI\\Definition\\ExtendsPreviousDefinition' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ExtendsPreviousDefinition',
    'namespace' => 'DI\\Definition',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\ExtendsPreviousDefinition',
    ),
  ),
  'DI\\Definition\\Helper\\DefinitionHelper' => 
  array (
    'type' => 'interface',
    'interfacename' => 'DefinitionHelper',
    'namespace' => 'DI\\Definition\\Helper',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\Helper\\DefinitionHelper',
    ),
  ),
  'DI\\Definition\\Resolver\\DefinitionResolver' => 
  array (
    'type' => 'interface',
    'interfacename' => 'DefinitionResolver',
    'namespace' => 'DI\\Definition\\Resolver',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\Resolver\\DefinitionResolver',
    ),
  ),
  'DI\\Definition\\SelfResolvingDefinition' => 
  array (
    'type' => 'interface',
    'interfacename' => 'SelfResolvingDefinition',
    'namespace' => 'DI\\Definition',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\SelfResolvingDefinition',
    ),
  ),
  'DI\\Definition\\Source\\Autowiring' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Autowiring',
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\Autowiring',
    ),
  ),
  'DI\\Definition\\Source\\DefinitionSource' => 
  array (
    'type' => 'interface',
    'interfacename' => 'DefinitionSource',
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\DefinitionSource',
    ),
  ),
  'DI\\Definition\\Source\\MutableDefinitionSource' => 
  array (
    'type' => 'interface',
    'interfacename' => 'MutableDefinitionSource',
    'namespace' => 'DI\\Definition\\Source',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Definition\\Source\\MutableDefinitionSource',
    ),
  ),
  'DI\\Factory\\RequestedEntry' => 
  array (
    'type' => 'interface',
    'interfacename' => 'RequestedEntry',
    'namespace' => 'DI\\Factory',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\Factory\\RequestedEntry',
    ),
  ),
  'DI\\FactoryInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'FactoryInterface',
    'namespace' => 'DI',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\DI\\FactoryInterface',
    ),
  ),
  'Psr\\Container\\ContainerExceptionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ContainerExceptionInterface',
    'namespace' => 'Psr\\Container',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Psr\\Container\\ContainerExceptionInterface',
    ),
  ),
  'Psr\\Container\\ContainerInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ContainerInterface',
    'namespace' => 'Psr\\Container',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Psr\\Container\\ContainerInterface',
    ),
  ),
  'Psr\\Container\\NotFoundExceptionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'NotFoundExceptionInterface',
    'namespace' => 'Psr\\Container',
    'extends' => 
    array (
      0 => 'WPDataTables\\Vendor\\Psr\\Container\\NotFoundExceptionInterface',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        public function autoload($class)
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile)
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
