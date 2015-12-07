Sapphire
==============================

[![Total Downloads](https://poser.pugx.org/lornewang/sapphire/downloads)](https://packagist.org/packages/lornewang/sapphire)
[![Latest Stable Version](https://poser.pugx.org/lornewang/sapphire/v/stable)](https://packagist.org/packages/lornewang/sapphire)
[![Latest Unstable Version](https://poser.pugx.org/lornewang/sapphire/v/unstable)](https://packagist.org/packages/lornewang/sapphire)
[![License](https://poser.pugx.org/lornewang/sapphire/license)](https://packagist.org/packages/lornewang/sapphire)

Installation
------------------
### Via Zip Package
Download the latest version from Github and unzip to your project directory
```bash
$ wget https://github.com/lornewang/sapphire/archive/master.zip
```

### Via Composer Create-Project
You may also install by issuing the Composer command in your terminal
```bash
$ composer lornewang/sapphire --prefer-dist
```

Usage
-----

```php
<?php
use Sapphire\Utilities\Text;

// string to be truncate
print Text::truncate('Hello World', 0, 5);
```

License
-------

Sapphire is licensed under the MIT License - see the `LICENSE` file for details