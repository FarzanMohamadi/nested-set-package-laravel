# Nested Set Package

A Laravel package implementing the Nested Set Model for hierarchical data structures. This package provides a trait to manage tree structures efficiently using the nested set model.

## Installation

1. **Download the Package**: Download or clone the package from the repository.
2. **Composer Installation**:
   Add the following to your `composer.json` file under the `repositories` key:
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "../path/to/nested-set-package"
       }
   ]
   ```
   Then, require the package using Composer:
   ```bash
   composer require vendor/package
   ```

3. **Service Provider**: If you're using Laravel versions before 5.5, add the service provider to your `config/app.php` file:
   ```php
   'providers' => [
       Vendor\Package\NestedSetServiceProvider::class,
   ],
   ```

## Usage

To use the `Hierarchy` trait in your models, include it in your model class:

```php
use Vendor\Package\Traits\Hierarchy;

class Category extends Model
{
    use Hierarchy;
}
```

### Methods

- `children()`: Fetches child nodes.
- `parent()`: Fetches the parent node.
- `getHierarchyNodes()`: Returns the current node.
- `createChildOf($targetObj)`: Creates a new child node under a specified parent.
- `shiftParentRgt()`: Updates the parent's `rgt` value.
- `createParent()`: Creates a parent node for the current instance.
- `buildTree()`: Builds the hierarchical tree from the database records.

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue on GitHub.

## License

This package is open-source software licensed under the MIT License. See the LICENSE file for more information.

## Author

Farzan Mohamadi (farzan.mohamadii@gmail.com)
