# SMS Gateway Setup Guide

This guide covers how to integrate various SMS gateways with the Custom SMS Authentication System.

## Overview

The system supports multiple SMS delivery methods:

1. **HTTP API Gateways** - Connect to SMS providers via REST API
2. **SMPP Protocol** - Direct connection to carriers
3. **Email-to-SMS** - Send SMS via carrier email gateways  
4. **Logger** - Development/testing mode

## HTTP API Gateways

### Generic HTTP API Setup

Most SMS providers offer HTTP API endpoints. Here's the general setup:

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://api.provider.com/send
SMS_USERNAME=your_api_key
SMS_PASSWORD=your_api_secret
SMS_SENDER_ID=YourBrand
```

### Provider-Specific Configurations

#### 1. Vonage (Nexmo)

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://rest.nexmo.com/sms/json
SMS_USERNAME=your_api_key
SMS_PASSWORD=your_api_secret
SMS_SENDER_ID=YourBrand
```

**Custom integration for Vonage:**

```php
private function sendViaVonage($phoneNumber, $message)
{
    $response = Http::post('https://rest.nexmo.com/sms/json', [
        'api_key' => $this->username,
        'api_secret' => $this->password,
        'to' => $this->formatPhoneNumber($phoneNumber),
        'from' => $this->senderId,
        'text' => $message,
    ]);

    if ($response->successful()) {
        $data = $response->json();
        $message = $data['messages'][0] ?? [];
        
        if (($message['status'] ?? '1') === '0') {
            return [
                'success' => true,
                'message_id' => $message['message-id'],
                'cost' => $message['message-price'],
                'method' => 'vonage'
            ];
        }
    }

    throw new Exception('Vonage API error: ' . $response->body());
}
```

#### 2. Amazon SNS

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://sns.us-east-1.amazonaws.com/
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
```

**Custom integration for AWS SNS:**

```php
private function sendViaAwsSns($phoneNumber, $message)
{
    $client = new \Aws\Sns\SnsClient([
        'version' => 'latest',
        'region' => config('aws.region', 'us-east-1'),
        'credentials' => [
            'key' => config('aws.access_key_id'),
            'secret' => config('aws.secret_access_key'),
        ],
    ]);

    try {
        $result = $client->publish([
            'PhoneNumber' => $this->formatPhoneNumber($phoneNumber),
            'Message' => $message,
        ]);

        return [
            'success' => true,
            'message_id' => $result['MessageId'],
            'method' => 'aws_sns'
        ];
    } catch (\Exception $e) {
        throw new Exception('AWS SNS error: ' . $e->getMessage());
    }
}
```

#### 3. MessageBird

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://rest.messagebird.com/messages
SMS_USERNAME=your_access_key
SMS_SENDER_ID=YourBrand
```

**Custom integration for MessageBird:**

```php
private function sendViaMessageBird($phoneNumber, $message)
{
    $response = Http::withHeaders([
        'Authorization' => 'AccessKey ' . $this->username,
        'Content-Type' => 'application/json',
    ])->post('https://rest.messagebird.com/messages', [
        'recipients' => [$this->formatPhoneNumber($phoneNumber)],
        'originator' => $this->senderId,
        'body' => $message,
    ]);

    if ($response->successful()) {
        $data = $response->json();
        return [
            'success' => true,
            'message_id' => $data['id'],
            'method' => 'messagebird'
        ];
    }

    throw new Exception('MessageBird API error: ' . $response->body());
}
```

#### 4. Plivo

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://api.plivo.com/v1/Account/{auth_id}/Message/
SMS_USERNAME=your_auth_id
SMS_PASSWORD=your_auth_token
SMS_SENDER_ID=YourBrand
```

#### 5. Clickatell

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://platform.clickatell.com/messages/http/send
SMS_USERNAME=your_api_key
SMS_SENDER_ID=YourBrand
```

### Adding Custom HTTP Gateway

To add a new HTTP API gateway:

1. **Extend the CustomSmsService:**

```php
private function sendViaCustomProvider($phoneNumber, $message)
{
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->username,
        'Content-Type' => 'application/json',
    ])->post($this->gatewayUrl, [
        'to' => $this->formatPhoneNumber($phoneNumber),
        'message' => $message,
        'from' => $this->senderId,
        // Add provider-specific fields
    ]);

    if ($response->successful()) {
        $data = $response->json();
        
        // Parse provider-specific response
        if ($data['status'] === 'success') {
            return [
                'success' => true,
                'message_id' => $data['id'],
                'cost' => $data['cost'] ?? null,
                'method' => 'custom_provider'
            ];
        }
    }

    throw new Exception('Custom provider error: ' . $response->body());
}
```

2. **Update the sendSms method:**

```php
switch ($method) {
    case 'custom_provider':
        return $this->sendViaCustomProvider($phoneNumber, $message);
    // ... other cases
}
```

3. **Add configuration:**

```php
// config/sms.php
'custom_provider' => [
    'enabled' => env('CUSTOM_PROVIDER_ENABLED', false),
    'api_key' => env('CUSTOM_PROVIDER_API_KEY'),
    'endpoint' => env('CUSTOM_PROVIDER_ENDPOINT'),
],
```

## SMPP Protocol Setup

SMPP (Short Message Peer-to-Peer) provides direct connection to SMS carriers.

### Requirements

```bash
# Install SMPP library
composer require onlinecity/php-smpp
```

### Configuration

```env
SMS_METHOD=smpp
SMPP_HOST=smpp.carrier.com
SMPP_PORT=2775
SMPP_USERNAME=your_system_id
SMPP_PASSWORD=your_password
SMPP_SYSTEM_TYPE=CP
SMPP_SOURCE_TON=1
SMPP_SOURCE_NPI=1
SMPP_DEST_TON=1
SMPP_DEST_NPI=1
```

### Implementation

```php
private function sendViaSmpp($phoneNumber, $message)
{
    $transport = new \OnlineCity\Smpp\SocketTransport([
        config('sms.smpp.host')
    ], config('sms.smpp.port', 2775));
    
    $transport->setRecvTimeout(config('sms.smpp.timeout', 60000));
    $smpp = new \OnlineCity\Smpp\SmppClient($transport);
    
    try {
        $transport->open();
        $smpp->bindTransceiver(
            config('sms.smpp.username'),
            config('sms.smpp.password')
        );
        
        $message_id = $smpp->sendSMS(
            new \OnlineCity\Smpp\SmppAddress(
                $this->senderId,
                \OnlineCity\Smpp\SMPP::TON_ALPHANUMERIC
            ),
            new \OnlineCity\Smpp\SmppAddress(
                $phoneNumber,
                \OnlineCity\Smpp\SMPP::TON_INTL
            ),
            $message
        );
        
        $smpp->close();
        
        return [
            'success' => true,
            'message_id' => $message_id,
            'method' => 'smpp'
        ];
        
    } catch (\Exception $e) {
        if (isset($smpp)) {
            $smpp->close();
        }
        throw new Exception('SMPP error: ' . $e->getMessage());
    }
}
```

### SMPP Configuration Options

```php
// config/sms.php
'smpp' => [
    'host' => env('SMPP_HOST'),
    'port' => env('SMPP_PORT', 2775),
    'username' => env('SMPP_USERNAME'),
    'password' => env('SMPP_PASSWORD'),
    'system_type' => env('SMPP_SYSTEM_TYPE', 'CP'),
    'interface_version' => env('SMPP_INTERFACE_VERSION', 0x34),
    'source_ton' => env('SMPP_SOURCE_TON', 1),
    'source_npi' => env('SMPP_SOURCE_NPI', 1),
    'dest_ton' => env('SMPP_DEST_TON', 1),
    'dest_npi' => env('SMPP_DEST_NPI', 1),
    'timeout' => env('SMPP_TIMEOUT', 60000),
],
```

## Email-to-SMS Gateway

Many carriers support SMS delivery via email.

### Configuration

```env
SMS_METHOD=email_gateway
SMS_EMAIL_GATEWAY_ENABLED=true
SMS_DEFAULT_CARRIER=verizon

# Mail configuration required
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### Supported Carriers

```php
// config/sms.php
'carriers' => [
    // US Carriers
    'verizon' => '@vtext.com',
    'att' => '@txt.att.net',
    'tmobile' => '@tmomail.net',
    'sprint' => '@messaging.sprintpcs.com',
    'boost' => '@myboostmobile.com',
    'cricket' => '@sms.cricketwireless.net',
    'metropcs' => '@mymetropcs.com',
    'tracfone' => '@mmst5.tracfone.com',
    'straighttalk' => '@vtext.com',
    
    // International (examples)
    'rogers_ca' => '@pcs.rogers.com',
    'bell_ca' => '@txt.bell.ca',
    'telus_ca' => '@msg.telus.com',
],
```

### Dynamic Carrier Detection

```php
private function detectCarrier($phoneNumber)
{
    // You can integrate with carrier lookup services
    // or maintain a phone number prefix database
    
    $area_code = substr($phoneNumber, 1, 3);
    
    $carrier_map = [
        '310' => 'verizon',
        '320' => 'att',
        '330' => 'tmobile',
        // Add more mappings
    ];
    
    return $carrier_map[$area_code] ?? config('sms.email_gateway.default_carrier');
}
```

## Bulk SMS Providers

### Local/Regional Providers

Many countries have local SMS providers that offer competitive rates:

#### Example: Local Provider Integration

```php
private function sendViaLocalProvider($phoneNumber, $message)
{
    $response = Http::asForm()->post('https://local-sms.com/api/send', [
        'username' => $this->username,
        'password' => $this->password,
        'destination' => $phoneNumber,
        'message' => $message,
        'source' => $this->senderId,
    ]);

    if ($response->successful()) {
        $result = $response->body();
        
        if (str_contains($result, 'OK')) {
            return [
                'success' => true,
                'message_id' => 'local_' . time(),
                'method' => 'local_provider'
            ];
        }
    }

    throw new Exception('Local provider error: ' . $response->body());
}
```

## Testing SMS Gateways

### 1. Test Environment Setup

```env
# Use logger for initial testing
SMS_METHOD=logger
SMS_LOG_ALL=true
```

### 2. Gateway Testing

```php
// Create a test command
php artisan make:command TestSmsGateway

// In the command
public function handle()
{
    $smsService = app(CustomSmsService::class);
    
    try {
        $result = $smsService->sendSms('+1234567890', 'Test message');
        $this->info('SMS sent successfully: ' . json_encode($result));
    } catch (\Exception $e) {
        $this->error('SMS failed: ' . $e->getMessage());
    }
}
```

### 3. Load Testing

```php
// Test rate limits and performance
for ($i = 0; $i < 10; $i++) {
    $result = $smsService->sendSms('+1234567890', "Test message #{$i}");
    $this->info("Message {$i}: " . ($result['success'] ? 'OK' : 'FAILED'));
    sleep(1); // Respect rate limits
}
```

## Monitoring and Analytics

### 1. SMS Delivery Tracking

```php
// Create delivery status table
Schema::create('sms_delivery_logs', function (Blueprint $table) {
    $table->id();
    $table->string('phone');
    $table->string('message_id')->nullable();
    $table->string('method');
    $table->string('status'); // sent, delivered, failed
    $table->text('message');
    $table->json('response')->nullable();
    $table->decimal('cost', 8, 4)->nullable();
    $table->timestamps();
});
```

### 2. Analytics Dashboard

```php
// Track SMS metrics
class SmsAnalytics
{
    public function getDailyStats()
    {
        return SmsDeliveryLog::whereDate('created_at', today())
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status');
    }
    
    public function getMethodPerformance()
    {
        return SmsDeliveryLog::groupBy('method')
            ->selectRaw('method, count(*) as total, avg(cost) as avg_cost')
            ->get();
    }
}
```

### 3. Webhook Handling (Future)

```php
// Handle delivery receipts from providers
Route::post('/webhook/sms/{provider}', function ($provider, Request $request) {
    $handler = app("SmsWebhook{$provider}Handler");
    $handler->handle($request->all());
    
    return response('OK');
});
```

## Cost Optimization

### 1. Provider Comparison

| Provider | Cost/SMS (US) | Reliability | Features |
|----------|---------------|-------------|----------|
| Vonage | $0.0075 | High | Global, APIs |
| AWS SNS | $0.00645 | High | Integration |
| MessageBird | $0.0075 | High | Global |
| Local Provider | $0.005 | Medium | Regional |

### 2. Smart Routing

```php
private function selectOptimalProvider($phoneNumber, $message)
{
    $country = $this->getCountryFromPhone($phoneNumber);
    
    $providers = config('sms.providers');
    
    // Sort by cost for the destination country
    $optimal = collect($providers)
        ->filter(fn($p) => $p['countries'][$country] ?? false)
        ->sortBy(fn($p) => $p['cost'][$country])
        ->first();
    
    return $optimal['method'];
}
```

### 3. Fallback Strategy

```php
private function sendWithFallback($phoneNumber, $message)
{
    $providers = ['primary', 'secondary', 'tertiary'];
    
    foreach ($providers as $provider) {
        try {
            return $this->{"sendVia{$provider}"}($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::warning("Provider {$provider} failed: " . $e->getMessage());
            continue;
        }
    }
    
    throw new Exception('All SMS providers failed');
}
```

## Troubleshooting

### Common Issues

1. **Authentication Failures**
   - Check API credentials
   - Verify account status
   - Test with provider's tools

2. **Rate Limiting**
   - Implement proper delays
   - Monitor usage quotas
   - Use multiple providers

3. **Message Delivery Issues**
   - Verify phone number format
   - Check message content restrictions
   - Monitor delivery receipts

### Debug Tools

```bash
# Test SMS sending
php artisan sms:test +1234567890 "Test message"

# Check SMS logs
tail -f storage/logs/sms.log

# Monitor network requests
tcpdump -i any -w sms-traffic.pcap port 80 or port 443
```

---

For more configuration options, see our [Configuration Guide](configuration.md) or [API Documentation](api.md).
