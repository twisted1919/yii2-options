# yii2-option

### Install via composer:   
`composer require twisted1919/yii2-options`  

### Run the migration:  
`./yiic.php migrate --migrationPath=@app/vendor/twisted1919/yii2-options/migrations`  

### Add the component in your configuration file:  
```php
'components' => [  
    [...]  
    'options' => [  
        'class' => '\twisted191\options\Options'  
    ],  
    [...]  
]
```

### Api:  

###### SET
```php
app()->options->set($key, $value);
```

###### GET
```php
app()->options->get($key, defaultValue = null);
```

###### REMOVE
```php
app()->options->remove($key);
```
