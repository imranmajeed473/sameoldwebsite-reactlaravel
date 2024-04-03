<?php

namespace App\Components\Passwords\Rules;

use App\Components\Passwords\Contracts\Blacklist as BlacklistContract;
use App\Components\Passwords\Rules\Blacklists\CommonPasswords;
use Closure;
use SensitiveParameter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Blacklist extends ValidationRule {
    protected static $blacklistMapping = [
        'common-passwords' => CommonPasswords::class
    ];

    public function __construct(
        protected readonly array $blacklists,
        protected readonly bool $substitutions = false
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool {
        return !empty($this->blacklists);
    }

    /**
     * @inheritDoc
     */
    public function validate(string $attribute, #[SensitiveParameter] mixed $value, Closure $fail) {
        foreach ($this->getVariants($value) as $variant) {
            if ($this->isBlacklisted($variant)) {
                $fail(__('The password has been blacklisted.'));
            }
        }
    }

    /**
     * Gets password variants to check for in blacklists.
     *
     * @param string $value
     * @return array
     */
    protected function getVariants(#[SensitiveParameter] string $value): array {
        $variants = [$value, Str::lower($value)];

        if ($this->substitutions) {
            array_push($variants, Str::unl33t($value), Str::l33t($value));
        }

        return $variants;
    }

    /**
     * Checks if blacklisted.
     *
     * @param string $value
     * @return boolean
     */
    protected function isBlacklisted(#[SensitiveParameter] string $value): bool {
        foreach ($this->getBlacklists() as $blacklist) {
            if ($blacklist->isBlacklisted($value))
                return true;
        }

        return false;
    }

    /**
     * Gets blacklists
     *
     * @return BlacklistContract[]
     */
    protected function getBlacklists(): array {
        return array_map(function ($list) {
            if (is_object($list) && $list instanceof BlacklistContract) {
                return $list;
            } else {
                return $this->getBlacklist($list);
            }
        }, $this->blacklists);
    }

    /**
     * Gets blacklist from key
     *
     * @param string $key
     * @return BlacklistContract
     * @throws InvalidArgumentException Thrown if blacklist cannot be resolved.
     */
    protected function getBlacklist(string $key): BlacklistContract {
        if ($this->isBlacklistMapped($key)) {
            return $this->mapBlacklist($key);
        } else if (is_subclass_of($key, BlacklistContract::class)) {
            return $this->createBlacklist($key);
        } else {
            throw new InvalidArgumentException(__("The provided key ':key' could not be resolved to anything meaningful.", ['key' => $key]));
        }
    }

    /**
     * Checks if key maps to blacklist
     *
     * @param string $key
     * @return boolean
     */
    protected function isBlacklistMapped(string $key): bool {
        return isset(static::$blacklistMapping[$key]);
    }

    /**
     * Maps key to Blacklist instance.
     *
     * @param string $key
     * @return BlacklistContract
     */
    protected function mapBlacklist(string $key): BlacklistContract {
        return $this->createBlacklist(static::$blacklistMapping[$key]);
    }

    /**
     * Creates a Blacklist instance.
     *
     * @param string $class
     * @return BlacklistContract
     */
    protected function createBlacklist(string $class): BlacklistContract {
        return App::make($class);
    }
}
