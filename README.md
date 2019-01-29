# Symfony CMF Resource

[![Latest Stable Version](https://poser.pugx.org/symfony-cmf/resource/v/stable)](https://packagist.org/packages/symfony-cmf/resource)
[![Latest Unstable Version](https://poser.pugx.org/symfony-cmf/resource/v/unstable)](https://packagist.org/packages/symfony-cmf/resource)
[![License](https://poser.pugx.org/symfony-cmf/resource/license)](https://packagist.org/packages/symfony-cmf/resource)

[![Total Downloads](https://poser.pugx.org/symfony-cmf/resource/downloads)](https://packagist.org/packages/symfony-cmf/resource)
[![Monthly Downloads](https://poser.pugx.org/symfony-cmf/resource/d/monthly)](https://packagist.org/packages/symfony-cmf/resource)
[![Daily Downloads](https://poser.pugx.org/symfony-cmf/resource/d/daily)](https://packagist.org/packages/symfony-cmf/resource)

Branch | Travis | Coveralls | Scrutinizer |
------ | ------ | --------- | ----------- |
1.1   | [![Build Status][travis_stable_badge]][travis_stable_link]     | [![Coverage Status][coveralls_stable_badge]][coveralls_stable_link]     | [![Scrutinizer Status][scrutinizer_stable_badge]][scrutinizer_stable_link] |
3.0-dev | [![Build Status][travis_unstable_badge]][travis_unstable_link] | [![Coverage Status][coveralls_unstable_badge]][coveralls_unstable_link] | [![Scrutinizer Status][scrutinizer_unstable_badge]][scrutinizer_unstable_link] |


This package is part of the [Symfony Content Management Framework (CMF)](https://cmf.symfony.com/) and licensed
under the [MIT License](LICENSE).

The Resource component provides PHPCR/ODM integration with Puli.

 > **CAUTION** As Puli is not yet stable, the complete component is marked
 > internal. Backwards compatibility of upcoming 1.x versions is not
 > guaranteed.


## Requirements

* PHP 7.2 / 7.3
* Symfony 
* See also the `require` section of [composer.json](composer.json)

## Documentation

For the install guide and reference, see:

* [symfony-cmf/resource Documentation](https://symfony.com/doc/master/cmf/components/resource/index.html)

See also:

* [All Symfony CMF documentation](https://symfony.com/doc/master/cmf/index.html) - complete Symfony CMF reference
* [Symfony CMF Website](https://cmf.symfony.com/) - introduction, live demo, support and community links

## Support

For general support and questions, please use [StackOverflow](https://stackoverflow.com/questions/tagged/symfony-cmf).

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/blob/master/CONTRIBUTING.md)
guide.

Unit and/or functional tests exist for this package. See the
[Testing documentation](https://symfony.com/doc/master/cmf/components/testing.html)
for a guide to running the tests.

Thanks to
[everyone who has contributed](contributors) already.

## License

This package is available under the [MIT license](src/Resources/meta/LICENSE).

[travis_stable_badge]: https://travis-ci.org/symfony-cmf/resource.svg?branch=1.1
[travis_stable_link]: https://travis-ci.org/symfony-cmf/resource
[travis_unstable_badge]: https://travis-ci.org/symfony-cmf/resource.svg?branch=3.0-dev
[travis_unstable_link]: https://travis-ci.org/symfony-cmf/resource

[coveralls_stable_badge]: https://coveralls.io/repos/github/symfony-cmf/resource/badge.svg?branch=1.1
[coveralls_stable_link]: https://coveralls.io/github/symfony-cmf/resource?branch=1.1
[coveralls_unstable_badge]: https://coveralls.io/repos/github/symfony-cmf/resource/badge.svg?branch=3.0-dev
[coveralls_unstable_link]: https://coveralls.io/github/symfony-cmf/resource?branch=3.0-dev

[scrutinizer_stable_badge]: https://scrutinizer-ci.com/g/symfony-cmf/resource/badges/quality-score.png?b=1.1
[scrutinizer_stable_link]: https://scrutinizer-ci.com/g/symfony-cmf/resource/?branch=1.1
[scrutinizer_unstable_badge]: https://scrutinizer-ci.com/g/symfony-cmf/resource/badges/quality-score.png?b=3.0-dev
[scrutinizer_unstable_link]: https://scrutinizer-ci.com/g/symfony-cmf/resource/?branch=3.0-dev
