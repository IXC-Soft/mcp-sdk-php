<?php

/**
 * Example MCP HTTP Server
 * 
 * This server provides the same functionality as the test server but over HTTP.
 * It's designed to work in standard PHP hosting environments including cPanel.
 *
 * (c) 2024 Logiscape LLC <https://logiscape.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    logiscape/mcp-sdk-php
 * @author     Josh Abbott <https://joshabbott.com>
 * @copyright  Logiscape LLC
 * @license    MIT License
 * @link       https://github.com/logiscape/mcp-sdk-php
 */

// Autoload dependencies
require __DIR__ . '/vendor/autoload.php';

use Mcp\Server\Server;
use Mcp\Server\HttpServerRunner;
use Mcp\Server\Transport\Http\StandardPhpAdapter;
use Mcp\Server\Transport\Http\Environment;
use Mcp\Server\Transport\Http\FileSessionStore;
use Mcp\Types\Prompt;
use Mcp\Types\PromptArgument;
use Mcp\Types\PromptMessage;
use Mcp\Types\ListPromptsResult;
use Mcp\Types\TextContent;
use Mcp\Types\Role;
use Mcp\Types\GetPromptResult;
use Mcp\Types\GetPromptRequestParams;
use Mcp\Types\Tool;
use Mcp\Types\ToolInputSchema;
use Mcp\Types\ToolInputProperties;
use Mcp\Types\ListToolsResult;
use Mcp\Types\CallToolResult;
use Mcp\Types\Resource;
use Mcp\Types\ResourceTemplate;
use Mcp\Types\ListResourcesResult;
use Mcp\Types\ListResourceTemplatesResult;
use Mcp\Types\ReadResourceResult;
use Mcp\Types\TextResourceContents;

// Only handle MCP requests to this specific endpoint
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$endpointPath = '/server_http.php'; // Adjust this if your file has a different name

if ($requestUri !== $endpointPath) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

ini_set('display_errors', '1');
error_reporting(E_ALL);

// Configure error handling for production
//if (getenv('MCP_DEBUG') !== 'true') {
//    error_reporting(E_ERROR | E_PARSE);
//    ini_set('display_errors', '0');
//}

// Check HTTP method
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'POST', 'DELETE'])) {
    http_response_code(405);
    header('Allow: GET, POST, DELETE');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Create server instance
$server = new Server('mcp-example-http-server');

// Register prompt handlers
$server->registerHandler('prompts/list', function($params) {
    $prompt = new Prompt(
        'example-prompt',
        'An example prompt template',
        [
            new PromptArgument(
                'arg1',
                'Example argument',
                true
            )
        ]
    );
    return new ListPromptsResult([$prompt]);
});

$server->registerHandler('prompts/get', function($params) {
    $name = $params->name;
    $arguments = $params->arguments;
    if ($name !== 'example-prompt') {
        throw new \InvalidArgumentException("Unknown prompt: {$name}");
    }
    
    // Get argument value safely
    $argValue = $arguments->arg1 ?? 'none';
    
    return new GetPromptResult(
        [
            new PromptMessage(
                Role::USER,
                new TextContent(
                    "Example prompt text with argument: $argValue"
                )
            )
        ],
        'Example prompt'
    );
});

// Register tool handlers
$server->registerHandler('tools/list', function($params) {
    // Create properties object
    $properties = ToolInputProperties::fromArray([
        'num1' => [
            'type' => 'number',
            'description' => 'First number'
        ],
        'num2' => [
            'type' => 'number',
            'description' => 'Second number'
        ]
    ]);

    // Create schema with properties and required fields
    $inputSchema = new ToolInputSchema(
        $properties,
        ['num1', 'num2']
    );

    // Create calculator tool
    $calculator = new Tool(
        'add-numbers',
        $inputSchema,
        'Adds two numbers together'
    );
    
    // Create a second tool for testing
    $properties2 = ToolInputProperties::fromArray([
        'text' => [
            'type' => 'string',
            'description' => 'Text to transform'
        ]
    ]);
    
    $inputSchema2 = new ToolInputSchema(
        $properties2,
        ['text']
    );
    
    $textTool = new Tool(
        'uppercase',
        $inputSchema2,
        'Converts text to uppercase'
    );

    return new ListToolsResult([$calculator, $textTool]);
});

$server->registerHandler('tools/call', function($params) {
    $name = $params->name;
    $arguments = $params->arguments ?? [];

    switch ($name) {
        case 'add-numbers':
            // Validate and convert arguments to numbers
            $num1 = filter_var($arguments['num1'] ?? null, FILTER_VALIDATE_FLOAT);
            $num2 = filter_var($arguments['num2'] ?? null, FILTER_VALIDATE_FLOAT);

            if ($num1 === false || $num2 === false) {
                return new CallToolResult(
                    [new TextContent(
                        "Error: Both arguments must be valid numbers"
                    )],
                    true
                );
            }

            $sum = $num1 + $num2;
            return new CallToolResult(
                [new TextContent(
                    "The sum of {$num1} and {$num2} is {$sum}"
                )]
            );
            
        case 'uppercase':
            $text = $arguments['text'] ?? '';
            if (empty($text)) {
                return new CallToolResult(
                    [new TextContent(
                        "Error: Text cannot be empty"
                    )],
                    true
                );
            }
            
            return new CallToolResult(
                [new TextContent(
                    "Uppercase version: " . strtoupper($text)
                )]
            );
            
        default:
            throw new \InvalidArgumentException("Unknown tool: {$name}");
    }
});

// Register resource handlers
$server->registerHandler('resources/list', function($params) {
    $resources = [
        new Resource(
            'Greeting Text',
            'example://greeting',
            'A simple greeting message',
            'text/plain'
        ),
        new Resource(
            'Server Information',
            'example://server-info',
            'Information about the server environment',
            'text/plain'
        )
    ];
    
    return new ListResourcesResult($resources);
});

$server->registerHandler('resources/read', function($params) {
    $uri = $params->uri;
    
    switch ($uri) {
        case 'example://greeting':
            return new ReadResourceResult(
                [new TextResourceContents(
                    "Hello from the example MCP HTTP server!",
                    $uri,
                    'text/plain'
                )]
            );
            
        case 'example://server-info':
            $info = [
                "Server Time: " . date('Y-m-d H:i:s'),
                "PHP Version: " . PHP_VERSION,
                "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
                "HTTP Transport: MCP HTTP Transport",
                "Environment: " . (Environment::isSharedHosting() ? 'Shared Hosting' : 'Standard Server'),
                "Session Timeout: " . (Environment::detectMaxExecutionTime() ?: 'No limit') . " seconds"
            ];
            
            return new ReadResourceResult(
                [new TextResourceContents(
                    implode("\n", $info),
                    $uri,
                    'text/plain'
                )]
            );
            
        default:
            throw new \InvalidArgumentException("Unknown resource: {$uri}");
    }
});

// Configure HTTP options
$httpOptions = [
    'session_timeout' => 1800, // 30 minutes
    'max_queue_size' => 500,   // Smaller queue for shared hosting
    'enable_sse' => false,     // No SSE for compatibility
    'shared_hosting' => true,  // Assume shared hosting for max compatibility
    'server_header' => 'MCP-PHP-Server/1.0',
];

try {
    // Create the adapter and handle the request
    // 1) Create a file-based store, pointing to an absolute or relative path
    $fileStore = new FileSessionStore(__DIR__ . '/mcp_sessions'); 
    
    // 2) Create a runner that uses that store
    $runner = new HttpServerRunner($server, $server->createInitializationOptions(), $httpOptions, null, $fileStore);
    
    // 3) Create a StandardPhpAdapter and pass your runner in directly
    $adapter = new StandardPhpAdapter($runner);
    
    // 4) Handle the request
    $adapter->handle();
} catch (\Exception $e) {
    // Log error to error_log
    error_log('MCP Server error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => getenv('MCP_DEBUG') === 'true' ? $e->getMessage() : 'An error occurred'
    ]);
}