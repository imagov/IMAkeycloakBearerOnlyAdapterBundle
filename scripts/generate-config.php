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

// Read the current .env content
$envContent = file_exists($envFile) ? file_get_contents($envFile) : '';


// Define the environment variables to be added
$envVariables = [
    "###> ima/keycloak-bearer-only-adapter-bundle ###",
    'OAUTH_KEYCLOAK_ISSUER=keycloak:8080',
    'OAUTH_KEYCLOAK_REALM=my_realm',
    'OAUTH_KEYCLOAK_CLIENT_ID=my_bearer_client',
    'OAUTH_KEYCLOAK_CLIENT_SECRET=my_bearer_client_secret',
];

// Add variables to .env if they don't exist
foreach ($envVariables as $variable) {
    if (strpos($envContent, $variable) === false) {
        $envContent .= "\n" . $variable;
        echo "Added variable to .env: {$variable}\n";
    }
}

// Add the closing comment to the .env file
$envFooter = "\n###< ima/keycloak-bearer-only-adapter-bundle ###";
if (strpos($envContent, $envFooter) === false) {
    $envContent .= $envFooter; // Append the comment at the end of the file
    echo "Added closing comment to .env.\n";
}

// Save the updated content back to the .env file
file_put_contents($envFile, $envContent);

echo "Keycloak configuration and .env variables setup completed.\n";
