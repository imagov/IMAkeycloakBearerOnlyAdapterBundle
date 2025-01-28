<?php

// Get the base directory of the project (where the script is executed)
$projectRoot = getcwd();

echo $projectRoot;

// Path to the YAML configuration file in the project directory
$yamlFile = $projectRoot . '/config/packages/ima_keycloak_bearer_only_adapter.yaml';

// Ensure that the directory exists, create it if not
$directory = dirname($yamlFile);
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);  // Create directory with full permissions
    echo "Created directory: {$directory}\n";
}

// Create the configuration YAML if it doesn't exist
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

// Path to the .env file in the project directory
$envFile = $projectRoot . '/.env';

// Check if the .env file exists, create it if not
if (!file_exists($envFile)) {
    file_put_contents($envFile, "###> ima/keycloak-bearer-only-adapter-bundle ###\n", FILE_APPEND);
    echo ".env file created.\n";
}

// Read the current .env content
$envContent = file_get_contents($envFile);

// Define the environment variables to be added
$envVariables = [
    'OAUTH_KEYCLOAK_ISSUER=keycloak:8080',
    'OAUTH_KEYCLOAK_REALM=my_realm',
    'OAUTH_KEYCLOAK_CLIENT_ID=my_bearer_client',
    'OAUTH_KEYCLOAK_CLIENT_SECRET=my_bearer_client_secret',
];

// Add variables to .env if they don't exist
foreach ($envVariables as $variable) {
    if (strpos($envContent, $variable) === false) {
        file_put_contents($envFile, "\n" . $variable, FILE_APPEND);
        echo "Added variable to .env: {$variable}\n";
    }
}

echo "Keycloak configuration and .env variables setup completed.\n";
