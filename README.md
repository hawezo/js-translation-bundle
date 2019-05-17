# JsTranslation Bundle

![Packagist Version](https://img.shields.io/packagist/v/hawezo/js-translation-bundle.svg?style=flat-square)
![npm](https://img.shields.io/npm/v/symfony-js-translator.svg?style=flat-square)
![Packagist](https://img.shields.io/packagist/dm/hawezo/js-translation-bundle.svg?style=flat-square)
![npm](https://img.shields.io/npm/dw/symfony-js-translator.svg?style=flat-square) 

**Symfony translation bundle for Javascript**

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require hawezo/js-translation-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Downloading the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require hawezo/js-translation-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enabling the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
    // ...
    
    Hawezo\JsTranslationBundle\JsTranslationBundle::class => ['all' => true],
];
```

### Step 3: Configuring the Bundle

Next, you will need to register the routes for the controller if you want to retrieve your translations via AJAX. 

```yaml
# config/routes/js_translation.yaml

js_translation_api:
    resource: '@JsTranslationBundle/Resources/config/routing.yml'
```

You can also edit the default configuration.

```yaml
# config/packages/js_translation.yaml

js_translation:

    # If you use the `translation:export-js` command, the translation file will
    # be exported to this file
    translation_extract_path: 'assets/js/_messages.js'

    # You can chose which domains to export in the translation file.
    # Let empty to export them all.
    export_domains: []

    # Same as above, with locales.
    export_locales: []
    
    # If true, the translation messages will automatically be exported on controller view.
    # You should only use it in development.
    auto_export: false
```

Usage
=====

Method 1: Passing the translation in the templating
---------------------------------------------------

The preferred way of using this bundle is to pass the translation in your templating. It is as simple as using one `js_translation_meta()` in Twig. 
This will print a `meta` tag containing all the languages and domains your application supports.

### Example

```twig
<!DOCTYPE html>
<html lang="{{ app.request.locale }}">
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Welcome!{% endblock %}</title>
        {{ js_translation_meta() }}
        {% block stylesheets %}{% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
        {% block javascripts %}
            {{ encore_entry_script_tags('app') }}
        {% endblock %}
    </body>
</html>
```

You can also specify domains to be translated: `js_translation_meta({ 'domains': [ 'messages' ] })`

Method 2: AJAX
--------------

Another way of loading the translations is in AJAX. 
I do not recommand it since you will have to work you way out with async/promises, but it's available.

Just pass the translation API URL to your Javascript in whatever way you want, and see how you can load it in the Javascript section bellow.

You can access the URL with the following in your templating: `path('js_translation_api')`.

Method 3: Extracting the translation in your assets folder
----------------------------------------------------------

Now, I do not consider this a good practice, but it's not that bad either. 
This bundle provides a command which will export a Javascript translation file in the folder of your choice, by default `%kernel.project_dir%/assets/js`. 

```console
$ php bin/console js-translation:extract
```

You will now need to include the created file in your Javascript. 
If you use this method, you can run this command once before building your assets, and your translation will be production-ready.


Working with Javascript
-----------------------

Now that your translation is ready to use, you will need the `js-translation` NPM package. 

```console
# Yarn
$ yarn add symfony-js-translator

# NPM
$ npm install symfony-js-translator
```

### Preparing your HTML

Before anything, you need to put a `lang` attribute to your `html` tag.

```twig
<html lang="en">
{# better use <html lang="{{ app.request.locale }}"> #}
```

### Loading the catalogue with the meta tag

If you decided to use the `meta` importation method, just include the package in your Javascript and you're done. 

```javascript
// assets/js/app.js

import i18n from 'symfony-js-translator';
```

### Loading the catalogue with the extraction strategy

If you decided to export your translations in your `assets` folder, you must include the translator differently.

```javascript
// assets/js/app.js

import { Translator, LoadTypes } from 'symfony-js-translator';
import translations from './messages.js'; // Or whatever file you exported your messages to

let i18n = new Translator({
    catalogue: translations,
    loadType: LoadTypes.LOAD_FROM_CATALOGUE
});
```

### Loading the catalogue with AJAX

You might want to load your translations via AJAX. In this case, you will need to query to the bundle's translation API, and then load the same way as the extraction strategy.

```javascript
// assets/js/app.js

import { Translator, LoadTypes } from 'symfony-js-translator';

let i18n,
    url = ...; // get the URL from DOM or whatever

$.get(url)
 .then((result) => {
    i18n = new Translator({
        catalogue: translations,
        loadType: LoadTypes.LOAD_FROM_CATALOGUE
    });
 });
```

Remember though that your translations may take time to load and that your Translator object will not be ready right after your `$.get`.

### Using the Translator

The `trans` method of the `Translator` object behave the same as the Symfony translation component one. Credits to @willdurand for his work.

```javascript
// assets/js/app.js

// Catalogue contains { 'en': { 'security': { 'login.label': 'Login' }} }
import i18n from 'symfony-js-translator';

// trans(id, domain, locale)
i18n.trans('login.label', 'security'); // outputs "Login"
```

### Using the settings

There are multiple options that you can modify to fit your needs. You will need to instanciate the Translator by calling the constructor by yourself, and pass an object with the following options:

| Setting | Description | Default value |
| ------- | ----------- | ------------- |
| fallbackLocale | A fallback locale | en |
| defaultDomain | The default domain when you don't provide one | messages |
| pluralSeparator | The separator of pluralization (you shouldn't touch that) | \| |
| removeMeta | Removes the translation meta after reading it | true |
| catalogue | Default catalogue | |
| loadType | Loading type (see LoadTypes export) | LoadTypes.LOAD_FROM_META |
| onUntranslatedMessageCallback | A callback called when a translation could not be found | |

#### Example

```javascript
import { Translator, LoadTypes } from 'symfony-js-translator';

let i18n = new Translator({
    loadType: LoadTypes.LOAD_FROM_META,
    fallbackLocale: 'fr',
    removeMeta: true,
    onUntranslatedMessageCallback: (id, domain, locale) => {
        console.log(`[${locale}] Message with ID '${id}' could not be found for domain '${domain}'.`);
    }
});
```

TODO
====

- [ ] Tests - I know. I should be developping tests at the same time as I was developping the bundle. I was in a hurry.
- [ ] Upload the Symfony recipe