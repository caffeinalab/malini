<?php

namespace Malini\Interfaces;

interface SerializableInterface extends \JsonSerializable
{

    public function toObject();

    public function toJson();

    public function toArray() : array;

}