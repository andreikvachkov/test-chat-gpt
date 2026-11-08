<?php

// No host PHP installation is required: invoked by scripts/docker.sh in a PHP container.
$path = '.env.docker';
if (is_file($path)) {
    echo "Using existing .env.docker; key and password preserved.\n";
    exit(0);
}
$template = file_get_contents('.env.docker.example');
$values = [
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'DB_PASSWORD' => bin2hex(random_bytes(32)),
    'LOCAL_UID' => getenv('KAMALI_LOCAL_UID') ?: '1000',
    'LOCAL_GID' => getenv('KAMALI_LOCAL_GID') ?: '1000',
];
foreach ($values as $name => $value) {
    $template = preg_replace('/^'.preg_quote($name, '/').'=.*$/m', $name.'='.$value, $template);
}
$file = fopen($path, 'x');
if ($file === false) {
    throw new RuntimeException('Cannot create .env.docker.');
}
chmod($path, 0600);
fwrite($file, $template);
fclose($file);
echo "Created .env.docker with a random application key and database password.\n";
