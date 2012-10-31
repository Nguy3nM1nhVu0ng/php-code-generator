<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CG\Generator;

use Doctrine\Common\Annotations\PhpParser;

use CG\Core\ReflectionUtils;

/**
 * Represents a PHP class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpClass extends AbstractBuilder
{
    private static $phpParser;

    private $name;
    private $parentClassName;
    private $interfaceNames = array();
    private $useStatements = array();
    private $constants = array();
    private $properties = array();
    private $requiredFiles = array();
    private $methods = array();
    private $abstract = false;
    private $final = false;
    private $docblock;

    public static function create($name = null)
    {
        return new self($name);
    }

    public static function fromReflection(\ReflectionClass $ref)
    {
        $class = new static();
        $class
            ->setName($ref->name)
            ->setAbstract($ref->isAbstract())
            ->setFinal($ref->isFinal())
            ->setConstants($ref->getConstants())
        ;

        if (null === self::$phpParser) {
            if (!class_exists('Doctrine\Common\Annotations\PhpParser')) {
                self::$phpParser = false;
            } else {
                self::$phpParser = new PhpParser();
            }
        }

        if (false !== self::$phpParser) {
            $class->setUseStatements(self::$phpParser->parseClass($ref));
        }

        if ($docComment = $ref->getDocComment()) {
            $class->setDocblock(ReflectionUtils::getUnindentedDocComment($docComment));
        }

        foreach ($ref->getMethods() as $method) {
            $class->setMethod(static::createMethod($method));
        }

        foreach ($ref->getProperties() as $property) {
            $class->setProperty(static::createProperty($property));
        }

        return $class;
    }

    /**
     * @return PhpMethod
     */
    protected static function createMethod(\ReflectionMethod $method)
    {
        return PhpMethod::fromReflection($method);
    }

    /**
     * @return PhpProperty
     */
    protected static function createProperty(\ReflectionProperty $property)
    {
        return PhpProperty::fromReflection($property);
    }

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string|null $name
     */
    public function setParentClassName($name)
    {
        $this->parentClassName = $name;

        return $this;
    }

    public function setInterfaceNames(array $names)
    {
        $this->interfaceNames = $names;

        return $this;
    }

    /**
     * @param string $name
     */
    public function addInterfaceName($name)
    {
        $this->interfaceNames[] = $name;

        return $this;
    }

    public function setRequiredFiles(array $files)
    {
        $this->requiredFiles = $files;

        return $this;
    }

    /**
     * @param string $file
     */
    public function addRequiredFile($file)
    {
        $this->requiredFiles[] = $file;

        return $this;
    }

    public function setUseStatements(array $useStatements)
    {
        $this->useStatements = $useStatements;

        return $this;
    }

    /**
     * @param string      $namespace
     * @param string|null $alias
     */
    public function addUseStatement($namespace, $alias = null)
    {
        if (null === $alias) {
            $alias = substr($namespace, strrpos($namespace, '\\') + 1);
        }

        $this->useStatements[$alias] = $namespace;

        return $this;
    }

    public function setConstants(array $constants)
    {
        $this->constants = $constants;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setConstant($name, $value)
    {
        $this->constants[$name] = $value;

        return $this;
    }

    public function hasConstant($name)
    {
        return array_key_exists($this->constants, $name);
    }

    /**
     * @param string $name
     */
    public function removeConstant($name)
    {
        if (!array_key_exists($name, $this->constants)) {
            throw new \InvalidArgumentException(sprintf('The constant "%s" does not exist.', $name));
        }

        unset($this->constants[$name]);

        return $this;
    }

    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    public function setProperty(PhpProperty $property)
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $property
     */
    public function hasProperty($property)
    {
        if ($property instanceof PhpProperty) {
            $property = $property->name;
        }

        return isset($this->properties[$property]);
    }

    /**
     * @param string $property
     */
    public function removeProperty($property)
    {
        if ($property instanceof PhpProperty) {
            $property = $property->name;
        }

        if (!array_key_exists($property, $this->properties)) {
            throw new \InvalidArgumentException(sprintf('The property "%s" does not exist.', $property));
        }
        unset($this->properties[$property]);

        return $this;
    }

    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    public function setMethod(PhpMethod $method)
    {
        $this->methods[$method->getName()] = $method;

        return $this;
    }

    /**
     * @param string $method
     */
    public function hasMethod($method)
    {
        if ($method instanceof PhpMethod) {
            $method = $method->name;
        }

        return isset($this->methods[$method]);
    }

    /**
     * @param string $method
     */
    public function removeMethod($method)
    {
        if ($method instanceof PhpMethod) {
            $method = $method->name;
        }

        if (!array_key_exists($method, $this->methods)) {
            throw new \InvalidArgumentException(sprintf('The method "%s" does not exist.', $method));
        }
        unset($this->methods[$method]);

        return $this;
    }

    /**
     * @param boolean $bool
     */
    public function setAbstract($bool)
    {
        $this->abstract = (Boolean) $bool;

        return $this;
    }

    /**
     * @param boolean $bool
     */
    public function setFinal($bool)
    {
        $this->final = (Boolean) $bool;

        return $this;
    }

    /**
     * @param string $block
     */
    public function setDocblock($block)
    {
        $this->docblock = $block;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParentClassName()
    {
        return $this->parentClassName;
    }

    public function getInterfaceNames()
    {
        return $this->interfaceNames;
    }

    public function getRequiredFiles()
    {
        return $this->requiredFiles;
    }

    public function getUseStatements()
    {
        return $this->useStatements;
    }

    public function getNamespace()
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return null;
        }

        return substr($this->name, 0, $pos);
    }

    public function getShortName()
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return $this->name;
        }

        return substr($this->name, $pos+1);
    }

    public function getConstants()
    {
        return $this->constants;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function isAbstract()
    {
        return $this->abstract;
    }

    public function isFinal()
    {
        return $this->final;
    }

    public function getDocblock()
    {
        return $this->docblock;
    }
}
