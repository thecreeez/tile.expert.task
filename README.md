## tile.expert

[Demo](http://dgrew.ru:5050/)

Admin data:
```
login: admin
password: foo
```

- rebase .env to .env.local
- change DATABASE_URL

then:

```
composer i

composer u

php bin/console d:d:c

php bin/console d:s:u -f

php bin/console lexik:jwt:generate-keypair --overwrite

symfony serve
