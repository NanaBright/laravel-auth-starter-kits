# SMS Gateways Integration Guide

This guide explains how to configure and use different SMS gateways with the Laravel Auth Starter Kits, specifically for phone authentication.

## 📋 Supported SMS Gateways

The Phone Auth kit supports the following SMS gateways out-of-the-box:

1. **Vonage** (formerly Nexmo) - Global SMS delivery with high deliverability
2. **Twilio** - Feature-rich SMS service with extensive global coverage
3. **AWS SNS** - Amazon Simple Notification Service with scalable pricing
4. **Custom** - Integrate your own SMS provider or API

## ⚙️ Gateway Configuration

### Vonage (formerly Nexmo)

#### Setup:

1. Create an account at [Vonage API](https://www.vonage.com/communications-apis/)
2. Obtain your API key and secret from the dashboard
3. Configure your application to use Vonage

#### Environment Variables:

```
SMS_PROVIDER=vonage
VONAGE_KEY=your_vonage_key
VONAGE_SECRET=your_vonage_secret
VONAGE_FROM="Laravel Auth"
```

#### Code Example:

```php
// Using the SMS service with Vonage
$smsService = app(App\Services\SmsServiceInterface::class);
$success = $smsService->send('+12125550123', 'Your verification code is: 123456');
```

### Twilio

#### Setup:

1. Create an account at [Twilio](https://www.twilio.com/)
2. Obtain your Account SID and Auth Token from the dashboard
3. Purchase a phone number to send SMS from
4. Configure your application to use Twilio

#### Environment Variables:

```
SMS_PROVIDER=twilio
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+12025550123  # Your Twilio phone number
```

#### Code Example:

```php
// Using the SMS service with Twilio
$smsService = app(App\Services\SmsServiceInterface::class);
$success = $smsService->send('+12125550123', 'Your verification code is: 123456');
```

### AWS SNS

#### Setup:

1. Create an AWS account if you don't have one
2. Set up an IAM user with SNS permissions
3. Generate Access Key and Secret
4. Configure your application to use AWS SNS

#### Environment Variables:

```
SMS_PROVIDER=sns
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_SNS_SENDER_ID=Laravel  # Optional sender ID (not supported in all regions)
```

#### Code Example:

```php
// Using the SMS service with AWS SNS
$smsService = app(App\Services\SmsServiceInterface::class);
$success = $smsService->send('+12125550123', 'Your verification code is: 123456');
```

## 🛠️ Creating a Custom SMS Gateway

You can integrate any SMS provider by creating a custom service that implements the `SmsServiceInterface`.

### Step 1: Create a Service Class

Create a new class that implements the interface:

```php
<?php

namespace App\Services;

class CustomSmsService implements SmsServiceInterface
{
    protected $apiKey;
    protected $baseUrl;
    
    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = $config['base_url'] ?? 'https://api.yoursmsgateway.com';
    }
    
    public function send(string $to, string $message): bool
    {
        try {
            // Implement the API call to your SMS provider
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/send', [
                'to' => $to,
                'message' => $message,
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            // Log the error
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }
}
```

### Step 2: Register Your Service

Edit `config/sms.php` to include your custom provider:

```php
'providers' => [
    // Other providers...
    
    'custom' => [
        'class' => App\Services\CustomSmsService::class,
        'api_key' => env('CUSTOM_SMS_API_KEY'),
        'base_url' => env('CUSTOM_SMS_BASE_URL'),
    ],
],
```

### Step 3: Configure Environment

Update your `.env` file:

```
SMS_PROVIDER=custom
CUSTOM_SMS_API_KEY=your_api_key
CUSTOM_SMS_BASE_URL=https://api.yoursmsgateway.com
```

## 🔄 Fallback SMS Providers

The Phone Auth kit supports automatic fallbacks between multiple SMS providers in case of delivery failures.

### Configuration

Enable and configure fallbacks in `config/sms.php`:

```php
'fallbacks' => [
    'enabled' => true,
    'providers' => ['vonage', 'twilio', 'sns'],
    'max_attempts' => 3,
],
```

### How It Works

1. The system attempts to send an SMS using the primary provider
2. If delivery fails, it automatically tries the next provider in the list
3. This continues until successful delivery or all providers are exhausted
4. Failures are logged for monitoring and analytics

## 📊 Delivery Analytics

The Phone Auth kit includes analytics for tracking SMS delivery performance across providers.

### Enabling Analytics

Configure analytics in `config/sms.php`:

```php
'analytics' => [
    'enabled' => true,
    'track_deliveries' => true,
    'track_failures' => true,
    'track_costs' => true,
    'storage' => 'database',
],
```

### Viewing Analytics

Access the analytics dashboard at `/admin/sms-analytics` or via the API at `/api/admin/stats/sms`.

### Cost Optimization

The kit can automatically optimize SMS sending costs by:

- Selecting the cheapest provider for each region
- Batch processing SMS for bulk discounts
- Timing non-urgent messages for off-peak pricing

Configure in `config/sms.php`:

```php
'cost_optimization' => [
    'enabled' => true,
    'region_routing' => true,
    'batch_processing' => true,
    'off_peak_sending' => false,
],
```

## 📱 Testing SMS Gateways

### Test Environment

For testing and development, enable test mode in `.env`:

```
SMS_TEST_MODE=true
```

In test mode, no actual SMS are sent, but the system simulates successful delivery.

### Test Phone Numbers

Configure test phone numbers in `config/sms.php`:

```php
'test_numbers' => [
    '+12025550142' => [
        'success' => true,
        'code' => '123456',
    ],
    '+12025550143' => [
        'success' => false,
        'error' => 'Delivery failure',
    ],
],
```

### Integration Tests

Run SMS gateway integration tests:

```bash
php artisan test --filter=SmsGatewayTest
```

### Manual Testing

You can manually test SMS delivery from the command line:

```bash
# Test sending an SMS
php artisan sms:test --to="+12125550123" --message="Test message"

# Test OTP flow
php artisan sms:test-otp --to="+12125550123"
```

## 🌍 International Phone Support

The Phone Auth kit supports international phone numbers with proper formatting and validation.

### Phone Number Formatting

Configure phone number formatting in `config/sms.php`:

```php
'phone' => [
    'format' => 'E164',
    'regions' => [], // Empty array for all regions
    'require_plus' => true,
],
```

### Region Restrictions

To restrict allowed regions:

```php
'phone' => [
    'format' => 'E164',
    'regions' => ['US', 'CA', 'GB'], // Only allow these regions
    'require_plus' => true,
],
```

## 🔍 Troubleshooting

### Common Issues

#### SMS Not Sending

**Possible Causes:**
- Incorrect API credentials
- Account has insufficient credits
- Phone number format is incorrect
- Service provider outage

**Solutions:**
- Verify API credentials in `.env`
- Check provider dashboard for credit status
- Ensure phone numbers use E.164 format (e.g., +12125550123)
- Check provider status page for outages

#### Invalid Phone Numbers

**Possible Causes:**
- Missing country code
- Incorrect formatting
- Number doesn't exist

**Solutions:**
- Implement client-side validation with libphonenumber-js
- Always require country code
- Use the built-in phone number validator

#### High SMS Costs

**Possible Causes:**
- Inefficient provider for your target region
- No batch processing
- Unnecessary SMS sending

**Solutions:**
- Use region-specific providers
- Implement rate limiting
- Enable cost optimization features

### Debugging Tools

The Phone Auth kit includes debugging tools for SMS delivery:

```bash
# View SMS logs
php artisan sms:logs

# Check provider status
php artisan sms:status

# Validate a phone number
php artisan sms:validate --phone="+12125550123"
```

### Logs

SMS delivery logs are stored in:

- Database: `sms_logs` table (if enabled)
- Log files: `storage/logs/sms-*.log`

## 🚀 Best Practices

### Security

- Store API credentials securely in `.env`
- Implement rate limiting for SMS sending
- Monitor for unusual sending patterns
- Rotate API keys periodically

### Performance

- Use queue jobs for SMS sending
- Implement caching for frequently sent messages
- Monitor delivery rates and latency

### Cost Management

- Use regional routing to optimize costs
- Implement fallbacks only for critical messages
- Set up alerts for unusual spending
- Consider using OTP apps instead of SMS where possible

## 📝 Provider Comparison

| Provider | Pros | Cons | Best For |
|----------|------|------|----------|
| Vonage | Global reach, good deliverability | Higher cost in some regions | Global applications |
| Twilio | Feature-rich, excellent docs | Higher cost | Feature-rich messaging |
| AWS SNS | Cost-effective, scales well | Complex setup | High-volume, cost-sensitive |
| Custom | Complete control | Development overhead | Specific regional needs |

## 🔮 Future Enhancements

- **Scheduled SMS** - Send SMS at specified times
- **Templates** - Reusable message templates
- **Channel Failover** - Fallback to email if SMS fails
- **Two-way SMS** - Support for receiving SMS replies
- **Rich Media** - MMS support for enhanced messages

---

**Need help with SMS integration?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
