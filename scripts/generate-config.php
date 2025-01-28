<?php

// Path to the bundle configuration file
$yamlFile = __DIR__ . '/../config/packages/ima_keycloak_bearer_only_adapter.yaml';

// Create the YAML configuration if it doesn't exist
if (!file_exists($yamlFile)) {
    $yamlContent = <<<YAML
ima_keycloak_bearer_only_adapter:
    issuer: '%env(OAUTH_KEYCLOAK_ISSUER)%'   # URL of the Keycloak server
    realm: '%env(OAUTH_KEYCLOAK_REALM)%'     # Name of the realm in Keycloak
    client_id: '%env(OAUTH_KEYCLOAK_CLIENT_ID)%'  # Client ID
    client_secret: '%env(OAUTH_KEYCLOAK_CLIENT_SECRET)%'  # Client secret
    #ssl_verification: False # Enable or disable SSL verification as needed
YAML;

    file_put_contents($yamlFile, $yamlContent);
    echo "Created configuration file: {$yamlFile}\n";
}

// Path to the .env file
$envFile = __DIR__ . '/../.env';

// Check and update the .env file
if (!file_exists($envFile)) {
    // Create the .env file if it doesn't exist
    file_put_contents($envFile, "###> ima/keycloak-bearer-only-adapter-bundle ###\n", FILE_APPEND);
}

$envContent = file_get_contents($envFile);
$envVariables = [
    'OAUTH_KEYCLOAK_ISSUER=keycloak:8080',
    'OAUTH_KEYCLOAK_REALM=my_realm',
    'OAUTH_KEYCLOAK_CLIENT_ID=my_bearer_client',
    'OAUTH_KEYCLOAK_CLIENT_SECRET=my_bearer_client_secret',
];

// Add variables if they don't exist
foreach ($envVariables as $variable) {
    if (strpos($envContent, $variable) === false) {
        file_put_contents($envFile, "\n" . $variable, FILE_APPEND);
        echo "Added variable to .env: {$variable}\n";
    }
}

echo "Configuration and .env file setup completed.\n";
