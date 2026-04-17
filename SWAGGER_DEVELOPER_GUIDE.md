# Swagger Integration Developer Guide

**Purpose**: Guide for maintaining and extending the Swagger documentation  
**Audience**: Backend developers, API maintainers  
**Last Updated**: Phase 9 Completion  

---

## Overview

The Swagger integration consists of three main components:

1. **SwaggerController** - Generates OpenAPI specification
2. **Swagger UI View** - Interactive testing interface
3. **Documentation View** - Beautiful HTML reference

---

## Architecture

### Component Diagram

```
Request to /api/docs/*
    ↓
Routes (api.php)
    ↓
SwaggerController
    ├─ spec()     → OpenAPI JSON
    ├─ ui()       → Swagger UI HTML
    └─ docs()     → Documentation HTML
        ↓
    Views
    ├─ swagger/ui.blade.php
    └─ swagger/docs.blade.php
```

### Data Flow

```
SwaggerController::spec()
    ↓
getPaths()           [All endpoints]
getComponents()      [Schemas & responses]
getTags()            [Resource categories]
    ↓
OpenAPI 3.1.0 JSON
    ↓
Both UI & Docs display this spec
```

---

## Adding New Endpoints to Documentation

### Step 1: Create the Endpoint in routes/api.php

```php
Route::post('/elections/{election}/special-action', [ElectionController::class, 'specialAction'])
    ->middleware('auth:sanctum')
    ->name('elections.special-action');
```

### Step 2: Update SwaggerController::getPaths()

Navigate to `app/Http/Controllers/SwaggerController.php` and add the endpoint in the `getPaths()` method:

```php
private function getPaths(): array
{
    return [
        // ... existing paths ...
        
        '/elections/{election}/special-action' => [
            'post' => [
                'summary' => 'Perform Special Action',
                'description' => 'Perform a special action on an election',
                'operationId' => 'specialAction',
                'tags' => ['Elections'],
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'election',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string', 'format' => 'uuid'],
                        'description' => 'Election ID',
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'data' => ['type' => 'string'],
                                ],
                                'required' => ['data'],
                            ],
                            'example' => [
                                'data' => 'some value',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Action successful',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Election'],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '404' => ['$ref' => '#/components/responses/NotFound'],
                ],
            ],
        ],
    ];
}
```

### Step 3: Test the Documentation

```bash
# Clear cache
php artisan cache:clear

# Visit documentation
# http://localhost:8000/api/docs

# Verify endpoint appears in list
# Check Swagger UI for the new endpoint
```

---

## Adding New Response Schemas

### Step 1: Update getComponents()

In `SwaggerController::getComponents()`, add your schema to the `schemas` array:

```php
private function getComponents(): array
{
    return [
        'schemas' => [
            // ... existing schemas ...
            
            'SpecialResponse' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string', 'format' => 'uuid'],
                    'title' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'completed']],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
                'required' => ['id', 'title', 'status'],
                'example' => [
                    'id' => '550e8400-e29b-41d4-a716-446655440000',
                    'title' => 'Example Title',
                    'status' => 'completed',
                    'created_at' => '2025-01-01T12:00:00Z',
                    'updated_at' => '2025-01-01T12:00:00Z',
                ],
            ],
        ],
        // ... rest of components
    ];
}
```

### Step 2: Reference Schema in Endpoint

```php
'responses' => [
    '200' => [
        'description' => 'Success',
        'content' => [
            'application/json' => [
                'schema' => ['$ref' => '#/components/schemas/SpecialResponse'],
            ],
        ],
    ],
],
```

---

## Documentation Convention

### Path Naming

```
/resource                    GET    list
/resource                    POST   create
/resource/{id}               GET    show
/resource/{id}               PATCH  update
/resource/{id}               DELETE destroy
/resource/{id}/action        POST   custom action
```

### Schema Naming

```
User                         - Singular resource
UserCollection               - Collection response
UserRequest                  - Request body
UserResponse                 - Response body
UserListResponse             - List response
```

### Operation IDs

```
operationId: 'listUsers'           - List operation
operationId: 'createUser'          - Create operation
operationId: 'getUser'             - Show operation
operationId: 'updateUser'          - Update operation
operationId: 'deleteUser'          - Delete operation
operationId: 'customUserAction'    - Custom action
```

### Security

```php
// For authenticated endpoints
'security' => [['bearerAuth' => []]],

// For public endpoints
// (omit security array)

// For voter token endpoints
'security' => [['voterAuth' => []]],
```

---

## Modifying Existing Documentation

### Example: Adding Query Parameters

```php
'/elections' => [
    'get' => [
        'summary' => 'List Elections',
        'parameters' => [
            [
                'name' => 'page',
                'in' => 'query',
                'description' => 'Page number',
                'schema' => ['type' => 'integer', 'default' => 1],
            ],
            [
                'name' => 'per_page',
                'in' => 'query',
                'description' => 'Items per page',
                'schema' => ['type' => 'integer', 'default' => 15],
            ],
            [
                'name' => 'status',
                'in' => 'query',
                'description' => 'Filter by status',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['draft', 'published', 'closed'],
                ],
            ],
        ],
        // ... rest of endpoint
    ],
],
```

### Example: Updating Response Example

```php
'example' => [
    'id' => '550e8400-e29b-41d4-a716-446655440000',
    'name' => 'Board Election 2025',
    'status' => 'published',
    'created_at' => '2025-01-01T12:00:00Z',
    'updated_at' => '2025-01-01T12:00:00Z',
],
```

---

## Testing Documentation

### Manual Testing

```bash
# 1. Start development server
php artisan serve

# 2. Visit Swagger UI
open http://localhost:8000/api/docs

# 3. Try endpoints
- Click endpoint
- Click "Try it out"
- Fill in parameters
- Click "Execute"
- Verify response

# 4. Check JSON spec
curl http://localhost:8000/api/docs/spec | jq
```

### Automated Testing

```bash
# Validate OpenAPI spec
npx @openapitools/openapi-generator-cli validate -i spec.json

# Generate client library (test spec completeness)
npx @openapitools/openapi-generator-cli generate \
  -i http://localhost:8000/api/docs/spec \
  -g typescript-axios \
  -o ./generated-client
```

### Common Issues

| Problem | Solution |
|---------|----------|
| Endpoint doesn't appear | Clear cache: `php artisan cache:clear` |
| Response example wrong | Update `example` in endpoint definition |
| Schema undefined | Add schema to `getComponents()` |
| Token not persisting | Check localStorage in browser dev tools |
| 404 on endpoint | Verify route exists in `routes/api.php` |

---

## Best Practices

### 1. Keep Documentation in Sync

**DO**:
```php
// Update documentation when endpoint changes
// Update examples when response format changes
// Test after making changes
```

**DON'T**:
```php
// Don't forget to update documentation
// Don't use outdated examples
// Don't ship without testing
```

### 2. Use Consistent Patterns

**Good**:
```php
'parameters' => [
    [
        'name' => 'id',
        'in' => 'path',
        'required' => true,
        'schema' => ['type' => 'string', 'format' => 'uuid'],
    ],
],
```

**Bad**:
```php
'parameters' => [
    ['name' => 'id', 'in' => 'path', 'required' => true],
],
```

### 3. Provide Examples

**Good**:
```php
'example' => [
    'id' => '550e8400-e29b-41d4-a716-446655440000',
    'name' => 'John Doe',
    'email' => 'john@example.com',
],
```

**Bad**:
```php
'example' => ['id' => 'something', 'name' => 'something'],
```

### 4. Clear Descriptions

**Good**:
```php
'description' => 'Get a specific election by ID. Returns full election details including positions and candidates.',
```

**Bad**:
```php
'description' => 'Get election',
```

---

## Performance Optimization

### Caching the Spec

In production, cache the OpenAPI spec:

```php
Route::get('/docs/spec', [SwaggerController::class, 'spec'])
    ->middleware('throttle:60,1')
    ->middleware('cache.headers:public;max_age=3600');
```

This caches for 1 hour (3600 seconds).

### Lazy Loading Paths

For very large APIs, consider lazy loading:

```php
private function getPaths(): array
{
    $paths = [];
    
    if (request()->has('tag')) {
        $tag = request()->get('tag');
        return $this->getPathsByTag($tag);
    }
    
    return $this->getAllPaths();
}
```

---

## Troubleshooting

### Issue: Swagger UI won't load

**Check**:
1. Is Laravel running? `php artisan serve`
2. Is route registered? `php artisan route:list | grep docs`
3. Is view file present? `ls resources/views/swagger/`
4. Clear cache: `php artisan cache:clear`

**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Issue: Spec not updating

**Check**:
1. Did you modify SwaggerController?
2. Did you clear cache?
3. Is PHP syntax correct? `php -l app/Http/Controllers/SwaggerController.php`

**Solution**:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear

# Verify syntax
php -l app/Http/Controllers/SwaggerController.php

# Reload browser with cache clear
curl -H "Cache-Control: no-cache" http://localhost:8000/api/docs/spec
```

### Issue: Authentication not working in Swagger

**Check**:
1. Is token in correct format? `Bearer {token}` (with space)
2. Has token expired?
3. Is user still in database?

**Solution**:
1. Get fresh token: POST /api/auth/login
2. Copy full token (including "Bearer " prefix)
3. Click "Authorize" button
4. Paste in field
5. Close dialog and retry endpoint

---

## Updating for New Laravel Features

### When upgrading Laravel

1. **Test all endpoints**
   ```bash
   php artisan test
   ```

2. **Verify API responses**
   ```bash
   curl http://localhost:8000/api/docs/spec | jq
   ```

3. **Update any changed schemas**
   - Check model changes
   - Check validation changes
   - Update examples

### When adding new features

1. **Create endpoint** in controller
2. **Register route** in `routes/api.php`
3. **Create Resource** (if needed)
4. **Document in Swagger** via SwaggerController
5. **Test endpoint** via Swagger UI
6. **Update HTML docs** if needed

---

## Integration with CI/CD

### GitHub Actions Example

```yaml
- name: Validate OpenAPI Spec
  run: |
    curl http://localhost:8000/api/docs/spec > spec.json
    npx @openapitools/openapi-generator-cli validate -i spec.json

- name: Generate API Client
  run: |
    npx @openapitools/openapi-generator-cli generate \
      -i spec.json \
      -g typescript-axios \
      -o ./generated
```

### Pre-commit Hook

```bash
#!/bin/sh
# Validate SwaggerController syntax
php -l app/Http/Controllers/SwaggerController.php || exit 1

# Verify spec endpoint works
curl -f http://localhost:8000/api/docs/spec > /dev/null || exit 1
```

---

## Migration Guide: Old to New Endpoints

### When endpoint URL changes

Update both places:
1. `routes/api.php`
2. `SwaggerController::getPaths()`

### When endpoint returns different schema

Update:
1. `Resource` class
2. SwaggerController `getComponents()` schema
3. SwaggerController endpoint response definition

### When adding new required parameter

1. Add to `getPaths()` parameters array
2. Mark as `required: true`
3. Update example to show new parameter

---

## Quick Reference

### Add Endpoint
1. Create route in `routes/api.php`
2. Add to `getPaths()` in SwaggerController
3. Reference schemas from `getComponents()`

### Add Schema
1. Add to `getComponents()` schemas
2. Reference in endpoint responses using `$ref`

### Update Example
1. Update `example` field in endpoint or schema
2. Clear cache: `php artisan cache:clear`

### Test Changes
1. Visit `/api/docs`
2. Search for endpoint
3. Try endpoint
4. Verify response

---

## Support

For questions or issues:

1. **Check documentation**: `/api/docs/api`
2. **Review OpenAPI spec**: `/api/docs/spec`
3. **Try Swagger UI**: `/api/docs`
4. **See test examples**: `/tests/*`
5. **Check Phase 9 docs**: `PHASE9_COMPLETE.md`

---

**Version**: 1.0  
**Last Updated**: Phase 9 Complete  
**Status**: Production Ready ✅
