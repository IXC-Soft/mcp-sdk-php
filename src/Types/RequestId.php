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
 * Filename: Types/RequestId.php
 */

declare(strict_types=1);

namespace Mcp\Types;

/**
 * A request ID can be a string or a number.
 */
class RequestId implements McpModel {
    /**
     * @var int|string
     */
    public $value;
    use ExtraFieldsTrait;

    /**
     * @param string|int $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function validate(): void {
        // No specific validation besides non-empty. 
        // The schema allows string or number. We consider both valid.
        if ($this->value === '') {
            throw new \InvalidArgumentException('RequestId cannot be empty');
        }
    }

    /**
     * @return mixed
     */
    public function jsonSerialize() {
        // Just serialize the value directly, plus any extra fields.
        return $this->value;
    }

    /**
     * @return int|string
     */
    public function getValue() {
        return $this->value;
    }
}