<?php
declare(strict_types=1);

function send_license_email(string $to, string $moduleName, string $licenseKey, string $domain, array $config): bool
{
    $subject = sprintf('Your %s License', $moduleName);
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $config['email']['from'],
    ];

    $body = sprintf(
        '<h2>Your License Details</h2>
        <p><strong>Module:</strong> %s</p>
        <p><strong>License Key:</strong> %s</p>
        <p><strong>Domain:</strong> %s</p>
        <p>Keep this information secure for future verification.</p>',
        htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($licenseKey, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($domain, ENT_QUOTES, 'UTF-8')
    );

    return mail($to, $subject, $body, implode("\r\n", $headers));
}
