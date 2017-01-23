Yii 2 Advanced API Project Template
===================================

Another Yii2 advanced template that add API application folder in the user.

This template uses http://asset-packagist.org as asset dependency manager. Therefore, we won't need to install `fxp/composer-asset-plugin`. Why? Because fxp plugin is so slow.

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
api
    config/              contains api configurations
    controllers/         contains Web controller classes
    models/              contains api-specific model classes
    modules/             contains modules that usually are used as API versioning 
        v1/              contains specific API version module
    runtime/             contains files generated during runtime
    tests/               contains tests for api application
    web/                 contains the entry script and Web resources
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
