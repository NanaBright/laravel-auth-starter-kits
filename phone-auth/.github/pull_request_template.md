# Pull Request

## Description

Brief description of changes made in this PR.

## Type of Change

- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] 📚 Documentation update
- [ ] 🎨 Code style/formatting changes
- [ ] ♻️ Code refactoring (no functional changes)
- [ ] ⚡ Performance improvements
- [ ] 🧪 Test improvements
- [ ] 🔧 Build/configuration changes

## Related Issues

Fixes #(issue number)
Closes #(issue number)
Addresses #(issue number)

## Changes Made

### Backend Changes
- [ ] Added new SMS gateway integration
- [ ] Modified authentication logic
- [ ] Updated database schema
- [ ] Changed API endpoints
- [ ] Other: ___________

### Frontend Changes
- [ ] Updated Vue components
- [ ] Modified styling (CSS/Tailwind)
- [ ] Changed routing
- [ ] Updated forms/validation
- [ ] Other: ___________

### Configuration Changes
- [ ] Added new environment variables
- [ ] Modified config files
- [ ] Updated documentation
- [ ] Changed default settings
- [ ] Other: ___________

## Testing

### Manual Testing
- [ ] Tested on development environment
- [ ] Tested SMS sending functionality
- [ ] Tested user registration flow
- [ ] Tested user login flow
- [ ] Tested OTP verification
- [ ] Tested error handling
- [ ] Cross-browser testing (if UI changes)

### Automated Testing
- [ ] All existing tests pass
- [ ] Added new tests for new functionality
- [ ] Updated existing tests
- [ ] Test coverage maintained/improved

### Test Commands Used
```bash
# List the commands you used for testing
php artisan test
npm run test
```

## Security Considerations

- [ ] No sensitive data exposed in logs
- [ ] Input validation implemented
- [ ] Authentication/authorization checked
- [ ] Rate limiting considered
- [ ] SQL injection prevention verified
- [ ] XSS prevention verified

## Performance Considerations

- [ ] Database queries optimized
- [ ] Caching implemented where appropriate
- [ ] No memory leaks introduced
- [ ] Load testing performed (if applicable)

## Documentation

- [ ] Code is self-documenting with clear variable names
- [ ] Added/updated inline comments
- [ ] Updated README.md (if needed)
- [ ] Updated API documentation (if needed)
- [ ] Updated configuration docs (if needed)
- [ ] Added examples for new features

## Screenshots

If this includes UI changes, please add screenshots:

### Before
<!-- Screenshot of before state -->

### After
<!-- Screenshot of after state -->

## Configuration Changes Required

List any new environment variables or configuration changes:

```env
# New environment variables
NEW_SMS_PROVIDER_API_KEY=your_key
NEW_FEATURE_ENABLED=true
```

## Migration Required

- [ ] No database changes
- [ ] Database migration included
- [ ] Requires manual migration steps

If manual steps required, list them:
```bash
# Migration commands
php artisan migrate
```

## Breaking Changes

If this is a breaking change, describe:

1. What breaks
2. How to migrate existing installations
3. Compatibility notes

## Checklist

- [ ] My code follows the project's style guidelines
- [ ] I have performed a self-review of my code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] Any dependent changes have been merged and published

## Deployment Notes

Any special considerations for deployment:

- [ ] No special deployment steps required
- [ ] Requires environment variable updates
- [ ] Requires cache clearing
- [ ] Requires queue restart
- [ ] Requires web server restart

## Additional Notes

Any additional information that reviewers should know:

- Known limitations
- Future improvements planned
- Alternative approaches considered
- Links to relevant discussions or resources

---

## For Reviewers

### Review Checklist

- [ ] Code quality and style
- [ ] Functionality works as described
- [ ] Tests are adequate and pass
- [ ] Documentation is updated
- [ ] Security considerations addressed
- [ ] Performance impact acceptable
- [ ] Breaking changes properly documented

### Review Notes

<!-- Reviewers can add their notes here -->
