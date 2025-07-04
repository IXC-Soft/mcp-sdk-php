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
 * Filename: Types/ResourceReference.php
 */

declare(strict_types=1);

namespace Mcp\Types;

/**
 * A reference to a resource or resource template definition
 */
class ResourceReference implements McpModel {
    /**
     * @readonly
     * @var string
     */
    public $uri;
    /**
     * @readonly
     * @var string
     */
    public $type = 'ref/resource';
    use ExtraFieldsTrait;

    public function __construct(string $uri, string $type = 'ref/resource')
    {
        $this->uri = $uri;
        $this->type = $type;
    }

    public static function fromArray(array $data): self {
        $uri = $data['uri'] ?? '';
        $type = $data['type'] ?? 'ref/resource';
        unset($data['uri'], $data['type']);

        $obj = new self($uri, $type);

        foreach ($data as $k => $v) {
            $obj->$k = $v; // ResourceReference uses ExtraFieldsTrait
        }

        $obj->validate();
        return $obj;
    }

    public function validate(): void {
        if (empty($this->uri)) {
            throw new \InvalidArgumentException('Resource reference URI cannot be empty');
        }
        if ($this->type !== 'ref/resource') {
            throw new \InvalidArgumentException('Resource reference type must be "ref/resource"');
        }
    }

    /**
     * @return mixed
     */
    public function jsonSerialize() {
        $data = get_object_vars($this);
        return array_merge($data, $this->extraFields);
    }
}