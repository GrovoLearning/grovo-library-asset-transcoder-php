<?php

namespace Grovo\AssetTranscoder;

class TranscoderFile
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $size;

    /**
     * TranscoderFile constructor.
     * @param string $id
     * @param string $name
     * @param int $size
     */
    public function __construct(string $id, string $name, int $size)
    {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
