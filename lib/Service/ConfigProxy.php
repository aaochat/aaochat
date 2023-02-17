<?php

namespace OCA\AaoChat\Service;

use OC\User\User;
use OCA\AaoChat\AppInfo\Application;
use OCP\IConfig;

/**
 * class Config.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class ConfigProxy
{
    /**
     * @var IConfig
     */
    protected $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
    }

    public function getAppValue(string $name, string $default): string
    {
        return (string) $this->config->getAppValue(Application::APP_ID, $name, $default);
    }

    public function getUserValue(User $user, string $name, string $default): string
    {
        return (string) $this->config->getUserValue($user->getUid(), Application::APP_ID, $name, $default);
    }

    public function getAppValueBool(string $name, string $default): bool
    {
        return (bool) $this->getAppValue($name, $default);
    }

    public function getAppValueArray(string $name, string $default): array
    {
        return (array) json_decode($this->getAppValue($name, $default), true);
    }

    public function getAppValueInt(string $name, string $default): int
    {
        return (int) $this->getAppValue($name, $default);
    }

    public function getUserValueBool(User $user, string $name, string $default): bool
    {
        return (bool) $this->getUserValue($user, $name, $default);
    }

    public function getUserValueArray(User $user, string $name, string $default): array
    {
        return (array) json_decode($this->getUserValue($user, $name, $default), true);
    }

    public function getUserValueInt(User $user, string $name, string $default): int
    {
        return (int) $this->getUserValue($user, $name, $default);
    }
}
