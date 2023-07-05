<?php

namespace JustSomeCode\FlysystemVault\DTO;

class VaultPathDTO
{
    public function __construct(
        public readonly string $mount,
        public readonly string $path,
        public readonly string $secret
    ){}

    public function getVaultPath(): string
    {
        return sprintf('/%s/%s', trim($this->mount, '/'), trim($this->path, '/'));
    }

    public function getReadPath(): string
    {
        return sprintf('/%s/%s', trim($this->mount, '/'), trim($this->path, '/'));
    }

    public function getReadPathKVv2(): string
    {
        $pieces = explode('/', $this->path);

        array_shift($pieces);

        array_splice($pieces, 1, 0, 'data');

        return sprintf('/%s', implode('/', $pieces));
    }

    public function getWritePath(): string
    {
        return sprintf('/%s/data/%s', trim($this->mount, '/'), trim($this->path, '/'));
    }

    public function getDeletePath(): string
    {
        return sprintf('/%s/metadata/%s', trim($this->mount, '/'), trim($this->path, '/'));
    }
}