<?php

namespace JustSomeCode\FlysystemVault;

use Vault\Client;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use Vault\Exceptions\RequestException;
use League\Flysystem\FilesystemAdapter;
use JustSomeCode\FlysystemVault\DTO\VaultPathDTO;
use JustSomeCode\FlysystemVault\Exceptions\InvalidVaultPath;
use JustSomeCode\FlysystemVault\Exceptions\UnsupportedAction;

class VaultAdapter implements FilesystemAdapter
{
    // prefix in (string) paths that denotes that protocol is vault
    // use case is for configuration (.env etc.), example:
    // MYSQL_PASSWORD=vault:mount-name/path/secret
    protected const T_VAULT_PROTOCOL = 'vault:';

    public function __construct(
        public readonly Client $client
    )
    {}

    public function fileExists(string $path): bool
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function directoryExists(string $path): bool
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function write(string $path, string $contents, Config $config): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function read(string $path): string
    {
        $dto = $this->getPathFromString($path);

        try
        {
            $result = $this->client->read($dto->getReadPathKVv2());

            $secret = $result->getData()['data'] ?? $result->getData() ?? null;

            if(is_null($secret))
            {
                throw new InvalidVaultPath('No secret found at given path: '. $path);
            }

            if(!isset($secret[$dto->secret]))
            {
                throw new InvalidVaultPath('The requested secret ('. $dto->secret .') not found at given path: '. $path);
            }

            return $secret[$dto->secret];
        }
        catch(RequestException $e)
        {
            throw new InvalidVaultPath('Secret not found: '. $path);
        }
    }

    public function readStream(string $path)
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function delete(string $path): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function deleteDirectory(string $path): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function createDirectory(string $path, Config $config): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function visibility(string $path): FileAttributes
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function mimeType(string $path): FileAttributes
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function lastModified(string $path): FileAttributes
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function fileSize(string $path): FileAttributes
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function move(string $source, string $destination, Config $config): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        throw new UnsupportedAction('Unsupported action: "'. __METHOD__ .'"');
    }

    protected function getPathFromString(string $path): VaultPathDTO
    {
        if(!str_contains($path, self::T_VAULT_PROTOCOL))
        {
            throw new InvalidVaultPath('Invalid path supplied, expected "'. self::T_VAULT_PROTOCOL .'" prefix - got none. Path given: '. $path);
        }

        $pieces = explode(self::T_VAULT_PROTOCOL, $path);

        if (sizeof($pieces) !== 2) {
            throw new InvalidVaultPath('Incorrect Vault path, expected "'. self::T_VAULT_PROTOCOL .'" as delimiter but it was not found. Path received: ' . $path);
        }

        $pathPieces = explode('/', $pieces[1]);

        $mount = $pieces[0];

        // Remove last element, that's the key
        $secret = array_pop($pathPieces);

        // Create the path for vault
        $vaultPath = sprintf('/%s', implode('/', $pathPieces));

        return new VaultPathDTO($mount, $vaultPath, $secret);
    }
}