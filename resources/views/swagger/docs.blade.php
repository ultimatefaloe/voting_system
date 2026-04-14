<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Voting System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .nav-tabs {
            display: flex;
            gap: 10px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .nav-tabs button {
            padding: 10px 20px;
            border: none;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .nav-tabs button.active {
            background: #667eea;
            color: white;
        }
        
        .nav-tabs button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .content {
            display: none;
        }
        
        .content.active {
            display: block;
        }
        
        .endpoint {
            background: white;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .method {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
            font-size: 12px;
            margin-right: 10px;
        }
        
        .method.get { background: #61affe; }
        .method.post { background: #49cc90; }
        .method.patch { background: #fca130; }
        .method.delete { background: #f93e3e; }
        
        .path {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 8px 12px;
            border-radius: 3px;
            display: inline-block;
            margin: 10px 0;
            word-break: break-all;
        }
        
        .description {
            margin: 15px 0;
            line-height: 1.8;
        }
        
        .section {
            margin: 30px 0;
        }
        
        .section h2 {
            font-size: 1.8em;
            color: #333;
            margin: 20px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .section h3 {
            font-size: 1.3em;
            color: #555;
            margin: 15px 0 10px 0;
        }
        
        .code-block {
            background: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .code-block code {
            display: block;
            line-height: 1.5;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .status-code {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            margin: 5px 5px 5px 0;
        }
        
        .status-code.success { background: #d4edda; color: #155724; }
        .status-code.error { background: #f8d7da; color: #721c24; }
        .status-code.info { background: #d1ecf1; color: #0c5460; }
        
        .parameter {
            background: #f9f9f9;
            padding: 10px;
            margin: 5px 0;
            border-left: 3px solid #667eea;
            border-radius: 3px;
        }
        
        .auth-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
        
        .link-section {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .link-section a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
        }
        
        .link-section a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <h1>🗳️ Voting System API</h1>
    <p>Complete documentation for the Voting System platform API</p>
</header>

<div class="container">
    <div class="link-section">
        <strong>Quick Links:</strong>
        <a href="{{ route('swagger.spec') }}" target="_blank">📋 OpenAPI Spec (JSON)</a>
        <a href="{{ route('swagger.ui') }}" target="_blank">🎨 Interactive Swagger UI</a>
        <a href="{{ route('health') }}">✅ Health Check</a>
    </div>
    
    <div class="section">
        <h2>Getting Started</h2>
        <p>The Voting System API is a RESTful API for managing organizations, elections, voting, and analytics.</p>
        
        <h3>Base URL</h3>
        <div class="path">{{ route('swagger.spec') }}</div>
        
        <h3>Authentication</h3>
        <p>Most endpoints require authentication using Bearer tokens (Laravel Sanctum):</p>
        <div class="code-block">
            <code>Authorization: Bearer YOUR_ACCESS_TOKEN</code>
        </div>
        
        <h3>Response Format</h3>
        <p>All responses are JSON formatted:</p>
        <div class="code-block">
            <code>{
  "message": "Operation successful",
  "data": { ... }
}</code>
        </div>
    </div>
    
    <div class="section">
        <h2>Authentication Endpoints</h2>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/auth/register</span>
            </div>
            <h3>Register New User</h3>
            <p>Create a new user account</p>
            <div class="code-block">
                <code>POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}</code>
            </div>
            <div class="auth-notice">
                <strong>Response:</strong> Returns user profile and authentication token
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/auth/login</span>
            </div>
            <h3>Login User</h3>
            <p>Authenticate with email and password</p>
            <div class="code-block">
                <code>POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}</code>
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/auth/me</span>
            </div>
            <h3>Get Current User</h3>
            <p>Retrieve the authenticated user's profile</p>
            <div class="auth-notice">
                <strong>⚠️ Authentication Required:</strong> Bearer token needed
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/auth/logout</span>
            </div>
            <h3>Logout User</h3>
            <p>Invalidate the current authentication token</p>
            <div class="auth-notice">
                <strong>⚠️ Authentication Required:</strong> Bearer token needed
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Organization Endpoints</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations</span>
            </div>
            <h3>List Organizations</h3>
            <p>Retrieve all organizations for the authenticated user</p>
            <div class="auth-notice">
                <strong>⚠️ Authentication Required</strong>
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/organizations</span>
            </div>
            <h3>Create Organization</h3>
            <p>Create a new organization</p>
            <div class="code-block">
                <code>POST /api/organizations
Authorization: Bearer TOKEN
Content-Type: application/json

{
  "name": "Board Association",
  "description": "Our organization's board"
}</code>
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations/{id}</span>
            </div>
            <h3>Get Organization Details</h3>
            <p>Retrieve details for a specific organization</p>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method patch">PATCH</span>
                <span class="path">/api/organizations/{id}</span>
            </div>
            <h3>Update Organization</h3>
            <p>Update organization information</p>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method delete">DELETE</span>
                <span class="path">/api/organizations/{id}</span>
            </div>
            <h3>Delete Organization</h3>
            <p>Delete an organization (irreversible)</p>
        </div>
    </div>
    
    <div class="section">
        <h2>Election Endpoints</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations/{org}/elections</span>
            </div>
            <h3>List Elections</h3>
            <p>Retrieve all elections for an organization</p>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/organizations/{org}/elections</span>
            </div>
            <h3>Create Election</h3>
            <p>Create a new election</p>
            <div class="code-block">
                <code>POST /api/organizations/1/elections
Authorization: Bearer TOKEN
Content-Type: application/json

{
  "title": "Board Election 2025",
  "description": "Annual board election",
  "type": "public"
}</code>
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/organizations/{org}/elections/{election}/publish</span>
            </div>
            <h3>Publish Election</h3>
            <p>Publish an election to make it live</p>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/organizations/{org}/elections/{election}/close</span>
            </div>
            <h3>Close Election</h3>
            <p>Close an election to stop voting</p>
        </div>
    </div>
    
    <div class="section">
        <h2>Voting Endpoints</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/elections/{election}/ballot</span>
            </div>
            <h3>Get Voter Ballot</h3>
            <p>Retrieve a voter's ballot (requires voter token)</p>
            <div class="auth-notice">
                <strong>🎟️ Voter Token Required:</strong> X-Voter-Token header
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/elections/{election}/vote</span>
            </div>
            <h3>Submit Vote</h3>
            <p>Submit a single vote for a candidate</p>
            <div class="code-block">
                <code>POST /api/elections/1/vote
X-Voter-Token: VOTER_TOKEN
Content-Type: application/json

{
  "candidate_id": "candidate-uuid"
}</code>
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <span class="path">/api/elections/{election}/votes</span>
            </div>
            <h3>Submit Batch Votes</h3>
            <p>Submit multiple votes at once</p>
            <div class="code-block">
                <code>POST /api/elections/1/votes
X-Voter-Token: VOTER_TOKEN
Content-Type: application/json

{
  "votes": [
    { "position_id": "pos-1", "candidate_id": "cand-1" },
    { "position_id": "pos-2", "candidate_id": "cand-2" }
  ]
}</code>
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/elections/{election}/results</span>
            </div>
            <h3>Get Election Results</h3>
            <p>Retrieve published results for an election (public)</p>
            <div class="auth-notice">
                <strong>🌐 Public Endpoint:</strong> No authentication required
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Analytics Endpoints</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations/{org}/analytics</span>
            </div>
            <h3>Organization Dashboard</h3>
            <p>Get comprehensive organization analytics</p>
            <div class="auth-notice">
                <strong>⚠️ Authentication Required:</strong> Organization members only
            </div>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations/{org}/analytics/trends</span>
            </div>
            <h3>Election Trends</h3>
            <p>Get trends over time across elections</p>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations/{org}/analytics/competitive</span>
            </div>
            <h3>Most Competitive Elections</h3>
            <p>Get the most competitive elections</p>
        </div>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <span class="path">/api/organizations/{org}/analytics/turnout</span>
            </div>
            <h3>High Turnout Elections</h3>
            <p>Get elections with high voter turnout</p>
        </div>
    </div>
    
    <div class="section">
        <h2>Status Codes</h2>
        
        <table>
            <tr>
                <th>Code</th>
                <th>Meaning</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><span class="status-code success">200</span></td>
                <td>OK</td>
                <td>Request successful</td>
            </tr>
            <tr>
                <td><span class="status-code success">201</span></td>
                <td>Created</td>
                <td>Resource created successfully</td>
            </tr>
            <tr>
                <td><span class="status-code error">400</span></td>
                <td>Bad Request</td>
                <td>Invalid request data</td>
            </tr>
            <tr>
                <td><span class="status-code error">401</span></td>
                <td>Unauthorized</td>
                <td>Authentication required/failed</td>
            </tr>
            <tr>
                <td><span class="status-code error">403</span></td>
                <td>Forbidden</td>
                <td>Insufficient permissions</td>
            </tr>
            <tr>
                <td><span class="status-code error">404</span></td>
                <td>Not Found</td>
                <td>Resource not found</td>
            </tr>
            <tr>
                <td><span class="status-code error">422</span></td>
                <td>Unprocessable Entity</td>
                <td>Validation failed</td>
            </tr>
            <tr>
                <td><span class="status-code error">500</span></td>
                <td>Server Error</td>
                <td>Internal server error</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Error Responses</h2>
        
        <h3>Validation Error (422)</h3>
        <div class="code-block">
            <code>{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}</code>
        </div>
        
        <h3>Unauthorized (401)</h3>
        <div class="code-block">
            <code>{
  "message": "Unauthenticated"
}</code>
        </div>
        
        <h3>Forbidden (403)</h3>
        <div class="code-block">
            <code>{
  "message": "Forbidden"
}</code>
        </div>
    </div>
    
    <div class="section">
        <h2>Rate Limiting</h2>
        <p>The API implements rate limiting to prevent abuse:</p>
        <ul>
            <li><strong>Authenticated Requests:</strong> 60 requests per minute</li>
            <li><strong>Public Endpoints:</strong> 30 requests per minute</li>
            <li><strong>Auth Endpoints:</strong> 10 requests per minute</li>
        </ul>
        <p>Rate limit information is provided in response headers:</p>
        <div class="code-block">
            <code>X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1681234567</code>
        </div>
    </div>
    
    <div class="section">
        <h2>Pagination</h2>
        <p>Collection endpoints support pagination using query parameters:</p>
        <div class="code-block">
            <code>GET /api/organizations?page=1&per_page=15</code>
        </div>
        <p>Response includes pagination metadata:</p>
        <div class="code-block">
            <code>{
  "data": [...],
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 15,
    "last_page": 7
  }
}</code>
        </div>
    </div>
    
</div>

<footer>
    <p>&copy; 2025 Voting System Platform. All rights reserved.</p>
    <p>For support or questions, please contact: support@example.com</p>
</footer>

</body>
</html>
