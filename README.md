## Calculate fees for cash transactions

### A toy project in PHP

##### Note
> Uses PHP >7.0 null coalescing operator `??`.
> Change the construct of `$a = $b ?? $c;` to `$a = isset($a) ? $a : $b;`
> in lines 116 and 118 of the file Transaction.php to run on lower PHP versions.

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
