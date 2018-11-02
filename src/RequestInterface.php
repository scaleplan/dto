<?php

namespace Scaleplan\DTO;

/**
 * Interface RequestInterface
 *
 * @package Scaleplan\DTO
 */
interface RequestInterface
{
    /**
     * @return array
     */
    public function getParams() : array;
}