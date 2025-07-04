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
 * Filename: Types/CompleteResult.php
 */

declare(strict_types=1);

namespace Mcp\Types;

/**
 * Result of a completion request
 * completion: { values: string[], total?: number, hasMore?: boolean }
 */
class CompleteResult extends Result {
    /**
     * @readonly
     * @var \Mcp\Types\CompletionObject
     */
    public $completion;
    public function __construct(
        CompletionObject $completion,
        ?Meta $_meta = null
    ) {
        $this->completion = $completion;
        parent::__construct($_meta);
    }

    public static function fromResponseData(array $data): self {
        // Extract _meta
        $meta = null;
        if (isset($data['_meta'])) {
            $metaData = $data['_meta'];
            unset($data['_meta']);
            $meta = new Meta();
            foreach ($metaData as $k => $v) {
                $meta->$k = $v;
            }
        }

        // Extract completion
        $completionData = $data['completion'] ?? [];
        unset($data['completion']);

        $completion = CompletionObject::fromArray($completionData);

        $obj = new self($completion, $meta);

        // Extra fields
        foreach ($data as $k => $v) {
            $obj->$k = $v;
        }

        $obj->validate();
        return $obj;
    }

    public function validate(): void {
        parent::validate();
        $this->completion->validate();
    }
}