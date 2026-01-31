<?php

namespace Swift\Core;

class Analyzer {
    private $signatures = [
        'SQL_INJECTION' => [
            'pattern' => '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|EXEC|DESCRIBE|SHOW)\b.*(FROM|JOIN|INTO|SET|TABLE|DATABASE|INFORMATION_SCHEMA)|--\s+|--$|\s#|\/\*|\*\/|\bOR\b\s+[\d\'"]+\s*=\s*[\d\'"]+|\bAND\b\s+[\d\'"]+\s*=\s*[\d\'"]+|BENCHMARK\s*\([^)]*\)|SLEEP\s*\([^)]*\))/i',
            'score' => 80
        ],
        'XSS' => [
            'pattern' => '/(<script|javascript:|onload=|onerror=|onclick=|onmouseover=|"><script|%3Cscript)/i',
            'score' => 70
        ],
        'LFI' => [
            'pattern' => '/(\.\.\/|\.\.\\\\|\/etc\/passwd|\/etc\/shadow|\/etc\/group|C:\\\\Windows\\\\)/i',
            'score' => 75
        ],
        'COMMAND_INJECTION' => [
            'pattern' => '/([;&|`$]\s*\b(cat|ls|whoami|id|nc|netcat|python|php|perl|ruby|sh|bash|curl|wget|hostname|uname|openssl)\b|\$\(|\$\{)/i',
            'score' => 85
        ]
    ];

    public function analyze($telemetry) {
        $riskScore = 0;
        $tags = [];
        
        
        $uri = urldecode($telemetry['uri']);
        $payload = urldecode($telemetry['payload']);
        $mainData = $uri . ' ' . $payload;
        
        
        $headers = json_decode($telemetry['headers'], true) ?: [];
        $headerBlob = $telemetry['headers'];

        foreach ($this->signatures as $name => $sig) {
            if (preg_match($sig['pattern'], $mainData)) {
                $riskScore += $sig['score'];
                $tags[] = $name;
            } 
            else {
                if (preg_match($sig['pattern'], $headerBlob)) {
                    $matchInSensitiveHeader = false;
                    foreach (['User-Agent', 'X-Forwarded-For', 'Host', 'Contact'] as $h) {
                        if (isset($headers[$h]) && preg_match($sig['pattern'], $headers[$h])) {
                            $matchInSensitiveHeader = true;
                            break;
                        }
                    }

                    if ($matchInSensitiveHeader) {
                        $riskScore += 45;
                        $tags[] = $name . '_IN_HEADERS';
                    } else {
                        if ($riskScore < 20) {
                            $tags[] = 'HISTORICAL_TRACE_' . $name;
                        }
                    }
                }
            }
        }


        if (strlen($telemetry['headers']) < 20) {
            $riskScore += 20;
            $tags[] = 'SUSPICIOUS_HEADERS';
        }

        $riskScore = min(100, $riskScore);

        $classification = 'normal';
        if ($riskScore >= 80) $classification = 'malicious';
        elseif ($riskScore >= 40) $classification = 'suspicious';

        return [
            'risk_score' => $riskScore,
            'classification' => $classification,
            'detection_tags' => implode(',', $tags)
        ];
    }
}
