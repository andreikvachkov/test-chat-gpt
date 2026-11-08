<?php

foreach (['/up', '/'] as $path) {
    $body = file_get_contents('http://web:8080'.$path);
    if ($body === false || ! str_contains($http_response_header[0] ?? '', '200')) {
        throw new RuntimeException('Application is not ready at '.$path);
    }
    if ($path === '/' && ! str_contains($body, 'data-page=')) {
        throw new RuntimeException('The storefront did not render an Inertia page.');
    }
}
echo "Storefront and database are ready.\n";
