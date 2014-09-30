<?php

class Sydney_View_Helper_ContentTypeCollection implements IteratorAggregate
{
    /**
     * @var Sydney_View_Helper_ContentType[]
     */
    private $contentTypes;

    public function __construct()
    {
        $this->contentTypes = array();
    }

    /**
     *
     * @param $identifier
     * @param Sydney_View_Helper_ContentType $item
     */
    public function add($identifier, Sydney_View_Helper_ContentType $item)
    {
        $this->contentTypes[$identifier] = $item;
    }

    /**
     *
     * @param $identifier string
     * @return Sydney_View_Helper_ContentType
     */
    public function get($identifier)
    {
        return $this->contentTypes[$identifier];
    }

    /**
     *
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->contentTypes);
    }

}