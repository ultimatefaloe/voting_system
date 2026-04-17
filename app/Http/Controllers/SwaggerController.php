<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SwaggerController extends Controller
{
    /**
     * Get OpenAPI/Swagger specification
     */
    public function spec(): JsonResponse
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Voting System API',
                'description' => 'Complete API for managing organizations, elections, voting, and analytics',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'Support',
                    'email' => 'support@example.com',
                ],
                'license' => [
                    'name' => 'MIT',
                ],
            ],
            'servers' => [
                [
                    'url' => env('APP_URL') . '/api',
                    'description' => 'Production Server',
                    'variables' => [],
                ],
                [
                    'url' => 'http://localhost:8000/api',
                    'description' => 'Development Server',
                    'variables' => [],
                ],
            ],
            'paths' => $this->getPaths(),
            'components' => $this->getComponents(),
            'tags' => $this->getTags(),
        ];

        return response()->json($spec);
    }

    /**
     * Get Swagger UI HTML
     */
    public function ui()
    {
        return view('swagger.ui');
    }

    /**
     * Get API documentation as HTML
     */
    public function docs()
    {
        return view('swagger.docs');
    }

    private function getPaths(): array
    {
        return [
            // ====================================================================
            // Authentication Paths
            // ====================================================================
            '/auth/register' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Register new user',
                    'description' => 'Create a new user account with email and password',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string', 'example' => 'John Doe'],
                                        'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                        'password' => ['type' => 'string', 'format' => 'password', 'example' => 'password123'],
                                        'password_confirmation' => ['type' => 'string', 'format' => 'password', 'example' => 'password123'],
                                    ],
                                    'required' => ['name', 'email', 'password', 'password_confirmation'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'User registered successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'user' => ['$ref' => '#/components/schemas/User'],
                                            'token' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],

            '/auth/login' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Login user',
                    'description' => 'Authenticate user and receive access token',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'email' => ['type' => 'string', 'format' => 'email'],
                                        'password' => ['type' => 'string', 'format' => 'password'],
                                    ],
                                    'required' => ['email', 'password'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'user' => ['$ref' => '#/components/schemas/User'],
                                            'token' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    ],
                ],
            ],

            '/auth/me' => [
                'get' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Get current user',
                    'description' => 'Retrieve the authenticated user\'s profile',
                    'security' => [['bearerAuth' => []]],
                    'responses' => [
                        '200' => [
                            'description' => 'Current user profile',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'data' => ['$ref' => '#/components/schemas/User'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    ],
                ],
            ],

            '/auth/logout' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Logout user',
                    'description' => 'Invalidate the current authentication token',
                    'security' => [['bearerAuth' => []]],
                    'responses' => [
                        '200' => [
                            'description' => 'Logout successful',
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    ],
                ],
            ],

            // ====================================================================
            // Organization Paths
            // ====================================================================
            '/organizations' => [
                'get' => [
                    'tags' => ['Organizations'],
                    'summary' => 'List organizations',
                    'description' => 'Retrieve all organizations for the authenticated user',
                    'security' => [['bearerAuth' => []]],
                    'responses' => [
                        '200' => [
                            'description' => 'List of organizations',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'array',
                                                'items' => ['$ref' => '#/components/schemas/Organization'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    ],
                ],
                'post' => [
                    'tags' => ['Organizations'],
                    'summary' => 'Create organization',
                    'description' => 'Create a new organization',
                    'security' => [['bearerAuth' => []]],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string', 'example' => 'Board Association'],
                                        'description' => ['type' => 'string'],
                                    ],
                                    'required' => ['name'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Organization created',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Organization'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],

            // ====================================================================
            // Election Paths
            // ====================================================================
            '/organizations/{organizationId}/elections' => [
                'get' => [
                    'tags' => ['Elections'],
                    'summary' => 'List elections',
                    'description' => 'Retrieve all elections for an organization',
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                        [
                            'name' => 'organizationId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string', 'format' => 'uuid'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'List of elections',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'array',
                                                'items' => ['$ref' => '#/components/schemas/Election'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '403' => ['$ref' => '#/components/responses/Forbidden'],
                    ],
                ],
                'post' => [
                    'tags' => ['Elections'],
                    'summary' => 'Create election',
                    'description' => 'Create a new election in an organization',
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                        [
                            'name' => 'organizationId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string', 'format' => 'uuid'],
                        ],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string', 'example' => 'Board Election 2025'],
                                        'description' => ['type' => 'string'],
                                        'type' => ['type' => 'string', 'enum' => ['public', 'private']],
                                    ],
                                    'required' => ['title', 'type'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Election created',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Election'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '403' => ['$ref' => '#/components/responses/Forbidden'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],

            '/elections/{electionId}/results' => [
                'get' => [
                    'tags' => ['Results'],
                    'summary' => 'Get election results',
                    'description' => 'Retrieve published results for an election',
                    'parameters' => [
                        [
                            'name' => 'electionId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string', 'format' => 'uuid'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Election results',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Results'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                    ],
                ],
            ],

            '/elections/{electionId}/vote' => [
                'post' => [
                    'tags' => ['Voting'],
                    'summary' => 'Submit vote',
                    'description' => 'Submit a single vote for a candidate',
                    'parameters' => [
                        [
                            'name' => 'electionId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string', 'format' => 'uuid'],
                        ],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'candidate_id' => ['type' => 'string', 'format' => 'uuid'],
                                    ],
                                    'required' => ['candidate_id'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Vote recorded successfully',
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],
        ];
    }

    private function getComponents(): array
    {
        return [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string', 'format' => 'uuid'],
                        'name' => ['type' => 'string'],
                        'email' => ['type' => 'string', 'format' => 'email'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Organization' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string', 'format' => 'uuid'],
                        'name' => ['type' => 'string'],
                        'description' => ['type' => 'string'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Election' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string', 'format' => 'uuid'],
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string'],
                        'type' => ['type' => 'string', 'enum' => ['public', 'private']],
                        'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'closed']],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Results' => [
                    'type' => 'object',
                    'properties' => [
                        'election_id' => ['type' => 'string', 'format' => 'uuid'],
                        'title' => ['type' => 'string'],
                        'positions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'position_id' => ['type' => 'string'],
                                    'position_title' => ['type' => 'string'],
                                    'candidates' => ['type' => 'array'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'responses' => [
                'Unauthorized' => [
                    'description' => 'Unauthorized - Missing or invalid authentication token',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'message' => ['type' => 'string', 'example' => 'Unauthenticated'],
                                ],
                            ],
                        ],
                    ],
                ],
                'Forbidden' => [
                    'description' => 'Forbidden - Insufficient permissions',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'message' => ['type' => 'string', 'example' => 'Forbidden'],
                                ],
                            ],
                        ],
                    ],
                ],
                'NotFound' => [
                    'description' => 'Not Found - Resource does not exist',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'message' => ['type' => 'string', 'example' => 'Not found'],
                                ],
                            ],
                        ],
                    ],
                ],
                'ValidationError' => [
                    'description' => 'Validation Error - Invalid request data',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'message' => ['type' => 'string'],
                                    'errors' => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Laravel Sanctum Bearer token',
                ],
            ],
        ];
    }

    private function getTags(): array
    {
        return [
            [
                'name' => 'Authentication',
                'description' => 'User authentication and token management',
            ],
            [
                'name' => 'Organizations',
                'description' => 'Organization management',
            ],
            [
                'name' => 'Elections',
                'description' => 'Election management and configuration',
            ],
            [
                'name' => 'Results',
                'description' => 'Election results and statistics',
            ],
            [
                'name' => 'Voting',
                'description' => 'Voting functionality',
            ],
            [
                'name' => 'Analytics',
                'description' => 'Organization and election analytics',
            ],
        ];
    }
}
