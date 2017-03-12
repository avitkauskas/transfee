## Calculate fees for cash transactions

### A toy project in PHP

##### Note
> Uses PHP 7.0+ null coalescing operator `??`.<br>
> Change the `$a = $b ?? $c;` to `$a = isset($a) ? $a : $b;`<br>
> in lines 116 and 118 of the file app/Transaction.php to run on lower PHP versions.

#### Install dependencies
```
composer install
```
#### Run script
```
php transfee.php input.csv
```
#### Run tests
```
phpunit tests
```
