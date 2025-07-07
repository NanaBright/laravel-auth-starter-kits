# Contributing to Custom SMS Authentication System

Thank you for your interest in contributing to the Custom SMS Authentication System! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/yourusername/custom-sms-auth.git
   cd custom-sms-auth
   ```
3. **Set up your development environment** (see [Installation Guide](docs/installation.md))
4. **Create a new branch** for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

### Types of Contributions

We welcome various types of contributions:

- 🐛 **Bug fixes**
- ✨ **New features**
- 📚 **Documentation improvements**
- 🧪 **Test coverage**
- 🔧 **Performance improvements**
- 🎨 **UI/UX enhancements**
- 🌐 **Translations**

## 📋 Development Guidelines

### Code Style

- **PHP**: Follow PSR-12 coding standards
- **JavaScript/Vue**: Use ESLint configuration
- **CSS**: Follow BEM methodology with Tailwind CSS

### PHP Standards
```php
<?php

namespace App\Services;

class ExampleService
{
    private $property;

    public function __construct()
    {
        // Constructor logic
    }

    public function methodName($parameter): string
    {
        // Method logic
        return 'result';
    }
}
```

### Vue.js Standards
```vue
<template>
  <div class="component-container">
    <!-- Template content -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Component logic
const data = ref('')

onMounted(() => {
  // Initialization
})
</script>
```

### Commit Message Format

Use conventional commits:
```
type(scope): description

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

Examples:
```
feat(auth): add SMS rate limiting
fix(sms): resolve SMPP connection timeout
docs(readme): update installation instructions
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=AuthControllerTest
```

### Writing Tests

Create tests for new features:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_feature_works()
    {
        // Test logic
        $this->assertTrue(true);
    }
}
```

## 📝 Documentation

### Documentation Standards

- Use clear, concise language
- Include code examples
- Add screenshots for UI changes
- Update relevant documentation files

### Files to Update

When making changes, consider updating:
- `README.md` - Main project documentation
- `docs/` - Detailed documentation
- Inline code comments
- API documentation

## 🔍 Pull Request Process

### Before Submitting

1. **Run tests**: Ensure all tests pass
2. **Check code style**: Run linting tools
3. **Update documentation**: Keep docs current
4. **Test manually**: Verify your changes work

### PR Checklist

- [ ] Tests added/updated and passing
- [ ] Documentation updated
- [ ] Code follows project conventions
- [ ] No breaking changes (or clearly documented)
- [ ] Related issues referenced

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Performance improvement

## Testing
- [ ] Unit tests pass
- [ ] Manual testing completed
- [ ] Cross-browser testing (if UI)

## Screenshots (if applicable)
[Add screenshots here]

## Related Issues
Fixes #123
```

## 🐛 Bug Reports

### Before Reporting

1. **Search existing issues** - Check if already reported
2. **Update to latest version** - Bug might be fixed
3. **Minimal reproduction** - Create simple test case

### Bug Report Template

```markdown
**Bug Description**
Clear description of the bug

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. See error

**Expected Behavior**
What should happen

**Screenshots**
If applicable

**Environment**
- OS: [e.g. macOS 12.0]
- PHP: [e.g. 8.1]
- Laravel: [e.g. 11.0]
- Browser: [e.g. Chrome 100]
```

## 💡 Feature Requests

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
Description of the problem

**Describe the solution you'd like**
Clear description of desired feature

**Describe alternatives you've considered**
Other solutions considered

**Additional context**
Any other context or screenshots
```

## 🏷️ Areas for Contribution

### High Priority
- [ ] Additional SMS gateway integrations
- [ ] Enhanced security features
- [ ] Performance optimizations
- [ ] Test coverage improvements

### Medium Priority
- [ ] UI/UX improvements
- [ ] Documentation enhancements
- [ ] Code quality improvements
- [ ] Accessibility features

### Low Priority
- [ ] Translations
- [ ] Additional examples
- [ ] Developer tools
- [ ] Analytics features

## 📚 SMS Gateway Integrations

### Adding New Gateways

To add a new SMS gateway:

1. **Extend CustomSmsService**:
   ```php
   private function sendViaNewGateway($phoneNumber, $message)
   {
       // Implementation
       return [
           'success' => true,
           'message_id' => 'id',
           'method' => 'new_gateway'
       ];
   }
   ```

2. **Add configuration** in `config/sms.php`
3. **Create documentation** in `docs/sms-gateways.md`
4. **Add tests** for the new integration
5. **Update README** with new option

### Popular Gateways to Add
- Vonage (Nexmo)
- Amazon SNS
- MessageBird
- Plivo
- Clickatell
- Bulk SMS providers

## 🎯 Code Review Guidelines

### For Reviewers

- **Be constructive** - Suggest improvements
- **Be specific** - Point to exact lines/issues
- **Consider security** - Look for vulnerabilities
- **Check performance** - Identify bottlenecks
- **Verify tests** - Ensure adequate coverage

### Review Checklist

- [ ] Code follows project standards
- [ ] Security considerations addressed
- [ ] Performance implications considered
- [ ] Documentation updated
- [ ] Tests adequate and passing

## 🏆 Recognition

Contributors will be:
- Listed in `CONTRIBUTORS.md`
- Mentioned in release notes
- Added to GitHub contributors
- Credited in documentation

## 📞 Getting Help

- **Issues**: [GitHub Issues](https://github.com/yourusername/custom-sms-auth/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/custom-sms-auth/discussions)
- **Documentation**: Check `docs/` folder

## 📜 Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, education, socio-economic status, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Expected Behavior

- Use welcoming and inclusive language
- Be respectful of differing viewpoints
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

### Unacceptable Behavior

- Trolling, insulting/derogatory comments
- Public or private harassment
- Publishing others' private information
- Other conduct inappropriate in a professional setting

## 🙏 Thank You!

Your contributions make this project better for everyone. We appreciate your time and effort in helping improve the Custom SMS Authentication System!

---

For questions about contributing, please open a discussion or issue on GitHub.
