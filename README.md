# yii2-option

### Install via composer:   
`composer require twisted1919/yii2-options`  

### Run the migration:  
`./yii migrate --migrationPath=@vendor/twisted1919/yii2-options/migrations`  

### Add the component in your configuration file:  
```php
'components' => [  
    [...]  
    'options' => [  
        'class' => '\twisted1919\options\Options'  
    ],  
    [...]  
]
```

### Api:  
Please note that `options()` is added by `twisted1919/yii2-shortcut-functions` automatically.  
  
  
###### SET
```php
options()->set($key, $value);
```

###### GET
```php
options()->get($key, $defaultValue = null);
```

###### REMOVE
```php
options()->remove($key);
```
