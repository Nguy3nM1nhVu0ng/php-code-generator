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

/**
 * Abstract PHP member class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class AbstractPhpMember
{
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PUBLIC = 'public';

    private $static = false;
    private $visibility = self::VISIBILITY_PUBLIC;
    private $name;
    private $docblock;
    private $attributes = array();

    public function __construct($name = null)
    {
        $this->setName($name);
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
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        if ($visibility !== self::VISIBILITY_PRIVATE
            && $visibility !== self::VISIBILITY_PROTECTED
            && $visibility !== self::VISIBILITY_PUBLIC) {
            throw new \InvalidArgumentException(sprintf('The visibility "%s" does not exist.', $visibility));
        }

        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @param boolean $bool
     */
    public function setStatic($bool)
    {
        $this->static = (Boolean) $bool;

        return $this;
    }

    /**
     * @param string|null $doc
     */
    public function setDocblock($doc)
    {
        $this->docblock = $doc;

        return $this;
    }

    public function isStatic()
    {
        return $this->static;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDocblock()
    {
        return $this->docblock;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function removeAttribute($key)
    {
        unset($this->attributes[$key]);
    }

    public function getAttribute($key)
    {
        if ( ! isset($this->attributes[$key])) {
            throw new \InvalidArgumentException(sprintf('There is no attribute named "%s".', $key));
        }

        return $this->attributes[$key];
    }

    public function getAttributeOrElse($key, $default)
    {
        if ( ! isset($this->attributes[$key])) {
            return $default;
        }

        return $this->attributes[$key];
    }

    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    public function setAttributes(array $attrs)
    {
        $this->attributes = $attrs;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
