---
name: SMS Gateway Integration
about: Request support for a new SMS gateway
title: '[GATEWAY] Add support for [Provider Name]'
labels: enhancement, sms-gateway
assignees: ''
---

## SMS Provider Information

**Provider Name**: [e.g. Vonage, AWS SNS, Custom Provider]
**Website**: [provider website URL]
**Documentation**: [link to API documentation]

## Provider Details

**API Type**:
- [ ] HTTP REST API
- [ ] SMPP Protocol
- [ ] SOAP API
- [ ] Webhook-based
- [ ] Other: ___________

**Authentication Method**:
- [ ] API Key
- [ ] Username/Password
- [ ] OAuth 2.0
- [ ] Bearer Token
- [ ] Other: ___________

**Geographic Coverage**:
- [ ] Global
- [ ] US/Canada
- [ ] Europe
- [ ] Asia-Pacific
- [ ] Specific countries: ___________

## API Information

**Base URL**: [e.g. https://api.provider.com/v1/]

**Send SMS Endpoint**: [e.g. POST /sms/send]

**Request Format**:
```json
{
  "to": "+1234567890",
  "from": "SENDER_ID",
  "message": "Your verification code is: 123456"
}
```

**Response Format**:
```json
{
  "id": "msg_123456",
  "status": "queued",
  "cost": 0.0075
}
```

**Rate Limits**: [e.g. 100 requests/second]

## Features

What features does this provider support?

- [ ] SMS sending
- [ ] Delivery receipts
- [ ] Two-way messaging
- [ ] Short codes
- [ ] Long codes
- [ ] Alphanumeric sender ID
- [ ] Unicode support
- [ ] Message templates
- [ ] Bulk sending
- [ ] Scheduling

## Pricing

**Cost per SMS**: [e.g. $0.0075 USD]
**Free tier**: [e.g. 100 free SMS/month]
**Volume discounts**: [describe if available]

## Why This Provider?

Explain why this SMS provider should be added:

- Cost advantages
- Better delivery rates
- Regional coverage
- Specific features
- Integration requirements

## Implementation Details

If you have implementation details, please share:

- Required configuration options
- Special headers or parameters
- Error handling considerations
- Testing endpoints

## Sample Integration Code

If you have sample code or have started an integration:

```php
// Sample code here
```

## Additional Resources

- Provider documentation links
- Sample implementations
- Community discussions
- Other relevant information

## Priority

How important is this integration?

- [ ] Critical - needed for production deployment
- [ ] High - would enable new use cases
- [ ] Medium - nice alternative to have
- [ ] Low - for completeness
