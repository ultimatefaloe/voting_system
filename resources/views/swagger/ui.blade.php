<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI - Voting System API</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #fafafa;
        }
        .topbar {
            background-color: #1b1c1d;
            padding: 10px;
            color: white;
        }
        .topbar h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="topbar">
    <h1>🗳️ Voting System API - Swagger UI</h1>
</div>

<div id="swagger-ui"></div>

<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui-bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui-standalone-preset.js"></script>
<script>
    const ui = SwaggerUIBundle({
        url: "{{ route('swagger.spec') }}",
        dom_id: '#swagger-ui',
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        layout: "BaseLayout",
        persistAuthorization: true,
        onComplete: function() {
            console.log('Swagger UI loaded successfully');
        }
    });
</script>
</body>
</html>
