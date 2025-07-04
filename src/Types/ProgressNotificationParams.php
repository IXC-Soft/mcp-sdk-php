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
 * Filename: Types/ProgressNotificationParams.php
 */

declare(strict_types=1);

namespace Mcp\Types;

/**
 * Params for progress notification:
 * {
 *   progressToken: string|number,
 *   progress: number,
 *   total?: number
 * }
 */
class ProgressNotificationParams implements McpModel {
    /**
     * @readonly
     * @var \Mcp\Types\ProgressToken
     */
    public $progressToken;
    /**
     * @readonly
     * @var float
     */
    public $progress;
    /**
     * @var float|null
     */
    public $total;
    /**
     * @var string|null
     */
    public $message;
    use ExtraFieldsTrait;

    public function __construct(ProgressToken $progressToken, float $progress, ?float $total = null, ?string $message = null)
    {
        $this->progressToken = $progressToken;
        $this->progress = $progress;
        $this->total = $total;
        $this->message = $message;
    }

    public function validate(): void {
        $this->progressToken->validate();
        if ($this->total !== null && $this->total < $this->progress) {
            throw new \InvalidArgumentException('Total progress cannot be less than current progress');
        }
    }

    /**
     * @return mixed
     */
    public function jsonSerialize() {
        $data = [
            'progressToken' => $this->progressToken,
            'progress' => $this->progress,
        ];
        if ($this->total !== null) {
            $data['total'] = $this->total;
        }
        if ($this->message !== null) {
            $data['message'] = $this->message;
        }
        return array_merge($data, $this->extraFields);
    }
}