#YAmoCRM
=======

AmoCRM extension for Yii (amocrm.ru)

**YAmoCRM** это расширение для **Yii PHP framework** для обращения к API сайта [AmoCRM](https://www.amocrm.ru/add-ons/api.php).
## Требования:

Yii Framework 1.1.0 или новее

## Установка:

- Скопировать папку `YAmoCRM` в `protected/extensions`
- В файле YAmoCRM.php изменить данные для авторизации :

```php
  private $subdomain = 'subdomain'; #Субдомен
	private $user=array(
		'USER_LOGIN'=>'example@example.ru', #Логин пользователя
		'USER_HASH'=>'c123ae456cd7891246bffb1e654abb9d' #Хэш для доступа к API (смотрите в профиле пользователя)
	);
```

- Добавить в секцию `components` конфигурационного файла:

```php
    'YAmoCRM' => array(
        'class' => 'application.extensions.YAmoCRM.YAmoCRM'
    ),
```
