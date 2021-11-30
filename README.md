# UFA Introduction

UFA: Uniform Frontend Archiecture. A PHP composer plugin.

# How to use?

 1. To add it in your `composer.json`.
 
 ```
 {
     "require": {
         "angejia/ufa": "0.3.*",
     },
     "repositories": {
         "ufa": {
             "type": "git",
             "url": "git@git.corp.angejia.com:frontend/ufa.git"
         }
     }
 }
 ```

 If this step success, you can find the `vendor/angejia/ufa/` folder under your project.

 1. To add ufa as a service in your `config/app.php`.

 ```
 return [
     'providers' => [
         ...
 
         `Angejia\Ufa\Providers\UfaServiceProvider::class,`
 
         ...
     ]
 ]
 ```

 After this step, you can use it anywhere without error, such as `ufa()->extJs()` or `ufa()->extCss()` .etc.

 Note: although it has no error but it still do nothing in your html. In other words, it doesn't include any styles or scripts when your page loading. It won't work, unless you finish next step.

 1. To add ufa views in your `config/view.php` and include `ufa styles` & `ufa scripts` in your HTML blade view.

 ```
 return [
    'paths' => [
        realpath(base_path('resources/views')),// default
        realpath(base_path('../vendor/angejia/ufa/src/views'))//ufa views folder
    ],
 ]
 ```

 Add `ufa styles` and `ufa/scripts`, for example:

 ```
 <!DOCTYPE html>
 <html>
     <head>
         @include('resources.styles')
     </head>
     <body>
         <!-- main content -->
         @include('resources.styles')
     </body>    
 </html>
 ```
 
 And untill now, the `ufa()->extJs` and `ufa()->extCss` works as you expected.

# API List

You can use all the following function as this: `ufa()->asset('image/home.jpg')`.

- __`extJs($data = [])`__
```
<?php
ufa()->extJs([
    'home.js',
    '../lib/jquery.js'
])
```

- __`extCss($data = [])`__
```
<?php
ufa()->extJs([
    'home.css',
    '../lib/jquery-ui.css'
])
```

- __`asset($data = [])`__
```
<div>
    <img src="{{ufa()->asset('image/logo.png')}}"/>
</div>
```

- __`addParam($value = [], $key = '')`__
```
<?php
ufa()->addParam(['count' => $count, 'pagename' => $page_name]);
```
- __`getParam($key)`__

Get single parameter.

- __`getParams()`__

Get all parameters.