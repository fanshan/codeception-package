# Objective PHP Codeception package

This is a Codeception integration for Objective PHP framework.

With this package, you can write functional tests.

## Installation

Use composer : `composer.phar require objective-php/codeception-package`

## Configuration

Your `functional.suite.yml` configuration file should contain:

```yaml
class_name: FunctionalTester
modules:
    enabled:
        - ObjectivePHP:
            application_class: Namespace\Where\Is\Your\Project\Application
        - \Helper\Functional
```

* `application_class`: contain your Application class namespace
