<?php

/**
 * Model Context Protocol SDK for PHP
 *
 * (c) 2024 Logiscape LLC <https://logiscape.com>
 *
 * Based on the Python SDK for the Model Context Protocol
 * https://github.com/modelcontextprotocol/python-sdk
 *
 * PHP conversion developed by:
 * - Josh Abbott
 * - Claude 3.5 Sonnet (Anthropic AI model)
 * - ChatGPT o1 pro mode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    logiscape/mcp-sdk-php
 * @author     Josh Abbott <https://joshabbott.com>
 * @copyright  Logiscape LLC
 * @license    MIT License
 * @link       https://github.com/logiscape/mcp-sdk-php
 *
 * Filename: Server/InitializationState.php
 */

declare(strict_types=1);

namespace Mcp\Server;

class InitializationState
{
    public const NotInitialized = 1;
    public const Initializing = 2;
    public const Initialized = 3;

    public static function from($state)
    {
        $state = (int) $state;


        switch ($state) {
            case self::Initializing:
                return self::Initializing;
            case self::Initialized:
                return self::Initialized;
            case self::NotInitialized:
                return self::NotInitialized;
        }
        throw new \InvalidArgumentException("unknown state: $state");
    }

}
