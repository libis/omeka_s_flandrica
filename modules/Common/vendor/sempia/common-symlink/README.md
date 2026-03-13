Common Symlink (Composer plugin)
================================

[Common Symlink] is a Composer plugin that creates a symlink for the [Omeka S]
module [Common], ensuring backward compatibility with older modules.

This plugin is needed exclusively for this usage. It is useless anywhere else.


Installation
------------

This plugin is installed automatically as a dependency of [Common]. It is
useless anywhere else.


Using Common as a dependency in a module
----------------------------------------

Once the [pull request #2412] will be merged, any omeka module will be able to
use Common as a dependency in a standard way, with the package `daniel-km/omeka-s-module-common`
under key `require`. Then a simple `use Common\TraitModule;` in Module.php will
be enough to include it.

Currently, many older Omeka S modules depend on Common root-level files via
`require_once` (see below, still supported).

When Common is installed via Composer to `composer-addons/modules/Common/`, this
path doesn't resolve. So this composer plugin creates a symlink `modules/Common`
pointing to `composer-addons/modules/Common`, ensuring these older modules
continue to work.

### Recommended (PSR-4 autoloading)

Modules should use standard PSR-4 autoloading instead of `require_once`, so they
must require the module Common in their own composer.json:

```sh
composer require daniel-km/omeka-s-module-common
```

Then, in Module.php:

```php
<?php declare(strict_types=1);

namespace MyModule;

use Common\TraitModule;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule
{
    use TraitModule;

    const NAMESPACE = __NAMESPACE__;
}
```

Available classes:
- `Common\AbstractModule` - Old base class for modules (deprecated)
- `Common\TraitModule` - Trait with common module functionality
- `Common\ManageModuleAndResources` - Helper for resource installation

### Legacy (deprecated)

Old modules using `require_once` will continue to work thanks to the symlink:

```php
// Still works but not recommended, with or without the subdirectory src/.
require_once dirname(__DIR__) . '/Common/src/TraitModule.php';
```


How it works
------------

The plugin subscribes to Composer `post-package-install` and `post-package-update`
events and create or delete the symlink in directory Common. When `modules/Common`
already exists as a real directory (local override), the symlink is not created.

| Event                  | Action                                           |
|------------------------|--------------------------------------------------|
| Common installed       | Create symlink `modules/Common`                  |
| Common updated         | Update symlink if target changed                 |
| Common uninstalled     | Remove symlink                                   |
| `modules/Common` exists| Skip symlink (local directory takes precedence)  |


Warning
-------

Use it at your own risk.

It's always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [plugin issues] page on GitLab.

### Symlink not created

- Ensure the `modules/` directory is writeable
- Check if `modules/Common` already exists as a directory (local override)
- Verify Common is installed: `composer show daniel-km/omeka-s-module-common`

### Permission denied

Ensure the web server user has write access to the `modules/` directory.


License
-------

This plugin is published under the [CeCILL v2.1] license, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

This software is governed by the CeCILL license under French law and abiding by
the rules of distribution of free software. You can use, modify and/ or
redistribute the software under the terms of the CeCILL license as circulated by
CEA, CNRS and INRIA at the following URL "http://www.cecill.info".

As a counterpart to the access to the source code and rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software's author, the holder of the economic rights, and the
successive licensors have only limited liability.

In this respect, the user's attention is drawn to the risks associated with
loading, using, modifying and/or developing or reproducing the software by the
user in light of its specific status of free software, that may mean that it is
complicated to manipulate, and that also therefore means that it is reserved for
developers and experienced professionals having in-depth computer knowledge.
Users are therefore encouraged to load and test the software's suitability as
regards their requirements in conditions enabling the security of their systems
and/or data to be ensured and, more generally, to use and operate it in the same
conditions as regards security.

The fact that you are presently reading this means that you have had knowledge
of the CeCILL license and that you accept its terms.


Copyright
---------

- Copyright Daniel Berthereau, 2026 (see [Daniel-KM] on GitLab)

This plugin was originally designed for [Omeka S] modules for the [digital library Manioc]
of the [Université des Antilles] (subvention Agence bibliographique de l’enseignement supérieur [Abes]).


[Common Symlink]: https://gitlab.com/sempia/composer-plugin-common-symlink
[Common]: https://gitlab.com/Daniel-KM/Omeka-S-module-Common
[Omeka S]: https://omeka.org/s
[pull request #2412]: https://github.com/omeka/omeka-s/pull/2412
[plugin issues]: https://gitlab.com/sempia/composer-plugin-common-symlink/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[digital library Manioc]: https://manioc.org
[Université des Antilles]: https://www.univ-antilles.fr
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
